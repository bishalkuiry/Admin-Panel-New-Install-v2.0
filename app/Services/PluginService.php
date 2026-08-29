<?php

namespace App\Services;

use App\Models\Plugin;
use App\Models\PluginHook;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class PluginService
{
    protected HookService $hookService;
    protected PluginSecurityScanner $securityScanner;
    protected PluginBackupService $backupService;

    public function __construct(
        HookService $hookService,
        PluginSecurityScanner $securityScanner,
        PluginBackupService $backupService
    ) {
        $this->hookService = $hookService;
        $this->securityScanner = $securityScanner;
        $this->backupService = $backupService;
        $this->registerAutoloader();
    }

    /**
     * Register PSR-4 style autoloader for dynamic plugins in plugins/ directory
     */
    public function registerAutoloader(): void
    {
        static $registered = false;
        if ($registered) return;
        $registered = true;

        spl_autoload_register(function ($class) {
            if (str_starts_with($class, 'Plugins\\')) {
                $relative = substr($class, 8);
                $parts = explode('\\', $relative);
                if (empty($parts[0])) return;

                $pluginFolder = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $parts[0]));

                if (count($parts) === 2 && $parts[1] === 'PluginServiceProvider') {
                    $file = base_path("plugins/{$pluginFolder}/PluginServiceProvider.php");
                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }

                $subPath = implode('/', array_slice($parts, 1));
                $file = base_path("plugins/{$pluginFolder}/src/{$subPath}.php");
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }

                $fileAlt = base_path("plugins/{$pluginFolder}/{$subPath}.php");
                if (file_exists($fileAlt)) {
                    require_once $fileAlt;
                    return;
                }
            }
        });
    }

    /**
     * Get plugins directory path
     */
    public function getPluginsPath(): string
    {
        return base_path('plugins');
    }

    /**
     * Ensure plugins directory exists
     */
    public function ensurePluginsDirectory(): void
    {
        $path = $this->getPluginsPath();
        
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    /**
     * Discover all plugins in the plugins directory
     */
    public function discoverPlugins(): array
    {
        $this->ensurePluginsDirectory();
        
        $pluginsPath = $this->getPluginsPath();
        $directories = File::directories($pluginsPath);
        $discovered = [];

        foreach ($directories as $directory) {
            $manifestPath = $directory . '/plugin.json';
            
            if (File::exists($manifestPath)) {
                try {
                    $manifest = json_decode(File::get($manifestPath), true);
                    
                    if ($manifest && isset($manifest['name'])) {
                        $normalizedDir = str_replace('\\', '/', $directory);
                        $normalizedBase = str_replace('\\', '/', base_path());
                        $relativePath = ltrim(str_replace($normalizedBase, '', $normalizedDir), '/');

                        $discovered[] = [
                            'path' => $relativePath,
                            'manifest' => $manifest,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to parse plugin manifest: {$manifestPath}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $discovered;
    }

    /**
     * Sync discovered plugins with database
     */
    public function syncPlugins(): array
    {
        $discovered = $this->discoverPlugins();
        $synced = [];

        foreach ($discovered as $pluginData) {
            $manifest = $pluginData['manifest'];
            
            $plugin = Plugin::updateOrCreate(
                ['name' => $manifest['name']],
                [
                    'display_name' => $manifest['display_name'] ?? $manifest['name'],
                    'description' => $manifest['description'] ?? null,
                    'version' => $manifest['version'] ?? '1.0.0',
                    'author' => $manifest['author'] ?? null,
                    'author_url' => $manifest['author_url'] ?? null,
                    'plugin_url' => $manifest['plugin_url'] ?? null,
                    // Use the actual discovered path, not the manifest name
                    'path' => $pluginData['path'],
                    'namespace' => isset($manifest['service_provider']) ? 
                        substr($manifest['service_provider'], 0, strrpos($manifest['service_provider'], '\\')) : null,
                    'requires_php' => $manifest['requires']['php'] ?? null,
                    'requires_laravel' => $manifest['requires']['laravel'] ?? null,
                    'requires_quixko' => $manifest['requires']['allincart'] ?? $manifest['requires']['quixko'] ?? null,
                    'metadata' => $manifest,
                ]
            );

            $synced[] = $plugin;
        }

        return $synced;
    }

    /**
     * Upload and extract plugin ZIP file
     */
    public function uploadPlugin($file, ?string $licenseKey = null, bool $overwrite = false): array
    {
        $this->ensurePluginsDirectory();

        // Validate file
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        if ($file->getClientOriginalExtension() !== 'zip') {
            throw new \Exception('File must be a ZIP archive');
        }

        // Create temporary directory
        $tempPath = storage_path('app/temp/plugin_' . Str::random(10));
        
        try {
            // Create temp directory
            File::makeDirectory($tempPath, 0755, true);

            // Extract ZIP
            $zip = new \ZipArchive();
            $zipPath = $file->getRealPath();

            if ($zip->open($zipPath) !== true) {
                throw new \Exception('Failed to open ZIP file');
            }

            // SECURITY: Scan ZIP for threats
            $threats = $this->securityScanner->scanZipFile($zip);
            if (!empty($threats)) {
                $zip->close();
                throw new \Exception('Security threats detected: ' . implode(', ', $threats));
            }

            $zip->extractTo($tempPath);
            $zip->close();

            // Find plugin.json
            $manifestPath = $this->findManifest($tempPath);
            
            if (!$manifestPath) {
                throw new \Exception('plugin.json not found in ZIP file');
            }

            // Parse manifest
            $manifest = json_decode(File::get($manifestPath), true);
            
            if (!$manifest || !isset($manifest['name'])) {
                throw new \Exception('Invalid plugin.json format');
            }

            // Validate manifest
            $this->validateManifest($manifest);

            // Get plugin directory from temp
            $pluginDir = dirname($manifestPath);

            // SECURITY: Scan extracted files for threats
            $dirThreats = $this->securityScanner->scanPluginDirectory($pluginDir);
            if (!empty($dirThreats)) {
                throw new \Exception('Security threats detected in plugin files: ' . implode(', ', $dirThreats));
            }

            // SECURITY: Validate namespace
            if (isset($manifest['service_provider'])) {
                $namespace = substr($manifest['service_provider'], 0, strrpos($manifest['service_provider'], '\\'));
                if (!$this->securityScanner->validateNamespace($namespace)) {
                    throw new \Exception('Invalid or unsafe namespace: ' . $namespace);
                }
            }

            // SECURITY: Check for conflicts with existing plugins
            $existingPlugins = Plugin::all()->toArray();
            $conflicts = $this->securityScanner->checkConflicts($manifest, $existingPlugins);
            if (!empty($conflicts)) {
                Log::warning("Plugin conflicts detected", [
                    'plugin' => $manifest['name'],
                    'conflicts' => $conflicts,
                ]);
            }

            $pluginName = $manifest['name'];
            $targetPath = $this->getPluginsPath() . '/' . $pluginName;
            $relativePath = 'plugins/' . $pluginName;

            // Check if plugin already exists
            $existingPlugin = Plugin::where('name', $manifest['name'])->first();
            
            if ($existingPlugin) {
                if (!$overwrite) {
                    throw new \Exception("Plugin '{$manifest['name']}' is already installed. Select 'Replace & Overwrite' option to overwrite existing files.");
                }

                // OVERWRITE FLOW: Remove old directory files & update record
                if (File::exists($targetPath)) {
                    File::deleteDirectory($targetPath);
                }
                File::moveDirectory($pluginDir, $targetPath);
                File::deleteDirectory($tempPath);

                $existingPlugin->update([
                    'display_name'     => $manifest['display_name'],
                    'description'      => $manifest['description'] ?? null,
                    'version'          => $manifest['version'],
                    'author'           => $manifest['author'] ?? null,
                    'author_url'       => $manifest['author_url'] ?? null,
                    'plugin_url'       => $manifest['plugin_url'] ?? null,
                    'path'             => $relativePath,
                    'namespace'        => $manifest['service_provider'] ? 
                        substr($manifest['service_provider'], 0, strrpos($manifest['service_provider'], '\\')) : null,
                    'requires_php'     => $manifest['requires']['php'] ?? null,
                    'requires_laravel' => $manifest['requires']['laravel'] ?? null,
                    'requires_quixko'  => $manifest['requires']['allincart'] ?? $manifest['requires']['quixko'] ?? null,
                    'dependencies'     => $manifest['dependencies'] ?? [],
                    'license_key'      => $licenseKey ?? $existingPlugin->license_key,
                    'metadata'         => $manifest,
                    'hooks'            => $manifest['hooks'] ?? [],
                    'installed_at'     => now(),
                ]);

                $this->clearCache();

                return [
                    'success'   => true,
                    'plugin'    => $existingPlugin,
                    'message'   => "Plugin '{$manifest['name']}' replaced and updated successfully",
                    'conflicts' => $conflicts ?? [],
                ];
            }

            // FRESH INSTALL FLOW: Move to plugins directory
            File::moveDirectory($pluginDir, $targetPath);

            // Clean up temp
            File::deleteDirectory($tempPath);

            // Create plugin record
            $plugin = $this->createPluginRecord($manifest, $licenseKey, $relativePath);

            return [
                'success' => true,
                'plugin' => $plugin,
                'message' => 'Plugin uploaded successfully',
                'conflicts' => $conflicts ?? [],
            ];

        } catch (\Exception $e) {
            // Clean up on error
            if (File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }

            throw $e;
        }
    }

    /**
     * Find plugin.json in extracted directory
     */
    protected function findManifest(string $path): ?string
    {
        // Check root
        if (File::exists($path . '/plugin.json')) {
            return $path . '/plugin.json';
        }

        // Check one level deep (common structure)
        $directories = File::directories($path);
        
        foreach ($directories as $dir) {
            if (File::exists($dir . '/plugin.json')) {
                return $dir . '/plugin.json';
            }
        }

        return null;
    }

    /**
     * Validate plugin manifest
     */
    protected function validateManifest(array $manifest): void
    {
        $required = ['name', 'display_name', 'version'];

        foreach ($required as $field) {
            if (!isset($manifest[$field])) {
                throw new \Exception("Missing required field in plugin.json: {$field}");
            }
        }

        // Validate name format (slug)
        if (!preg_match('/^[a-z0-9-]+$/', $manifest['name'])) {
            throw new \Exception('Plugin name must be lowercase alphanumeric with hyphens only');
        }
    }

    /**
     * Create plugin database record
     */
    protected function createPluginRecord(array $manifest, ?string $licenseKey = null, ?string $path = null): Plugin
    {
        return Plugin::create([
            'name' => $manifest['name'],
            'display_name' => $manifest['display_name'],
            'description' => $manifest['description'] ?? null,
            'version' => $manifest['version'],
            'author' => $manifest['author'] ?? null,
            'author_url' => $manifest['author_url'] ?? null,
            'plugin_url' => $manifest['plugin_url'] ?? null,
            'path' => $path ?? ('plugins/' . $manifest['name']),
            'namespace' => $manifest['service_provider'] ? 
                substr($manifest['service_provider'], 0, strrpos($manifest['service_provider'], '\\')) : null,
            'requires_php' => $manifest['requires']['php'] ?? null,
            'requires_laravel' => $manifest['requires']['laravel'] ?? null,
            'requires_quixko' => $manifest['requires']['allincart'] ?? $manifest['requires']['quixko'] ?? null,
            'dependencies' => $manifest['dependencies'] ?? [],
            'license_key' => $licenseKey,
            'license_status' => $licenseKey ? 'valid' : 'none',
            'metadata' => $manifest,
            'hooks' => $manifest['hooks'] ?? [],
            'is_installed' => true,
            'is_active' => false,
            'installed_at' => now(),
        ]);
    }

    /**
     * Install plugin (run migrations, publish assets)
     */
    public function installPlugin(Plugin $plugin): array
    {
        $backupPath = null;

        try {
            // Check requirements
            $requirementErrors = $plugin->meetsRequirements();
            if (!empty($requirementErrors)) {
                throw new \Exception('Requirements not met: ' . implode(', ', $requirementErrors));
            }

            // Create backup before installation
            if ($plugin->is_installed) {
                $backupPath = $this->backupService->createBackup($plugin);
                $plugin->update(['backup_path' => $backupPath]);
            }

            // Run migrations with rollback support
            $migrationsRun = $this->runMigrations($plugin);
            $plugin->update(['migrations_run' => $migrationsRun]);

            // Publish assets
            $this->publishAssets($plugin);

            // Register hooks
            $this->registerHooks($plugin);

            // Mark as installed
            $plugin->update([
                'is_installed' => true,
                'installed_at' => now(),
            ]);




            // Clean old backups (keep last 5)
            $this->backupService->cleanOldBackups($plugin->name, 5);

            return [
                'success' => true,
                'message' => 'Plugin installed successfully',
            ];

        } catch (\Exception $e) {
            
            // Attempt rollback if backup exists
            if ($backupPath && File::exists($backupPath)) {
                try {
                    $this->backupService->restoreBackup($backupPath);
                    Log::info("Plugin installation rolled back successfully", [
                        'plugin' => $plugin->name,
                    ]);
                } catch (\Exception $rollbackError) {
                    Log::error("Plugin rollback failed", [
                        'plugin' => $plugin->name,
                        'error' => $rollbackError->getMessage(),
                    ]);
                }
            }
            
            Log::error("Plugin installation failed: {$plugin->name}", [
                'error' => $e->getMessage(),
            ]);



            throw $e;
        }
    }

    /**
     * Activate plugin
     */
    public function activatePlugin(Plugin $plugin): array
    {
        try {
            // Check if already active
            if ($plugin->isActive()) {
                return [
                    'success' => true,
                    'message' => 'Plugin is already active',
                ];
            }

            // Check dependencies
            $dependencyErrors = $plugin->checkDependencies();
            if (!empty($dependencyErrors)) {
                throw new \Exception('Dependencies not met: ' . implode(', ', $dependencyErrors));
            }

            // Run plugin migrations upon activation
            $migrationsRun = $this->runMigrations($plugin);

            // Publish plugin assets if available
            $this->publishAssets($plugin);

            // Load service provider
            $this->loadServiceProvider($plugin);

            // Register & activate hooks
            $this->registerHooks($plugin);
            $this->activateHooks($plugin);

            // Mark as active & installed
            $plugin->update([
                'is_installed' => true,
                'is_active' => true,
                'activated_at' => now(),
                'installed_at' => $plugin->installed_at ?? now(),
                'migrations_run' => $migrationsRun,
                'hook_failure_count' => 0, // Reset failure count
            ]);

            // Clear cache
            $this->clearCache();

            return [
                'success' => true,
                'message' => 'Plugin activated and migrations executed successfully',
            ];

        } catch (\Exception $e) {
            Log::error("Plugin activation failed: {$plugin->name}", [
                'error' => $e->getMessage(),
            ]);



            throw $e;
        }
    }

    /**
     * Deactivate plugin
     */
    public function deactivatePlugin(Plugin $plugin): array
    {
        try {
            // Check if already inactive
            if (!$plugin->isActive()) {
                return [
                    'success' => true,
                    'message' => 'Plugin is already inactive',
                ];
            }

            // Deactivate hooks
            $this->deactivateHooks($plugin);

            // Mark as inactive
            $plugin->update([
                'is_active' => false,
                'activated_at' => null,
            ]);

            // Clear cache
            $this->clearCache();



            return [
                'success' => true,
                'message' => 'Plugin deactivated successfully',
            ];

        } catch (\Exception $e) {
            Log::error("Plugin deactivation failed: {$plugin->name}", [
                'error' => $e->getMessage(),
            ]);



            throw $e;
        }
    }

    /**
     * Uninstall plugin
     */
    public function uninstallPlugin(Plugin $plugin, bool $deleteFiles = true): array
    {
        try {

            // Deactivate first
            if ($plugin->isActive()) {
                $this->deactivatePlugin($plugin);
            }

            // Rollback migrations if they were run
            // We force a cleanup here to avoid "Ghost" migration records if a previous install failed
            $this->rollbackMigrations($plugin);

            // Delete plugin files
            if ($deleteFiles) {
                $pluginPath = $plugin->getDirectoryPath();
                if (File::exists($pluginPath)) {
                    File::deleteDirectory($pluginPath);
                }

                // Delete published assets
                $assetsPath = public_path('plugins/' . $plugin->name);
                if (File::exists($assetsPath)) {
                    File::deleteDirectory($assetsPath);
                }
            }



            // Delete database record (cascades to hooks and settings)
            $plugin->delete();

            // Clear cache
            $this->clearCache();

            return [
                'success' => true,
                'message' => 'Plugin uninstalled successfully',
            ];

        } catch (\Exception $e) {
            
            Log::error("Plugin uninstallation failed: {$plugin->name}", [
                'error' => $e->getMessage(),
            ]);



            throw $e;
        }
    }

    /**
     * Run plugin migrations
     */
    protected function runMigrations(Plugin $plugin): array
    {
        $manifest = $plugin->metadata;
        $migrationsRun = [];

        // 1. Always run main system migrations first so required core tables exist
        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            Log::warning("Main system migrations check during plugin activation: " . $e->getMessage());
        }
        
        $candidatePaths = array_unique(array_filter([
            isset($manifest['migrations']) ? $plugin->getDirectoryPath() . '/' . $manifest['migrations'] : null,
            $plugin->getDirectoryPath() . '/database/migrations',
            $plugin->getDirectoryPath() . '/src/Migrations',
            $plugin->getDirectoryPath() . '/migrations',
        ]));

        foreach ($candidatePaths as $migrationsPath) {
            if (File::exists($migrationsPath)) {
                try {
                    $migrationFiles = collect(File::files($migrationsPath))
                        ->map(fn($file) => $file->getFilename())
                        ->toArray();

                    if (!empty($migrationFiles)) {
                        $exitCode = Artisan::call('migrate', [
                            '--path' => $migrationsPath,
                            '--realpath' => true,
                            '--force' => true,
                        ]);

                        if ($exitCode !== 0) {
                            $errorOutput = Artisan::output();
                            throw new \Exception("Migration failed (Exit Code: $exitCode): " . $errorOutput);
                        }

                        $migrationsRun = array_merge($migrationsRun, $migrationFiles);
                    }
                } catch (\Exception $e) {
                    $this->rollbackMigrations($plugin, $migrationsPath);
                    throw $e;
                }
            }
        }

        return array_unique($migrationsRun);
    }

    /**
     * Rollback plugin migrations
     */
    protected function rollbackMigrations(Plugin $plugin, ?string $migrationsPath = null): void
    {
        if (!$migrationsPath) {
            $manifest = $plugin->metadata;
            $migrationsPath = $plugin->getDirectoryPath() . '/' . ($manifest['migrations'] ?? 'src/Migrations');
        }

        if ($migrationsPath && File::exists($migrationsPath)) {
            try {
                $exitCode = Artisan::call('migrate:reset', [
                    '--path' => $migrationsPath,
                    '--realpath' => true,
                    '--force' => true,
                ]);

                if ($exitCode !== 0) {
                    $errorOutput = Artisan::output();
                    throw new \Exception("Migration rollback failed (Exit Code: $exitCode): " . $errorOutput);
                }

                $migrationFiles = collect(File::files($migrationsPath))
                    ->sortBy(fn($file) => $file->getFilename())
                    ->toArray();

                foreach ($migrationFiles as $file) {
                    $content = File::get($file->getPathname());
                    $filename = $file->getFilenameWithoutExtension();

                    // Aggressive Drop: Scan for BOTH up() and down() for safety
                    // We look for Schema::create or Schema::dropIfExists to identify tables
                    if (preg_match_all("/Schema::(?:create|dropIfExists|table)\(\s*['\"]([^'\"]+)['\"]/i", $content, $matches)) {
                        $tables = array_unique($matches[1]);
                        foreach ($tables as $table) {
                            // Specifically avoid dropping core system tables accidentally
                            if ($table !== 'users' && $table !== 'migrations' && $table !== 'plugin_settings') {
                                Schema::disableForeignKeyConstraints();
                                Schema::dropIfExists($table);
                                Schema::enableForeignKeyConstraints();
                            }
                        }
                    }

                    // Purge from migrations table metadata
                    DB::table('migrations')->where('migration', $filename)->delete();
                }

                Log::info("Plugin migrations rolled back and records purged", [
                    'plugin' => $plugin->name,
                ]);
            } catch (\Exception $e) {
                Log::error("Migration rollback failed, attempting hard purge", [
                    'plugin' => $plugin->name,
                    'error' => $e->getMessage(),
                ]);

                try {
                    $migrationFiles = File::files($migrationsPath);
                    Schema::disableForeignKeyConstraints();
                    foreach ($migrationFiles as $file) {
                        $content = File::get($file->getPathname());
                        if (preg_match_all("/Schema::(?:create|dropIfExists|table)\(\s*['\"]([^'\"]+)['\"]/i", $content, $matches)) {
                            foreach (array_unique($matches[1]) as $table) {
                                if (!in_array($table, ['users', 'migrations', 'failed_jobs'])) {
                                    Schema::dropIfExists($table);
                                }
                            }
                        }
                        DB::table('migrations')->where('migration', $file->getFilenameWithoutExtension())->delete();
                    }
                    Schema::enableForeignKeyConstraints();
                } catch (\Exception $purgeError) {
                    Log::error("Hard purge failed", ['error' => $purgeError->getMessage()]);
                }
            }
        }
    }

    /**
     * Publish plugin assets
     */
    protected function publishAssets(Plugin $plugin): void
    {
        $manifest = $plugin->metadata;
        
        if (isset($manifest['assets'])) {
            $assetsPath = $plugin->getDirectoryPath() . '/resources/assets';
            $publicPath = public_path('plugins/' . $plugin->name);
            
            if (File::exists($assetsPath)) {
                File::copyDirectory($assetsPath, $publicPath);
            }
        }
    }

    /**
     * Register plugin hooks
     */
    protected function registerHooks(Plugin $plugin): void
    {
        $hooks = $plugin->hooks ?? [];

        foreach ($hooks as $hookName => $callback) {
            PluginHook::updateOrCreate(
                [
                    'plugin_id' => $plugin->id,
                    'hook_name' => $hookName,
                ],
                [
                    'callback' => $callback,
                    'priority' => 10,
                    'is_active' => false, // Will be activated when plugin is activated
                ]
            );
        }
    }

    /**
     * Activate plugin hooks
     */
    protected function activateHooks(Plugin $plugin): void
    {
        $plugin->pluginHooks()->update(['is_active' => true]);
        $this->hookService->clearCache();
    }

    /**
     * Deactivate plugin hooks
     */
    protected function deactivateHooks(Plugin $plugin): void
    {
        $plugin->pluginHooks()->update(['is_active' => false]);
        $this->hookService->clearCache();
    }

    /**
     * Load plugin service provider.
     * Uses the path stored in the DB record — no guessing.
     */
    protected function loadServiceProvider(Plugin $plugin): void
    {
        $manifest = $plugin->metadata;

        $providerClass = $manifest['service_provider'] ?? ($plugin->namespace ? $plugin->namespace . '\\PluginServiceProvider' : null);

        if (empty($providerClass)) {
            return;
        }

        // Already loaded (e.g. called twice during the same request)
        if (class_exists($providerClass, false)) {
            if (!app()->getProviders($providerClass)) {
                app()->register($providerClass);
            }
            return;
        }

        // Require the file directly — the autoloader will also handle it,
        // but being explicit here means errors surface immediately.
        $providerFile = $plugin->getDirectoryPath() . '/PluginServiceProvider.php';

        if (!file_exists($providerFile)) {
            Log::error("Plugin PluginServiceProvider.php not found", [
                'plugin'   => $plugin->name,
                'expected' => $providerFile,
            ]);
            return;
        }

        require_once $providerFile;

        if (!class_exists($providerClass, false)) {
            Log::error("Plugin service provider class not found in file", [
                'plugin' => $plugin->name,
                'class'  => $providerClass,
                'file'   => $providerFile,
            ]);
            return;
        }

        app()->register($providerClass);
    }

    /**
     * Clear plugin cache
     */
    protected function clearCache(): void
    {
        Cache::forget('plugins.active');
        Cache::forget('plugins.all');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');

        if (class_exists(\Plugins\Website\Helpers\WebsiteHelper::class)) {
            try {
                \Plugins\Website\Helpers\WebsiteHelper::clearCache();
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Get all active plugins
     */
    public function getActivePlugins(): array
    {
        return Cache::remember('plugins.active', 3600, function () {
            return Plugin::active()->get()->toArray();
        });
    }

    /**
     * Load all active plugins.
     * If a plugin's stored path no longer exists on disk, attempt to find it
     * by scanning plugin.json files and self-heal the DB record.
     */
    public function loadActivePlugins(): void
    {
        $plugins = Plugin::active()->get();

        foreach ($plugins as $plugin) {
            try {
                // Self-heal: if stored path is wrong, find the real folder
                if (!is_dir($plugin->getDirectoryPath())) {
                    $healed = $this->healPluginPath($plugin);
                    if (!$healed) {
                        Log::warning("Plugin folder not found, skipping: {$plugin->name}", [
                            'stored_path' => $plugin->path,
                        ]);
                        continue;
                    }
                    $plugin->refresh();
                }

                $this->loadServiceProvider($plugin);
            } catch (\Throwable $e) {
                Log::error("Failed to load plugin: {$plugin->name}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Scan the plugins directory to find the actual folder for a plugin
     * whose stored path is stale, then update the DB record.
     */
    protected function healPluginPath(Plugin $plugin): bool
    {
        $manifest = $plugin->metadata;
        $spClass  = $manifest['service_provider'] ?? '';
        $nsPrefix = substr($spClass, 0, strrpos($spClass, '\\'));

        if (!$nsPrefix || !is_dir($this->getPluginsPath())) {
            return false;
        }

        foreach (scandir($this->getPluginsPath()) as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            $manifestFile = $this->getPluginsPath() . '/' . $dir . '/plugin.json';
            if (!file_exists($manifestFile)) {
                continue;
            }
            $data = json_decode(file_get_contents($manifestFile), true);
            $sp   = $data['service_provider'] ?? '';
            $spNs = substr($sp, 0, strrpos($sp, '\\'));

            if ($spNs === $nsPrefix) {
                $newPath = 'plugins/' . $dir;
                $plugin->update([
                    'path'      => $newPath,
                    'namespace' => $nsPrefix,
                ]);
                Log::info("Plugin path self-healed", [
                    'plugin'   => $plugin->name,
                    'old_path' => $plugin->path,
                    'new_path' => $newPath,
                ]);
                return true;
            }
        }

        return false;
    }
}

<?php

namespace App\Services;

use App\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PluginBackupService
{
    /**
     * Create backup of plugin before installation/update
     */
    public function createBackup(Plugin $plugin): string
    {
        $backupDir = storage_path('app/plugin-backups');
        $backupPath = $backupDir . '/' . $plugin->name . '-' . time();

        // Ensure backup directory exists
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        File::makeDirectory($backupPath, 0755, true);

        // Backup plugin files
        $pluginPath = $plugin->getDirectoryPath();
        if (File::exists($pluginPath)) {
            File::copyDirectory($pluginPath, $backupPath . '/files');
        }

        // Backup database state
        $dbBackup = [
            'plugin' => $plugin->toArray(),
            'hooks' => $plugin->pluginHooks->toArray(),
            'settings' => $plugin->pluginSettings->toArray(),
            'timestamp' => now()->toDateTimeString(),
        ];

        File::put($backupPath . '/database.json', json_encode($dbBackup, JSON_PRETTY_PRINT));

        // Backup database tables created by plugin
        if ($plugin->migrations_run) {
            $this->backupPluginTables($plugin, $backupPath);
        }

        Log::info("Plugin backup created", [
            'plugin' => $plugin->name,
            'backup_path' => $backupPath,
        ]);

        return $backupPath;
    }

    /**
     * Restore plugin from backup
     */
    public function restoreBackup(string $backupPath): bool
    {
        try {
            if (!File::exists($backupPath)) {
                throw new \Exception("Backup not found: {$backupPath}");
            }

            // Load backup metadata
            $dbBackup = json_decode(File::get($backupPath . '/database.json'), true);

            DB::beginTransaction();

            // Restore database records
            $plugin = Plugin::find($dbBackup['plugin']['id']);
            
            if ($plugin) {
                $plugin->update($dbBackup['plugin']);

                // Restore hooks
                $plugin->pluginHooks()->delete();
                foreach ($dbBackup['hooks'] as $hookData) {
                    $plugin->pluginHooks()->create($hookData);
                }

                // Restore settings
                $plugin->pluginSettings()->delete();
                foreach ($dbBackup['settings'] as $settingData) {
                    $plugin->pluginSettings()->create($settingData);
                }
            }

            // Restore files
            if (File::exists($backupPath . '/files')) {
                $pluginPath = base_path('plugins/' . $dbBackup['plugin']['name']);
                
                if (File::exists($pluginPath)) {
                    File::deleteDirectory($pluginPath);
                }
                
                File::copyDirectory($backupPath . '/files', $pluginPath);
            }

            DB::commit();

            Log::info("Plugin restored from backup", [
                'plugin' => $dbBackup['plugin']['name'],
                'backup_path' => $backupPath,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Plugin restore failed", [
                'backup_path' => $backupPath,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Backup database tables created by plugin
     */
    protected function backupPluginTables(Plugin $plugin, string $backupPath): void
    {
        $manifest = $plugin->metadata;
        
        if (isset($manifest['database_tables'])) {
            $tablesBackup = [];
            
            foreach ($manifest['database_tables'] as $tableName) {
                if (DB::getSchemaBuilder()->hasTable($tableName)) {
                    $tablesBackup[$tableName] = DB::table($tableName)->get()->toArray();
                }
            }
            
            if (!empty($tablesBackup)) {
                File::put(
                    $backupPath . '/tables.json',
                    json_encode($tablesBackup, JSON_PRETTY_PRINT)
                );
            }
        }
    }

    /**
     * Clean old backups (keep last 5)
     */
    public function cleanOldBackups(string $pluginName, int $keep = 5): void
    {
        $backupDir = storage_path('app/plugin-backups');
        
        if (!File::exists($backupDir)) {
            return;
        }

        $backups = collect(File::directories($backupDir))
            ->filter(fn($dir) => str_starts_with(basename($dir), $pluginName . '-'))
            ->sortByDesc(fn($dir) => File::lastModified($dir))
            ->values();

        // Delete old backups
        $backups->slice($keep)->each(function ($dir) {
            File::deleteDirectory($dir);
            Log::info("Old plugin backup deleted", ['path' => $dir]);
        });
    }

    /**
     * Get available backups for a plugin
     */
    public function getBackups(string $pluginName): array
    {
        $backupDir = storage_path('app/plugin-backups');
        
        if (!File::exists($backupDir)) {
            return [];
        }

        return collect(File::directories($backupDir))
            ->filter(fn($dir) => str_starts_with(basename($dir), $pluginName . '-'))
            ->map(function ($dir) {
                $metadata = json_decode(File::get($dir . '/database.json'), true);
                
                return [
                    'path' => $dir,
                    'name' => basename($dir),
                    'created_at' => $metadata['timestamp'] ?? null,
                    'size' => $this->getDirectorySize($dir),
                ];
            })
            ->sortByDesc('created_at')
            ->values()
            ->toArray();
    }

    /**
     * Get directory size
     */
    protected function getDirectorySize(string $path): int
    {
        $size = 0;
        
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }
}

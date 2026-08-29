<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Ensure Helpers are loaded
        if (!function_exists('storage_url')) {
            require_once app_path('Helpers/StorageHelper.php');
        }
        if (!class_exists('App\Helpers\DemoHelper') && file_exists(app_path('Helpers/DemoHelper.php'))) {
            require_once app_path('Helpers/DemoHelper.php');
        }

        // Plugin class autoloader.
        // Resolution order:
        //   1. DB plugins table  — path column stores the actual folder (e.g. plugins/InAllCart-ride-sharing)
        //   2. Filesystem scan   — reads plugin.json service_provider to match namespace
        // No regex-based name guessing. Any folder name works.
        spl_autoload_register(function (string $class): void {
            if (strpos($class, 'Plugins\\') !== 0) {
                return;
            }

            $parts         = explode('\\', $class);
            $pluginsBase   = base_path('plugins');
            // e.g. Plugins\AllInCartRideSharing
            $nsPrefix      = $parts[0] . '\\' . $parts[1];
            // e.g. Controllers/Api/RideBookingController  or  PluginServiceProvider
            $relPath       = implode('/', array_slice($parts, 2));

            // --- Strategy 1: DB lookup (fast, works after first sync) ---
            $pluginDir = null;
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('plugins')) {
                    $dbPath = \Illuminate\Support\Facades\DB::table('plugins')
                        ->where('namespace', $nsPrefix)
                        ->value('path');
                    if ($dbPath && is_dir(base_path($dbPath))) {
                        $pluginDir = base_path($dbPath);
                    }
                }
            } catch (\Throwable $e) {
                // DB not ready yet — fall through
            }

            // --- Strategy 2: Filesystem scan (fresh install / before sync) ---
            if (!$pluginDir && is_dir($pluginsBase)) {
                foreach (scandir($pluginsBase) as $dir) {
                    if ($dir === '.' || $dir === '..') {
                        continue;
                    }
                    $manifest = $pluginsBase . '/' . $dir . '/plugin.json';
                    if (!file_exists($manifest)) {
                        continue;
                    }
                    $data = json_decode(file_get_contents($manifest), true);
                    $sp   = $data['service_provider'] ?? '';
                    // Namespace prefix of the service_provider
                    $spNs = substr($sp, 0, strrpos($sp, '\\'));
                    if ($spNs === $nsPrefix) {
                        $pluginDir = $pluginsBase . '/' . $dir;
                        break;
                    }
                }
            }

            if (!$pluginDir) {
                return;
            }

            // Resolve file path:
            // PluginServiceProvider lives at plugin root; everything else is under src/
            if ($relPath === '' || $relPath === 'PluginServiceProvider') {
                $file = $pluginDir . '/PluginServiceProvider.php';
            } else {
                $file = $pluginDir . '/src/' . $relPath . '.php';
                if (!file_exists($file)) {
                    // Fallback: root-level file (rare)
                    $file = $pluginDir . '/' . $relPath . '.php';
                }
            }

            if (file_exists($file)) {
                require_once $file;
            }
        });

        // Core plugin services
        $this->app->singleton('plugin.hooks',    fn () => new \App\Services\HookService());
        $this->app->singleton('plugin.security', fn () => new \App\Services\PluginSecurityScanner());
        $this->app->singleton('plugin.backup',   fn () => new \App\Services\PluginBackupService());
        $this->app->singleton('plugin.service',  fn ($app) => new \App\Services\PluginService(
            $app->make('plugin.hooks'),
            $app->make('plugin.security'),
            $app->make('plugin.backup')
        ));

        $this->app->alias('plugin.service',  \App\Services\PluginService::class);
        $this->app->alias('plugin.hooks',    \App\Services\HookService::class);
        $this->app->alias('plugin.security', \App\Services\PluginSecurityScanner::class);
        $this->app->alias('plugin.backup',   \App\Services\PluginBackupService::class);

        $this->app->singleton(\App\Services\StorageService::class);
        $this->app->alias(\App\Services\StorageService::class, 'storage.service');
    }

    public function boot(): void
    {
        // Load active plugins (registers their routes, views, hooks, etc.)
        if (file_exists(storage_path('installed'))) {
            try {
                app('plugin.service')->loadActivePlugins();
            } catch (\Throwable $e) {
                \Log::debug('Plugin loading skipped: ' . $e->getMessage());
            }
        }

        // Timezone from settings
        try {
            config(['app.timezone' => \App\Models\Setting::get('app_timezone', 'UTC')]);
            date_default_timezone_set(config('app.timezone'));
        } catch (\Throwable $e) {
            // DB not ready
        }

        $this->loadFirebaseConfig();

        View::composer('*', function ($view) {
            try {
                $view->with('currencySymbol', \App\Helpers\CurrencyHelper::getSymbol());
                $decimals = (int) \App\Models\Setting::get('currency_decimal_places', 2);
            } catch (\Throwable $e) {
                $decimals = 2;
                $view->with('currencySymbol', '');
            }
            $view->with('priceDecimals', $decimals);
            $view->with('priceStep', $decimals > 0 ? '0.' . str_repeat('0', $decimals - 1) . '1' : '1');
        });

        // Branding values for the admin layout (app name, favicon, sidebar logo).
        // Pulled out of resources/views/admin/layouts/app.blade.php so the view
        // no longer runs queries directly.
        View::composer(
            'admin.layouts.app',
            \App\View\Composers\AdminLayoutComposer::class
        );
    }

    // -------------------------------------------------------------------------

    private function loadFirebaseConfig(): void
    {
        if (!file_exists(storage_path('installed')) || app()->runningInConsole()) {
            return;
        }
        try {
            $settings = \DB::table('settings')
                ->where('group', 'mobile_app')
                ->whereIn('key', ['firebase_api_key','firebase_auth_domain','firebase_database_url','firebase_project_id','firebase_storage_bucket','firebase_messaging_sender_id','firebase_app_id','firebase_server_key'])
                ->pluck('value', 'key');

            if ($settings->isNotEmpty()) {
                config([
                    'services.firebase.api_key'              => $settings->get('firebase_api_key'),
                    'services.firebase.auth_domain'          => $settings->get('firebase_auth_domain'),
                    'services.firebase.database_url'         => $settings->get('firebase_database_url'),
                    'services.firebase.project_id'           => $settings->get('firebase_project_id'),
                    'services.firebase.storage_bucket'       => $settings->get('firebase_storage_bucket'),
                    'services.firebase.messaging_sender_id'  => $settings->get('firebase_messaging_sender_id'),
                    'services.firebase.app_id'               => $settings->get('firebase_app_id'),
                    'services.firebase.server_key'           => $settings->get('firebase_server_key'),
                ]);
            }
        } catch (\Throwable $e) {
            // Silently fail
        }
    }
}

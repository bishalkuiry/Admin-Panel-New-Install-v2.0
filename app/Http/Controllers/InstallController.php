<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use App\Models\User;

class InstallController extends Controller
{
    /**
     * Step 0: Welcome page
     */
    public function step0()
    {
        return view('installation.step0');
    }

    /**
     * Step 1: Check server requirements
     */
    public function step1(Request $request)
    {
        if (Hash::check('step_1', $request['token'])) {
            $permission['curl_enabled'] = function_exists('curl_version');
            $permission['curl'] = function_exists('curl_version');
            $permission['bcmath'] = extension_loaded('bcmath');
            $permission['ctype'] = extension_loaded('ctype');
            $permission['json'] = extension_loaded('json');
            $permission['mbstring'] = extension_loaded('mbstring');
            $permission['openssl'] = extension_loaded('openssl');
            $permission['pdo'] = defined('PDO::ATTR_DRIVER_NAME');
            $permission['tokenizer'] = extension_loaded('tokenizer');
            $permission['xml'] = extension_loaded('xml');
            $permission['zip'] = extension_loaded('zip');
            $permission['fileinfo'] = extension_loaded('fileinfo');
            $permission['gd'] = extension_loaded('gd');
            $permission['pdo_mysql'] = extension_loaded('pdo_mysql');
            $permission['db_file_write_perm'] = is_writable(base_path('.env'));
            $permission['storage_write_perm'] = is_writable(storage_path());

            return view('installation.step1', compact('permission'));
        }

        session()->flash('error', 'Access denied!');
        return redirect()->route('step0');
    }

    /**
     * Step 3: Database configuration form
     */
    public function step3(Request $request)
    {
        if (Hash::check('step_3', $request['token'])) {
            return view('installation.step3');
        }

        session()->flash('error', 'Access denied!');
        return redirect()->route('step0');
    }

    /**
     * Step 4: Import database
     */
    public function step4(Request $request)
    {
        if (Hash::check('step_4', $request['token'])) {
            return view('installation.step4');
        }

        session()->flash('error', 'Access denied!');
        return redirect()->route('step0');
    }

    /**
     * Step 5: Admin account setup
     */
    public function step5(Request $request)
    {
        if (Hash::check('step_5', $request['token'])) {
            return view('installation.step5');
        }

        session()->flash('error', 'Access denied!');
        return redirect()->route('step0');
    }

    /**
     * Test and save database configuration
     */
    public function database_installation(Request $request)
    {
        if ($this->check_database_connection($request->DB_HOST, $request->DB_DATABASE, $request->DB_USERNAME, $request->DB_PASSWORD)) {

            $key = base64_encode(random_bytes(32));

            $output = 'APP_NAME=InAllCart
APP_ENV=production
APP_KEY=base64:' . $key . '
APP_DEBUG=false
APP_URL=' . URL::to('/') . '

DB_CONNECTION=mysql
DB_HOST=' . $request->DB_HOST . '
DB_PORT=3306
DB_DATABASE=' . $request->DB_DATABASE . '
DB_USERNAME=' . $request->DB_USERNAME . '
DB_PASSWORD="' . $request->DB_PASSWORD . '"

SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync
CACHE_STORE=file

SOFTWARE_VERSION=1.0
';

            $file = fopen(base_path('.env'), 'w');
            fwrite($file, $output);
            fclose($file);

            $path = base_path('.env');
            if (file_exists($path)) {
                return redirect()->route('step4', ['token' => $request['token']]);
            } else {
                session()->flash('error', 'Database error!');
                return redirect()->route('step3', ['token' => bcrypt('step_3')]);
            }
        } else {
            session()->flash('error', 'Database connection failed! Please check your credentials.');
            return redirect()->route('step3', ['token' => bcrypt('step_3')]);
        }
    }

    /**
     * Run database migrations
     */
    public function import_sql()
    {
        try {
            $bootstrapCache = base_path('bootstrap/cache');
            foreach (['config.php', 'routes-v7.php', 'packages.php', 'services.php'] as $f) {
                @unlink($bootstrapCache . DIRECTORY_SEPARATOR . $f);
            }

            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);

            // Switch to database drivers now that tables exist
            $this->setEnvironmentValue('SESSION_DRIVER', 'database');
            $this->setEnvironmentValue('QUEUE_CONNECTION', 'database');
            $this->setEnvironmentValue('CACHE_STORE', 'database');

            foreach (['config.php', 'routes-v7.php', 'packages.php', 'services.php'] as $f) {
                @unlink($bootstrapCache . DIRECTORY_SEPARATOR . $f);
            }

            return redirect()->route('step5', ['token' => bcrypt('step_5')]);
        } catch (\Throwable $exception) {
            session()->flash('error', 'Migration failed: ' . $exception->getMessage());
            return back();
        }
    }

    /**
     * Force import - wipe and migrate
     */
    public function force_import_sql()
    {
        try {
            // Clear config cache by deleting bootstrap cache files directly.
            // Do NOT use Artisan::call('config:clear') — it can reset the PHP
            // process mid-request and prevent the redirect from being sent.
            $bootstrapCache = base_path('bootstrap/cache');
            foreach (['config.php', 'routes-v7.php', 'packages.php', 'services.php'] as $f) {
                @unlink($bootstrapCache . DIRECTORY_SEPARATOR . $f);
            }

            Artisan::call('db:wipe', ['--force' => true]);
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);

            // Switch to database drivers now that tables exist
            $this->setEnvironmentValue('SESSION_DRIVER', 'database');
            $this->setEnvironmentValue('QUEUE_CONNECTION', 'database');
            $this->setEnvironmentValue('CACHE_STORE', 'database');

            // Clear cache files directly (same reason as above — no Artisan::call)
            foreach (['config.php', 'routes-v7.php', 'packages.php', 'services.php'] as $f) {
                @unlink($bootstrapCache . DIRECTORY_SEPARATOR . $f);
            }

            return redirect()->route('step5', ['token' => bcrypt('step_5')]);
        } catch (\Throwable $exception) {
            // Use URL parameter so the error survives even if the session is unavailable
            // (e.g. the sessions table was just wiped by db:wipe).
            return redirect()->route('step4', ['token' => bcrypt('step_4'), 'err' => urlencode($exception->getMessage())]);
        }
    }

    /**
     * Save system settings and create admin user
     */
    public function system_settings(Request $request)
    {
        if (!Hash::check('step_6', $request['token'])) {
            session()->flash('error', 'Access denied!');
            return redirect()->route('step0');
        }

        try {
            // Create admin user as super_admin so they can access the admin panel
            User::updateOrCreate(
                ['email' => $request['admin_email']],
                [
                    'name' => $request['admin_name'],
                    'email' => $request['admin_email'],
                    'password' => bcrypt($request['admin_password']),
                    'email_verified_at' => now(),
                    'role' => 'super_admin',
                    'is_active' => true,
                ]
            );

            // Update app name in .env
            $this->setEnvironmentValue('APP_NAME', '"' . $request['app_name'] . '"');

            // Also persist app_name to settings table so the admin panel reads it correctly
            \App\Models\Setting::updateOrCreate(
                ['key' => 'app_name'],
                ['value' => $request['app_name'], 'group' => 'app_settings']
            );

            // Seed a default copyright using the chosen name
            \App\Models\Setting::updateOrCreate(
                ['key' => 'app_copyright'],
                ['value' => '© ' . date('Y') . ' ' . $request['app_name'] . '. All rights reserved.', 'group' => 'app_settings']
            );

            // Mark as installed FIRST (before cache clearing which can cause issues)
            File::put(storage_path('installed'), json_encode([
                'installed_at' => now()->toDateTimeString(),
                'version' => '1.0.0',
            ]));

            // Create storage link using native PHP — never use Artisan::call() here
            // because it can reset the PHP process and prevent the redirect being sent.
            try {
                $storageTarget = storage_path('app/public');
                $storageLink   = public_path('storage');
                if (!file_exists($storageLink) && !is_link($storageLink)) {
                    @symlink($storageTarget, $storageLink);
                }
            } catch (\Throwable $e) {
                // Non-fatal: storage link can be created later via `php artisan storage:link`
            }

            // Delete cache files directly — never use Artisan::call() for cache clearing
            // inside a running request (it resets the PHP process and breaks the redirect).
            $bootstrapCache = base_path('bootstrap/cache');
            foreach (['config.php', 'routes-v7.php', 'packages.php', 'services.php'] as $file) {
                @unlink($bootstrapCache . DIRECTORY_SEPARATOR . $file);
            }
            // Clear file-based cache store
            try {
                \Illuminate\Support\Facades\Cache::flush();
            } catch (\Exception $e) {}
            // Clear compiled views
            $viewCache = storage_path('framework/views');
            if (is_dir($viewCache)) {
                foreach (glob($viewCache . '/*.php') as $f) { @unlink($f); }
            }

            return redirect('/install-complete');

        } catch (\Exception $e) {
            session()->flash('error', 'Installation failed: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Check database connection
     */
    private function check_database_connection($db_host = "", $db_name = "", $db_user = "", $db_pass = ""): bool
    {
        try {
            if (@mysqli_connect($db_host, $db_user, $db_pass, $db_name)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Update .env file value
     */
    private function setEnvironmentValue($envKey, $envValue)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        $keyPattern = "/^{$envKey}=.*/m";

        if (preg_match($keyPattern, $str)) {
            $str = preg_replace($keyPattern, "{$envKey}={$envValue}", $str);
        } else {
            $str = rtrim($str, "\n") . "\n{$envKey}={$envValue}\n";
        }

        file_put_contents($envFile, $str);

        return $envValue;
    }
}

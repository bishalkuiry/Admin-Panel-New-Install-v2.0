<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Auto-create .env file if not exists
$envFile = dirname(__DIR__) . '/.env';
$envExample = dirname(__DIR__) . '/.env.example';

if (!file_exists($envFile)) {
    if (file_exists($envExample)) {
        copy($envExample, $envFile);
    } else {
        // Create minimal .env
        $defaultEnv = "APP_NAME=InAllCart\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=http://localhost\n\nDB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=\nDB_USERNAME=\nDB_PASSWORD=\n\nSESSION_DRIVER=file\nSESSION_LIFETIME=120\nQUEUE_CONNECTION=sync\nCACHE_STORE=file\n";
        file_put_contents($envFile, $defaultEnv);
    }
}

// Auto-generate APP_KEY if empty
$envContent = file_get_contents($envFile);
if (preg_match('/^APP_KEY=\s*$/m', $envContent) || strpos($envContent, 'APP_KEY=') === false) {
    $key = 'base64:' . base64_encode(random_bytes(32));
    if (strpos($envContent, 'APP_KEY=') !== false) {
        $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
    } else {
        $envContent = "APP_KEY={$key}\n" . $envContent;
    }
    file_put_contents($envFile, $envContent);
}

// Auto-detect installation status
$isInstalled = file_exists(dirname(__DIR__) . '/storage/installed');

// Force file-based drivers during installation (database may not exist yet)
if (!$isInstalled) {
    putenv('SESSION_DRIVER=file');
    putenv('CACHE_STORE=file');
    putenv('QUEUE_CONNECTION=sync');
}

if ($isInstalled) {
    // Normal mode - application is installed
    return Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
            web: __DIR__.'/../routes/web.php',
            api: __DIR__.'/../routes/api.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
        )
        ->withMiddleware(function (Middleware $middleware): void {
            $middleware->web(append: [
                \App\Http\Middleware\ClearFiltersOnReload::class,
                \App\Http\Middleware\PreventDemoDelete::class,
            ]);

            // Ensure our API is stateless for Cloudflare caching
            $middleware->api(prepend: [
                \App\Http\Middleware\PreventSessionForApi::class,
                \App\Http\Middleware\PreventDemoDelete::class,
            ]);

            $middleware->alias([
                'role' => \App\Http\Middleware\CheckRole::class,
                'permission' => \App\Http\Middleware\CheckPermission::class,
            ]);
        })
        ->withExceptions(function (Exceptions $exceptions): void {
            //
        })->create();
} else {
    // Install mode - show installation wizard
    return Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
            web: __DIR__.'/../routes/install.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
        )
        ->withMiddleware(function (Middleware $middleware): void {
            //
        })
        ->withExceptions(function (Exceptions $exceptions): void {
            //
        })->create();
}

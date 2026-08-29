<?php
/**
 * Composer Setup Page
 * Pure-PHP standalone page — loaded BEFORE Laravel boots.
 * Shown when vendor/autoload.php is missing (e.g. fresh Envato upload).
 */

define('BASE_PATH', dirname(__DIR__));
define('COMPOSER_LOCK', BASE_PATH . '/composer.lock');
define('VENDOR_AUTOLOAD', BASE_PATH . '/vendor/autoload.php');

// ── AJAX: Run composer install ───────────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'run_composer') {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
    ob_implicit_flush(true);
    @ob_end_flush();

    $send = function (string $type, string $msg) {
        echo "data: " . json_encode(['type' => $type, 'msg' => $msg]) . "\n\n";
        if (ob_get_level()) ob_flush();
        flush();
    };

    // Verify composer.json / composer.lock exist
    if (!file_exists(BASE_PATH . '/composer.json')) {
        $send('error', 'composer.json not found. Please re-upload all source files.');
        $send('done', 'failed');
        exit;
    }

    // Detect composer binary
    $composerBin = null;
    foreach (['composer', 'composer.phar', '/usr/local/bin/composer', '/usr/bin/composer'] as $candidate) {
        exec('which ' . escapeshellarg($candidate) . ' 2>/dev/null', $out, $rc);
        if ($rc === 0 && !empty($out[0])) { $composerBin = $out[0]; break; }
        unset($out);
        // Windows: where
        exec('where ' . escapeshellarg($candidate) . ' 2>NUL', $out2, $rc2);
        if ($rc2 === 0 && !empty($out2[0])) { $composerBin = $out2[0]; break; }
        unset($out2);
    }

    if (!$composerBin) {
        // Try running as a PHP script
        exec('php -r "echo PHP_VERSION;" 2>&1', $phpOut, $phpRc);
        if ($phpRc === 0 && file_exists(BASE_PATH . '/composer.phar')) {
            $composerBin = 'php ' . escapeshellarg(BASE_PATH . '/composer.phar');
        }
    }

    if (!$composerBin) {
        $send('error', 'Composer not found on this server. Please run "composer install" manually via SSH/terminal, then reload this page.');
        $send('done', 'failed');
        exit;
    }

    // Pre-create bootstrap/cache and storage directories with write permissions for Laravel package discovery
    $composerHomeDir = BASE_PATH . '/storage/framework/cache/composer';
    $dirsToCreate = [
        BASE_PATH . '/bootstrap/cache',
        BASE_PATH . '/storage',
        BASE_PATH . '/storage/app',
        BASE_PATH . '/storage/app/public',
        BASE_PATH . '/storage/framework',
        BASE_PATH . '/storage/framework/cache',
        BASE_PATH . '/storage/framework/cache/data',
        BASE_PATH . '/storage/framework/sessions',
        BASE_PATH . '/storage/framework/views',
        BASE_PATH . '/storage/logs',
        $composerHomeDir,
    ];

    foreach ($dirsToCreate as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        @chmod($dir, 0777);
    }

    // Set COMPOSER_HOME & HOME environment variables for shared hosting web environments
    putenv('COMPOSER_HOME=' . $composerHomeDir);
    putenv('HOME=' . $composerHomeDir);
    putenv('COMPOSER_MEMORY_LIMIT=-1');
    $_ENV['COMPOSER_HOME'] = $composerHomeDir;
    $_ENV['HOME'] = $composerHomeDir;
    $_ENV['COMPOSER_MEMORY_LIMIT'] = '-1';

    $isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    if ($isWindows) {
        $envPrefix = 'set COMPOSER_HOME=' . escapeshellarg($composerHomeDir) . ' && set HOME=' . escapeshellarg($composerHomeDir) . ' && set COMPOSER_MEMORY_LIMIT=-1 && ';
    } else {
        $envPrefix = 'export COMPOSER_HOME=' . escapeshellarg($composerHomeDir) . ' && export HOME=' . escapeshellarg($composerHomeDir) . ' && export COMPOSER_MEMORY_LIMIT=-1 && ';
    }

    $send('info', 'Composer found: ' . $composerBin);
    $send('info', 'Running: composer install --no-dev --optimize-autoloader --ignore-platform-reqs ...');

    $cmd = $envPrefix . $composerBin . ' install --no-dev --optimize-autoloader --ignore-platform-reqs --no-interaction --working-dir=' . escapeshellarg(BASE_PATH) . ' 2>&1';

    $proc = false;
    if (function_exists('popen')) {
        $proc = @popen($cmd, 'r');
    }

    if ($proc) {
        while (!feof($proc)) {
            $line = fgets($proc, 4096);
            if ($line !== false && trim($line) !== '') {
                $send('output', trim($line));
            }
        }
        $exitCode = pclose($proc);
    } elseif (function_exists('exec')) {
        $outputLines = [];
        $exitCode = 1;
        @exec($cmd, $outputLines, $exitCode);
        foreach ($outputLines as $line) {
            if (trim($line) !== '') {
                $send('output', trim($line));
            }
        }
    } else {
        $send('error', 'Both popen() and exec() are disabled on this PHP configuration. Please ask your host to enable popen() or run "composer install" manually via SSH.');
        $send('done', 'failed');
        exit;
    }

    if ($exitCode === 0 && file_exists(VENDOR_AUTOLOAD)) {
        $send('success', 'Composer install completed successfully!');

        // Auto-create .env from .env.example if .env doesn't exist yet
        $envFile     = BASE_PATH . '/.env';
        $envExample  = BASE_PATH . '/.env.example';
        if (!file_exists($envFile) && file_exists($envExample)) {
            if (copy($envExample, $envFile)) {
                $send('info', '.env file created from .env.example — ready for configuration.');
            } else {
                $send('error', 'Could not create .env file automatically. Please copy .env.example to .env manually.');
            }
        } elseif (file_exists($envFile)) {
            $send('info', '.env file already exists — skipping copy.');
        }

        $send('done', 'ok');
    } else {
        $send('error', 'Composer install failed (exit code: ' . $exitCode . '). Check the output above.');
        $send('done', 'failed');
    }
    exit;
}

// ── AJAX: Check if vendor is ready ──────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'check_vendor') {
    header('Content-Type: application/json');
    echo json_encode(['ready' => file_exists(VENDOR_AUTOLOAD)]);
    exit;
}

// ── HTML Page ────────────────────────────────────────────────────────────────
$hasComposerLock = file_exists(COMPOSER_LOCK);
$hasComposerJson = file_exists(BASE_PATH . '/composer.json');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - InAllCart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50:'#f0f9ff',100:'#e0f2fe',200:'#bae6fd',300:'#7dd3fc',
                            400:'#38bdf8',500:'#0ea5e9',600:'#0284c7',700:'#0369a1',
                            800:'#075985',900:'#0c4a6e'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen">
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">

        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">InAllCart</h1>
            <p class="text-slate-400">E-Commerce Admin Panel</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex justify-between text-sm text-slate-400 mb-2">
                <span>Installation Progress</span>
                <span>0%</span>
            </div>
            <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-primary-500 to-primary-400 transition-all duration-500" style="width:0%"></div>
            </div>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-8 sm:p-12">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-orange-100 rounded-2xl mb-4">
                        <svg class="w-10 h-10 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Install Dependencies</h2>
                    <p class="text-gray-500 max-w-md mx-auto">
                        The <code class="bg-gray-100 px-1.5 py-0.5 rounded text-sm font-mono">vendor</code> folder is missing.
                        Click the button below to run <strong>Composer Install</strong> and download all required PHP packages before setup begins.
                    </p>
                </div>

                <!-- Status icons -->
                <div class="space-y-3 mb-8">
                    <div class="flex items-center justify-between p-3 <?= $hasComposerJson ? 'bg-green-50' : 'bg-red-50' ?> rounded-lg">
                        <span class="text-gray-700">composer.json present</span>
                        <?php if ($hasComposerJson): ?>
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?php else: ?>
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center justify-between p-3 <?= $hasComposerLock ? 'bg-green-50' : 'bg-yellow-50' ?> rounded-lg">
                        <span class="text-gray-700">composer.lock present</span>
                        <?php if ($hasComposerLock): ?>
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?php else: ?>
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?php endif; ?>
                    </div>
                    <div id="vendor-status" class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <span class="text-gray-700">vendor/ dependencies installed</span>
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                </div>

                <!-- Output console (hidden by default) -->
                <div id="console-wrap" class="hidden mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Composer Output</span>
                        <span id="console-status" class="text-xs text-gray-400">Running...</span>
                    </div>
                    <div id="console-output"
                         class="bg-gray-900 rounded-xl p-4 h-56 overflow-y-auto font-mono text-xs text-green-400 space-y-1">
                    </div>
                </div>

                <!-- Actions -->
                <?php if (!$hasComposerJson): ?>
                <div class="text-center">
                    <div class="p-4 bg-red-50 rounded-xl text-red-600 text-sm">
                        <strong>composer.json is missing.</strong> Please re-upload the complete source files from the Envato package.
                    </div>
                </div>
                <?php else: ?>
                <div id="action-area" class="flex flex-col items-center gap-4">
                    <button id="run-btn" onclick="runComposer()"
                            class="inline-flex items-center px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-600/30">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Run Composer Install
                    </button>
                    <p class="text-xs text-gray-400 text-center">This may take 1–3 minutes depending on your server speed.</p>
                </div>

                <div id="success-area" class="hidden flex-col items-center gap-4 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-2xl mb-2">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-gray-700 font-medium">Dependencies installed successfully!</p>
                    <a id="continue-btn" href="/"
                       class="inline-flex items-center px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-green-600/30">
                        Continue to Installation
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-slate-500 text-sm">
            &copy; <?= date('Y') ?> InAllCart. All rights reserved.
        </div>
    </div>
</div>

<script>
function runComposer() {
    const runBtn     = document.getElementById('run-btn');
    const actionArea = document.getElementById('action-area');
    const consoleWrap = document.getElementById('console-wrap');
    const output     = document.getElementById('console-output');
    const statusEl   = document.getElementById('console-status');
    const successArea = document.getElementById('success-area');
    const vendorStatus = document.getElementById('vendor-status');

    runBtn.disabled = true;
    runBtn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Running...';
    consoleWrap.classList.remove('hidden');

    const evtSource = new EventSource('?action=run_composer');

    evtSource.onmessage = function(e) {
        const data = JSON.parse(e.data);

        if (data.type === 'output' || data.type === 'info') {
            const line = document.createElement('div');
            line.className = data.type === 'info' ? 'text-blue-400' : 'text-green-300';
            line.textContent = data.msg;
            output.appendChild(line);
            output.scrollTop = output.scrollHeight;
        }

        if (data.type === 'error') {
            const line = document.createElement('div');
            line.className = 'text-red-400 font-bold';
            line.textContent = '✗ ' + data.msg;
            output.appendChild(line);
            output.scrollTop = output.scrollHeight;
        }

        if (data.type === 'success') {
            const line = document.createElement('div');
            line.className = 'text-green-400 font-bold';
            line.textContent = '✓ ' + data.msg;
            output.appendChild(line);
            output.scrollTop = output.scrollHeight;
        }

        if (data.type === 'done') {
            evtSource.close();
            if (data.msg === 'ok') {
                statusEl.textContent = 'Completed ✓';
                statusEl.className = 'text-xs text-green-500';
                // Update vendor status icon
                vendorStatus.className = 'flex items-center justify-between p-3 bg-green-50 rounded-lg';
                vendorStatus.querySelector('svg').outerHTML = '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                actionArea.classList.add('hidden');
                successArea.classList.remove('hidden');
                successArea.classList.add('flex');
            } else {
                statusEl.textContent = 'Failed ✗';
                statusEl.className = 'text-xs text-red-500';
                runBtn.disabled = false;
                runBtn.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Retry Composer Install';
            }
        }
    };

    evtSource.onerror = function() {
        evtSource.close();
        statusEl.textContent = 'Connection lost';
        statusEl.className = 'text-xs text-red-500';
        const line = document.createElement('div');
        line.className = 'text-red-400';
        line.textContent = 'Connection to server lost. Please refresh and try again.';
        output.appendChild(line);
        runBtn.disabled = false;
        runBtn.innerHTML = 'Retry Composer Install';
    };
}
</script>
</body>
</html>

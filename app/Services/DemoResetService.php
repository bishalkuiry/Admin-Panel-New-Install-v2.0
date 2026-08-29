<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Exception;

class DemoResetService
{
    protected string $baselineDir;
    protected string $sqlFilePath;
    protected string $publicBaselineDir;
    protected string $currentPublicDir;

    public function __construct()
    {
        $this->baselineDir = storage_path('app/demo_baseline');
        $this->sqlFilePath = $this->baselineDir . '/database.sql';
        $this->publicBaselineDir = $this->baselineDir . '/public';
        $this->currentPublicDir = storage_path('app/public');
    }

    /**
     * Check if DEMO_MODE is enabled in .env or config
     */
    public function isDemoModeEnabled(): bool
    {
        return filter_var(env('DEMO_MODE', config('app.demo_mode', true)), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get baseline storage paths and metadata
     */
    public function getBaselineInfo(): array
    {
        $hasSql = File::exists($this->sqlFilePath);
        $hasPublic = File::isDirectory($this->publicBaselineDir);
        $isConfigured = $hasSql && $hasPublic;

        $lastReset = Setting::get('demo_last_reset_at');
        $baselineCreated = Setting::get('demo_baseline_created_at');
        $interval = Setting::get('demo_reset_interval', '45'); // minutes
        $enabledSetting = Setting::get('demo_reset_enabled', '1'); // '1' or '0'
        $demoModeEnv = $this->isDemoModeEnabled();

        return [
            'is_configured'        => $isConfigured,
            'has_sql'               => $hasSql,
            'has_public'            => $hasPublic,
            'sql_file_size'         => $hasSql ? File::size($this->sqlFilePath) : 0,
            'last_reset_at'        => $lastReset,
            'baseline_created_at'  => $baselineCreated,
            'reset_interval'       => (int) $interval,
            'demo_mode_env'        => $demoModeEnv,
            'is_enabled'            => $demoModeEnv && ($enabledSetting === '1'),
            'sql_path'              => $this->sqlFilePath,
            'baseline_dir'          => $this->baselineDir,
        ];
    }

    /**
     * Save current DB + uploaded images as the pristine baseline snapshot
     */
    public function saveBaseline(): bool
    {
        try {
            if (!File::exists($this->baselineDir)) {
                File::makeDirectory($this->baselineDir, 0755, true);
            }

            // 1. Export MySQL Database to database.sql
            $this->dumpDatabase($this->sqlFilePath);

            // 2. Clone current storage/app/public directory to demo_baseline/public
            if (!File::exists($this->publicBaselineDir)) {
                File::makeDirectory($this->publicBaselineDir, 0755, true);
            }

            if (File::isDirectory($this->currentPublicDir)) {
                File::cleanDirectory($this->publicBaselineDir);
                $this->safeCopyDirectory($this->currentPublicDir, $this->publicBaselineDir);
            }

            Setting::set('demo_baseline_created_at', now()->toDateTimeString(), 'demo');
            Log::info('DemoResetService: Baseline snapshot successfully saved.');

            return true;
        } catch (Exception $e) {
            Log::error('DemoResetService::saveBaseline failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restore database and uploaded files from pristine baseline snapshot
     */
    public function restoreBaseline(): bool
    {
        try {
            // Auto-create baseline snapshot if one does not exist yet
            if (!File::exists($this->sqlFilePath)) {
                Log::info('DemoResetService: Baseline SQL not found. Capturing baseline before reset...');
                $this->saveBaseline();
            }

            // 1. Preserve pre-existing & newly registered customer accounts in users table
            $existingUsers = [];
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
                    $existingUsers = DB::table('users')->get()->map(fn($u) => (array) $u)->toArray();
                }
            } catch (Exception $ue) {
                Log::warning('DemoResetService: User preservation notice: ' . $ue->getMessage());
            }

            // 2. Import Database SQL (Restores products, categories, stores, settings, etc.)
            $this->importDatabase($this->sqlFilePath);

            // 3. Re-insert preserved user accounts so testing customers are never lost
            if (!empty($existingUsers)) {
                try {
                    foreach (array_chunk($existingUsers, 50) as $chunk) {
                        DB::table('users')->insertOrIgnore($chunk);
                    }
                } catch (Exception $re) {
                    Log::warning('DemoResetService: Re-inserting users notice: ' . $re->getMessage());
                }
            }

            // 4. Restore Public Storage Images/Files
            if (File::isDirectory($this->publicBaselineDir)) {
                if (!File::isDirectory($this->currentPublicDir)) {
                    File::makeDirectory($this->currentPublicDir, 0755, true);
                }
                File::cleanDirectory($this->currentPublicDir);
                $this->safeCopyDirectory($this->publicBaselineDir, $this->currentPublicDir);
            }

            // 3. Clear Laravel Application Caches
            Cache::flush();
            try {
                Artisan::call('cache:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
            } catch (Exception $ce) {
                Log::warning('DemoResetService: Cache clear artisan warning: ' . $ce->getMessage());
            }

            // 4. Record last reset timestamp
            Setting::set('demo_last_reset_at', now()->toDateTimeString(), 'demo');
            Log::info('DemoResetService: Demo environment successfully restored to baseline state.');

            return true;
        } catch (Exception $e) {
            Log::error('DemoResetService::restoreBaseline failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export database tables and contents to SQL file using PDO
     */
    protected function dumpDatabase(string $outputFile): void
    {
        $pdo = DB::getPdo();
        $handle = fopen($outputFile, 'w+');

        if (!$handle) {
            throw new Exception("Unable to create dump file at {$outputFile}");
        }

        fwrite($handle, "-- Demo Baseline Database Export\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n");

        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Drop table statement
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            // Create table statement
            $createTableStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $createSql = $createTableStmt['Create Table'] ?? $createTableStmt['Create View'] ?? null;
            if ($createSql) {
                fwrite($handle, $createSql . ";\n\n");
            }

            // Dump data rows in chunks for memory safety
            $rows = $pdo->query("SELECT * FROM `{$table}`");
            while ($row = $rows->fetch(\PDO::FETCH_NUM)) {
                $values = array_map(function ($val) use ($pdo) {
                    if ($val === null) {
                        return 'NULL';
                    }
                    return $pdo->quote($val);
                }, $row);

                fwrite($handle, "INSERT INTO `{$table}` VALUES (" . implode(',', $values) . ");\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    /**
     * Import database SQL file into MySQL using PDO
     */
    protected function importDatabase(string $inputFile): void
    {
        if (!File::exists($inputFile)) {
            throw new Exception("Baseline SQL file not found at {$inputFile}");
        }

        $sql = File::get($inputFile);
        $pdo = DB::getPdo();

        $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');

        // Process SQL in queries
        $queries = $this->splitSqlStatements($sql);
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                try {
                    $pdo->exec($query);
                } catch (Exception $eq) {
                    Log::warning("DemoResetService: Query execution notice: " . $eq->getMessage());
                }
            }
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Split raw SQL dump string into individual executable queries safely
     */
    protected function splitSqlStatements(string $sql): array
    {
        $queries = [];
        $length = strlen($sql);
        $currentQuery = '';
        $inString = false;
        $stringChar = '';
        $inComment = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $nextChar = $i + 1 < $length ? $sql[$i + 1] : '';

            // Handle line comments (-- or #)
            if (!$inString && !$inComment) {
                if (($char === '-' && $nextChar === '-') || $char === '#') {
                    $inComment = true;
                    continue;
                }
            }

            if ($inComment) {
                if ($char === "\n") {
                    $inComment = false;
                }
                continue;
            }

            // Handle strings (' or ")
            if (($char === "'" || $char === '"') && ($i === 0 || $sql[$i - 1] !== '\\')) {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($stringChar === $char) {
                    $inString = false;
                }
            }

            if ($char === ';' && !$inString) {
                $queries[] = $currentQuery;
                $currentQuery = '';
                continue;
            }

            $currentQuery .= $char;
        }

        if (trim($currentQuery) !== '') {
            $queries[] = $currentQuery;
        }

        return $queries;
    }

    /**
     * Locate binary path if exists in OS
     */
    protected function findExecutable(string $name): ?string
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $cmd = $isWindows ? "where {$name}" : "which {$name}";

        @exec($cmd, $output, $returnVar);

        if ($returnVar === 0 && !empty($output[0])) {
            return trim($output[0]);
        }

        // Try standard XAMPP paths on Windows
        if ($isWindows) {
            $xamppPath = "C:\\xampp\\mysql\\bin\\{$name}.exe";
            if (File::exists($xamppPath)) {
                return $xamppPath;
            }
        }

        return null;
    }

    /**
     * Safely copy directory recursively, ignoring missing/broken files or transient links
     */
    protected function safeCopyDirectory(string $source, string $destination): void
    {
        if (!File::isDirectory($source)) {
            return;
        }

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $subPath = $iterator->getSubPathName();
                $targetPath = $destination . DIRECTORY_SEPARATOR . $subPath;

                try {
                    if ($item->isDir()) {
                        if (!File::exists($targetPath)) {
                            File::makeDirectory($targetPath, 0755, true);
                        }
                    } else {
                        if (file_exists($item->getPathname())) {
                            @copy($item->getPathname(), $targetPath);
                        }
                    }
                } catch (Exception $ex) {
                    Log::warning("DemoResetService: Copy skip notice for {$subPath}: " . $ex->getMessage());
                }
            }
        } catch (Exception $e) {
            Log::warning("DemoResetService: Directory traversal notice for {$source}: " . $e->getMessage());
        }
    }
}

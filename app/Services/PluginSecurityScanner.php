<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use ZipArchive;

class PluginSecurityScanner
{
    /**
     * Blocked file extensions
     */
    protected array $blockedExtensions = [
        '.exe', '.sh', '.bat', '.cmd', '.com', '.pif', '.scr', '.vbs',
        '.js', '.jar', '.app', '.deb', '.rpm', '.dmg', '.pkg',
    ];

    /**
     * Suspicious patterns in PHP code
     */
    protected array $suspiciousPatterns = [
        '/eval\s*\(/i',
        '/base64_decode\s*\(/i',
        '/exec\s*\(/i',
        '/shell_exec\s*\(/i',
        '/system\s*\(/i',
        '/passthru\s*\(/i',
        '/proc_open\s*\(/i',
        '/popen\s*\(/i',
        '/curl_exec\s*\(/i',
        '/curl_multi_exec\s*\(/i',
        '/parse_ini_file\s*\(/i',
        '/show_source\s*\(/i',
        '/file_get_contents\s*\(\s*[\'"]https?:\/\//i',
    ];

    /**
     * Maximum file size per file (10MB)
     */
    protected int $maxFileSize = 10 * 1024 * 1024;

    /**
     * Scan ZIP file for security threats
     */
    public function scanZipFile(ZipArchive $zip): array
    {
        $threats = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $filename = $stat['name'];

            // Skip directories
            if (substr($filename, -1) === '/') {
                continue;
            }

            // Check for path traversal
            if ($this->hasPathTraversal($filename)) {
                $threats[] = "Path traversal detected: {$filename}";
            }

            // Check for blocked extensions
            if ($this->hasBlockedExtension($filename)) {
                $threats[] = "Blocked file type: {$filename}";
            }

            // Check file size
            if ($stat['size'] > $this->maxFileSize) {
                $threats[] = "File too large: {$filename} (" . $this->formatBytes($stat['size']) . ")";
            }

            // Check for hidden files (except .gitignore, .editorconfig)
            if ($this->isSuspiciousHiddenFile($filename)) {
                $threats[] = "Suspicious hidden file: {$filename}";
            }
        }

        return $threats;
    }

    /**
     * Scan extracted plugin directory for threats
     */
    public function scanPluginDirectory(string $path): array
    {
        $threats = [];

        // Scan PHP files for suspicious code
        $phpFiles = File::allFiles($path);
        
        foreach ($phpFiles as $file) {
            if ($file->getExtension() === 'php') {
                $content = File::get($file->getPathname());
                
                // Check for suspicious patterns
                foreach ($this->suspiciousPatterns as $pattern) {
                    if (preg_match($pattern, $content)) {
                        $threats[] = "Suspicious code pattern in: {$file->getFilename()} (pattern: {$pattern})";
                    }
                }

                // Validate PHP syntax
                $syntaxError = $this->validatePHPSyntax($file->getPathname());
                if ($syntaxError) {
                    $threats[] = "PHP syntax error in: {$file->getFilename()} - {$syntaxError}";
                }
            }
        }

        return $threats;
    }

    /**
     * Check for path traversal attempts
     */
    protected function hasPathTraversal(string $filename): bool
    {
        return str_contains($filename, '..') || 
               str_starts_with($filename, '/') ||
               str_contains($filename, '\\..') ||
               str_contains($filename, '../');
    }

    /**
     * Check if file has blocked extension
     */
    protected function hasBlockedExtension(string $filename): bool
    {
        $filename = strtolower($filename);
        
        foreach ($this->blockedExtensions as $ext) {
            if (str_ends_with($filename, $ext)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if hidden file is suspicious
     */
    protected function isSuspiciousHiddenFile(string $filename): bool
    {
        $basename = basename($filename);
        
        // Allow common hidden files
        $allowed = ['.gitignore', '.editorconfig', '.env.example'];
        
        if (in_array($basename, $allowed)) {
            return false;
        }

        // Flag other hidden files
        return str_starts_with($basename, '.');
    }

    /**
     * Validate PHP file syntax
     * Falls back to token-based check when exec() is disabled (shared hosting).
     */
    protected function validatePHPSyntax(string $filepath): ?string
    {
        // Use exec() only when it is available and not in the disabled_functions list
        if ($this->isExecAvailable()) {
            $output = [];
            $return = 0;
            exec('php -l ' . escapeshellarg($filepath) . ' 2>&1', $output, $return);
            if ($return !== 0) {
                return implode("\n", $output);
            }
            return null;
        }

        // Fallback: token_get_all() catches most fatal parse errors without exec()
        $source = @file_get_contents($filepath);
        if ($source === false) {
            return null; // Can't read — skip
        }

        // Suppress the E_COMPILE_ERROR that token_get_all emits on bad syntax
        set_error_handler(function () { return true; });
        try {
            token_get_all($source, TOKEN_PARSE);
        } catch (\ParseError $e) {
            restore_error_handler();
            return $e->getMessage();
        } finally {
            restore_error_handler();
        }

        return null;
    }

    /**
     * Check whether exec() is callable in the current environment.
     */
    protected function isExecAvailable(): bool
    {
        if (!function_exists('exec')) {
            return false;
        }

        $disabled = array_map(
            'trim',
            explode(',', ini_get('disable_functions') ?: '')
        );

        return !in_array('exec', $disabled, true);
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if plugin namespace is safe
     */
    public function validateNamespace(string $namespace): bool
    {
        // Must start with Plugins\
        if (!str_starts_with($namespace, 'Plugins\\')) {
            return false;
        }

        // Must not contain dangerous patterns
        $dangerous = ['..', '/', '\\\\', 'App\\', 'Illuminate\\', 'Laravel\\'];
        
        foreach ($dangerous as $pattern) {
            if (str_contains($namespace, $pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Scan for potential conflicts with existing plugins
     */
    public function checkConflicts(array $manifest, array $existingPlugins): array
    {
        $conflicts = [];

        foreach ($existingPlugins as $existing) {
            // Check namespace conflicts
            if (isset($manifest['service_provider']) && isset($existing['namespace'])) {
                $newNamespace = substr($manifest['service_provider'], 0, strrpos($manifest['service_provider'], '\\'));
                
                if ($newNamespace === $existing['namespace']) {
                    $conflicts[] = "Namespace conflict with plugin: {$existing['display_name']}";
                }
            }

            // Check hook conflicts (same hook with same priority)
            if (isset($manifest['hooks']) && isset($existing['hooks'])) {
                $commonHooks = array_intersect_key($manifest['hooks'], $existing['hooks']);
                
                if (!empty($commonHooks)) {
                    $conflicts[] = "Hook conflict with plugin '{$existing['display_name']}': " . implode(', ', array_keys($commonHooks));
                }
            }
        }

        return $conflicts;
    }
}

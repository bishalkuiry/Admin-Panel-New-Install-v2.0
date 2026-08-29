<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * StorageService
 *
 * Single source of truth for all file storage operations.
 * Automatically routes uploads to the configured disk (local 'public' or S3),
 * and generates correct public URLs regardless of the active driver.
 *
 * Usage:
 *   app(StorageService::class)->store($file, 'products')
 *   app(StorageService::class)->url('products/image.jpg')
 *   app(StorageService::class)->delete('products/image.jpg')
 */
class StorageService
{
    /**
     * The active disk name.
     * - 'public'  → local storage/app/public  (served via /storage symlink)
     * - 's3'      → Amazon S3 (or any S3-compatible service)
     */
    public function disk(): string
    {
        $configured = config('filesystems.default', 'local');

        // Normalise: anything other than 's3' routes to the public local disk
        return $configured === 's3' ? 's3' : 'public';
    }

    /**
     * Store an uploaded file and return the stored path (relative, no leading slash).
     *
     * @param  UploadedFile  $file
     * @param  string        $folder   e.g. 'products', 'stores/logos'
     * @param  string|null   $filename Optional custom filename (without extension)
     * @return string                  Stored path, e.g. 'products/uuid.jpg'
     */
    public function store(UploadedFile $file, string $folder, ?string $filename = null): string
    {
        $name = ($filename ?? Str::uuid()->toString()) . '.' . $file->getClientOriginalExtension();
        $disk = $this->disk();

        if ($disk === 's3') {
            try {
                return $file->storeAs($folder, $name, [
                    'disk'       => 's3',
                    'visibility' => 'public',
                ]);
            } catch (Throwable $e) {
                Log::error("StorageService store error on S3 disk, falling back to public disk: " . $e->getMessage());
                return $file->storeAs($folder, $name, 'public');
            }
        }

        return $file->storeAs($folder, $name, 'public');
    }

    /**
     * Store raw content (string/binary) at a given path.
     *
     * @param  string  $path     Full path including filename, e.g. 'onboarding/img.png'
     * @param  string  $content  Raw file content
     * @return bool
     */
    public function put(string $path, string $content): bool
    {
        $disk = $this->disk();

        if ($disk === 's3') {
            try {
                return Storage::disk('s3')->put($path, $content, 'public');
            } catch (Throwable $e) {
                Log::error("StorageService put error on S3 disk, falling back to public disk: " . $e->getMessage());
                return Storage::disk('public')->put($path, $content);
            }
        }

        return Storage::disk('public')->put($path, $content);
    }

    /**
     * Delete a file by its stored path.
     *
     * @param  string|null  $path  Relative path as stored in DB, e.g. 'products/uuid.jpg'
     * @return bool
     */
    public function delete(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Strip any accidental leading /storage/ prefix that old code may have stored
        $path = $this->normalisePath($path);
        $disk = $this->disk();

        if ($disk === 's3') {
            try {
                return Storage::disk('s3')->delete($path);
            } catch (Throwable $e) {
                Log::error("StorageService delete error on S3 disk, attempting public disk: " . $e->getMessage());
                return Storage::disk('public')->delete($path);
            }
        }

        return Storage::disk('public')->delete($path);
    }

    /**
     * Check whether a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        $path = $this->normalisePath($path);
        try {
            return Storage::disk($this->disk())->exists($path);
        } catch (Throwable $e) {
            Log::error("StorageService exists error on disk {$this->disk()}: " . $e->getMessage());
            if ($this->disk() === 's3') {
                try {
                    return Storage::disk('public')->exists($path);
                } catch (Throwable $ex) {
                    return false;
                }
            }
            return false;
        }
    }

    /**
     * Generate a fully-qualified public URL for a stored path.
     *
     * For S3 this returns the S3 (or CDN) URL.
     * For local it returns the /storage/... URL.
     *
     * @param  string|null  $path  Relative path as stored in DB
     * @return string|null
     */
    public function url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Already an absolute URL (e.g. stored by old code) — return directly
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = $this->normalisePath($path);

        try {
            return Storage::disk($this->disk())->url($path);
        } catch (Throwable $e) {
            Log::error("StorageService url error on disk {$this->disk()}: " . $e->getMessage());
            return Storage::disk('public')->url($path);
        }
    }

    /**
     * List all files in a folder on the active disk.
     * Passing an empty directory lists everything (works for both local and S3).
     *
     * @param  string  $directory
     * @return array
     */
    public function allFiles(string $directory = ''): array
    {
        try {
            // S3 does not support listing from '/' — pass empty string for root
            return Storage::disk($this->disk())->allFiles($directory);
        } catch (Throwable $e) {
            Log::error("StorageService allFiles error on disk {$this->disk()}: " . $e->getMessage());
            if ($this->disk() === 's3') {
                try {
                    return Storage::disk('public')->allFiles($directory);
                } catch (Throwable $ex) {
                    return [];
                }
            }
            return [];
        }
    }

    /**
     * Get file size in bytes.
     *
     * @param  string  $path
     * @return int
     */
    public function size(string $path): int
    {
        try {
            return Storage::disk($this->disk())->size($this->normalisePath($path));
        } catch (Throwable $e) {
            Log::error("StorageService size error on disk {$this->disk()}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get file MIME type.
     *
     * @param  string  $path
     * @return string|null
     */
    public function mimeType(string $path): ?string
    {
        try {
            return Storage::disk($this->disk())->mimeType($this->normalisePath($path));
        } catch (Throwable $e) {
            Log::error("StorageService mimeType error on disk {$this->disk()}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get last modified timestamp.
     *
     * @param  string  $path
     * @return int
     */
    public function lastModified(string $path): int
    {
        try {
            return Storage::disk($this->disk())->lastModified($this->normalisePath($path));
        } catch (Throwable $e) {
            Log::error("StorageService lastModified error on disk {$this->disk()}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Strip legacy /storage/ prefix that some old code stored in the DB.
     * Paths in the DB should always be relative (e.g. 'products/uuid.jpg').
     * If a full URL is passed (e.g. https://...) it is returned as-is so
     * callers that already have an absolute URL don't get mangled.
     */
    public function normalisePath(string $path): string
    {
        // Already an absolute URL — nothing to strip
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return ltrim(str_replace('/storage/', '/', $path), '/');
    }
}

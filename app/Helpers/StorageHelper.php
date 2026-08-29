<?php

if (!function_exists('storage_url')) {
    /**
     * Generate a public URL for a stored file path.
     * Works with both local public disk and S3.
     *
     * @param  string|null  $path  Relative path as stored in DB (e.g. 'products/uuid.jpg')
     * @return string|null
     */
    function storage_url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return app(\App\Services\StorageService::class)->url($path);
    }
}

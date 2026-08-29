<?php

namespace App\Helpers;

use App\Models\Setting;

class DemoHelper
{
    /**
     * Keys related to Maintenance Mode & Force Update settings
     * that must never be altered or deleted when DEMO_MODE is active.
     */
    protected static array $restrictedSettingKeys = [
        'maintenance_mode',
        'maintenance_title',
        'maintenance_message',
        'maintenance_image',
        'app_force_update',
        'app_min_version',
        'app_latest_version',
        'app_play_store_url',
        'app_app_store_url',
    ];

    /**
     * Check if DEMO_MODE is enabled across environment, config, or database settings.
     */
    public static function isDemoMode(): bool
    {
        $envDemo = env('DEMO_MODE', null);
        if ($envDemo !== null) {
            return filter_var($envDemo, FILTER_VALIDATE_BOOLEAN);
        }

        $configDemo = config('app.demo_mode', null);
        if ($configDemo !== null) {
            return filter_var($configDemo, FILTER_VALIDATE_BOOLEAN);
        }

        return Setting::get('demo_mode', '0') === '1';
    }

    /**
     * Check if a given setting key is restricted from modification in Demo Mode.
     */
    public static function isRestrictedSettingKey(string $key): bool
    {
        return in_array($key, static::$restrictedSettingKeys, true);
    }

    /**
     * Get array of all restricted setting keys.
     */
    public static function getRestrictedSettingKeys(): array
    {
        return static::$restrictedSettingKeys;
    }
}

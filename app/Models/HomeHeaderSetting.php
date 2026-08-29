<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class HomeHeaderSetting extends Model
{
    protected $fillable = ['tabs_active', 'background_active', 'cards_active', 'cards_horizontal', 'tabs_horizontal_style', 'module_icon_style'];

    protected $casts = [
        'tabs_active' => 'boolean',
        'background_active' => 'boolean',
        'cards_active' => 'boolean',
        'cards_horizontal' => 'boolean',
        'tabs_horizontal_style' => 'boolean',
    ];

    /**
     * Get the singleton settings instance
     */
    public static function getSettings(): self
    {
        return Cache::remember('home_header_settings', 3600, function () {
            return self::first() ?? self::create([
                'tabs_active' => false,
                'background_active' => false,
                'cards_active' => false,
                'cards_horizontal' => false,
                'tabs_horizontal_style' => false,
                'module_icon_style' => 'image_and_name',
            ]);
        });
    }

    /**
     * Update settings and clear cache
     */
    public static function updateSettings(array $data): self
    {
        $settings = self::first() ?? new self();
        $settings->fill($data);
        $settings->save();
        
        Cache::forget('home_header_settings');
        Cache::forget('home_header_config');
        
        return $settings;
    }
}

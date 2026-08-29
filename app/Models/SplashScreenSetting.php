<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SplashScreenSetting extends Model
{
    use HasFactory;

    protected $table = 'splash_screen_settings';

    protected $fillable = [
        'active_screen_style',
        'logo_url',
        'fullscreen_media_url',
        'logo_animation',
        'logo_size',
        'logo_size_px',
        'background_style',
        'primary_color',
        'secondary_color',
        'background_color',
        'title_text',
        'subtitle_text',
        'tagline_text',
        'text_color',
        'show_tagline',
        'show_loading_bar',
        'custom_css',
        'is_active',
    ];

    protected $casts = [
        'active_screen_style' => 'integer',
        'logo_size_px' => 'integer',
        'show_tagline' => 'boolean',
        'show_loading_bar' => 'boolean',
        'is_active' => 'boolean',
    ];
}

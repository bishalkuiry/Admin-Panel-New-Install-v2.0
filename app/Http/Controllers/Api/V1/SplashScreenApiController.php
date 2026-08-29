<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SplashScreenSetting;
use Illuminate\Http\Request;

class SplashScreenApiController extends Controller
{
    /**
     * Get Dynamic Splash Screen Configuration for Mobile App
     */
    public function getSplashConfig()
    {
        $setting = SplashScreenSetting::first();

        if (!$setting) {
            $setting = (object) [
                'active_screen_style' => 1,
                'logo_url' => null,
                'logo_animation' => 'pulse',
                'background_style' => 'gradient_vibrant',
                'primary_color' => '#F97316',
                'secondary_color' => '#EA580C',
                'background_color' => '#0F172A',
                'title_text' => 'InAllCart',
                'subtitle_text' => 'Everything Delivered to Your Doorstep',
                'tagline_text' => 'Fast · Reliable · Premium',
                'text_color' => '#FFFFFF',
                'show_tagline' => true,
                'show_loading_bar' => true,
                'is_active' => true,
            ];
        }

        $logoFullUrl = null;
        if (!empty($setting->logo_url)) {
            $logoFullUrl = filter_var($setting->logo_url, FILTER_VALIDATE_URL)
                ? $setting->logo_url
                : asset($setting->logo_url);
        }

        $fullscreenMediaFullUrl = null;
        if (!empty($setting->fullscreen_media_url)) {
            $fullscreenMediaFullUrl = filter_var($setting->fullscreen_media_url, FILTER_VALIDATE_URL)
                ? $setting->fullscreen_media_url
                : asset($setting->fullscreen_media_url);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'active_screen_style' => (int) ($setting->active_screen_style ?? 1),
                'logo_url' => $logoFullUrl,
                'fullscreen_media_url' => $fullscreenMediaFullUrl,
                'logo_animation' => $setting->logo_animation ?? 'pulse',
                'logo_size' => $setting->logo_size ?? 'medium',
                'logo_size_px' => !empty($setting->logo_size_px) ? (int) $setting->logo_size_px : null,
                'background_style' => $setting->background_style ?? 'gradient_vibrant',
                'primary_color' => $setting->primary_color ?? '#F97316',
                'secondary_color' => $setting->secondary_color ?? '#EA580C',
                'background_color' => $setting->background_color ?? '#0F172A',
                'title_text' => $setting->title_text ?? null,
                'subtitle_text' => $setting->subtitle_text ?? null,
                'tagline_text' => $setting->tagline_text ?? 'Fast · Reliable · Premium',
                'text_color' => $setting->text_color ?? '#FFFFFF',
                'show_tagline' => (bool) ($setting->show_tagline ?? true),
                'show_loading_bar' => (bool) ($setting->show_loading_bar ?? true),
                'is_active' => (bool) ($setting->is_active ?? true),
            ]
        ]);
    }
}

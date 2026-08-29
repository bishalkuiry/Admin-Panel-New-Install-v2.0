<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SplashScreenSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SplashScreenController extends Controller
{
    /**
     * Display the Splash Screen Builder UI
     */
    public function index()
    {
        $setting = SplashScreenSetting::firstOrCreate(
            ['id' => 1],
            [
                'active_screen_style' => 1,
                'logo_animation' => 'pulse',
                'logo_size' => 'medium',
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
            ]
        );

        return view('admin.splash.builder', compact('setting'));
    }

    /**
     * Save Splash Screen Builder Configuration
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'active_screen_style' => 'required|integer|in:1,2',
            'logo_file' => 'nullable|image|max:5120',
            'logo_url' => 'nullable|string|max:500',
            'fullscreen_media_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'fullscreen_media_url' => 'nullable|string|max:500',
            'logo_animation' => 'required|string|in:pulse,bounce,scale_fade,rotating_crown,shimmer_sheen',
            'logo_size' => 'required|string|in:small,medium,extra_medium,large,extra_large,custom',
            'logo_size_px' => 'nullable|integer|min:30|max:500',
            'background_style' => 'required|string|in:gradient_vibrant,dark_glassmorphic,solid_brand,geometric_particles,floating_rings',
            'primary_color' => 'required|string|max:10',
            'secondary_color' => 'required|string|max:10',
            'background_color' => 'required|string|max:10',
            'title_text' => 'nullable|string|max:100',
            'subtitle_text' => 'nullable|string|max:255',
            'tagline_text' => 'nullable|string|max:255',
            'text_color' => 'required|string|max:10',
            'show_tagline' => 'nullable|boolean',
            'show_loading_bar' => 'nullable|boolean',
        ]);

        $setting = SplashScreenSetting::firstOrCreate(['id' => 1]);

        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('splash', 'public');
            $validated['logo_url'] = Storage::url($path);
        }

        if ($request->hasFile('fullscreen_media_file')) {
            $path = $request->file('fullscreen_media_file')->store('splash', 'public');
            $validated['fullscreen_media_url'] = Storage::url($path);
        }

        $validated['title_text'] = $validated['title_text'] ?? '';
        $validated['subtitle_text'] = $validated['subtitle_text'] ?? '';
        $validated['tagline_text'] = $validated['tagline_text'] ?? '';
        $validated['show_tagline'] = $request->has('show_tagline') ? true : false;
        $validated['show_loading_bar'] = $request->has('show_loading_bar') ? true : false;
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $setting->update($validated);

        return redirect()->back()->with('success', 'Splash Screen Builder settings updated successfully!');
    }
}

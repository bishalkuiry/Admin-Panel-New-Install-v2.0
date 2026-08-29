<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Models\Setting;
use App\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Plugins\Website\Helpers\WebsiteHelper;

class WebsiteSettingsController extends Controller
{
    public function __construct(
        private StorageService $storage
    ) {}

    /**
     * Display the website management and SEO settings page.
     */
    public function index()
    {
        if (!$this->isWebsitePluginActive()) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'Website plugin is not active. Please activate it from Plugins management.');
        }

        $settings = Setting::getAll();

        return view('admin.settings.website', compact('settings'));
    }

    /**
     * Update website management and SEO settings.
     */
    public function update(Request $request)
    {
        if (!$this->isWebsitePluginActive()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Website plugin is not active.'], 403);
            }
            return redirect()->route('admin.settings.index')->with('error', 'Website plugin is not active.');
        }

        $request->validate([
            // Branding & Logos
            'website_header_logo_desktop' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:3072',
            'website_header_logo_mobile'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:3072',
            'website_footer_logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:3072',
            'website_description'         => 'nullable|string|max:1000',
            'website_copyright'           => 'nullable|string|max:500',
            'website_primary_color'       => 'nullable|string|max:20',
            'website_secondary_color'     => 'nullable|string|max:20',

            // SEO Settings
            'website_seo_title'           => 'nullable|string|max:150',
            'website_seo_description'     => 'nullable|string|max:500',
            'website_seo_keywords'        => 'nullable|string|max:500',
            'website_seo_og_image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'website_seo_twitter_handle'  => 'nullable|string|max:50',
            'website_seo_indexing_enabled'=> 'nullable|in:0,1',
        ]);

        try {
            $group = 'website';

            // Text fields
            $textFields = [
                'website_description',
                'website_copyright',
                'website_primary_color',
                'website_secondary_color',
                'website_seo_title',
                'website_seo_description',
                'website_seo_keywords',
                'website_seo_twitter_handle',
            ];

            foreach ($textFields as $field) {
                if ($request->has($field)) {
                    Setting::updateOrCreate(
                        ['key' => $field],
                        ['value' => $request->input($field, ''), 'group' => $group]
                    );
                }
            }

            // Checkboxes / Toggles
            $indexingValue = $request->has('website_seo_indexing_enabled') ? '1' : '0';
            Setting::updateOrCreate(
                ['key' => 'website_seo_indexing_enabled'],
                ['value' => $indexingValue, 'group' => $group]
            );

            // File Uploads & Removals: Laptop Header Logo
            if ($request->hasFile('website_header_logo_desktop')) {
                $path = $this->storage->store($request->file('website_header_logo_desktop'), 'website/branding');
                Setting::updateOrCreate(['key' => 'website_header_logo_desktop'], ['value' => $path, 'group' => $group]);
            } elseif ($request->input('remove_website_header_logo_desktop') === '1') {
                $old = Setting::where('key', 'website_header_logo_desktop')->value('value');
                if ($old) { $this->storage->delete($old); }
                Setting::updateOrCreate(['key' => 'website_header_logo_desktop'], ['value' => '', 'group' => $group]);
            }

            // Mobile Header Logo
            if ($request->hasFile('website_header_logo_mobile')) {
                $path = $this->storage->store($request->file('website_header_logo_mobile'), 'website/branding');
                Setting::updateOrCreate(['key' => 'website_header_logo_mobile'], ['value' => $path, 'group' => $group]);
            } elseif ($request->input('remove_website_header_logo_mobile') === '1') {
                $old = Setting::where('key', 'website_header_logo_mobile')->value('value');
                if ($old) { $this->storage->delete($old); }
                Setting::updateOrCreate(['key' => 'website_header_logo_mobile'], ['value' => '', 'group' => $group]);
            }

            // Footer Logo
            if ($request->hasFile('website_footer_logo')) {
                $path = $this->storage->store($request->file('website_footer_logo'), 'website/branding');
                Setting::updateOrCreate(['key' => 'website_footer_logo'], ['value' => $path, 'group' => $group]);
            } elseif ($request->input('remove_website_footer_logo') === '1') {
                $old = Setting::where('key', 'website_footer_logo')->value('value');
                if ($old) { $this->storage->delete($old); }
                Setting::updateOrCreate(['key' => 'website_footer_logo'], ['value' => '', 'group' => $group]);
            }

            // OpenGraph Social Image
            if ($request->hasFile('website_seo_og_image')) {
                $path = $this->storage->store($request->file('website_seo_og_image'), 'website/seo');
                Setting::updateOrCreate(['key' => 'website_seo_og_image'], ['value' => $path, 'group' => $group]);
            } elseif ($request->input('remove_website_seo_og_image') === '1') {
                $old = Setting::where('key', 'website_seo_og_image')->value('value');
                if ($old) { $this->storage->delete($old); }
                Setting::updateOrCreate(['key' => 'website_seo_og_image'], ['value' => '', 'group' => $group]);
            }

            // Clear App & Website Caches
            Cache::flush();
            if (class_exists(WebsiteHelper::class)) {
                WebsiteHelper::clearCache();
            }

            try {
                Artisan::call('config:clear');
            } catch (\Throwable $e) {}

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Website settings and SEO saved successfully!',
                ]);
            }

            return back()->with('success', 'Website settings and SEO saved successfully!');
        } catch (\Exception $e) {
            Log::error('WebsiteSettings update error: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to save settings: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Failed to save settings: ' . $e->getMessage());
        }
    }

    /**
     * Clear website caches explicitly.
     */
    public function clearCache(Request $request)
    {
        try {
            Cache::flush();
            if (class_exists(WebsiteHelper::class)) {
                WebsiteHelper::clearCache();
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Website cache cleared successfully!']);
            }

            return back()->with('success', 'Website cache cleared successfully!');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to clear cache: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Helper to check if website plugin is installed & active.
     */
    private function isWebsitePluginActive(): bool
    {
        try {
            return Plugin::where('name', 'website')
                ->where('is_active', true)
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }
}

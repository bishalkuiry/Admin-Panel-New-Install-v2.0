<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\StaticPage;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = StaticPage::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Pull the same settings the website plugin uses
        $keys     = ['app_name', 'app_logo', 'app_favicon', 'website_primary_color', 'website_footer_text', 'currency_symbol'];
        $raw      = Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();
        $settings = [
            'app_name'      => $raw['app_name']      ?? config('app.name', 'Store'),
            'app_logo'      => $raw['app_logo']       ?? '',
            'app_favicon'   => $raw['app_favicon']    ?? '',
            'primary_color' => $raw['website_primary_color'] ?? '#f97316',
            'footer_text'   => $raw['website_footer_text']   ?? '',
        ];

        return view('page', compact('page', 'settings'));
    }
}

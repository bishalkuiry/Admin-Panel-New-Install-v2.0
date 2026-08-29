<?php

namespace App\View\Composers;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

/**
 * Supplies branding values (app name, favicon, sidebar logo) to the admin
 * layout. Replaces the direct `Setting::where()` queries that previously
 * lived inside resources/views/admin/layouts/app.blade.php.
 *
 * Results are cached per-request to avoid three round-trips per admin page
 * load. The cache is invalidated whenever settings are updated because
 * SettingsController calls Cache::flush() on save.
 */
class AdminLayoutComposer
{
    /**
     * Settings loaded for the admin layout. These are branding keys only
     * and safe to expose in every admin view.
     *
     * @var array<int, string>
     */
    private const SETTING_KEYS = [
        'app_name',
        'app_favicon',
        'admin_sidebar_logo',
    ];

    private const CACHE_KEY = 'admin_layout_branding_v1';
    private const CACHE_TTL = 300; // 5 minutes

    public function compose(View $view): void
    {
        $values = $this->loadSettings();

        $view->with([
            'adminAppName'      => $values['app_name'] !== '' ? $values['app_name'] : config('app.name', 'InAllCart'),
            'adminFavicon'      => $values['app_favicon'],
            'adminSidebarLogo'  => $values['admin_sidebar_logo'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function loadSettings(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function (): array {
                $rows = Setting::query()
                    ->whereIn('key', self::SETTING_KEYS)
                    ->pluck('value', 'key')
                    ->all();

                $out = [];
                foreach (self::SETTING_KEYS as $key) {
                    $out[$key] = (string) ($rows[$key] ?? '');
                }
                return $out;
            });
        } catch (\Throwable $e) {
            // DB not ready (e.g. fresh install, migrations pending).
            return array_fill_keys(self::SETTING_KEYS, '');
        }
    }
}

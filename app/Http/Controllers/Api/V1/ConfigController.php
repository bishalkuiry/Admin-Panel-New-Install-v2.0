<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Zone;
use App\Models\Plugin;
use App\Models\StaticPage;
use App\Services\CurrencyExchangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ConfigController extends Controller
{
    public function __construct(
        private CurrencyExchangeService $currencyService
    ) {}

    /**
     * Get app configuration for mobile app
     */
    public function app(Request $request): JsonResponse
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'app_name' => $settings['app_name'] ?? config('app.name', 'InAllCart'),
                'app_version' => $settings['app_version'] ?? '1.0.0',
                'support_email' => $settings['support_email'] ?? null,
                'support_phone' => $settings['support_phone'] ?? null,
                'is_demo_mode' => class_exists('\App\Helpers\DemoHelper') ? \App\Helpers\DemoHelper::isDemoMode() : false,
                'demo_otp' => '123456',
                
                // Onboarding
                'onboarding_enabled' => filter_var($settings['onboarding_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'onboarding_screens' => $this->getOnboardingScreens($settings),
                
                // Map Provider
                'map_provider' => $settings['map_provider'] ?? 'osm', // 'google' or 'osm'
                'google_maps_api_key' => $settings['google_maps_api_key'] ?? null,
                
                // Push Notifications
                'push_notification' => [
                    'enabled' => filter_var($settings['push_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'order_updates' => filter_var($settings['push_order_updates'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'promotions' => filter_var($settings['push_promotions'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'new_products' => filter_var($settings['push_new_products'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ],
                
                // Firebase Configuration
                'firebase_database_url' => $settings['firebase_database_url'] ?? null,
                
                // Currency Settings (zone-aware)
                'currency' => $this->getCurrencyConfig($settings, $request),
                
                // Timezone Settings
                'timezone' => $this->getTimezoneConfig($settings),

                // Maintenance Mode
                'maintenance_mode'      => filter_var($settings['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'maintenance_title'     => $settings['maintenance_title'] ?? "We'll be back soon!",
                'maintenance_message'   => $settings['maintenance_message'] ?? 'We are performing scheduled maintenance. Please check back shortly.',
                'maintenance_image_url' => storage_url($settings['maintenance_image'] ?? null),

                // Active Plugins (used by the mobile app to show/hide module buttons)
                'plugins' => $this->getActivePluginsForApp(),

                // Order & Cancellation Configuration (Requirement #20)
                'order_confirmation_by' => $settings['order_confirmation_by'] ?? 'store',
                'driver_cancellation_enabled' => filter_var($settings['driver_cancellation_enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN),
                'driver_cancellation_reasons' => json_decode($settings['driver_cancellation_reasons'] ?? '["Customer unreachable", "Store closed", "Vehicle breakdown", "Address unreachable", "Other"]', true),
                'store_cancellation_enabled' => filter_var($settings['store_cancellation_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN),
                'store_cancellation_reasons' => json_decode($settings['store_cancellation_reasons'] ?? '["Item out of stock", "Store overload", "Closing time", "Invalid order", "Other"]', true),

                // Product Rating & Review Display Configuration
                'show_star_rating' => filter_var($settings['show_star_rating'] ?? '1', FILTER_VALIDATE_BOOLEAN),
                'show_review_count' => filter_var($settings['show_review_count'] ?? '1', FILTER_VALIDATE_BOOLEAN),

                // Auth Configuration
                'auth' => [
                    'manual_login_enabled' => filter_var($settings['auth_manual_login_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN),
                    'phone_otp_enabled' => filter_var($settings['auth_phone_otp_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN),
                    'email_otp_enabled' => filter_var($settings['auth_email_otp_enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN),
                    'google_enabled' => filter_var($settings['auth_google_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN),
                    'apple_enabled' => filter_var($settings['auth_apple_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN),
                    'account_deletion_grace_period' => (int) ($settings['auth_account_deletion_grace_period'] ?? 7),
                    'user_app_forgot_password_enabled' => filter_var($settings['auth_user_app_forgot_password_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN),
                    'store_app_forgot_password_enabled' => filter_var($settings['auth_store_app_forgot_password_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN),
                    'driver_app_forgot_password_enabled' => filter_var($settings['auth_driver_app_forgot_password_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN),
                ],

                // App Update Configuration
                'app_update' => [
                    'min_version' => $settings['app_min_version'] ?? '1.0.0',
                    'latest_version' => $settings['app_latest_version'] ?? '1.0.0',
                    'force_update' => filter_var($settings['app_force_update'] ?? '0', FILTER_VALIDATE_BOOLEAN),
                    'play_store_url' => $settings['app_play_store_url'] ?? 'https://play.google.com/store/apps/details?id=com.inallcart.app',
                    'app_store_url' => $settings['app_app_store_url'] ?? 'https://apps.apple.com/app/inallcart/id123456789',
                ],

                // Driver Tips Configuration
                'tips' => [
                    'suggested_tips' => array_map('floatval', explode(',', $settings['suggested_tips'] ?? '10,20,50,100')),
                    'allow_custom_tips' => filter_var($settings['allow_custom_tips'] ?? '1', FILTER_VALIDATE_BOOLEAN),
                ],
            ],
        ])->header('Cache-Control', 'private, no-store')
          ->header('Vary', 'X-User-Lat, X-User-Lng');
    }

    /**
     * Get onboarding screens
     */
    public function onboarding(): JsonResponse
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        
        $enabled = filter_var($settings['onboarding_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        
        if (!$enabled) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->getOnboardingScreens($settings),
        ]);
    }

    /**
     * Parse onboarding screens from settings
     */
    private function getOnboardingScreens(array $settings): array
    {
        $screensJson = $settings['onboarding_screens'] ?? null;
        
        if ($screensJson) {
            $screens = json_decode($screensJson, true);
            if (is_array($screens)) {
                return array_map(function ($screen, $index) {
                    $img = $screen['image_url'] ?? $screen['image'] ?? '';
                    if (!empty($img) && !str_starts_with($img, 'http://') && !str_starts_with($img, 'https://') && !str_starts_with($img, 'assets/')) {
                        $img = asset('storage/' . ltrim($img, '/'));
                    }
                    return [
                        'id' => $screen['id'] ?? $index + 1,
                        'title' => $screen['title'] ?? '',
                        'subtitle' => $screen['subtitle'] ?? '',
                        'image_url' => $img,
                        'order' => $screen['order'] ?? $index + 1,
                    ];
                }, $screens, array_keys($screens));
            }
        }

        // Default onboarding screens
        return [
            [
                'id' => 1,
                'title' => 'Welcome to ' . config('app.name', 'InAllCart'),
                'subtitle' => 'Your one-stop shop for everything you need',
                'image_url' => asset('images/onboarding/welcome.png'),
                'order' => 1,
            ],
            [
                'id' => 2,
                'title' => 'Fast Delivery',
                'subtitle' => 'Get your orders delivered in minutes',
                'image_url' => asset('images/onboarding/delivery.png'),
                'order' => 2,
            ],
            [
                'id' => 3,
                'title' => 'Easy Payments',
                'subtitle' => 'Multiple payment options for your convenience',
                'image_url' => asset('images/onboarding/payment.png'),
                'order' => 3,
            ],
        ];
    }

    /**
     * Get currency configuration (zone-aware)
     */
    private function getCurrencyConfig(array $settings, ?Request $request = null): array
    {
        $defaultCurrency = $settings['default_currency'] ?? 'INR';
        $activeCurrency = $defaultCurrency;
        $zoneCurrency = null;

        // Check if the user is in a zone with a specific currency
        if ($request) {
            // Accept from headers OR query params (query params act as cache-busting key)
            $userLat = $request->header('X-User-Lat') ?? $request->query('lat');
            $userLng = $request->header('X-User-Lng') ?? $request->query('lng');

            Log::info('Zone currency lookup', [
                'lat' => $userLat,
                'lng' => $userLng,
                'source_header_lat' => $request->header('X-User-Lat'),
                'source_query_lat' => $request->query('lat'),
            ]);

            if ($userLat && $userLng) {
                try {
                    $lat = (float) $userLat;
                    $lng = (float) $userLng;

                    // First: find any zone covering this point (to confirm spatial query works)
                    $anyZone = Zone::active()
                        ->covering($lat, $lng)
                        ->first(['id', 'name', 'currency']);

                    Log::info('Zone lookup result', [
                        'zone_found' => $anyZone ? $anyZone->toArray() : null,
                    ]);

                    if ($anyZone && !empty($anyZone->currency)) {
                        $activeCurrency = $anyZone->currency;
                        $zoneCurrency = $anyZone->currency;
                    } else {
                        // Fallback: If coordinates fall outside defined zone polygon, use primary active zone currency
                        $defaultZone = Zone::active()
                            ->whereNotNull('currency')
                            ->where('currency', '!=', '')
                            ->orderBy('sort_order')
                            ->first(['id', 'name', 'currency']);

                        if ($defaultZone && !empty($defaultZone->currency)) {
                            $activeCurrency = $defaultZone->currency;
                            $zoneCurrency = $defaultZone->currency;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Zone currency lookup failed: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                // Fallback: If lat/lng headers are not passed, use currency from first active zone
                $defaultZone = Zone::active()
                    ->whereNotNull('currency')
                    ->where('currency', '!=', '')
                    ->orderBy('sort_order')
                    ->first(['id', 'name', 'currency']);

                if ($defaultZone && !empty($defaultZone->currency)) {
                    $activeCurrency = $defaultZone->currency;
                    $zoneCurrency = $defaultZone->currency;
                }
            }
        }

        $currencies = $this->currencyService->getSupportedCurrencies();

        Log::info('Currency config resolved', [
            'default' => $defaultCurrency,
            'active' => $activeCurrency,
            'zone_currency' => $zoneCurrency,
        ]);
        
        return [
            'default' => $defaultCurrency,
            'active' => $activeCurrency,
            'zone_currency' => $zoneCurrency,
            'symbol' => $currencies[$activeCurrency]['symbol'] ?? $activeCurrency,
            'symbol_position' => $settings['currency_symbol_position'] ?? 'left',
            'decimal_places' => (int) ($settings['currency_decimal_places'] ?? 2),
            'thousand_separator' => $settings['currency_thousand_separator'] ?? ',',
            'multi_currency_enabled' => filter_var($settings['multi_currency_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'supported_currencies' => $currencies,
        ];
    }

    /**
     * Get active plugins visible to the mobile app
     * Returns a map of plugin-slug => boolean so the app can toggle module UI.
     */
    private function getActivePluginsForApp(): array
    {
        try {
            $pluginsMap = [];
            $modules = [];

            $activePlugins = Plugin::where('is_active', true)->get();

            foreach ($activePlugins as $plugin) {
                $rawName = $plugin->name; // e.g. "quixko-service-booking", "hotel-booking", "b2b-portal"
                $cleanKey = str_replace('-', '_', preg_replace('/^quixko-/', '', $rawName));
                
                $isEnabled = Setting::get("{$cleanKey}_is_active", '1') == '1';

                $pluginsMap[$cleanKey] = (bool)$isEnabled;
                $pluginsMap[$rawName] = (bool)$isEnabled;
                $pluginsMap[str_replace('-', '_', $rawName)] = (bool)$isEnabled;
                
                if ($cleanKey === 'service_booking') {
                    $pluginsMap['house_shifting'] = (bool)$isEnabled;
                }

                $modules[$cleanKey] = [
                    'name'        => Setting::get("{$cleanKey}_module_name", $plugin->display_name ?? ucwords(str_replace('_', ' ', $cleanKey))),
                    'icon_url'    => Setting::get("{$cleanKey}_module_icon", ''),
                    'description' => Setting::get("{$cleanKey}_module_description", $plugin->description ?? ''),
                    'is_active'   => (bool)$isEnabled,
                ];
            }

            $pluginsMap['modules'] = $modules;

            return $pluginsMap;
        } catch (\Exception $e) {
            return [
                'modules' => [],
            ];
        }
    }

    /**
     * Get timezone configuration
     */
    private function getTimezoneConfig(array $settings): array
    {
        return [
            'timezone' => $settings['system_timezone'] ?? 'Asia/Kolkata',
            'date_format' => $settings['date_format'] ?? 'd/m/Y',
            'time_format' => $settings['time_format'] ?? '12',
        ];
    }

    /**
     * Get static pages for mobile app
     */
    public function pages(): JsonResponse
    {
        $pages = StaticPage::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'title', 'slug', 'content', 'icon', 'order', 'updated_at'])
            ->map(fn($p) => [
                'id'         => $p->id,
                'title'      => $p->title,
                'slug'       => $p->slug,
                'content'    => $p->content,
                'icon'       => $p->icon,
                'order'      => $p->order,
                'updated_at' => $p->updated_at?->toISOString(),
            ]);

        return response()->json([
            'success' => true,
            'data'    => $pages,
        ])->header('Cache-Control', 'public, max-age=300');
    }

    /**
     * TEMP DEBUG: Show zone lookup result for given coordinates
     */
    public function zoneDebug(Request $request): JsonResponse
    {
        $lat = (float) ($request->header('X-User-Lat') ?? $request->query('lat', 0));
        $lng = (float) ($request->header('X-User-Lng') ?? $request->query('lng', 0));

        // All active zones
        $allZones = Zone::active()->get(['id', 'name', 'city', 'currency', 'is_active']);

        // Zones covering this point
        $coveringZones = Zone::active()
            ->covering($lat, $lng)
            ->get(['id', 'name', 'city', 'currency']);

        // Raw spatial test
        $rawTest = null;
        try {
            $rawTest = DB::select(
                "SELECT id, name, currency,
                    ST_Contains(area, ST_GeomFromText(?)) as covers
                FROM zones WHERE is_active = 1 AND deleted_at IS NULL",
                ["POINT($lng $lat)"]
            );
        } catch (\Exception $e) {
            $rawTest = ['error' => $e->getMessage()];
        }

        // Check actual stored geometry
        $geoData = null;
        try {
            $geoData = DB::select(
                "SELECT id, name, ST_AsGeoJSON(area) as geojson, ST_SRID(area) as srid, ST_IsValid(area) as is_valid
                FROM zones WHERE is_active = 1 AND deleted_at IS NULL LIMIT 3"
            );
        } catch (\Exception $e) {
            $geoData = ['error' => $e->getMessage()];
        }

        return response()->json([
            'input' => ['lat' => $lat, 'lng' => $lng],
            'all_active_zones' => $allZones,
            'covering_zones' => $coveringZones,
            'raw_spatial_test' => $rawTest,
            'stored_geometry' => $geoData,
        ]);
    }

    /**
     * Get exchange rates
     */
    public function exchangeRates(): JsonResponse
    {
        $baseCurrency = Setting::get('default_currency', 'INR');
        $rates = $this->currencyService->getExchangeRates($baseCurrency);
        
        return response()->json([
            'success' => true,
            'data' => [
                'base_currency' => $baseCurrency,
                'rates' => $rates,
                'last_updated' => Setting::get('exchange_rate_last_update'),
            ],
        ]);
    }
}

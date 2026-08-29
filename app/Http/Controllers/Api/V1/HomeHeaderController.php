<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeHeaderCard;
use App\Models\HomeHeaderSetting;
use App\Models\HomeHeaderTab;
use App\Models\Product;
use App\Models\Store;
use App\Models\Setting;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class HomeHeaderController extends Controller
{
    /**
     * Get home header configuration for mobile app
     * Supports ETag for cache validation
     */
    public function index(Request $request): JsonResponse
    {
        // Check Global Zone Setting & Location
        $globalZoneEnabled = Setting::get('global_zone_enabled', 1);
        $userLat = $request->header('X-User-Lat') ?? $request->query('lat');
        $userLng = $request->header('X-User-Lng') ?? $request->query('lng');

        $lastUpdate = HomeHeaderTab::max('updated_at')?->timestamp ?? 0;
        $cacheKey = "home_header_config_v2_z" . ($globalZoneEnabled ? '1' : '0') . "_{$lastUpdate}_{$userLat}_{$userLng}";
        
        $cachedData = Cache::remember($cacheKey, 60, function() use ($globalZoneEnabled, $userLat, $userLng) {
            $settings = HomeHeaderSetting::getSettings();

            // Store Filtering Logic
            $validStoreIds = null;

            if (!$globalZoneEnabled && $userLat && $userLng) {
                 // 1. Find zones covering the user
                 $zones = Zone::active()->covering($userLat, $userLng)->pluck('id');

                 if ($zones->isEmpty()) {
                     // NO SERVICE IN AREA
                     return ['etag' => 'no_service', 'data' => ['tabs' => [], 'service_unavailable' => true]];
                 }

                 // 2. Find stores in those zones
                 $validStoreIds = DB::table('store_zone')
                    ->whereIn('zone_id', $zones)
                    ->where('is_active', true)
                    ->pluck('store_id')
                    ->toArray();
            }

            $tabs = HomeHeaderTab::with(['cards', 'category:id,name,image'])
                ->active()
                ->get();

            // Filter Tab Cards based on Valid Stores (if filtering is active)
            if ($validStoreIds !== null) {
                $tabs->each(function($tab) use ($validStoreIds) {
                    // Filter cards linked to stores, checking if store_id is in valid list
                    // Note: Cards link_id is store_id when link_type is 'store'
                    // For 'product' type, we'd need to check product->store_id (complex query, maybe skip for now or doing deeper check)
                    $tab->setRelation('cards', $tab->cards->filter(function($card) use ($validStoreIds) {
                        if ($card->link_type === 'store') {
                            return in_array($card->link_id, $validStoreIds);
                        }
                        return true; // Keep other cards for now (categories/products might belong to valid stores, hard to check without deep load)
                    }));
                });
            }

            // Generate ETag from:
            // 1. Settings updated_at
            // 2. Tab count + max updated_at + IDs + sort orders
            // 3. Card count + max updated_at
            $settingsUpdated = $settings->updated_at?->timestamp ?? 0;
            $tabCount = $tabs->count();
            $tabMaxUpdated = $tabs->max('updated_at')?->timestamp ?? 0;
            $tabIds = $tabs->pluck('id')->sort()->implode(',');
            $tabSortOrders = $tabs->pluck('sort_order')->implode(',');
            
            $cardCount = HomeHeaderCard::count();
            $cardMaxUpdated = Carbon::parse(HomeHeaderCard::max('updated_at'))->timestamp ?? 0;
            
            $etag = 'hh_v2_' . md5("{$settingsUpdated}_{$tabCount}_{$tabMaxUpdated}_{$tabIds}_{$tabSortOrders}_{$cardCount}_{$cardMaxUpdated}_" . ($validStoreIds ? implode(',', $validStoreIds) : 'all'));

            return [
                'etag' => $etag,
                'data' => $this->buildConfig($settings, $tabs),
            ];
        });

        $etag = $cachedData['etag'];

        // Check if client has latest version
        if ($request->header('If-None-Match') === $etag) {
            return response()->json(null, 304);
        }

        return response()->json([
            'success' => true,
            'data' => $cachedData['data'],
            'meta' => ['version' => $etag],
        ])->header('ETag', $etag)
          ->header('Cache-Control', 'public, max-age=60');
    }

    /**
     * Build the complete header configuration
     */
    private function buildConfig($settings, $tabs): array
    {
        // Find the primary configured top header background (from first tab with custom colors or any tab)
        $primaryCustomTab = $tabs->first(function ($t) {
            return !empty($t->header_gradient_color1) && strtolower($t->header_gradient_color1) !== '#ff5722';
        }) ?? $tabs->first();

        $defaultTopBg = $primaryCustomTab ? [
            'type'   => $primaryCustomTab->header_bg_type ?? 'gradient',
            'color1' => strtolower($primaryCustomTab->header_gradient_color1) !== '#ff5722' ? $primaryCustomTab->header_gradient_color1 : null,
            'color2' => strtolower($primaryCustomTab->header_gradient_color2) !== '#ff9800' ? $primaryCustomTab->header_gradient_color2 : null,
            'style'  => $primaryCustomTab->header_gradient_style ?? 'top_to_bottom',
            'image_url' => storage_url($primaryCustomTab->header_bg_image_url),
        ] : null;

        $formattedTabs = $tabs->map(function ($tab) use ($settings, $defaultTopBg) {
            $activeCards = $tab->cards->take(6)->values();
            
            // Get category image URL with proper prefix
            $categoryImage = null;
            if ($tab->category && $tab->category->image) {
                $categoryImage = str_starts_with($tab->category->image, '/storage/') 
                    ? $tab->category->image 
                    : '/storage/' . $tab->category->image;
            }
            // Determine icon image (use explicit icon_url if set, otherwise category image)
            $iconImage = !empty($tab->icon_url) ? storage_url($tab->icon_url) : $categoryImage;
            
            // Determine tab display name based on use_header_name toggle
            $tabDisplayName = $tab->use_header_name 
                ? $tab->name 
                : ($tab->category?->name ?? $tab->name);

            $hasOwnCustomColors = !empty($tab->header_gradient_color1) && strtolower($tab->header_gradient_color1) !== '#ff5722';
            
            $topHeaderBg = $hasOwnCustomColors ? [
                'type'   => $tab->header_bg_type ?? 'gradient',
                'color1' => $tab->header_gradient_color1,
                'color2' => $tab->header_gradient_color2,
                'style'  => $tab->header_gradient_style ?? 'top_to_bottom',
                'image_url' => storage_url($tab->header_bg_image_url),
            ] : ($defaultTopBg ?? [
                'type'   => $tab->header_bg_type ?? 'gradient',
                'color1' => null,
                'color2' => null,
                'style'  => $tab->header_gradient_style ?? 'top_to_bottom',
                'image_url' => storage_url($tab->header_bg_image_url),
            ]);
            
            return [
                'id' => $tab->id,
                'category_id' => $tab->category_id,
                'name' => $tab->display_name,
                'tab_display_name' => $tabDisplayName,
                'icon_url' => $iconImage,
                'icon' => $iconImage,
                'category_image' => $iconImage,
                'use_header_name' => $tab->use_header_name,
                'cards_horizontal' => $tab->cards_horizontal, // Per-header setting
                'sticky_header_color' => $tab->sticky_header_color,
                'top_header_background' => $topHeaderBg,
                'background' => $settings->background_active ? [
                    'type' => $tab->background_type,
                    'url'  => storage_url($tab->background_url),
                ] : null,
                'cards' => $settings->cards_active && $activeCards->count() >= 3
                    ? $activeCards->map(fn($card) => $this->formatCard($card))
                    : [],
            ];
        });

        return [
            'tabs_active' => $settings->tabs_active,
            'background_active' => $settings->background_active,
            'cards_active' => $settings->cards_active,
            'tabs_horizontal_style' => $settings->tabs_horizontal_style,
            'module_icon_style' => $settings->module_icon_style ?? 'image_and_name',
            'tabs' => $formattedTabs,
        ];
    }

    /**
     * Format card for API response
     * Auto-fetches linked entity image if not set
     */
    private function formatCard(HomeHeaderCard $card): array
    {
        $imageUrl = $card->image_url ? storage_url($card->image_url) : null;

        // Get image from linked entity if not set
        if (!$imageUrl && $card->link_id) {
            switch ($card->link_type) {
                case 'category':
                    $category = Category::select('id', 'image')->find($card->link_id);
                    if ($category && $category->image) {
                        $imageUrl = storage_url($category->image);
                    }
                    break;
                case 'product':
                    $product = Product::with('primaryImage:id,product_id,image')->select('id')->find($card->link_id);
                    if ($product && $product->primaryImage?->image) {
                        $imageUrl = storage_url($product->primaryImage->image);
                    }
                    break;
                case 'store':
                    $store = Store::select('id', 'logo')->find($card->link_id);
                    if ($store && $store->logo) {
                        $imageUrl = storage_url($store->logo);
                    }
                    break;
            }
        }

        return [
            'id'        => $card->id,
            'image_url' => $imageUrl,
            'link_type' => $card->link_type,
            'link_id'   => $card->link_id,
            'link_url'  => $card->link_url,
        ];
    }
}

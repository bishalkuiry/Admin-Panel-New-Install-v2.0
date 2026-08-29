<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppContent;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Setting;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class AppContentController extends Controller
{
    /**
     * Get app content for mobile app
     * Supports ETag for cache validation
     * 
     * NOTE: Category screen content is now handled by a dedicated endpoint:
     * /api/v1/config/category-screen-content
     */
    public function index(Request $request): JsonResponse
    {
        $tabId = $request->query('tab_id');
        
        // tab_id is now required - category screen has its own dedicated endpoint
        // Allow "0" as a valid tab_id
        if ($tabId === null || $tabId === '') {
            return response()->json([
                'success' => false,
                'message' => 'tab_id is required. For category screen content, use /api/v1/config/category-screen-content',
            ], 400);
        }

        // Check Global Zone Setting & Location
        $globalZoneEnabled = Setting::get('global_zone_enabled', 1);
        $userLat = $request->header('X-User-Lat') ?? $request->query('lat');
        $userLng = $request->header('X-User-Lng') ?? $request->query('lng');

        // BACKGROUND SHIELD: Cache the entire response structure for 60 seconds
        $cacheKey = "app_content_v1_{$tabId}_z" . ($globalZoneEnabled ? '1' : '0') . "_{$userLat}_{$userLng}";
        
        $cachedData = Cache::remember($cacheKey, 60, function() use ($tabId, $globalZoneEnabled, $userLat, $userLng) {
            
            // ZONE VALIDATION LOGIC
            // If Strict Mode is ON, check if user is in a served area
            if (!$globalZoneEnabled && $userLat && $userLng) {
                $hasCoverage = Zone::active()->covering($userLat, $userLng)->exists();

                if (!$hasCoverage) {
                     // NO SERVICE IN AREA
                     // Return empty content list to indicate no service
                     // The frontend will show "Service Unavailable" if it detects this state
                     // OR we can return a specific error structure if the frontend supports it.
                     // For now, consistent with HomeHeader, we return empty data but with a special flag if possible.
                     // However, AppContent expects a list of widgets.
                     // We'll return an empty list, effectively hiding all content.
                     return [
                        'etag' => 'no_service_' . md5($userLat . $userLng),
                        'data' => [], 
                     ];
                }
            }

            $contents = AppContent::where('header_tab_id', $tabId)
                ->forApp()
                ->where('show_on_tracking', false)
                ->active()
                ->get();
            
            // Database indexes on updated_at make these queries O(1)
            $maxDataUpdated = max(
                $contents->max('updated_at')?->timestamp ?? 0,
                Carbon::parse(Store::max('updated_at'))->timestamp ?? 0,
                Carbon::parse(Category::max('updated_at'))->timestamp ?? 0,
                Carbon::parse(Product::max('updated_at'))->timestamp ?? 0
            );
            
            $contentCount = $contents->count();
            $contentIds = $contents->pluck('id')->sort()->implode(',');
            // v2: salt bumped to bust client caches after media URL format fix
            $etag = 'ac_v2_' . md5("{$tabId}_{$contentCount}_{$maxDataUpdated}_{$contentIds}");

            return [
                'etag' => $etag,
                'data' => $contents->map(fn($c) => $this->formatContent($c))->toArray(),
            ];
        });

        $etag = $cachedData['etag'];

        // Check if client has latest version (ETag validation)
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
     * Format content widget for API response
     */
    private function formatContent(AppContent $content): array
    {
        $data = [
            'id' => $content->id,
            'header_tab_id' => $content->header_tab_id,
            'type' => $content->type,
            'style' => $content->style,
            'title' => $content->title,
            'show_title' => $content->show_title,
            'subtitle' => $content->subtitle,
            'show_subtitle' => $content->show_subtitle,
            'show_view_all' => $content->show_view_all ?? false,
            'sort_order' => $content->sort_order,
        ];

        // Add background configuration for product/category/brand/media/store widgets
        if (in_array($content->type, ['product', 'category', 'brand', 'media', 'store'])) {
            $data['background'] = [
                'enabled' => $content->enable_background,
                'type' => $content->background_type,
                'color' => $content->background_color,
                'media_url' => $this->formatImageUrl($content->background_media_url),
            ];
            $data['grid_columns'] = $content->grid_columns ?? 2;
            $data['grid_rows'] = $content->grid_rows ?? 2;
            $data['enable_horizontal_animation'] = $content->enable_horizontal_animation ?? false;
        }

        switch ($content->type) {
            case 'product':
                $data['source'] = $content->source;
                $data['products'] = $this->getProducts($content);
                break;

            case 'category':
                $data['source'] = $content->source;
                $data['categories'] = $this->getCategories($content);
                break;

            case 'brand':
                $data['source'] = $content->source;
                $data['brands'] = $this->getBrands($content);
                break;

            case 'store':
                $data['source'] = $content->source;
                $data['stores'] = $this->getStores($content);
                break;

            case 'media':
                // Support both new multi-media and legacy single media
                $mediaItems = is_array($content->media_items) ? $content->media_items : [];
                
                if (!empty($mediaItems)) {
                    // Convert link_id to integer and resolve media URLs for each media item
                    $mediaItems = array_map(function($item) {
                        if (isset($item['link_id']) && $item['link_id'] !== null) {
                            $item['link_id'] = (int) $item['link_id'];
                        }
                        if (!empty($item['url'])) {
                            $item['url'] = $this->formatImageUrl($item['url']);
                        }
                        return $item;
                    }, $mediaItems);
                    
                    $data['media_items'] = $mediaItems;
                    $data['media'] = [
                        'height' => $content->media_height ?? 200,
                        'width' => $content->media_width,
                    ];
                } else {
                    // Legacy single media support
                    $data['media'] = [
                        'url' => $this->formatImageUrl($content->media_url),
                        'type' => $content->media_type,
                        'height' => $content->media_height ?? 200,
                        'width' => $content->media_width,
                    ];
                    $data['link'] = [
                        'type' => $content->link_type,
                        'id' => $content->link_id,
                        'url' => $content->link_url,
                    ];
                }
                $data['grid_columns'] = $content->grid_columns ?? 1;
                $data['grid_rows'] = $content->grid_rows ?? 1;
                break;
        }

        return $data;
    }

    /**
     * Get products for product widget
     */
    private function getProducts(AppContent $content): array
    {
        $query = Product::where('is_active', true)
            ->with([
                'primaryImage:id,product_id,image',
                'approvedReviews:id,product_id,rating'
            ])
            ->select('id', 'name', 'price', 'compare_price', 'is_featured', 'unit', 'quantity');

        switch ($content->source) {
            case 'custom':
                if (!empty($content->custom_items)) {
                    $ids = $content->custom_items;
                    $products = $query->whereIn('id', $ids)->get();
                    // Maintain custom order
                    $products = $products->sortBy(fn($p) => array_search($p->id, $ids))->values();
                } else {
                    $products = collect();
                }
                break;

            case 'recent':
                $products = $query->latest()->limit(10)->get();
                break;

            case 'featured':
                $products = $query->where('is_featured', true)->limit(10)->get();
                break;

            default:
                $products = collect();
        }

        return $products->map(function($p) {
            $reviews = $p->approvedReviews;
            $avgRating = $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : null;
            $reviewCount = $reviews->count();

            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
                'compare_price' => $p->compare_price,
                'image' => $this->formatImageUrl($p->primaryImage?->image),
                'unit' => $p->unit,
                'rating' => $avgRating,
                'review_count' => $reviewCount > 0 ? $reviewCount : null,
                'in_stock' => $p->quantity > 0,
            ];
        })->toArray();
    }

    /**
     * Get categories for category widget
     */
    private function getCategories(AppContent $content): array
    {
        $query = Category::where('is_active', true)
            ->select('id', 'name', 'image');

        switch ($content->source) {
            case 'custom':
                if (!empty($content->custom_items)) {
                    $ids = $content->custom_items;
                    $categories = $query->whereIn('id', $ids)->get();
                    $categories = $categories->sortBy(fn($c) => array_search($c->id, $ids))->values();
                } else {
                    $categories = collect();
                }
                break;

            case 'featured':
                $categories = $query->where('is_featured', true)->limit(10)->get();
                break;

            default:
                $categories = $query->whereNull('parent_id')->limit(10)->get();
        }

        // For style_3, include products for each category
        $includeProducts = $content->style === 'style_3';

        return $categories->map(function($c) use ($includeProducts) {
            $categoryData = [
                'id' => $c->id,
                'name' => $c->name,
                'image' => $this->formatImageUrl($c->image),
            ];

            if ($includeProducts) {
                $categoryData['products'] = $this->getProductsForCategory($c->id);
            }

            return $categoryData;
        })->toArray();
    }

    /**
     * Get products for a specific category (for style_3)
     */
    private function getProductsForCategory(int $categoryId, int $limit = 10): array
    {
        $products = Product::where('is_active', true)
            ->where('category_id', $categoryId)
            ->with([
                'primaryImage:id,product_id,image',
                'approvedReviews:id,product_id,rating'
            ])
            ->select('id', 'name', 'price', 'compare_price', 'category_id', 'unit', 'quantity')
            ->limit($limit)
            ->get();

        return $products->map(function($p) {
            $reviews = $p->approvedReviews;
            $avgRating = $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : null;
            $reviewCount = $reviews->count();

            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
                'compare_price' => $p->compare_price,
                'image' => $this->formatImageUrl($p->primaryImage?->image),
                'unit' => $p->unit,
                'rating' => $avgRating,
                'review_count' => $reviewCount > 0 ? $reviewCount : null,
                'in_stock' => $p->quantity > 0,
            ];
        })->toArray();
    }

    /**
     * Get brands for brand widget
     */
    private function getBrands(AppContent $content): array
    {
        $query = Brand::where('is_active', true)
            ->select('id', 'name', 'logo');

        switch ($content->source) {
            case 'custom':
                if (!empty($content->custom_items)) {
                    $ids = $content->custom_items;
                    $brands = $query->whereIn('id', $ids)->get();
                    $brands = $brands->sortBy(fn($b) => array_search($b->id, $ids))->values();
                } else {
                    $brands = collect();
                }
                break;

            case 'featured':
                $brands = $query->where('is_featured', true)->limit(10)->get();
                break;

            case 'recent':
                $brands = $query->latest()->limit(10)->get();
                break;

            default:
                $brands = $query->orderBy('sort_order')->limit(10)->get();
        }

        return $brands->map(fn($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'logo' => $this->formatImageUrl($b->logo),
        ])->toArray();
    }

    /**
     * Get stores for store widget
     */
    private function getStores(AppContent $content): array
    {
        // Simply delegate to valid model method we added
        // But we need to format the results for API
        $stores = $content->getStores(10); 
        
        return $stores->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'logo' => $this->formatImageUrl($s->logo),
            'rating' => (float) $s->rating,
            'delivery_time' => $s->preparation_time . ' min', // Approximate
            'is_promoted' => $s->is_featured,
            'discount_text' => $s->discount_text,
            'cover_image' => $this->formatImageUrl($s->banner),
        ])->toArray();
    }

    /**
     * Format image URL with proper prefix
     */
    private function formatImageUrl(?string $image): ?string
    {
        if (!$image) return null;
        
        if (str_starts_with($image, 'http')) {
            return $image;
        }

        // Strip any existing /storage/ prefix before building the full URL
        return storage_url(preg_replace('#^/?storage/#', '', $image));
    }
}

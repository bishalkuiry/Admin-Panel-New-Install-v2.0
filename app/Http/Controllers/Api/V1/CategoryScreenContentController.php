<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CategoryScreenContent;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Zone;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CategoryScreenContentController extends Controller
{
    /**
     * Get category screen content for mobile app
     * Supports ETag-based caching for efficient data transfer
     */
    public function index(Request $request): JsonResponse
    {
        // Check Global Zone Setting & Location
        $globalZoneEnabled = Setting::get('global_zone_enabled', 1);
        $userLat = $request->header('X-User-Lat') ?? $request->query('lat');
        $userLng = $request->header('X-User-Lng') ?? $request->query('lng');
        
        $zoneStoreIds = null;
        if (!$globalZoneEnabled && $userLat && $userLng) {
            $zones = Zone::active()->covering($userLat, $userLng)->pluck('id');
            if ($zones->isEmpty()) {
                 return response()->json([
                    'success' => true,
                    'data' => [],
                    'meta' => ['count' => 0]
                ]);
            }
            $zoneStoreIds = DB::table('store_zone')
                ->whereIn('zone_id', $zones)
                ->where('is_active', true)
                ->pluck('store_id')
                ->toArray();
        }

        // BACKGROUND SHIELD: Cache the entire response structure for 60 seconds
        $cacheKey = "category_screen_content_v1_" . ($zoneStoreIds ? implode(',', $zoneStoreIds) : 'all') . "_z" . ($globalZoneEnabled ? '1' : '0');
        
        $cachedData = Cache::remember($cacheKey, 60, function() use ($zoneStoreIds) {
            $contents = CategoryScreenContent::active()->get();
            
            // Database indexes on updated_at make these queries O(1)
            $maxDataUpdated = max(
                $contents->max('updated_at')?->timestamp ?? 0,
                Carbon::parse(Category::max('updated_at'))->timestamp ?? 0,
                Carbon::parse(Product::max('updated_at'))->timestamp ?? 0
            );
            
            $contentCount = $contents->count();
            $contentIds = $contents->pluck('id')->sort()->implode(',');
            
            // v2: salt bumped to bust client caches after media URL format fix
            $etag = 'csc_v2_' . md5("category_screen_{$contentCount}_{$maxDataUpdated}_{$contentIds}");

            return [
                'etag' => $etag,
                'data' => $contents->map(fn($c) => $this->formatContent($c, $zoneStoreIds))->toArray(),
            ];
        });

        $etag = $cachedData['etag'];
        
        // Check If-None-Match header for caching
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
    private function formatContent(CategoryScreenContent $content, ?array $zoneStoreIds = null): array
    {
        $data = [
            'id' => $content->id,
            'type' => $content->type,
            'style' => $content->style,
            'title' => $content->title,
            'show_title' => $content->show_title,
            'subtitle' => $content->subtitle,
            'show_subtitle' => $content->show_subtitle,
            'sort_order' => $content->sort_order,
        ];

        // Add background configuration
        $data['background'] = [
            'enabled' => $content->enable_background,
            'type' => $content->background_type,
            'color' => $content->background_color,
            'media_url' => $this->formatImageUrl($content->background_media_url),
        ];
        $data['grid_columns'] = $content->grid_columns ?? 2;
        $data['grid_rows'] = $content->grid_rows ?? 2;
        $data['enable_horizontal_animation'] = $content->enable_horizontal_animation ?? false;

        switch ($content->type) {
            case 'category':
                $data['source'] = $content->source;
                $data['categories'] = $this->getCategories($content, $zoneStoreIds);
                break;

            case 'media':
                // Support both multi-media and single media
                $mediaItems = is_array($content->media_items) ? $content->media_items : [];
                
                if (!empty($mediaItems)) {
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
                break;
        }

        return $data;
    }

    /**
     * Get categories for category widget
     */
    private function getCategories(CategoryScreenContent $content, ?array $zoneStoreIds = null): array
    {
        $query = Category::where('is_active', true)
            ->select('id', 'name', 'image');

        if ($zoneStoreIds !== null) {
            $query->where(function($q) use ($zoneStoreIds) {
                // Show category if it OR any descendants have products in the allowed stores
                $q->whereHas('products', function($pq) use ($zoneStoreIds) {
                    $pq->whereIn('store_id', $zoneStoreIds);
                })->orWhereHas('children.products', function($pq) use ($zoneStoreIds) {
                     $pq->whereIn('store_id', $zoneStoreIds);
                })->orWhereHas('children.children.products', function($pq) use ($zoneStoreIds) {
                     $pq->whereIn('store_id', $zoneStoreIds);
                });
            });
        }

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
                $categories = $query->where('is_featured', true)->limit(20)->get();
                break;

            case 'all':
            default:
                $categories = $query->whereNull('parent_id')->limit(20)->get();
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
                $categoryData['products'] = $this->getProductsForCategory($c->id, 10, $zoneStoreIds);
            }

            return $categoryData;
        })->toArray();
    }

    /**
     * Get products for a specific category (for style_3)
     */
    private function getProductsForCategory(int $categoryId, int $limit = 10, ?array $zoneStoreIds = null): array
    {
        $query = \App\Models\Product::where('is_active', true)
            ->where('category_id', $categoryId);

        if ($zoneStoreIds !== null) {
            $query->whereIn('store_id', $zoneStoreIds);
        }

        $products = $query->with([
                'primaryImage:id,product_id,image',
                'approvedReviews:id,product_id,rating'
            ])
            ->select('id', 'name', 'price', 'compare_price', 'category_id', 'unit')
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
            ];
        })->toArray();
    }

    /**
     * Format image URL with proper prefix
     */
    private function formatImageUrl(?string $image): ?string
    {
        if (!$image) return null;
        
        if (str_starts_with($image, 'http') || str_starts_with($image, '/storage/')) {
            return $image;
        }
        
        return '/storage/' . $image;
    }
}

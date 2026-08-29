<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * List all categories with ETag support
     */
    public function index(Request $request)
    {
        // Check Global Zone Setting & Location
        $globalZoneEnabled = Setting::get('global_zone_enabled', 1);
        $userLat = $request->header('X-User-Lat') ?? $request->query('lat');
        $userLng = $request->header('X-User-Lng') ?? $request->query('lng');
        
        $zoneStoreIds = null;
        if (!$globalZoneEnabled && $userLat && $userLng) {
            $zones = Zone::active()->covering($userLat, $userLng)->pluck('id');
            if ($zones->isEmpty()) {
                 return response()->json(['success' => true, 'data' => [], 'meta' => ['count' => 0]]);
            }
            $zoneStoreIds = DB::table('store_zone')
                ->whereIn('zone_id', $zones)
                ->where('is_active', true)
                ->pluck('store_id')
                ->toArray();
        }

        // BACKGROUND SHIELD: Cache categories for 60s
        $cacheKey = "categories_index_v1_" . ($zoneStoreIds ? implode(',', $zoneStoreIds) : 'all') . "_z" . ($globalZoneEnabled ? '1' : '0');
        
        $cachedData = Cache::remember($cacheKey, 60, function() use ($zoneStoreIds) {
            $query = Category::where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('sort_order');

            if ($zoneStoreIds !== null) {
                // Show root categories if they OR any descendants have products in the allowed stores
                $query->where(function($q) use ($zoneStoreIds) {
                    $q->whereHas('products', function($pq) use ($zoneStoreIds) {
                        $pq->whereIn('store_id', $zoneStoreIds);
                    })->orWhereHas('children.products', function($pq) use ($zoneStoreIds) {
                         $pq->whereIn('store_id', $zoneStoreIds);
                    })->orWhereHas('children.children.products', function($pq) use ($zoneStoreIds) {
                         $pq->whereIn('store_id', $zoneStoreIds);
                    });
                });
                
                $query->with(['children' => function ($q) {
                    $q->where('is_active', true)
                      ->with(['children' => function($subQ) {
                          $subQ->where('is_active', true)
                             ->orderBy('sort_order');
                      }])
                      ->orderBy('sort_order');
                }]);
                
                $query->withCount(['products' => function($q) use ($zoneStoreIds) {
                    $q->whereIn('store_id', $zoneStoreIds);
                }, 'children']);
            } else {
                $query->with(['children.children.children'])
                    ->withCount(['products', 'children']);
            }

            $categories = $query->get();

            // Generate ETag from count + max updated_at + IDs
            $count = $categories->count();
            $maxUpdated = $categories->max('updated_at')?->timestamp ?? 0;
            $ids = $categories->pluck('id')->sort()->implode(',');
            
            $etag = 'cat_' . md5("{$count}_{$maxUpdated}_{$ids}");

            return [
                'etag' => $etag,
                'data' => CategoryResource::collection($categories),
                'meta' => [
                    'version' => $etag,
                    'count' => $count,
                ]
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
            'meta' => $cachedData['meta'],
        ])->header('ETag', $etag)
          ->header('Cache-Control', 'public, max-age=60');
    }

    /**
     * Get single category
     */
    public function show(Category $category)
    {
        if (!$category->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Note: For 'show', we might also want to filter products inside it?
        // But 'show' usually returns category details. Product listing inside it is handled by ProductController::byCategory.
        // So we just need to ensure the Category ITSELF is valid? 
        // Let's assume if it exists it's visible, but the products endpoint will be empty if out of zone.
        // Or if we want strict strict, we should 404 if it has no products in zone. 
        // For now, leaving 'show' as is (metadata), relying on ProductController::byCategory for the content.
        
        // BACKGROUND SHIELD: Cache individual category details
        $cacheKey = "category_show_v1_{$category->id}";

        $cachedData = Cache::remember($cacheKey, 60, function() use ($category) {
            $category->load(['children' => function ($query) {
                $query->where('is_active', true);
            }]);
            $category->loadCount('products');
            
            $updatedAt = $category->updated_at?->timestamp ?? 0;
            $productsCount = $category->products_count;
            
            $etag = 'cat_show_' . md5("{$category->id}_{$updatedAt}_{$productsCount}");

            return [
                'etag' => $etag,
                'data' => new CategoryResource($category),
            ];
        });

        $etag = $cachedData['etag'];

        if (request()->header('If-None-Match') === $etag) {
            return response()->json(null, 304);
        }

        return response()->json([
            'success' => true,
            'data' => $cachedData['data'],
        ])->header('ETag', $etag)
          ->header('Cache-Control', 'public, max-age=60');
    }

    /**
     * Get featured categories with ETag support
     */
    public function featured(Request $request)
    {
        $limit = $request->query('limit', 8);
        
        // Check Global Zone Setting & Location
        $globalZoneEnabled = Setting::get('global_zone_enabled', 1);
        $userLat = $request->header('X-User-Lat') ?? $request->query('lat');
        $userLng = $request->header('X-User-Lng') ?? $request->query('lng');
        
        $zoneStoreIds = null;
        if (!$globalZoneEnabled && $userLat && $userLng) {
            $zones = Zone::active()->covering($userLat, $userLng)->pluck('id');
            if ($zones->isEmpty()) {
                 return response()->json(['success' => true, 'data' => [], 'meta' => ['count' => 0]]);
            }
            $zoneStoreIds = DB::table('store_zone')
                ->whereIn('zone_id', $zones)
                ->where('is_active', true)
                ->pluck('store_id')
                ->toArray();
        }

        $cacheKey = "categories_featured_v1_{$limit}_" . ($zoneStoreIds ? implode(',', $zoneStoreIds) : 'all') . "_z" . ($globalZoneEnabled ? '1' : '0');

        // BACKGROUND SHIELD: Cache featured categories for 60 seconds
        $cachedData = Cache::remember($cacheKey, 60, function() use ($limit, $zoneStoreIds) {
            $query = Category::where('is_active', true)
                ->where('is_featured', true)
                ->orderBy('sort_order');
                
            if ($zoneStoreIds !== null) {
                $query->whereHas('products', function($q) use ($zoneStoreIds) {
                    $q->whereIn('store_id', $zoneStoreIds);
                });
                
                $query->withCount(['products' => function($q) use ($zoneStoreIds) {
                    $q->whereIn('store_id', $zoneStoreIds);
                }]);
            } else {
                $query->withCount('products');
            }

            $categories = $query->limit($limit)->get();

            // Generate ETag from count + max updated_at + IDs
            $count = $categories->count();
            $maxUpdated = $categories->max('updated_at')?->timestamp ?? 0;
            $ids = $categories->pluck('id')->sort()->implode(',');
            
            $etag = 'featured_' . md5("{$count}_{$maxUpdated}_{$ids}");

            return [
                'etag' => $etag,
                'data' => CategoryResource::collection($categories),
                'meta' => [
                    'version' => $etag,
                    'count' => $count,
                ],
            ];
        });

        $etag = $cachedData['etag'];

        // Check if client has latest version
        $clientETag = $request->header('If-None-Match');
        if ($clientETag === $etag) {
            return response()->json(null, 304);
        }

        return response()->json([
            'success' => true,
            'data' => $cachedData['data'],
            'meta' => $cachedData['meta'],
        ])->header('ETag', $etag)
          ->header('Cache-Control', 'public, max-age=60');
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Zone;
use App\Models\Category;
use App\Services\ProductService;
use App\Enums\ProductStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * List all products with filters
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'is_active', 'stock', 'min_price', 'max_price', 'brand', 'brand_id', 'store_id']);
        if ($request->has('q') && empty($filters['search'])) {
            $filters['search'] = $request->query('q');
        }
        $filters['is_active'] = true; // Only show active products in API
        $perPage = $request->query('per_page', 15);
        $page = $request->query('page', 1);

        // Check Global Zone Setting & Location
        $globalZoneEnabled = Setting::get('global_zone_enabled', 1);
        $userLat = $request->header('X-User-Lat') ?? $request->query('lat');
        $userLng = $request->header('X-User-Lng') ?? $request->query('lng');
        
        $zoneStoreIds = null;
        if (!$globalZoneEnabled && $userLat && $userLng) {
            $zones = Zone::active()->covering($userLat, $userLng)->pluck('id');
            if ($zones->isEmpty()) {
                // Return empty if out of zone
                 return response()->json([
                    'success' => true,
                    'data' => [],
                    'meta' => ['total' => 0, 'page' => $page]
                ]);
            }
            $zoneStoreIds = DB::table('store_zone')
                ->whereIn('zone_id', $zones)
                ->where('is_active', true)
                ->pluck('store_id')
                ->toArray();
        }

        // BACKGROUND SHIELD: Cache listing results for 60 seconds
        // Included filters and pagination in cache key
        $filterHash = md5(json_encode($filters) . "_{$perPage}_{$page}_" . ($zoneStoreIds ? implode(',', $zoneStoreIds) : 'all') . "_z" . ($globalZoneEnabled ? '1' : '0'));
        $cacheKey = "products_index_v1_{$filterHash}";

        $cachedData = Cache::remember($cacheKey, 60, function() use ($filters, $perPage, $zoneStoreIds) {
            // Add zone store filter to filters array pass to service
            if ($zoneStoreIds !== null) {
                // If store_id already set in filters, we intersect, or just overwrite if we want strict zone
                // Usually zone filter is broader, so we can use 'store_ids' array filter if service supports it
                // Extending filters to support 'store_ids'
                $filters['store_ids'] = $zoneStoreIds;
            }
            
            $products = $this->productService->getProducts($filters, $perPage);
            
            // Generate ETag from pagination info + max updated_at
            $count = $products->total();
            $page = $products->currentPage();
            $maxUpdated = $products->getCollection()->max('updated_at')?->timestamp ?? 0;
            $etag = 'prod_' . md5("{$count}_{$page}_{$maxUpdated}");

            return [
                'etag' => $etag,
                'data' => ProductResource::collection($products),
                'meta' => [
                    'version' => $etag,
                    'total' => $count,
                    'page' => $page,
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
     * Get single product
     */
    public function show(Product $product)
    {
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // BACKGROUND SHIELD: Cache individual product details for 60 seconds
        $cacheKey = "product_show_v1_{$product->id}";

        $cachedData = Cache::remember($cacheKey, 60, function() use ($product) {
            $product->load(['category', 'images', 'attributes.attribute', 'foodVariationGroups.options', 'variants']);
            $product->loadAvg('reviews', 'rating');
            $product->loadCount('reviews');

            // Generate ETag from updated_at + stock quantity (important for "Low Stock" checks)
            $updatedAt = $product->updated_at?->timestamp ?? 0;
            $stock = $product->quantity;
            $etag = 'prod_show_' . md5("{$product->id}_{$updatedAt}_{$stock}");

            return [
                'etag' => $etag,
                'data' => new ProductResource($product),
            ];
        });

        $etag = $cachedData['etag'];

        // Check if client has latest version
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
     * Get featured products with ETag support
     */
    public function featured(Request $request)
    {
        $limit = $request->query('limit', 10);
        
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

        $cacheKey = "products_featured_v1_{$limit}_" . ($zoneStoreIds ? implode(',', $zoneStoreIds) : 'all') . "_z" . ($globalZoneEnabled ? '1' : '0');

        // BACKGROUND SHIELD: Cache featured products for 60 seconds
        $cachedData = Cache::remember($cacheKey, 60, function() use ($limit, $zoneStoreIds) {
            $query = Product::where('is_active', true)
                ->where('is_featured', true);

            if ($zoneStoreIds !== null) {
                $query->whereIn('store_id', $zoneStoreIds);
            }

            $products = $query->with(['category', 'primaryImage', 'images'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->limit($limit)
                ->get();

            // Generate ETag from count + max updated_at + IDs
            $count = $products->count();
            $maxUpdated = $products->max('updated_at')?->timestamp ?? 0;
            $ids = $products->pluck('id')->sort()->implode(',');
            
            $etag = 'featured_' . md5("{$count}_{$maxUpdated}_{$ids}");

            return [
                'etag' => $etag,
                'data' => ProductResource::collection($products),
                'meta' => [
                    'version' => $etag,
                    'count' => $count,
                ],
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
     * Get products by category
     */
    public function byCategory(int $categoryId, Request $request)
    {
        // BACKGROUND SHIELD: Cache category product listing
        $perPage = $request->query('per_page', 15);
        $page = $request->query('page', 1);

        // Check Global Zone Setting & Location
        $globalZoneEnabled = Setting::get('global_zone_enabled', 1);
        $userLat = $request->header('X-User-Lat') ?? $request->query('lat');
        $userLng = $request->header('X-User-Lng') ?? $request->query('lng');
        
        $zoneStoreIds = null;
        if (!$globalZoneEnabled && $userLat && $userLng) {
            $zones = Zone::active()->covering($userLat, $userLng)->pluck('id');
            if ($zones->isEmpty()) {
                 return response()->json(['data' => [], 'meta' => ['total' => 0]]);
            }
            $zoneStoreIds = DB::table('store_zone')
                ->whereIn('zone_id', $zones)
                ->where('is_active', true)
                ->pluck('store_id')
                ->toArray();
        }

        $cacheKey = "products_cat_{$categoryId}_p{$page}_l{$perPage}_" . ($zoneStoreIds ? implode(',', $zoneStoreIds) : 'all') . "_z" . ($globalZoneEnabled ? '1' : '0');

        $cachedData = Cache::remember($cacheKey, 60, function() use ($categoryId, $perPage, $zoneStoreIds) {
            $category = Category::find($categoryId);
            $categoryIds = $category ? $category->getAllDescendantIds() : [$categoryId];

            $query = Product::where('is_active', true)
                ->whereIn('category_id', $categoryIds);
            
            if ($zoneStoreIds !== null) {
                $query->whereIn('store_id', $zoneStoreIds);
            }

            $products = $query->with(['category', 'primaryImage'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->paginate($perPage);

            // ETag based on collection stats
            $count = $products->total();
            $items = $products->getCollection();
            $maxUpdated = $items->max('updated_at')?->timestamp ?? 0;
            $ids = $items->pluck('id')->sort()->implode(',');
            
            $etag = 'prod_cat_' . md5("{$categoryId}_{$count}_{$maxUpdated}_{$ids}");

            return [
                'etag' => $etag,
                'data' => $products,
            ];
        });

        $etag = $cachedData['etag'];

        if (request()->header('If-None-Match') === $etag) {
            return response()->json(null, 304);
        }

        return (new ProductCollection($cachedData['data']))
            ->additional(['meta' => ['version' => $etag]])
            ->response()
            ->header('ETag', $etag)
            ->header('Cache-Control', 'public, max-age=60');
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $query = $request->query('q');
        $perPage = $request->query('per_page', 15);

        // Check Global Zone Setting & Location
        $globalZoneEnabled = Setting::get('global_zone_enabled', 1);
        $userLat = $request->header('X-User-Lat') ?? $request->query('lat');
        $userLng = $request->header('X-User-Lng') ?? $request->query('lng');
        
        $zoneStoreIds = null;
        if (!$globalZoneEnabled && $userLat && $userLng) {
            $zones = Zone::active()->covering($userLat, $userLng)->pluck('id');
            if ($zones->isEmpty()) {
                 return new ProductCollection(new LengthAwarePaginator([], 0, $perPage));
            }
            $zoneStoreIds = DB::table('store_zone')
                ->whereIn('zone_id', $zones)
                ->where('is_active', true)
                ->pluck('store_id')
                ->toArray();
        }

        // USE FULL-TEXT SEARCH for production-level speed with 1M items
        // This is significantly faster than 'LIKE %query%'
        $productsQuery = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%") 
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('search_tags', 'like', "%{$query}%")
                  ->orWhere('generic_name', 'like', "%{$query}%");
            });

        if ($zoneStoreIds !== null) {
            $productsQuery->whereIn('store_id', $zoneStoreIds);
        }

        if ($request->has('store_id')) {
            $productsQuery->where('store_id', $request->query('store_id'));
        }

        $products = $productsQuery->with(['category', 'primaryImage'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->paginate($perPage); 

        return new ProductCollection($products);
    }

    /**
     * Auto-suggest products and categories (Advanced Search)
     */
    public function suggest(Request $request)
    {
        $query = $request->query('q');
        
        // Check Global Zone Setting & Location
        $globalZoneEnabled = Setting::get('global_zone_enabled', 1);
        $userLat = $request->header('X-User-Lat') ?? $request->query('lat');
        $userLng = $request->header('X-User-Lng') ?? $request->query('lng');
        
        $zoneStoreIds = null;
        if (!$globalZoneEnabled && $userLat && $userLng) {
            $zones = Zone::active()->covering($userLat, $userLng)->pluck('id');
            if ($zones->isEmpty()) {
                 return response()->json(['success' => true, 'data' => ['categories' => [], 'products' => []]]);
            }
            $zoneStoreIds = DB::table('store_zone')
                ->whereIn('zone_id', $zones)
                ->where('is_active', true)
                ->pluck('store_id')
                ->toArray();
        }

        $categoryQuery = Category::where('is_active', true)
            ->where('name', 'like', "%{$query}%");
            
        if ($zoneStoreIds !== null) {
            $categoryQuery->whereHas('products', function($q) use ($zoneStoreIds) {
                $q->whereIn('store_id', $zoneStoreIds);
            });
        }
            
        $matchedCategories = $categoryQuery->limit(3)->get(['id', 'name', 'slug']);

        // 2. Products (Fast indexed search for suggestions)
        $productQuery = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                 $q->where('name', 'like', "%{$query}%")
                   ->orWhere('sku', 'like', "%{$query}%")
                   ->orWhere('search_tags', 'like', "%{$query}%")
                   ->orWhere('generic_name', 'like', "%{$query}%");
            });
            
        if ($zoneStoreIds !== null) {
            $productQuery->whereIn('store_id', $zoneStoreIds);
        }

        if ($request->has('store_id')) {
            $productQuery->where('store_id', $request->query('store_id'));
        }

        $products = $productQuery->with(['category', 'primaryImage'])
            ->select('id', 'name', 'price', 'quantity', 'category_id', 'slug', 'unit', 'weight', 'store_id')
            ->limit(5)
            ->get();

        // Format for lightweight response
        $formattedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'category_name' => $product->category->name ?? '',
                'image' => storage_url($product->primaryImage?->image),
                'slug' => $product->slug,
                'unit_display' => $product->weight . ' ' . ($product->unit ?? '')
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $matchedCategories,
                'products' => $formattedProducts
            ]
        ]);
    }

    /**
     * Get related products
     */
    public function related(Product $product, Request $request)
    {
        $related = Product::where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with(['primaryImage'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->limit($request->query('limit', 6))
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($related),
        ]);
    }

    /**
     * Get reviews and rating stats for a product
     */
    public function reviews(int $id, Request $request)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $reviewsQuery = \App\Models\Review::with('user:id,name,avatar')
            ->where('product_id', $product->id)
            ->where('is_approved', true)
            ->latest();

        $totalReviews = (clone $reviewsQuery)->count();

        $distribution = [
            5 => (clone $reviewsQuery)->where('rating', 5)->count(),
            4 => (clone $reviewsQuery)->where('rating', 4)->count(),
            3 => (clone $reviewsQuery)->where('rating', 3)->count(),
            2 => (clone $reviewsQuery)->where('rating', 2)->count(),
            1 => (clone $reviewsQuery)->where('rating', 1)->count(),
        ];

        $avgRating = $totalReviews > 0 ? (clone $reviewsQuery)->avg('rating') : 5.0;

        $reviews = $reviewsQuery->paginate($request->query('per_page', 10));

        // Format items with full storage URLs for images
        $items = collect($reviews->items())->map(function ($rev) {
            $images = [];
            if (!empty($rev->images)) {
                $rawImages = is_array($rev->images) ? $rev->images : json_decode($rev->images, true);
                if (is_array($rawImages)) {
                    foreach ($rawImages as $img) {
                        $images[] = storage_url($img);
                    }
                }
            }

            return [
                'id'         => $rev->id,
                'user_name'  => $rev->user?->name ?? 'Verified Buyer',
                'user_avatar'=> $rev->user?->avatar ? storage_url($rev->user->avatar) : null,
                'rating'     => (int) $rev->rating,
                'comment'    => $rev->comment,
                'images'     => $images,
                'date'       => $rev->created_at ? $rev->created_at->format('M d, Y') : '',
            ];
        });

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->primaryImage ? storage_url($product->primaryImage->image) : null,
                'price' => \App\Helpers\CurrencyHelper::format($product->price),
                'unit' => $product->unit,
                'brand' => $product->brand,
            ],
            'stats' => [
                'average_rating' => round((float)$avgRating, 1),
                'total_reviews'  => $totalReviews,
                'distribution'   => $distribution,
            ],
            'data' => $items,
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'total'        => $reviews->total(),
            ]
        ]);
    }

    /**
     * Subscribe to Out of Stock notification
     */
    public function notifyStock(Request $request, $product)
    {
        $user = $request->user('sanctum') ?? \Illuminate\Support\Facades\Auth::guard('sanctum')->user() ?? $request->user();
        $userId = $user?->id;
        $email = $request->input('email', $user?->email);
        $productId = $product instanceof Product ? $product->id : (int) $product;

        if (!\Illuminate\Support\Facades\Schema::hasTable('stock_notifications')) {
            \Illuminate\Support\Facades\Schema::create('stock_notifications', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->string('email')->nullable();
                $table->boolean('notified')->default(false);
                $table->timestamps();
            });
        }

        if ($userId) {
            \Illuminate\Support\Facades\DB::table('stock_notifications')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'product_id' => $productId,
                ],
                [
                    'email' => $email,
                    'notified' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } else {
            \Illuminate\Support\Facades\DB::table('stock_notifications')->updateOrInsert(
                [
                    'email' => $email ?? 'guest@inallcart.com',
                    'product_id' => $productId,
                ],
                [
                    'user_id' => null,
                    'notified' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'You will be notified as soon as this item is back in stock!',
        ]);
    }
}

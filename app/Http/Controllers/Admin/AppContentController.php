<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppContent;
use App\Models\Category;
use App\Models\HomeHeaderTab;
use App\Models\Product;
use App\Models\Store;
use App\Models\Brand;
use App\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Setting;
use App\Services\CloudflareService;
use Illuminate\Support\Facades\Log;

class AppContentController extends Controller
{
    protected $cloudflareService;
    protected StorageService $storage;

    public function __construct(CloudflareService $cloudflareService, StorageService $storage)
    {
        $this->cloudflareService = $cloudflareService;
        $this->storage = $storage;
    }
    /**
     * Display app content management page
     */
    public function index(Request $request)
    {
        $tabs = HomeHeaderTab::orderBy('sort_order')->get();
        
        // Default to first tab if none selected
        $selectedTabId = $request->query('tab_id') ?? $tabs->first()?->id;
        
        $contents = collect();
        if ($selectedTabId) {
            $contents = AppContent::where('header_tab_id', $selectedTabId)
                ->forApp()
                ->orderBy('sort_order')
                ->get();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'tabs' => $tabs,
                    'contents' => $contents,
                ],
            ]);
        }

        return view('admin.settings.app-content', compact('tabs', 'contents', 'selectedTabId'));
    }

    /**
     * Get contents for a specific tab (AJAX)
     */
    public function getByTab(Request $request): JsonResponse
    {
        $tabId = $request->get('tab_id');
        
        if (!$tabId) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }
        
        $contents = AppContent::where('header_tab_id', $tabId)
            ->forApp()
            ->orderBy('sort_order')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $contents,
        ]);
    }

    /**
     * Store a new content widget
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'header_tab_id' => 'nullable|exists:home_header_tabs,id',
            'type' => ['required', Rule::in(['product', 'category', 'brand', 'media', 'store'])],
            'style' => ['required', Rule::in(['style_1', 'style_2', 'style_3', 'style_4'])],
            'title' => 'nullable|string|max:100',
            'show_title' => 'boolean',
            'subtitle' => 'nullable|string|max:200',
            'show_subtitle' => 'boolean',
            'show_view_all' => 'boolean',
            'source' => ['nullable', Rule::in(['custom', 'recent', 'featured'])],
            'enable_background' => 'boolean',
            'background_type' => ['nullable', Rule::in(['color', 'image', 'gif', 'video'])],
            'background_color' => 'nullable|string|max:20',
            'background_media_url' => 'nullable|string|max:500',
            'grid_columns' => 'nullable|integer|min:1|max:4',
            'grid_rows' => 'nullable|integer|min:1|max:10',
            'enable_horizontal_animation' => 'boolean',
            'show_on_category_screen' => 'boolean',
            'show_on_tracking' => 'boolean',
            'media_items' => 'nullable|array',
            'media_items.*.url' => 'nullable|string|max:500',
            'media_items.*.type' => ['nullable', Rule::in(['image', 'gif', 'video'])],
            'media_items.*.link_type' => ['nullable', Rule::in(['none', 'product', 'category', 'brand', 'store', 'url'])],
            'media_items.*.link_id' => 'nullable|integer',
            'media_items.*.link_url' => 'nullable|string|max:500',
            'media_url' => 'nullable|string|max:500',
            'media_type' => ['nullable', Rule::in(['image', 'gif', 'video'])],
            'media_height' => 'nullable|integer|min:50|max:500',
            'media_width' => 'nullable|integer', // Deprecated: Width auto-fills based on columns
            'link_type' => ['nullable', Rule::in(['none', 'product', 'category', 'brand', 'store', 'url'])],
            'link_id' => 'nullable|integer',
            'link_url' => 'nullable|string|max:500',
            'custom_items' => 'nullable|array',
            'is_active' => 'boolean',
            'target' => ['nullable', Rule::in(['all', 'app', 'website'])],
        ]);

        // Filter out null media_items
        if (isset($validated['media_items'])) {
            $validated['media_items'] = array_values(array_filter($validated['media_items'], function($item) {
                return $item !== null && !empty($item);
            }));
        }

        // Get max sort order for this tab
        $maxOrder = AppContent::where('header_tab_id', $validated['header_tab_id'] ?? null)
            ->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        // Automatically activate newly created widgets by default
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $validated['target'] = 'app';

        $content = AppContent::create($validated);
        
        $this->purgeCloudflareCache();

        return response()->json([
            'success' => true,
            'message' => 'Content widget created successfully',
            'data' => $content,
        ], 201);
    }

    /**
     * Update a content widget
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $content = AppContent::findOrFail($id);

        $validated = $request->validate([
            'header_tab_id' => 'nullable|exists:home_header_tabs,id',
            'type' => ['sometimes', Rule::in(['product', 'category', 'brand', 'media', 'store'])],
            'style' => ['sometimes', Rule::in(['style_1', 'style_2', 'style_3', 'style_4'])],
            'title' => 'nullable|string|max:100',
            'show_title' => 'boolean',
            'subtitle' => 'nullable|string|max:200',
            'show_subtitle' => 'boolean',
            'show_view_all' => 'boolean',
            'source' => ['nullable', Rule::in(['custom', 'recent', 'featured'])],
            'enable_background' => 'boolean',
            'background_type' => ['nullable', Rule::in(['color', 'image', 'gif', 'video'])],
            'background_color' => 'nullable|string|max:20',
            'background_media_url' => 'nullable|string|max:500',
            'grid_columns' => 'nullable|integer|min:1|max:4',
            'grid_rows' => 'nullable|integer|min:1|max:10',
            'enable_horizontal_animation' => 'boolean',
            'show_on_category_screen' => 'boolean',
            'show_on_tracking' => 'boolean',
            'media_items' => 'nullable|array',
            'media_items.*.url' => 'nullable|string|max:500',
            'media_items.*.type' => ['nullable', Rule::in(['image', 'gif', 'video'])],
            'media_items.*.link_type' => ['nullable', Rule::in(['none', 'product', 'category', 'brand', 'store', 'url'])],
            'media_items.*.link_id' => 'nullable|integer',
            'media_items.*.link_url' => 'nullable|string|max:500',
            'media_url' => 'nullable|string|max:500',
            'media_type' => ['nullable', Rule::in(['image', 'gif', 'video'])],
            'media_height' => 'nullable|integer|min:50|max:500',
            'media_width' => 'nullable|integer', // Deprecated: Width auto-fills based on columns
            'link_type' => ['nullable', Rule::in(['none', 'product', 'category', 'brand', 'store', 'url'])],
            'link_id' => 'nullable|integer',
            'link_url' => 'nullable|string|max:500',
            'custom_items' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Filter out null media_items
        if (isset($validated['media_items'])) {
            $validated['media_items'] = array_values(array_filter($validated['media_items'], function($item) {
                return $item !== null && !empty($item);
            }));
        }

        $content->update($validated);
        
        $this->purgeCloudflareCache();

        return response()->json([
            'success' => true,
            'message' => 'Content widget updated successfully',
            'data' => $content->fresh(),
        ]);
    }

    /**
     * Delete a content widget
     */
    public function destroy(int $id): JsonResponse
    {
        $content = AppContent::findOrFail($id);
        
        // Delete media file if exists
        if ($content->media_url) {
            $this->storage->delete($content->media_url);
        }
        
        $content->delete();
        
        $this->purgeCloudflareCache();

        return response()->json([
            'success' => true,
            'message' => 'Content widget deleted successfully',
        ]);
    }

    /**
     * Reorder content widgets
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contents' => 'required|array',
            'contents.*.id' => 'required|exists:app_contents,id',
            'contents.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['contents'] as $item) {
                AppContent::where('id', $item['id'])
                    ->update([
                        'sort_order' => $item['sort_order'],
                        'updated_at' => now(),
                    ]);
            }
        });
        
        $this->purgeCloudflareCache();

        return response()->json([
            'success' => true,
            'message' => 'Content widgets reordered successfully',
        ]);
    }

    /**
     * Upload media for content widget
     */
    public function uploadMedia(Request $request, int $id): JsonResponse
    {
        $content = AppContent::findOrFail($id);

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm|max:20480',
            'type' => ['required', Rule::in(['image', 'gif', 'video'])],
        ]);

        // Delete old file if exists
        if ($content->media_url) {
            $this->storage->delete($content->media_url);
        }

        $path = $this->storage->store($request->file('file'), 'app-content/media');
        
        $content->update([
            'media_url' => $path,
            'media_type' => $request->type,
        ]);
        
        $this->purgeCloudflareCache();

        return response()->json([
            'success' => true,
            'message' => 'Media uploaded successfully',
            'data' => [
                'media_url' => $content->media_url,
                'media_type' => $content->media_type,
            ],
        ]);
    }

    /**
     * Upload background media for product/category widget
     */
    public function uploadBackgroundMedia(Request $request, int $id): JsonResponse
    {
        $content = AppContent::findOrFail($id);

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm|max:20480',
            'type' => ['required', Rule::in(['image', 'gif', 'video'])],
        ]);

        // Delete old background file if exists
        if ($content->background_media_url) {
            $this->storage->delete($content->background_media_url);
        }

        $path = $this->storage->store($request->file('file'), 'app-content/backgrounds');
        
        $content->update([
            'background_media_url' => $path,
            'background_type' => $request->type,
        ]);
        
        $this->purgeCloudflareCache();

        return response()->json([
            'success' => true,
            'message' => 'Background media uploaded successfully',
            'data' => [
                'background_media_url' => $content->background_media_url,
                'background_type' => $content->background_type,
            ],
        ]);
    }

    /**
     * Upload individual media item for media widget grid
     */
    public function uploadMediaItem(Request $request, int $id): JsonResponse
    {
        $content = AppContent::findOrFail($id);

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm|max:20480',
            'type' => ['required', Rule::in(['image', 'gif', 'video'])],
            'index' => 'required|integer|min:0',
        ]);

        $path = $this->storage->store($request->file('file'), 'app-content/media-items');
        
        return response()->json([
            'success' => true,
            'message' => 'Media item uploaded successfully',
            'data' => [
                'url' => $path,
                'type' => $request->type,
                'index' => $request->index,
            ],
        ]);
    }

    /**
     * Get available products for selection
     */
    public function availableProducts(Request $request): JsonResponse
    {
        $search = $request->get('search');
        
        $query = Product::where('is_active', true)
            ->with('primaryImage:id,product_id,image')
            ->select('id', 'name', 'price');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->orderBy('name')->limit(50)->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
                'image' => $p->primaryImage?->image,
            ]);

        return response()->json(['success' => true, 'data' => $products]);
    }

    /**
     * Get available categories for selection
     */
    public function availableCategories(Request $request): JsonResponse
    {
        $search = $request->get('search');
        
        $query = Category::where('is_active', true)
            ->select('id', 'name', 'image');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('name')->limit(50)->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    /**
     * Get available stores for linking
     */
    public function availableStores(Request $request): JsonResponse
    {
        $search = $request->get('search');

        $query = Store::where('status', \App\Enums\StoreStatus::ACTIVE)
            ->select('id', 'name', 'logo');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $stores = $query->orderBy('name')->limit(50)->get();

        return response()->json(['success' => true, 'data' => $stores]);
    }

    /**
     * Get available brands for selection
     */
    public function availableBrands(Request $request): JsonResponse
    {
        $search = $request->get('search');
        
        $query = Brand::where('is_active', true)
            ->select('id', 'name', 'logo');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $brands = $query->orderBy('name')->limit(50)->get();

        return response()->json(['success' => true, 'data' => $brands]);
    }

    /**
     * Duplicate a content widget
     */
    public function duplicate(int $id): JsonResponse
    {
        $content = AppContent::findOrFail($id);

        $newContent = null;
        DB::transaction(function () use ($content, &$newContent) {
            $query = AppContent::query();
            if ($content->header_tab_id === null) {
                $query->whereNull('header_tab_id');
            } else {
                $query->where('header_tab_id', $content->header_tab_id);
            }
            $query->where('sort_order', '>', $content->sort_order)->increment('sort_order');

            $newContent = $content->replicate();
            $newContent->title = $content->title ? $content->title . ' (Copy)' : null;
            $newContent->sort_order = $content->sort_order + 1;
            $newContent->save();
        });

        $this->purgeCloudflareCache();

        return response()->json([
            'success' => true,
            'message' => 'Content widget duplicated successfully',
            'data' => $newContent,
        ]);
    }

    /**
     * Helper to purge Cloudflare Cache
     */
    protected function purgeCloudflareCache()
    {
        try {
            $email = Setting::where('key', 'cloudflare_email')->value('value');
            $apiKey = Setting::where('key', 'cloudflare_api_key')->value('value');
            $zoneId = Setting::where('key', 'cloudflare_zone_id')->value('value');

            if ($email && $apiKey && $zoneId) {
                $this->cloudflareService->purgeCache($email, $apiKey, $zoneId);
            }
        } catch (\Exception $e) {
            Log::error('Failed to purge Cloudflare cache: ' . $e->getMessage());
        }
    }
}

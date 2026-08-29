<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteContent;
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
use Plugins\Website\Helpers\WebsiteHelper;

class WebsiteContentController extends Controller
{
    protected $cloudflareService;
    protected StorageService $storage;

    public function __construct(CloudflareService $cloudflareService, StorageService $storage)
    {
        $this->cloudflareService = $cloudflareService;
        $this->storage = $storage;
    }

    /**
     * Display website content builder page
     */
    public function index(Request $request)
    {
        $tabs = HomeHeaderTab::orderBy('sort_order')->get();
        
        $selectedTabId = $request->query('tab_id') ?? $tabs->first()?->id;
        
        $contents = collect();
        if ($selectedTabId) {
            $contents = WebsiteContent::where('header_tab_id', $selectedTabId)
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

        return view('admin.settings.website-content', compact('tabs', 'contents', 'selectedTabId'));
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
        
        $contents = WebsiteContent::where('header_tab_id', $tabId)
            ->orderBy('sort_order')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $contents,
        ]);
    }

    /**
     * Store a new content widget for website
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
            'grid_columns' => 'nullable|integer|min:1|max:7',
            'grid_columns_mobile' => 'nullable|integer|min:1|max:3',
            'grid_columns_laptop' => 'nullable|integer|min:2|max:7',
            'grid_rows' => 'nullable|integer|min:1|max:10',
            'enable_horizontal_animation' => 'boolean',
            'show_on_mobile' => 'boolean',
            'show_on_laptop' => 'boolean',
            'media_items' => 'nullable|array',
            'media_items.*.url' => 'nullable|string|max:500',
            'media_items.*.type' => ['nullable', Rule::in(['image', 'gif', 'video'])],
            'media_items.*.link_type' => ['nullable', Rule::in(['none', 'product', 'category', 'brand', 'store', 'url'])],
            'media_items.*.link_id' => 'nullable|integer',
            'media_items.*.link_url' => 'nullable|string|max:500',
            'media_url' => 'nullable|string|max:500',
            'media_type' => ['nullable', Rule::in(['image', 'gif', 'video'])],
            'media_height' => 'nullable|integer|min:50|max:500',
            'media_width' => 'nullable|integer',
            'link_type' => ['nullable', Rule::in(['none', 'product', 'category', 'brand', 'store', 'url'])],
            'link_id' => 'nullable|integer',
            'link_url' => 'nullable|string|max:500',
            'custom_items' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['media_items'])) {
            $validated['media_items'] = array_values(array_filter($validated['media_items'], function($item) {
                return $item !== null && !empty($item);
            }));
        }

        $maxOrder = WebsiteContent::where('header_tab_id', $validated['header_tab_id'] ?? null)
            ->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $validated['show_on_mobile'] = $request->has('show_on_mobile') ? $request->boolean('show_on_mobile') : true;
        $validated['show_on_laptop'] = $request->has('show_on_laptop') ? $request->boolean('show_on_laptop') : true;

        $content = WebsiteContent::create($validated);
        
        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Website content widget created successfully',
            'data' => $content,
        ], 201);
    }

    /**
     * Update a website content widget
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $content = WebsiteContent::findOrFail($id);

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
            'grid_columns' => 'nullable|integer|min:1|max:7',
            'grid_columns_mobile' => 'nullable|integer|min:1|max:3',
            'grid_columns_laptop' => 'nullable|integer|min:2|max:7',
            'grid_rows' => 'nullable|integer|min:1|max:10',
            'enable_horizontal_animation' => 'boolean',
            'show_on_mobile' => 'boolean',
            'show_on_laptop' => 'boolean',
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
            'media_width' => 'nullable|integer',
            'link_type' => ['nullable', Rule::in(['none', 'product', 'category', 'brand', 'store', 'url'])],
            'link_id' => 'nullable|integer',
            'link_url' => 'nullable|string|max:500',
            'custom_items' => 'nullable|array',
            'is_active' => 'boolean',
            'target' => ['nullable', Rule::in(['all', 'app', 'website'])],
        ]);

        if (isset($validated['media_items'])) {
            $validated['media_items'] = array_values(array_filter($validated['media_items'], function($item) {
                return $item !== null && !empty($item);
            }));
        }

        $content->update($validated);
        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Website content widget updated successfully',
            'data' => $content->fresh(),
        ]);
    }

    /**
     * Delete a website content widget
     */
    public function destroy(int $id): JsonResponse
    {
        $content = WebsiteContent::findOrFail($id);
        $content->delete();
        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Website content widget deleted successfully',
        ]);
    }

    /**
     * Reorder widgets
     */
    public function reorder(Request $request): JsonResponse
    {
        $items = $request->input('contents') ?? $request->input('items') ?? [];

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                if (isset($item['id']) && isset($item['sort_order'])) {
                    WebsiteContent::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
                }
            }
        });

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Website widgets reordered successfully',
        ]);
    }

    /**
     * Duplicate a widget
     */
    public function duplicate(int $id): JsonResponse
    {
        $content = WebsiteContent::findOrFail($id);

        $newContent = null;
        DB::transaction(function () use ($content, &$newContent) {
            $query = WebsiteContent::query();
            if ($content->header_tab_id === null) {
                $query->whereNull('header_tab_id');
            } else {
                $query->where('header_tab_id', $content->header_tab_id);
            }
            $query->where('sort_order', '>', $content->sort_order)->increment('sort_order');

            $newContent = $content->replicate();
            $newContent->title = ($content->title ? $content->title . ' (Copy)' : 'Copy');
            $newContent->sort_order = $content->sort_order + 1;
            $newContent->save();
        });

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Widget duplicated successfully',
            'data' => $newContent,
        ]);
    }

    /**
     * Helper methods for selecting custom items
     */
    public function availableProducts(Request $request): JsonResponse
    {
        $query = Product::where('is_active', true);
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $products = $query->select('id', 'name', 'price', 'sku')->limit(50)->get();
        return response()->json(['success' => true, 'data' => $products]);
    }

    public function availableCategories(Request $request): JsonResponse
    {
        $query = Category::where('is_active', true);
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $categories = $query->select('id', 'name')->limit(50)->get();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function availableBrands(Request $request): JsonResponse
    {
        $query = Brand::where('is_active', true);
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $brands = $query->select('id', 'name')->limit(50)->get();
        return response()->json(['success' => true, 'data' => $brands]);
    }

    public function availableStores(Request $request): JsonResponse
    {
        $query = Store::where('status', \App\Enums\StoreStatus::ACTIVE);
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $stores = $query->select('id', 'name')->limit(50)->get();
        return response()->json(['success' => true, 'data' => $stores]);
    }

    /**
     * Upload media for website content widget
     */
    public function uploadMedia(Request $request, int $id): JsonResponse
    {
        $content = WebsiteContent::findOrFail($id);

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm,webp|max:20480',
            'type' => ['required', Rule::in(['image', 'gif', 'video'])],
        ]);

        if ($content->media_url) {
            $this->storage->delete($content->media_url);
        }

        $path = $this->storage->store($request->file('file'), 'website-content/media');
        $fullUrl = $this->storage->url($path);
        
        $content->update([
            'media_url' => $path,
            'media_type' => $request->type,
        ]);
        
        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Media uploaded successfully',
            'data' => [
                'media_url' => $fullUrl,
                'media_type' => $content->media_type,
            ],
        ]);
    }

    /**
     * Upload background media for website widget
     */
    public function uploadBackgroundMedia(Request $request, int $id): JsonResponse
    {
        $content = WebsiteContent::findOrFail($id);

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm,webp|max:20480',
            'type' => ['required', Rule::in(['image', 'gif', 'video'])],
        ]);

        if ($content->background_media_url) {
            $this->storage->delete($content->background_media_url);
        }

        $path = $this->storage->store($request->file('file'), 'website-content/backgrounds');
        $fullUrl = $this->storage->url($path);
        
        $content->update([
            'background_media_url' => $path,
            'background_type' => $request->type,
        ]);
        
        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Background media uploaded successfully',
            'data' => [
                'background_media_url' => $fullUrl,
                'background_type' => $content->background_type,
            ],
        ]);
    }

    /**
     * Upload individual media item for media widget grid
     */
    public function uploadMediaItem(Request $request, int $id): JsonResponse
    {
        $content = WebsiteContent::findOrFail($id);

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm,webp|max:20480',
            'type' => ['required', Rule::in(['image', 'gif', 'video'])],
            'index' => 'required|integer|min:0',
        ]);

        $path = $this->storage->store($request->file('file'), 'website-content/items');
        $fullUrl = $this->storage->url($path);
        
        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Media item uploaded successfully',
            'data' => [
                'url' => $fullUrl,
                'type' => $request->type,
                'index' => (int) $request->index,
            ],
        ]);
    }

    private function clearCache(): void
    {
        try {
            if (class_exists(WebsiteHelper::class)) {
                WebsiteHelper::clearCache();
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }
}

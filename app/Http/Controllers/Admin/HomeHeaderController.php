<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeHeaderCard;
use App\Models\HomeHeaderSetting;
use App\Models\HomeHeaderTab;
use App\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Setting;
use App\Services\CloudflareService;
use Illuminate\Support\Facades\Log;

class HomeHeaderController extends Controller
{
    protected $cloudflareService;
    protected StorageService $storage;

    public function __construct(CloudflareService $cloudflareService, StorageService $storage)
    {
        $this->cloudflareService = $cloudflareService;
        $this->storage = $storage;
    }
    /**
     * Get all home header configuration for admin
     */
    public function index()
    {
        $settings = HomeHeaderSetting::getSettings();
        $tabs = HomeHeaderTab::with('allCards', 'category:id,name,image')
            ->orderBy('sort_order')
            ->get()
            ->each(function ($tab) {
                // Resolve raw stored paths to full public URLs for the blade/Alpine
                if ($tab->background_url && !str_starts_with($tab->background_url, 'http')) {
                    $tab->background_url = $this->storage->url($tab->background_url);
                }
                $tab->allCards->each(function ($card) {
                    if ($card->image_url && !str_starts_with($card->image_url, 'http')) {
                        $card->image_url = $this->storage->url($card->image_url);
                    }
                });
            });

        // Get available categories for new tabs
        $usedCategoryIds = HomeHeaderTab::whereNotNull('category_id')
            ->pluck('category_id')
            ->toArray();

        $availableCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->whereNotIn('id', $usedCategoryIds)
            ->select('id', 'name', 'image')
            ->orderBy('name')
            ->get();

        // Return view for web requests, JSON for API
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'settings' => $settings,
                    'tabs' => $tabs,
                ],
            ]);
        }

        return view('admin.settings.home-header', compact('settings', 'tabs', 'availableCategories'));
    }

    /**
     * Update main settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        if (filter_var(env('DEMO_MODE', false), FILTER_VALIDATE_BOOLEAN)) {
            return response()->json([
                'success' => false,
                'message' => 'Modifying Global Module Settings is disabled in Demo Mode.',
            ], 403);
        }

        $validated = $request->validate([
            'tabs_active' => 'boolean',
            'background_active' => 'boolean',
            'cards_active' => 'boolean',
            'cards_horizontal' => 'boolean',
            'tabs_horizontal_style' => 'boolean',
            'module_icon_style' => 'nullable|string|in:image_only,image_and_name',
        ]);

        $settings = HomeHeaderSetting::updateSettings($validated);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $settings,
        ]);
    }

    /**
     * Get available categories for tabs
     */
    public function availableCategories(): JsonResponse
    {
        $usedCategoryIds = HomeHeaderTab::whereNotNull('category_id')
            ->pluck('category_id')
            ->toArray();

        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->whereNotIn('id', $usedCategoryIds)
            ->select('id', 'name', 'image')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get all categories for card linking
     */
    public function allCategories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->select('id', 'name', 'image')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get available products for card linking
     */
    public function availableProducts(): JsonResponse
    {
        $products = \App\Models\Product::where('is_active', true)
            ->with('primaryImage:id,product_id,image')
            ->select('id', 'name')
            ->orderBy('name')
            ->limit(100)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'thumbnail' => $product->primaryImage?->image,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get available stores for card linking
     */
    public function availableStores(): JsonResponse
    {
        $stores = \App\Models\Store::where('status', \App\Enums\StoreStatus::ACTIVE)
            ->select('id', 'name', 'logo')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stores,
        ]);
    }

    /**
     * Create a new tab
     */
    public function storeTab(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:50',
            'module_type' => 'nullable|string|in:grocery,food,pharmacy,ecommerce,cosmetic,flower,ride,parcel,service',
            'use_header_name' => 'boolean',
            'background_type' => ['required', Rule::in(['image', 'video', 'gif'])],
            'background_url' => 'nullable|string|max:500',
            'sticky_header_color' => 'nullable|string|max:7',
            'cards_horizontal' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['module_type'] = $validated['module_type'] ?? 'grocery';

        // Get max sort order
        $maxOrder = HomeHeaderTab::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        $tab = HomeHeaderTab::create($validated);

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Module created successfully',
            'data' => $tab->load('category:id,name,image'),
        ], 201);
    }

    /**
     * Dedicated view to edit a single module
     */
    public function editTab(int $tabId)
    {
        $tab = HomeHeaderTab::with('allCards')->findOrFail($tabId);

        if ($tab->background_url && !str_starts_with($tab->background_url, 'http')) {
            $tab->background_url = $this->storage->url($tab->background_url);
        }
        if ($tab->header_bg_image_url && !str_starts_with($tab->header_bg_image_url, 'http')) {
            $tab->header_bg_image_url = $this->storage->url($tab->header_bg_image_url);
        }
        $tab->allCards->each(function ($card) {
            if ($card->image_url && !str_starts_with($card->image_url, 'http')) {
                $card->image_url = $this->storage->url($card->image_url);
            }
        });

        $categories = Category::withoutGlobalScopes()
            ->where(function ($q) use ($tabId) {
                $q->where('module_id', $tabId)->orWhereNull('module_id');
            })
            ->where('is_active', true)
            ->select('id', 'name', 'image')
            ->orderBy('name')
            ->get();

        $products = \App\Models\Product::withoutGlobalScopes()
            ->where(function ($q) use ($tabId) {
                $q->where('module_id', $tabId)->orWhereNull('module_id');
            })
            ->where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $stores = \App\Models\Store::withoutGlobalScopes()
            ->where(function ($q) use ($tabId) {
                $q->where('module_id', $tabId)->orWhereNull('module_id');
            })
            ->where('status', \App\Enums\StoreStatus::ACTIVE)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admin.settings.edit-module', compact('tab', 'categories', 'products', 'stores'));
    }

    /**
     * Update a tab
     */
    public function updateTab(Request $request, int $tabId): JsonResponse
    {
        $tab = HomeHeaderTab::findOrFail($tabId);

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'nullable|string|max:50',
            'icon_url' => 'nullable|string|max:500',
            'module_type' => 'nullable|string|in:grocery,food,pharmacy,ecommerce,cosmetic,flower,ride,parcel,service',
            'use_header_name' => 'boolean',
            'background_type' => ['sometimes', Rule::in(['image', 'video', 'gif'])],
            'background_url' => 'nullable|string|max:500',
            'sticky_header_color' => 'nullable|string|max:7',
            'header_bg_type' => ['sometimes', Rule::in(['gradient', 'solid', 'image'])],
            'header_gradient_color1' => 'nullable|string|max:10',
            'header_gradient_color2' => 'nullable|string|max:10',
            'header_gradient_style' => ['sometimes', Rule::in([
                'top_to_bottom', 'bottom_to_top',
                'left_to_right', 'right_to_left',
                'top_left_to_bottom_right', 'bottom_right_to_top_left',
                'top_right_to_bottom_left', 'bottom_left_to_top_right',
                'diagonal'
            ])],
            'header_bg_image_url' => 'nullable|string|max:500',
            'cards_horizontal' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $tab->update($validated);

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Tab updated successfully',
            'data' => $tab->fresh()->load('category:id,name,image', 'allCards'),
        ]);
    }

    /**
     * Delete a tab
     */
    public function destroyTab(int $tabId): JsonResponse
    {
        $tab = HomeHeaderTab::findOrFail($tabId);
        $tab->delete();

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Tab deleted successfully',
        ]);
    }

    /**
     * Reorder tabs
     */
    public function reorderTabs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tabs' => 'required|array',
            'tabs.*.id' => 'required|exists:home_header_tabs,id',
            'tabs.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['tabs'] as $tabData) {
                $tab = HomeHeaderTab::find($tabData['id']);
                if ($tab) {
                    $tab->sort_order = $tabData['sort_order'];
                    $tab->save();
                }
            }
        });

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Tabs reordered successfully',
            'data' => HomeHeaderTab::with('allCards', 'category:id,name,image')->orderBy('sort_order')->get()
        ]);
    }

    /**
     * Upload background media for a tab
     */
    public function uploadBackground(Request $request, int $tabId): JsonResponse
    {
        $tab = HomeHeaderTab::findOrFail($tabId);

        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm,webp|max:20480', // 20MB max
            'type' => ['nullable', Rule::in(['image', 'video', 'gif'])],
        ]);

        $type = $request->input('type') ?: ($tab->background_type ?: 'image');

        // Delete old file if exists
        if ($tab->background_url && !str_starts_with($tab->background_url, 'http')) {
            $this->storage->delete($tab->background_url);
        }

        // Store new file
        $path = $this->storage->store($request->file('file'), 'home-header/backgrounds');
        $url = $this->storage->url($path);

        $tab->update([
            'background_type' => $type,
            'background_url' => $url,
        ]);

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Background media uploaded successfully',
            'url' => $url,
            'data' => [
                'background_type' => $tab->background_type,
                'background_url'  => $url,
            ],
        ]);
    }

    /**
     * Upload icon image for a tab
     */
    public function uploadIcon(Request $request, int $tabId): JsonResponse
    {
        $tab = HomeHeaderTab::findOrFail($tabId);

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
        ]);

        if ($tab->icon_url && !str_starts_with($tab->icon_url, 'http')) {
            $this->storage->delete($tab->icon_url);
        }

        $path = $this->storage->store($request->file('file'), 'home-header/icons');
        $url = $this->storage->url($path);

        $tab->update(['icon_url' => $url]);

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Module icon uploaded successfully',
            'url' => $url,
            'data' => [
                'icon_url' => $url,
            ],
        ]);
    }

    /**
     * Create a card for a tab
     */
    public function storeCard(Request $request, int $tabId): JsonResponse
    {
        $tab = HomeHeaderTab::findOrFail($tabId);

        // Check if tab already has 6 cards
        if ($tab->allCards()->count() >= 6) {
            return response()->json([
                'success' => false,
                'message' => 'Tab already has maximum 6 cards',
            ], 422);
        }

        $validated = $request->validate([
            'image_url' => 'nullable|string|max:500',
            'link_type' => ['required', Rule::in(['category', 'product', 'store', 'url'])],
            'link_id' => 'nullable|integer',
            'link_url' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $maxOrder = $tab->allCards()->max('sort_order') ?? 0;
        $validated['tab_id'] = $tabId;
        $validated['sort_order'] = $maxOrder + 1;

        $card = HomeHeaderCard::create($validated);

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Card created successfully',
            'data' => $card,
        ], 201);
    }

    /**
     * Update a card
     */
    public function updateCard(Request $request, int $cardId): JsonResponse
    {
        $card = HomeHeaderCard::findOrFail($cardId);

        $validated = $request->validate([
            'image_url' => 'nullable|string|max:500',
            'link_type' => ['sometimes', Rule::in(['category', 'product', 'store', 'url'])],
            'link_id' => 'nullable|integer',
            'link_url' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $card->update($validated);

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Card updated successfully',
            'data' => $card->fresh(),
        ]);
    }

    /**
     * Delete a card
     */
    public function destroyCard(int $cardId): JsonResponse
    {
        $card = HomeHeaderCard::findOrFail($cardId);
        $card->delete();

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Card deleted successfully',
        ]);
    }

    /**
     * Reorder cards within a tab
     */
    public function reorderCards(Request $request, int $tabId): JsonResponse
    {
        $cardIds = $request->input('card_ids');
        $cards = $request->input('cards');

        DB::transaction(function () use ($cardIds, $cards, $tabId) {
            if (is_array($cardIds)) {
                foreach ($cardIds as $order => $id) {
                    HomeHeaderCard::where('id', $id)
                        ->where('tab_id', $tabId)
                        ->update(['sort_order' => $order + 1]);
                }
            } elseif (is_array($cards)) {
                foreach ($cards as $cardData) {
                    if (isset($cardData['id'])) {
                        HomeHeaderCard::where('id', $cardData['id'])
                            ->where('tab_id', $tabId)
                            ->update(['sort_order' => $cardData['sort_order'] ?? 0]);
                    }
                }
            }
        });

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Cards reordered successfully',
            'data' => HomeHeaderCard::where('tab_id', $tabId)->orderBy('sort_order')->get()
        ]);
    }

    /**
     * Upload card image
     */
    public function uploadCardImage(Request $request, int $cardId = 0): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
        ]);

        $path = $this->storage->store($request->file('file'), 'home-header/cards');
        $url = $this->storage->url($path);

        if ($cardId > 0) {
            $card = HomeHeaderCard::find($cardId);
            if ($card) {
                $card->update(['image_url' => $path]);
            }
        }

        $this->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Card image uploaded successfully',
            'url' => $url,
            'data' => ['image_url' => $url],
        ]);
    }

    /**
     * Clear all related caches (legacy - kept for manual cache clearing if needed)
     */
    private function clearCache(): void
    {
        try {
            Cache::flush();
        } catch (\Exception $e) {
            Cache::forget('home_header_config');
            Cache::forget('home_header_settings');
            Cache::forget('home_header_last_updated');
        }

        // Trigger Cloudflare Purge
        $this->purgeCloudflareCache();
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

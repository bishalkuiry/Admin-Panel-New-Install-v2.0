<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Unit;
use App\Services\SearchService;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Brand;
use App\Models\Setting;
use App\Models\Store;
use App\Services\CloudflareService;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected $searchService;
    protected $cloudflareService;
    protected StorageService $storage;

    public function __construct(SearchService $searchService, CloudflareService $cloudflareService, StorageService $storage)
    {
        $this->searchService = $searchService;
        $this->cloudflareService = $cloudflareService;
        $this->storage = $storage;
    }

    public function index(Request $request)
    {
        // Default listing for in-house products (store_id IS NULL)
        $request->merge(['in_house' => true]);

        if ($request->filled('search')) {
            try {
                $searchResults = $this->searchService->searchProducts($request);
                $products = $searchResults->paginate(15);
            } catch (\Exception $e) {
                $products = $this->fallbackSearch($request);
            }
        } else {
            $products = $this->fallbackSearch($request);
        }

        $categories = Category::active()->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Display seller products
     */
    public function sellerProducts(Request $request)
    {
        $request->merge(['is_seller' => true]);

        if ($request->filled('search')) {
            try {
                $searchResults = $this->searchService->searchProducts($request);
                $products = $searchResults->paginate(15);
            } catch (\Exception $e) {
                $products = $this->fallbackSearch($request);
            }
        } else {
            $products = $this->fallbackSearch($request);
        }

        $categories = Category::active()->get();
        $stores = Store::active()->get();

        return view('admin.products.index', compact('products', 'categories', 'stores'));
    }

    /**
     * Fallback search using database queries
     */
    private function fallbackSearch(Request $request)
    {
        $query = Product::with('category', 'store', 'primaryImage');

        if ($request->boolean('in_house')) {
            $query->whereNull('store_id');
        }

        if ($request->boolean('is_seller')) {
            $query->whereNotNull('store_id');
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('stock')) {
            if ($request->stock === 'low') {
                $query->lowStock();
            } elseif ($request->stock === 'out') {
                $query->where('quantity', 0);
            } elseif ($request->stock === 'in') {
                $query->where('quantity', '>', 0);
            }
        }

        return $query->latest()->paginate(15);
    }

    public function create()
    {
        $categories = Category::active()->get();
        $attributes = Attribute::with('values')->get();
        $units = Unit::active()->orderBy('sort_order')->get();
        $brands = Brand::active()->orderBy('name')->get();
        $stores = Store::active()->orderBy('name')->get();

        $activeModuleId = session('admin_active_module');
        $activeModule = null;
        if ($activeModuleId && $activeModuleId !== 'all') {
            $activeModule = \App\Models\HomeHeaderTab::find($activeModuleId);
        }
        $moduleType = $activeModule?->module_type ?? 'grocery';

        $foodAddons = \App\Models\FoodAddon::where('is_active', true)->orderBy('name')->get();

        $existingWarranties = Product::whereNotNull('warranty_summary')
            ->where('warranty_summary', '!=', '')
            ->distinct()
            ->pluck('warranty_summary');

        $existingGuarantees = Product::whereNotNull('guarantee_summary')
            ->where('guarantee_summary', '!=', '')
            ->distinct()
            ->pluck('guarantee_summary');

        $taxes = class_exists(\App\Models\Tax::class) ? \App\Models\Tax::where('is_active', true)->get() : collect();

        return view('admin.products.create', compact('categories', 'attributes', 'units', 'brands', 'stores', 'taxes', 'activeModule', 'moduleType', 'foodAddons', 'existingWarranties', 'existingGuarantees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
            'vendor_sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'tax_class' => 'nullable|string|max:100',
            'hsn_code' => 'nullable|string|max:50',
            'quantity' => 'integer|min:0',
            'low_stock_threshold' => 'integer|min:0',
            'track_inventory' => 'boolean',
            'allow_backorder' => 'boolean',
            'unit' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric',
            'weight_unit' => 'nullable|string|max:20',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'dimension_unit' => 'nullable|string|max:20',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'brand' => 'nullable|string|max:255',
            'store_id' => 'nullable|exists:stores,id',
            'module_type' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        'is_veg' => 'nullable|string',
        'is_prescription_required' => 'boolean',
        'generic_name' => 'nullable|string|max:255',
        'prep_time_min' => 'nullable|integer|min:0',
        'prep_time_max' => 'nullable|integer|min:0',
        'prep_time_unit' => 'nullable|string|in:minutes,hours',
        'available_time_starts' => 'nullable|string',
        'available_time_ends' => 'nullable|string',
        'is_halal' => 'boolean',
        'nutrition_info' => 'nullable|string',
        'nutritional_info' => 'nullable|string',
        'search_tags' => 'nullable|string',
        'video_type' => 'nullable|string|in:upload,youtube,vimeo,url',
        'video_url' => 'nullable|string|max:500',
        'return_period_days' => 'nullable|integer|min:0',
        'replacement_period_days' => 'nullable|integer|min:0',
        'warranty_summary' => 'nullable|string|max:255',
        'guarantee_summary' => 'nullable|string|max:255',
        'delivered_by_lead_hours' => 'nullable|integer|min:0',
        'manufacture_date' => 'nullable|date',
        'expiry_date' => 'nullable|date',
        'shelf_life_days' => 'nullable|integer|min:0',
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string',
        'food_variation_groups' => 'nullable|array',
    ]);

    $validated['return_period_days'] = $validated['return_period_days'] ?? 0;
    $validated['replacement_period_days'] = $validated['replacement_period_days'] ?? 0;
    $validated['delivered_by_lead_hours'] = $validated['delivered_by_lead_hours'] ?? 12;

    if ($request->input('is_veg') === 'none' || !$request->has('is_veg')) {
        $validated['is_veg'] = null;
    } else {
        $validated['is_veg'] = (int)$request->input('is_veg');
    }

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);

        $product = Product::create($validated);

        if ($request->has('food_addon_ids')) {
            $product->foodAddons()->sync($request->input('food_addon_ids', []));
        }

        if ($request->has('food_variation_groups')) {
            foreach ($request->input('food_variation_groups', []) as $groupIndex => $groupData) {
                if (empty($groupData['name'])) continue;
                $group = $product->foodVariationGroups()->create([
                    'name' => $groupData['name'],
                    'is_required' => !empty($groupData['is_required']),
                    'selection_type' => $groupData['selection_type'] ?? 'single',
                    'min_selection' => $groupData['min_selection'] ?? 0,
                    'max_selection' => $groupData['max_selection'] ?? 1,
                    'sort_order' => $groupIndex,
                ]);

                if (isset($groupData['options']) && is_array($groupData['options'])) {
                    foreach ($groupData['options'] as $optionIndex => $optData) {
                        if (empty($optData['option_name'])) continue;
                        $group->options()->create([
                            'option_name' => $optData['option_name'],
                            'price' => $optData['price'] ?? 0.00,
                            'is_default' => !empty($optData['is_default']),
                            'sort_order' => $optionIndex,
                        ]);
                    }
                }
            }
        }
        
        // TRIGGER CLOUDFLARE PURGE
        $this->purgeCloudflareCache();

        // Handle product images (File Uploads)
    // Handle product images (File Uploads)
    $imageCount = 0;
    
    // Check for explicit primary image selection from gallery
    $primaryImage = $request->input('primary_image');
    $primaryPath = $primaryImage ? str_replace('/storage/', '', $primaryImage) : null;
    $primarySet = false;
    
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $path = $this->storage->store($image, 'products');
            
            // If explicit primary is set (from gallery), files are not primary
            // Otherwise, first file is primary
            $isPrimary = $primaryPath ? false : ($imageCount === 0);
            
            ProductImage::create([
                'product_id' => $product->id,
                'image' => $path,
                'sort_order' => $imageCount,
                'is_primary' => $isPrimary,
            ]);
            
            if ($isPrimary) $primarySet = true;
            $imageCount++;
        }
    }

    // Handle gallery/AI images (Paths already on server)
    if ($request->has('gallery_images') && is_array($request->gallery_images)) {
        foreach ($request->gallery_images as $imageUrl) {
            // Normalise: strip full URL prefix and /storage/ to get relative path
            $path = app(\App\Services\StorageService::class)->normalisePath(
                preg_replace('#^https?://[^/]+#', '', $imageUrl)
            );
            
            // Determine primary status
            if ($primaryPath) {
                $isPrimary = ($path === $primaryPath);
            } else {
                // Fallback: if no primary set yet and this is the first image processed (and no file was primary)
                $isPrimary = (!$primarySet && $imageCount === 0);
            }
            
            ProductImage::create([
                'product_id' => $product->id,
                'image' => $path,
                'sort_order' => $imageCount,
                'is_primary' => $isPrimary,
            ]);
            $imageCount++;
        }
    }

    $this->ensurePrimaryImageExists($product);

        // Handle variants
        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $index => $variantData) {
                // Handle variant image
                $imagePath = null;
                if (isset($variantData['image']) && $variantData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $imagePath = $this->storage->store($variantData['image'], 'products/variants');
                }

                $variant = $product->variants()->create([
                    'name' => $variantData['name'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'mrp' => $variantData['mrp'] ?? null,
                    'selling_price' => $variantData['selling_price'],
                    'quantity' => $variantData['quantity'] ?? 0,
                    'unit_value' => $variantData['unit_value'] ?? null,
                    'weight' => $variantData['weight'] ?? null,
                    'is_active' => isset($variantData['is_active']) ? true : false,
                    'image' => $imagePath,
                    'sort_order' => $index,
                ]);

                // Sync variant attributes
                if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
                    $attributeValues = array_filter($variantData['attributes']);
                    if (!empty($attributeValues)) {
                        $variant->attributeValues()->sync($attributeValues);
                    }
                }
            }
        }

        // Set meta_image from primary image for SEO
        $primaryImage = $product->primaryImage()->first();
        if ($primaryImage && empty($product->meta_image)) {
            $imageUrl = storage_url($primaryImage->image);
            $product->update(['meta_image' => $imageUrl]);
        }

        return redirect()->route($product->store_id ? 'admin.products.seller' : 'admin.products.index')
            ->with('success', 'Product created successfully!');
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
                // We purge everything to ensure consistency across all paginated pages and filters
                // This is the "Brutal" approach ensuring no stale data exists
                $this->cloudflareService->purgeCache($email, $apiKey, $zoneId);
            }
        } catch (\Exception $e) {
            Log::error('Failed to purge Cloudflare cache: ' . $e->getMessage());
        }
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'attributes', 'variants.attributeValues.attribute', 'foodAddons', 'foodVariationGroups.options']);
        $categories = Category::active()->get();
        $attributes = Attribute::with('values')->get();
        $units = Unit::active()->orderBy('sort_order')->get();
        $brands = Brand::active()->orderBy('name')->get();
        $stores = Store::active()->orderBy('name')->get();
        $foodAddons = \App\Models\FoodAddon::where('is_active', true)->orderBy('name')->get();

        $activeModuleId = session('admin_active_module');
        $activeModule = null;
        if ($activeModuleId && $activeModuleId !== 'all') {
            $activeModule = \App\Models\HomeHeaderTab::find($activeModuleId);
        }
        $moduleType = $activeModule?->module_type ?? 'grocery';

        $existingWarranties = Product::whereNotNull('warranty_summary')
            ->where('warranty_summary', '!=', '')
            ->distinct()
            ->pluck('warranty_summary');

        $existingGuarantees = Product::whereNotNull('guarantee_summary')
            ->where('guarantee_summary', '!=', '')
            ->distinct()
            ->pluck('guarantee_summary');

        return view('admin.products.edit', compact('product', 'categories', 'attributes', 'units', 'brands', 'stores', 'foodAddons', 'existingWarranties', 'existingGuarantees', 'activeModule', 'moduleType'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'vendor_sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'tax_class' => 'nullable|string|max:100',
            'hsn_code' => 'nullable|string|max:50',
            'quantity' => 'integer|min:0',
            'low_stock_threshold' => 'integer|min:0',
            'track_inventory' => 'boolean',
            'allow_backorder' => 'boolean',
            'unit' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric',
            'weight_unit' => 'nullable|string|max:20',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'dimension_unit' => 'nullable|string|max:20',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'brand' => 'nullable|string|max:255',
            'store_id' => 'nullable|exists:stores,id',
            'module_type' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_veg' => 'nullable',
            'is_prescription_required' => 'boolean',
            'generic_name' => 'nullable|string|max:255',
            'prep_time_min' => 'nullable|integer|min:0',
            'prep_time_max' => 'nullable|integer|min:0',
            'prep_time_unit' => 'nullable|string|in:minutes,hours',
            'available_time_starts' => 'nullable|string',
            'available_time_ends' => 'nullable|string',
            'is_halal' => 'boolean',
            'nutrition_info' => 'nullable|string',
            'nutritional_info' => 'nullable|string',
            'search_tags' => 'nullable|string',
            'video_type' => 'nullable|string|in:upload,youtube,vimeo,url',
            'video_url' => 'nullable|string|max:500',
            'return_period_days' => 'nullable|integer|min:0',
            'replacement_period_days' => 'nullable|integer|min:0',
            'warranty_summary' => 'nullable|string|max:255',
            'guarantee_summary' => 'nullable|string|max:255',
            'delivered_by_lead_hours' => 'nullable|integer|min:0',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'shelf_life_days' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'food_variation_groups' => 'nullable|array',
        ]);

        $validated['return_period_days'] = $validated['return_period_days'] ?? 0;
        $validated['replacement_period_days'] = $validated['replacement_period_days'] ?? 0;
        $validated['delivered_by_lead_hours'] = $validated['delivered_by_lead_hours'] ?? 12;

        $validated['is_prescription_required'] = $request->boolean('is_prescription_required');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->input('is_veg') === 'none' || !$request->has('is_veg') || $request->input('is_veg') === '' || $request->input('is_veg') === null) {
            $validated['is_veg'] = null;
        } else {
            $validated['is_veg'] = (int) filter_var($request->input('is_veg'), FILTER_VALIDATE_BOOLEAN);
        }

        $previousQuantity = (int) $product->quantity;
        $product->update($validated);

        if ($previousQuantity <= 0 && (int)$product->quantity > 0) {
            $this->notifyUsersForRestock($product);
        }

        if ($request->has('food_variation_groups')) {
            $product->foodVariationGroups()->delete();
            foreach ($request->input('food_variation_groups', []) as $groupIndex => $groupData) {
                if (empty($groupData['name'])) continue;
                $group = $product->foodVariationGroups()->create([
                    'name' => $groupData['name'],
                    'is_required' => !empty($groupData['is_required']),
                    'selection_type' => $groupData['selection_type'] ?? 'single',
                    'min_selection' => $groupData['min_selection'] ?? 0,
                    'max_selection' => $groupData['max_selection'] ?? 1,
                    'sort_order' => $groupIndex,
                ]);

                if (isset($groupData['options']) && is_array($groupData['options'])) {
                    foreach ($groupData['options'] as $optionIndex => $optData) {
                        if (empty($optData['option_name'])) continue;
                        $group->options()->create([
                            'option_name' => $optData['option_name'],
                            'price' => $optData['price'] ?? 0.00,
                            'is_default' => !empty($optData['is_default']),
                            'sort_order' => $optionIndex,
                        ]);
                    }
                }
            }
        }

        if ($request->has('food_addon_ids')) {
            $product->foodAddons()->sync($request->input('food_addon_ids', []));
        }
        
        // TRIGGER CLOUDFLARE PURGE
        $this->purgeCloudflareCache();

        // Handle new product images (File Uploads)
    // Handle new product images (File Uploads)
    $lastOrder = $product->images()->max('sort_order') ?? -1;
    $newImageCount = 1;
    
    // Check for explicit primary image selection (for new images)
    $primaryImage = $request->input('primary_image');
    $primaryPath = $primaryImage ? str_replace('/storage/', '', $primaryImage) : null;
    
    // If a new image is marked as primary, unmark all existing images
    if ($primaryPath) {
        $product->images()->update(['is_primary' => false]);
    }
    
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $path = $this->storage->store($image, 'products');
            ProductImage::create([
                'product_id' => $product->id,
                'image' => $path,
                'sort_order' => $lastOrder + $newImageCount,
                // Files uploaded in Edit are not primary by default unless we add logic
            ]);
            $newImageCount++;
        }
    }

    // Handle new gallery/AI images
    if ($request->has('gallery_images') && is_array($request->gallery_images)) {
        foreach ($request->gallery_images as $imageUrl) {
            // Normalise: strip full URL prefix and /storage/ to get relative path
            $path = app(\App\Services\StorageService::class)->normalisePath(
                preg_replace('#^https?://[^/]+#', '', $imageUrl)
            );
            
            $isPrimary = ($primaryPath && $path === $primaryPath);
            
            ProductImage::create([
                'product_id' => $product->id,
                'image' => $path,
                'sort_order' => $lastOrder + $newImageCount,
                'is_primary' => $isPrimary,
            ]);
            $newImageCount++;
        }
    }

    $this->ensurePrimaryImageExists($product);

        // Handle variants
        if ($request->has('variants') && is_array($request->variants)) {
            $submittedVariantIds = [];
            
            foreach ($request->variants as $index => $variantData) {
                // Handle variant image
                $imagePath = null;
                if (isset($variantData['image']) && $variantData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $imagePath = $this->storage->store($variantData['image'], 'products/variants');
                }

                $variantAttributes = [
                    'name' => $variantData['name'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'mrp' => $variantData['mrp'] ?? null,
                    'selling_price' => $variantData['selling_price'],
                    'quantity' => $variantData['quantity'] ?? 0,
                    'unit_value' => $variantData['unit_value'] ?? null,
                    'weight' => $variantData['weight'] ?? null,
                    'is_active' => isset($variantData['is_active']) ? true : false,
                    'sort_order' => $index,
                ];

                // Only update image if a new one was uploaded
                if ($imagePath) {
                    $variantAttributes['image'] = $imagePath;
                }

                // Update existing or create new variant
                if (!empty($variantData['id'])) {
                    $variant = $product->variants()->find($variantData['id']);
                    if ($variant) {
                        $variant->update($variantAttributes);
                        $submittedVariantIds[] = $variant->id;
                    }
                } else {
                    // Use updateOrCreate with SKU to prevent duplicates
                    $sku = $variantData['sku'] ?? null;
                    if ($sku) {
                        $variant = $product->variants()->updateOrCreate(
                            ['sku' => $sku],
                            $variantAttributes
                        );
                    } else {
                        $variant = $product->variants()->create($variantAttributes);
                    }
                    $submittedVariantIds[] = $variant->id;
                }

                // Sync variant attributes
                if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
                    $attributeValues = array_filter($variantData['attributes']);
                    if (!empty($attributeValues)) {
                        $variant->attributeValues()->sync($attributeValues);
                    }
                }
            }

            // Delete variants that were removed
            $product->variants()->whereNotIn('id', $submittedVariantIds)->delete();
        }

        // Update meta_image if primary image changed or if it's empty
        $product->refresh();
        $primaryImage = $product->primaryImage()->first();
        if ($primaryImage) {
            $newImageUrl = storage_url($primaryImage->image);
            if ($product->meta_image !== $newImageUrl) {
                $product->update(['meta_image' => $newImageUrl]);
            }
        }

        return redirect()->route($product->store_id ? 'admin.products.seller' : 'admin.products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        $isSeller = $product->store_id !== null;
        $product->delete();
        
        // TRIGGER CLOUDFLARE PURGE
        $this->purgeCloudflareCache();

        return redirect()->route($isSeller ? 'admin.products.seller' : 'admin.products.index')
            ->with('success', 'Product deleted successfully!');
    }

    /**
     * Delete a product image
     */
    public function deleteImage(Request $request, Product $product)
    {
        $request->validate(['image_id' => 'required|exists:product_images,id']);
        
        $image = ProductImage::where('id', $request->image_id)
            ->where('product_id', $product->id)
            ->first();
        
        if (!$image) {
            return response()->json(['success' => false, 'message' => 'Image not found'], 404);
        }
        
        // Delete file from storage
        if ($this->storage->exists($image->image)) {
            $this->storage->delete($image->image);
        }
        
        $wasPrimary = $image->is_primary;
        $image->delete();
        
        $this->ensurePrimaryImageExists($product);
        
        return response()->json(['success' => true]);
    }

    /**
     * Reorder product images
     */
    public function reorderImages(Request $request, Product $product)
    {
        $request->validate(['order' => 'required|array']);
        
        foreach ($request->order as $index => $imageId) {
            ProductImage::where('id', $imageId)
                ->where('product_id', $product->id)
                ->update(['sort_order' => $index]);
        }
        
        $this->ensurePrimaryImageExists($product);

        return response()->json(['success' => true]);
    }

    /**
     * Set primary image
     */
    public function setPrimaryImage(Request $request, Product $product)
    {
        $request->validate(['image_id' => 'required|exists:product_images,id']);
        
        // Remove primary from all images
        $product->images()->update(['is_primary' => false]);
        
        // Set new primary
        $primaryImage = ProductImage::where('id', $request->image_id)
            ->where('product_id', $product->id)
            ->first();
            
        if ($primaryImage) {
            $primaryImage->update(['is_primary' => true]);
            
            // Update meta_image for SEO
            $imageUrl = storage_url($primaryImage->image);
            $product->update(['meta_image' => $imageUrl]);
        }
        
        $this->ensurePrimaryImageExists($product);

        return response()->json(['success' => true]);
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        
        // TRIGGER CLOUDFLARE PURGE
        $this->purgeCloudflareCache();
        
        return response()->json([
            'success' => true,
            'is_active' => $product->is_active
        ]);
    }

    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        
        // TRIGGER CLOUDFLARE PURGE
        $this->purgeCloudflareCache();
        
        return response()->json([
            'success' => true,
            'is_featured' => $product->is_featured
        ]);
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function searchSuggestions(Request $request)
    {
        $query = $request->query('q', '');
        $type = $request->query('type', 'products');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        try {
            $suggestions = $this->searchService->getSearchSuggestions($query, $type);
            return response()->json($suggestions);
        } catch (\Exception $e) {
            // Fallback to database search
            if ($type === 'products') {
                $results = Product::where('name', 'like', '%' . $query . '%')
                    ->orWhere('sku', 'like', '%' . $query . '%')
                    ->limit(5)
                    ->get(['id', 'name', 'sku']);
                    
                return response()->json($results->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'type' => 'product'
                    ];
                }));
            }
            
            return response()->json([]);
        }
    }

    /**
     * Clone/Duplicate an existing product item
     */
    public function clone(Product $product)
    {
        try {
            $product->load(['images', 'variants', 'attachments']);

            // Keep exact same name as original product
            $newName = $product->name;
            $newSlug = Str::slug($newName) . '-' . Str::lower(Str::random(5));
            $newSku  = $product->sku ? ($product->sku . '-' . strtoupper(Str::random(4))) : ('SKU-' . strtoupper(Str::random(6)));

            // Replicate base product model
            $clonedProduct = $product->replicate([
                'slug',
                'sku',
                'created_at',
                'updated_at',
                'deleted_at'
            ]);

            $clonedProduct->name = $newName;
            $clonedProduct->slug = $newSlug;
            $clonedProduct->sku  = $newSku;
            $clonedProduct->is_active = $product->is_active; // Keep exact same status
            $clonedProduct->save();

            // Duplicate Images
            foreach ($product->images as $img) {
                $clonedProduct->images()->create([
                    'image'      => $img->image,
                    'is_primary' => $img->is_primary,
                    'sort_order' => $img->sort_order ?? 0,
                ]);
            }

            // Duplicate Variants
            foreach ($product->variants as $variant) {
                $newVariantSku = $variant->sku ? ($variant->sku . '-COPY-' . strtoupper(Str::random(3))) : null;
                $clonedProduct->variants()->create([
                    'name'          => $variant->name,
                    'sku'           => $newVariantSku,
                    'price'         => $variant->price,
                    'compare_price' => $variant->compare_price,
                    'quantity'      => $variant->quantity,
                    'image'         => $variant->image,
                    'options'       => $variant->options,
                    'is_active'     => $variant->is_active ?? true,
                ]);
            }

            // Duplicate Product Attachments if any
            if (method_exists($product, 'attachments')) {
                foreach ($product->attachments as $att) {
                    $clonedProduct->attachments()->create([
                        'title'     => $att->title,
                        'file_path' => $att->file_path,
                        'file_type' => $att->file_type,
                    ]);
                }
            }

            // Duplicate Food Variation Groups & Options
            foreach ($product->foodVariationGroups as $group) {
                $newGroup = $clonedProduct->foodVariationGroups()->create([
                    'name'           => $group->name,
                    'is_required'    => $group->is_required,
                    'selection_type' => $group->selection_type,
                    'min_selection'  => $group->min_selection,
                    'max_selection'  => $group->max_selection,
                    'sort_order'     => $group->sort_order,
                ]);

                foreach ($group->options as $opt) {
                    $newGroup->options()->create([
                        'option_name' => $opt->option_name,
                        'price'       => $opt->price,
                        'is_default'  => $opt->is_default,
                        'sort_order'  => $opt->sort_order,
                    ]);
                }
            }

            // Purge Cloudflare Cache
            $this->purgeCloudflareCache();

            return redirect()->route('admin.products.edit', $clonedProduct->id)
                ->with('success', 'Product item cloned successfully! You can now edit the duplicate.');
        } catch (\Exception $e) {
            Log::error('Product clone failed', ['error' => $e->getMessage(), 'product_id' => $product->id]);
            return back()->with('error', 'Failed to clone product item: ' . $e->getMessage());
        }
    }

    private function notifyUsersForRestock(Product $product)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('stock_notifications')) return;

        $subscribers = \Illuminate\Support\Facades\DB::table('stock_notifications')
            ->where('product_id', $product->id)
            ->where('notified', false)
            ->get();

        foreach ($subscribers as $sub) {
            if ($sub->user_id) {
                $user = \App\Models\User::find($sub->user_id);
                if ($user && $user->fcm_token) {
                    \App\Helpers\NotificationHelper::sendFcmNotification(
                        $user->fcm_token,
                        '🎉 Back in Stock!',
                        "{$product->name} is now back in stock! Order before it sells out again.",
                        [
                            'type' => 'product_restocked',
                            'product_id' => (string) $product->id,
                        ]
                    );
                }
            }
            \Illuminate\Support\Facades\DB::table('stock_notifications')
                ->where('id', $sub->id)
                ->update(['notified' => true, 'updated_at' => now()]);
        }
    }

    /**
     * Enforce primary image rules:
     * - If 1 image exists: set as primary automatically.
     * - If >1 images exist and no primary image is set: set 1st image as primary by default.
     */
    private function ensurePrimaryImageExists(Product $product): void
    {
        $images = $product->images()->orderBy('sort_order')->get();
        $count = $images->count();

        if ($count === 1) {
            $single = $images->first();
            if (!$single->is_primary) {
                $product->images()->update(['is_primary' => false]);
                $single->update(['is_primary' => true]);
                $product->update(['meta_image' => storage_url($single->image)]);
            }
        } elseif ($count > 1) {
            $hasPrimary = $images->contains('is_primary', true);
            if (!$hasPrimary) {
                $first = $images->first();
                $first->update(['is_primary' => true]);
                $product->update(['meta_image' => storage_url($first->image)]);
            }
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Tag;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(private StorageService $storage) {}
    /**
     * List store products
     */
    public function index(Request $request)
    {
        $store = $request->user()->getCurrentStore();

        $query = $store->products()->with(['category', 'primaryImage', 'images']);

        if ($request->query('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->query('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->query('low_stock')) {
            $query->whereColumn('quantity', '<=', 'low_stock_threshold');
        }

        $products = $query->with(['category', 'tags', 'primaryImage', 'variants.attributeValues.attribute'])
            ->latest()
            ->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Create product
     */
    public function store(Request $request)
    {
        $store = $request->user()->getCurrentStore();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|in:g,kg,lb,oz',
            'brand' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'track_inventory' => 'boolean',
            'allow_backorder' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120', // 5MB max
            'variants' => 'nullable|array',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.sku' => 'nullable|string',
            'variants.*.mrp' => 'nullable|numeric|min:0',
            'variants.*.selling_price' => 'required_with:variants|numeric|min:0',
            'variants.*.quantity' => 'nullable|integer|min:0',
            'variants.*.unit_value' => 'nullable|string',
            'variants.*.weight' => 'nullable|numeric',
            'variants.*.is_active' => 'nullable|boolean',
            'variants.*.image' => 'nullable|image|max:5120',
            'variants.*.attributes' => 'nullable|array',
        ]);

        $validated['store_id'] = $store->id;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['sku'] = $validated['sku'] ?? 'SKU-' . strtoupper(Str::random(8));

        $product = Product::create($validated);

        // Handle Image Uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $this->storage->store($image, 'products/' . $product->id);
                
                $product->images()->create([
                    'image' => $path,
                    'is_primary' => $index === 0, // First image is primary
                    'sort_order' => $index,
                ]);
            }
        } else {
            Log::warning('No images found in request for product ' . $product->id);
        }

        $store->logActivity('product_created', $request->user(), [
            'entity_type' => 'product',
            'entity_id' => $product->id,
        ]);

        // Handle Tags
        if ($request->has('tags') && is_array($request->tags)) {
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                if (!empty($tagName)) {
                     $tag = Tag::firstOrCreate(['name' => trim($tagName)]);
                     $tagIds[] = $tag->id;
                }
            }
            $product->tags()->sync($tagIds);
        }

        // Handle variants
        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $index => $variantData) {
                // Handle variant image
                $imagePath = null;
                if (isset($variantData['image']) && $variantData['image'] instanceof UploadedFile) {
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
                    'is_active' => isset($variantData['is_active']) ? filter_var($variantData['is_active'], FILTER_VALIDATE_BOOLEAN) : false,
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

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => new ProductResource($product->load(['category', 'images', 'primaryImage', 'tags', 'variants.attributeValues.attribute'])),
        ], 201);
    }

    /**
     * Get product details
     */
    public function show(Request $request, int $productId)
    {
        $store = $request->user()->getCurrentStore();
        $product = $store->products()->with(['category', 'tags', 'images', 'attributes', 'variants.attributeValues.attribute'])->findOrFail($productId);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Update product
     */
    public function update(Request $request, int $productId)
    {
        $store = $request->user()->getCurrentStore();
        $product = $store->products()->findOrFail($productId);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
            'price' => 'sometimes|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'brand' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
        ]);

        $product->update($validated);

        // Handle New Image Uploads
        if ($request->hasFile('images')) {
            $currentCount = $product->images()->count();
            
            foreach ($request->file('images') as $index => $image) {
                $path = $this->storage->store($image, 'products/' . $product->id);
                
                $product->images()->create([
                    'image' => $path,
                    'is_primary' => $currentCount === 0 && $index === 0, // Set primary if no images existed
                    'sort_order' => $currentCount + $index,
                ]);
            }
        } else {
             Log::warning('No images found in update request for product ' . $product->id);
        }

        $store->logActivity('product_updated', $request->user(), [
            'entity_type' => 'product',
            'entity_id' => $product->id,
        ]);

        // Handle Tags
        if ($request->has('tags') && is_array($request->tags)) {
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                if (!empty($tagName)) {
                     $tag = Tag::firstOrCreate(['name' => trim($tagName)]);
                     $tagIds[] = $tag->id;
                }
            }
            $product->tags()->sync($tagIds);
        }

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
                    'mrp' => $variantData['mrp'] ?? $variantData['selling_price'],
                    'selling_price' => $variantData['selling_price'],
                    'quantity' => $variantData['quantity'] ?? 0,
                    'unit_value' => $variantData['unit_value'] ?? null,
                    'weight' => $variantData['weight'] ?? null,
                    'is_active' => isset($variantData['is_active']) ? filter_var($variantData['is_active'], FILTER_VALIDATE_BOOLEAN) : false,
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
                    $variant = $product->variants()->create($variantAttributes);
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

        return response()->json([
            'success' => true,
            'message' => 'Product updated',
            'data' => new ProductResource($product->fresh(['category', 'tags', 'images', 'primaryImage', 'variants.attributeValues.attribute'])),
        ]);
    }

    /**
     * Delete product
     */
    public function destroy(Request $request, int $productId)
    {
        $store = $request->user()->getCurrentStore();
        $product = $store->products()->findOrFail($productId);

        $store->logActivity('product_deleted', $request->user(), [
            'entity_type' => 'product',
            'entity_id' => $product->id,
            'old_values' => ['name' => $product->name],
        ]);

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted',
        ]);
    }

    /**
     * Update stock
     */
    public function updateStock(Request $request, int $productId)
    {
        $store = $request->user()->getCurrentStore();
        $product = $store->products()->findOrFail($productId);

        $request->validate([
            'quantity' => 'required|integer|min:0',
            'action' => 'required|in:set,add,subtract',
        ]);

        $oldQuantity = $product->quantity;

        switch ($request->action) {
            case 'set':
                $product->quantity = $request->quantity;
                break;
            case 'add':
                $product->quantity += $request->quantity;
                break;
            case 'subtract':
                $product->quantity = max(0, $product->quantity - $request->quantity);
                break;
        }

        $product->save();

        $store->logActivity('stock_updated', $request->user(), [
            'entity_type' => 'product',
            'entity_id' => $product->id,
            'old_values' => ['quantity' => $oldQuantity],
            'new_values' => ['quantity' => $product->quantity],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock updated',
            'data' => ['quantity' => $product->quantity],
        ]);
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(Request $request, int $productId)
    {
        $store = $request->user()->getCurrentStore();
        $product = $store->products()->findOrFail($productId);

        $product->update(['is_active' => !$product->is_active]);

        return response()->json([
            'success' => true,
            'message' => $product->is_active ? 'Product activated' : 'Product deactivated',
            'data' => ['is_active' => $product->is_active],
        ]);
    }

    /**
     * Upload product image
     */
    public function uploadImage(Request $request, int $productId)
    {
        $store = $request->user()->getCurrentStore();
        $product = $store->products()->findOrFail($productId);

        $request->validate([
            'image' => 'required|image|max:5120',
            'is_primary' => 'boolean',
        ]);

        $path = $this->storage->store($request->file('image'), 'products/' . $product->id);

        // If primary, unset other primary images
        if ($request->boolean('is_primary')) {
            $product->images()->update(['is_primary' => false]);
        }

        $image = $product->images()->create([
            'image' => $path,
            'is_primary' => $request->boolean('is_primary', $product->images()->count() === 0),
            'sort_order' => $product->images()->count(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $image,
        ]);
    }

    /**
     * Delete product image
     */
    public function deleteImage(Request $request, int $productId, int $imageId)
    {
        $store = $request->user()->getCurrentStore();
        $product = $store->products()->findOrFail($productId);
        
        $image = $product->images()->where('id', $imageId)->firstOrFail();

        // Delete file from storage
        if ($this->storage->exists($image->image)) {
            $this->storage->delete($image->image);
        }

        // Delete record
        $image->delete();

        // If primary image was deleted, set new primary
        if ($image->is_primary) {
            $newPrimary = $product->images()->orderBy('sort_order')->first();
            if ($newPrimary) {
                $newPrimary->update(['is_primary' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
            'data' => new ProductResource($product->fresh(['images', 'primaryImage'])),
        ]);
    }

    /**
     * Get low stock products
     */
    public function lowStock(Request $request)
    {
        $store = $request->user()->getCurrentStore();

        $products = $store->products()
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->with('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
        ]);
    }
}

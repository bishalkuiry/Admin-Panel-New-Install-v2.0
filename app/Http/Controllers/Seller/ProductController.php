<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\ProductImage;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct(private StorageService $storage) {}
    public function index(Request $request)
    {
        $store = $request->current_store;
        $query = Product::where('store_id', $store->id)->with(['category', 'primaryImage']);

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

        $products = $query->latest()->paginate(30);
        $categories = Category::active()->get();

        return view('seller.products.index', compact('products', 'categories', 'store'));
    }

    public function create(Request $request)
    {
        $store = $request->current_store;
        $categories = Category::active()->get();
        $brands = Brand::active()->orderBy('name')->get();
        $units = Unit::active()->orderBy('sort_order')->get();
        $attributes = Attribute::with('values')->get();

        return view('seller.products.create', compact('categories', 'brands', 'units', 'attributes', 'store'));
    }

    public function store(Request $request)
    {
        $store = $request->current_store;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'variants' => 'nullable|array',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.sku' => 'nullable|string',
            'variants.*.mrp' => 'nullable|numeric|min:0',
            'variants.*.selling_price' => 'required_with:variants|numeric|min:0',
            'variants.*.quantity' => 'nullable|integer|min:0',
            'variants.*.is_active' => 'nullable|boolean',
            'variants.*.attributes' => 'nullable|array',
        ]);

        $validated['store_id'] = $store->id;
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        $validated['is_active'] = $request->boolean('is_active');

        $product = Product::create($validated);

        // Handle Variants
        if ($request->has('variants') && is_array($request->input('variants'))) {
            foreach ($request->input('variants') as $index => $variantData) {
                $variant = $product->variants()->create([
                    'name' => $variantData['name'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'mrp' => $variantData['mrp'] ?? null,
                    'selling_price' => $variantData['selling_price'],
                    'quantity' => $variantData['quantity'] ?? 0,
                    'is_active' => isset($variantData['is_active']) ? true : false,
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

        // Handle Images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $this->storage->store($image, 'products');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        $this->ensurePrimaryImageExists($product);

        return redirect()->route('seller.products.index')->with('success', 'Product created successfully');
    }

    public function edit(Request $request, Product $product)
    {
        $store = $request->current_store;
        
        if ($product->store_id !== $store->id) {
            abort(403);
        }

        $product->load(['images', 'variants.attributeValues.attribute']);
        $categories = Category::active()->get();
        $brands = Brand::active()->orderBy('name')->get();
        $units = Unit::active()->orderBy('sort_order')->get();
        $attributes = Attribute::with('values')->get();

        return view('seller.products.edit', compact('product', 'categories', 'brands', 'units', 'attributes', 'store'));
    }

    public function update(Request $request, Product $product)
    {
        $store = $request->current_store;
        
        if ($product->store_id !== $store->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.sku' => 'nullable|string',
            'variants.*.mrp' => 'nullable|numeric|min:0',
            'variants.*.selling_price' => 'required_with:variants|numeric|min:0',
            'variants.*.quantity' => 'nullable|integer|min:0',
            'variants.*.is_active' => 'nullable|boolean',
            'variants.*.attributes' => 'nullable|array',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $product->update($validated);

        // Handle Variants
        if ($request->has('variants') && is_array($request->input('variants'))) {
            $submittedVariantIds = [];
            
            foreach ($request->input('variants') as $index => $variantData) {
                $variantAttributes = [
                    'name' => $variantData['name'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'mrp' => $variantData['mrp'] ?? null,
                    'selling_price' => $variantData['selling_price'],
                    'quantity' => $variantData['quantity'] ?? 0,
                    'is_active' => isset($variantData['is_active']) ? true : false,
                    'sort_order' => $index,
                ];

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

        // Handle New Images
        if ($request->hasFile('images')) {
            $lastSortOrder = $product->images()->max('sort_order') ?? -1;
            foreach ($request->file('images') as $index => $image) {
                $path = $this->storage->store($image, 'products');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                    'is_primary' => $product->images()->count() === 0,
                    'sort_order' => $lastSortOrder + $index + 1,
                ]);
            }
        }

        $this->ensurePrimaryImageExists($product);

        return redirect()->route('seller.products.index')->with('success', 'Product updated successfully');
    }

    public function toggleStatus(Request $request, Product $product)
    {
        $store = $request->current_store;
        
        if ($product->store_id !== $store->id) {
            abort(403);
        }

        $product->update([
            'is_active' => !$product->is_active
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'is_active' => $product->is_active]);
        }

        return back()->with('success', 'Status updated successfully');
    }

    public function destroy(Request $request, Product $product)
    {
        $store = $request->current_store;
        
        if ($product->store_id !== $store->id) {
            abort(403);
        }

        // Delete images from storage
        foreach ($product->images as $image) {
            $this->storage->delete($image->image);
            $image->delete();
        }

        $product->delete();

        return back()->with('success', 'Product deleted successfully');
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

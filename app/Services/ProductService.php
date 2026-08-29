<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImport;
use App\Models\Store;
use App\Models\User;
use App\Models\Setting;
use App\Enums\ProductStatus;
use App\Enums\ProductVisibility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class ProductService
{
    public function __construct(
        private RealtimeService $realtimeService
    ) {}

    /**
     * Create product with variants
     */
    public function create(array $data, ?Store $store = null, ?User $user = null): Product
    {
        return DB::transaction(function () use ($data, $store, $user) {
            // Determine initial status
            $status = ProductStatus::DRAFT;
            if ($store && Setting::get('product_auto_approve', false)) {
                $status = ProductStatus::PUBLISHED;
            } elseif ($store) {
                $status = ProductStatus::PENDING_APPROVAL;
            }

            // Create product
            $product = Product::create([
                'store_id' => $store?->id,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'sku' => $data['sku'] ?? 'PRD-' . strtoupper(Str::random(8)),
                'vendor_sku' => $data['vendor_sku'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'nutritional_info' => $data['nutritional_info'] ?? null,
                'price' => $data['price'],
                'compare_price' => $data['compare_price'] ?? null,
                'tax_rate' => $data['tax_rate'] ?? 0,
                'tax_class' => $data['tax_class'] ?? null,
                'hsn_code' => $data['hsn_code'] ?? null,
                'quantity' => $data['quantity'] ?? 0,
                'low_stock_threshold' => $data['low_stock_threshold'] ?? 5,
                'unit' => $data['unit'] ?? 'piece',
                'weight' => $data['weight'] ?? null,
                'weight_unit' => $data['weight_unit'] ?? 'g',
                'category_id' => $data['category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'brand' => $data['brand'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_featured' => $data['is_featured'] ?? false,
                'status' => $status,
                'visibility' => $data['visibility'] ?? ProductVisibility::GLOBAL,
                'track_inventory' => $data['track_inventory'] ?? true,
                'manufacture_date' => $data['manufacture_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'shelf_life_days' => $data['shelf_life_days'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
            ]);

            // Create variants if provided
            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $index => $variantData) {
                    $this->createVariant($product, $variantData, $index === 0);
                }
            }

            // Attach tags
            if (!empty($data['tag_ids'])) {
                $product->tags()->sync($data['tag_ids']);
            }

            // Note: ETag is computed from actual data, no cache invalidation needed

            // Notify if pending approval
            if ($status === ProductStatus::PENDING_APPROVAL) {
                $this->realtimeService->adminNotification(
                    'Product Pending Approval',
                    "Product '{$product->name}' from store '{$store->name}' needs approval.",
                    'info'
                );
            }

            return $product->load(['category', 'variants', 'tags', 'images']);
        });
    }

    /**
     * Create product variant
     */
    public function createVariant(Product $product, array $data, bool $isDefault = false): ProductVariant
    {
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $data['sku'] ?? null, // Auto-generated if null
            'barcode' => $data['barcode'] ?? null, // Auto-generated if null
            'name' => $data['name'] ?? null,
            'mrp' => $data['mrp'] ?? $product->compare_price ?? $product->price,
            'selling_price' => $data['selling_price'] ?? $product->price,
            'tax_rate' => $data['tax_rate'] ?? $product->tax_rate,
            'unit_id' => $data['unit_id'] ?? null,
            'unit_value' => $data['unit_value'] ?? 1,
            'quantity' => $data['quantity'] ?? 0,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? 5,
            'weight' => $data['weight'] ?? $product->weight,
            'weight_unit' => $data['weight_unit'] ?? $product->weight_unit,
            'is_active' => $data['is_active'] ?? true,
            'is_default' => $isDefault,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        // Attach attribute values
        if (!empty($data['attribute_value_ids'])) {
            $variant->attributeValues()->sync($data['attribute_value_ids']);
        }

        return $variant;
    }

    /**
     * Update product
     */
    public function update(Product $product, array $data, ?User $user = null): Product
    {
        return DB::transaction(function () use ($product, $data, $user) {
            $product->update($data);

            // Update tags
            if (isset($data['tag_ids'])) {
                $product->tags()->sync($data['tag_ids']);
            }

            // Note: ETag is computed from actual data, no cache invalidation needed

            return $product->fresh(['category', 'variants', 'tags', 'images']);
        });
    }

    /**
     * Approve product (for multi-vendor)
     */
    public function approve(Product $product, User $admin, ?string $notes = null): Product
    {
        $product->update([
            'status' => ProductStatus::PUBLISHED,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Notify store owner
        if ($product->store) {
            $this->realtimeService->notifyUser($product->store->owner_id, 'product-approved', [
                'product_id' => $product->id,
                'product_name' => $product->name,
            ]);
        }

        return $product->fresh();
    }

    /**
     * Reject product
     */
    public function reject(Product $product, User $admin, string $reason): Product
    {
        $product->update([
            'status' => ProductStatus::REJECTED,
            'rejection_reason' => $reason,
        ]);

        // Notify store owner
        if ($product->store) {
            $this->realtimeService->notifyUser($product->store->owner_id, 'product-rejected', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'reason' => $reason,
            ]);
        }

        return $product->fresh();
    }

    /**
     * Duplicate product
     */
    public function duplicate(Product $product, ?int $storeId = null): Product
    {
        return $product->duplicate($storeId);
    }

    /**
     * Bulk update products
     */
    public function bulkUpdate(array $productIds, array $data): int
    {
        return Product::whereIn('id', $productIds)->update($data);
    }

    /**
     * Inline update (price/stock)
     */
    public function inlineUpdate(Product $product, array $data): Product
    {
        $allowedFields = ['price', 'compare_price', 'quantity', 'is_active', 'is_featured'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        $product->update($updateData);
        
        return $product->fresh();
    }

    /**
     * Update variant stock
     */
    public function updateVariantStock(ProductVariant $variant, int $quantity, string $action = 'set'): ProductVariant
    {
        $oldQuantity = $variant->quantity;

        switch ($action) {
            case 'add':
                $variant->quantity += $quantity;
                break;
            case 'subtract':
                $variant->quantity = max(0, $variant->quantity - $quantity);
                break;
            default:
                $variant->quantity = $quantity;
        }

        $variant->save();

        // Check low stock
        if ($variant->isLowStock() && $oldQuantity > $variant->low_stock_threshold) {
            $this->realtimeService->lowStock([
                'id' => $variant->product_id,
                'name' => $variant->product->name . ' - ' . $variant->display_name,
                'sku' => $variant->sku,
                'quantity' => $variant->quantity,
                'threshold' => $variant->low_stock_threshold,
            ]);
        }

        return $variant;
    }

    /**
     * Import products from CSV/XLSX
     */
    public function startImport(UploadedFile $file, ?Store $store, User $user): ProductImport
    {
        $path = $file->store('imports', 'local');

        $import = ProductImport::create([
            'store_id' => $store?->id,
            'user_id' => $user->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'pending',
        ]);

        // Dispatch job to process import
        // ProcessProductImport::dispatch($import);

        return $import;
    }

    /**
     * Get products with filters
     */
    public function getProducts(array $filters = [], int $perPage = 20)
    {
        $query = Product::with(['category', 'brandRelation', 'primaryImage', 'store'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Store filter
        if (!empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        // Multiple Stores Filter (for Zone enforcement)
        if (!empty($filters['store_ids'])) {
            $query->whereIn('store_id', $filters['store_ids']);
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Brand filter
        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        // Search
        if (!empty($filters['search'])) {
            $searchTerm = trim($filters['search']);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%")
                  ->orWhere('barcode', 'like', "%{$searchTerm}%")
                  ->orWhere('search_tags', 'like', "%{$searchTerm}%")
                  ->orWhere('generic_name', 'like', "%{$searchTerm}%");
            });
        }

        // Low stock
        if (!empty($filters['low_stock'])) {
            $query->lowStock();
        }

        // Near expiry
        if (!empty($filters['near_expiry'])) {
            $query->nearExpiry($filters['near_expiry_days'] ?? 30);
        }

        // Active only
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Featured only
        if (!empty($filters['is_featured'])) {
            $query->where('is_featured', true);
        }

        // Sorting — whitelist columns to prevent SQL injection via orderBy
        $allowedSortColumns = ['created_at', 'updated_at', 'name', 'price', 'quantity', 'rating'];
        $sortBy = in_array($filters['sort_by'] ?? '', $allowedSortColumns, true)
            ? $filters['sort_by']
            : 'created_at';
        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    /**
     * Get pending approval products
     */
    public function getPendingApproval(int $perPage = 20)
    {
        return Product::with(['category', 'store.owner', 'primaryImage'])
            ->pendingApproval()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get low stock products
     */
    public function getLowStock(?int $storeId = null, int $perPage = 20)
    {
        $query = Product::with(['category', 'primaryImage'])
            ->lowStock()
            ->published();

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->orderBy('quantity')->paginate($perPage);
    }

    /**
     * Get near expiry products
     */
    public function getNearExpiry(?int $storeId = null, int $days = 30, int $perPage = 20)
    {
        $query = Product::with(['category', 'primaryImage'])
            ->nearExpiry($days)
            ->published();

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->orderBy('expiry_date')->paginate($perPage);
    }
}

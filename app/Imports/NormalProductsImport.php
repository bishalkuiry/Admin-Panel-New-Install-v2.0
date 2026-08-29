<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductImage;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class NormalProductsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Check required fields
            if (empty($row['name'])) continue;
            
            // Validate Category & Brand IDs existence
            $categoryId = !empty($row['category_id']) ? $row['category_id'] : null;
            $brandId = !empty($row['brand_id']) ? $row['brand_id'] : null;

            $status = isset($row['status']) ? $row['status'] : 'published';
            
            // Map status logic
            if ($status === 1 || $status === '1' || $status === 'published') {
                $statusEnum = \App\Enums\ProductStatus::PUBLISHED;
            } elseif ($status === 0 || $status === '0' || $status === 'draft') {
                $statusEnum = \App\Enums\ProductStatus::DRAFT;
            } elseif ($status === 'archived') {
                $statusEnum = \App\Enums\ProductStatus::ARCHIVED;
            } else {
                $statusEnum = \App\Enums\ProductStatus::PUBLISHED;
            }

            // Find existing product by SKU or Slug (including soft-deleted ones to prevent unique constraint errors)
            $product = null;
            if (!empty($row['sku'])) {
                $product = Product::withTrashed()->where('sku', $row['sku'])->first();
            }
            
            if (!$product) {
                $product = Product::withTrashed()->where('slug', Str::slug($row['name']))->first();
            }

            if (!$product) {
                $product = new Product();
            }

            // Restore if it was soft-deleted
            if ($product->exists && $product->trashed()) {
                $product->restore();
            }

            // Define payload
            $data = [
                'name' => $row['name'],
                // Update slug if necessary, or ensure it's set on create. 
                // However, creating by slug search automatically sets slug.
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'description' => $row['description'] ?? '',
                'short_description' => $row['short_description'] ?? '',
                'is_active' => isset($row['is_active']) ? (bool)$row['is_active'] : true,
                'is_featured' => isset($row['is_featured']) ? (bool)$row['is_featured'] : false,
                'status' => $statusEnum,
                'commission' => isset($row['commission']) && $row['commission'] !== '' ? $row['commission'] : null,
            ];

            // Only update numeric fields if they are explicitly provided (not null/empty string) to avoid zeroing out existing values on update
            // However, typical import expects overwrite. But standard Excel behavior for valid Import is overwrite.
            // If user provided 0 in Excel, it becomes 0.
            if (isset($row['selling_price']) && $row['selling_price'] !== '') $data['price'] = $row['selling_price'];
            if (isset($row['mrp']) && $row['mrp'] !== '') $data['compare_price'] = $row['mrp'];
            if (isset($row['quantity']) && $row['quantity'] !== '') $data['quantity'] = $row['quantity'];

            // Only set barcode if provided; otherwise let system generate on create
            if (!empty($row['barcode'])) {
                $data['barcode'] = $row['barcode'];
            }
            
            // Store ID Logic
            if (Auth::check() && Auth::user()->store_id) {
                // If logged in as Vendor, force their store ID
                $data['store_id'] = Auth::user()->store_id;
            } else {
                // If Admin, use Excel value or null (Admin Product)
                $data['store_id'] = !empty($row['store_id']) ? $row['store_id'] : null;
            }

            // Auto-generate SEO fields
            $productName = $row['name'];
            $shortDesc = $row['short_description'] ?? '';
            $description = $row['description'] ?? '';
            $category = $categoryId ? Category::find($categoryId) : null;
            $brand = $brandId ? Brand::find($brandId) : null;
            $categoryName = $category ? $category->name : '';
            $brandName = $brand ? $brand->name : '';
            
            // Meta Title: Product Name | Category | Brand (max 60 chars for SEO)
            $metaTitleParts = array_filter([$productName, $categoryName, $brandName]);
            $data['meta_title'] = Str::limit(implode(' | ', $metaTitleParts), 60, '');
            
            // Meta Description: Short description or first 155 chars of description
            if (!empty($shortDesc)) {
                $data['meta_description'] = Str::limit($shortDesc, 155, '...');
            } elseif (!empty($description)) {
                $data['meta_description'] = Str::limit(strip_tags($description), 155, '...');
            } else {
                // Generate from product details
                $priceText = isset($row['selling_price']) ? 'Starting at ₹' . $row['selling_price'] . '.' : '';
                $data['meta_description'] = Str::limit("Buy {$productName}" . ($categoryName ? " in {$categoryName}" : '') . ($brandName ? " from {$brandName}" : '') . ". {$priceText} Shop now!", 155, '...');
            }

            
            // Assign explicitly provided data
            $product->fill($data);

            // If creating new, ensure required fields have defaults
            if (!$product->exists) {
                if (!isset($data['price'])) $product->price = 0;
                if (!isset($data['compare_price'])) $product->compare_price = 0;
                if (!isset($data['quantity'])) $product->quantity = 0;
                // Ensure slug is unique/randomized if it matched by slug but we are creating (?)
                // If we are creating, it means searchAttributes failed.
                // $searchAttributes had either 'sku' OR 'slug'.
                // If it matched, $product->exists is true.
                // If not, we are new.
                if (empty($product->sku)) $product->sku = 'PRD-' . strtoupper(Str::random(8));
                // Note: Boot() generates SKU/Barcode if empty.
            }

            $product->save();

            if (!empty($row['image_url'])) {
                $this->processImage($product, $row['image_url']);
            }
        }
    }

    private function processImage($product, $url)
    {
         // Assuming URL is from the system's media manager or public URL
         // We try to extract the relative path if it's local
         $path = str_replace(url('/storage') . '/', '', $url);
         $path = str_replace('/storage/', '', $path);
         
         ProductImage::firstOrCreate([
             'product_id' => $product->id,
             'image' => $path
         ], ['is_primary' => true]);
    }
}

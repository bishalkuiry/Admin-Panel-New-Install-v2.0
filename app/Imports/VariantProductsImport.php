<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductVariant;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductImage;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VariantProductsImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    public function onError(Throwable $e)
    {
        // Log but don't throw - prevents transaction rollback
        Log::warning('Variant import row error: ' . $e->getMessage());
    }

    public function collection(Collection $rows)
    {
        // Add a temporary grouping key to rows without modifying actual SKU
        $grouped = $rows->groupBy(function ($item) {
            return !empty($item['sku']) ? $item['sku'] : Str::slug($item['name']); 
        });

        foreach ($grouped as $groupKey => $productRows) {
            if ($productRows->isEmpty()) continue;
            
            $firstRow = $productRows->first();
            
            // Validate Category & Brand IDs existence
            $categoryId = !empty($firstRow['category_id']) ? $firstRow['category_id'] : null;
            $brandId = !empty($firstRow['brand_id']) ? $firstRow['brand_id'] : null;
            
            $status = isset($firstRow['status']) ? $firstRow['status'] : 'published';
            
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

            // Find existing parent product by SKU or Slug (including soft-deleted ones to prevent unique constraint errors)
            $product = null;
            if (!empty($firstRow['sku'])) {
                $product = Product::withTrashed()->where('sku', $firstRow['sku'])->first();
            }
            
            if (!$product) {
                $product = Product::withTrashed()->where('slug', Str::slug($firstRow['name']))->first();
            }

            if (!$product) {
                $product = new Product();
            }

            // Restore if it was soft-deleted
            if ($product->exists && $product->trashed()) {
                $product->restore();
            }

            $data = [
                'name' => $firstRow['name'],
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'description' => $firstRow['description'] ?? '',
                'short_description' => $firstRow['short_description'] ?? '',
                'is_active' => isset($firstRow['is_active']) ? (bool)$firstRow['is_active'] : true,
                'is_featured' => isset($firstRow['is_featured']) ? (bool)$firstRow['is_featured'] : false,
                'status' => $statusEnum,
                'has_variants' => true,  // Important: Mark as variant product
            ];

             if (isset($firstRow['base_price']) && $firstRow['base_price'] !== '') $data['price'] = $firstRow['base_price'];
             if (isset($firstRow['base_mrp']) && $firstRow['base_mrp'] !== '') $data['compare_price'] = $firstRow['base_mrp'];
             if (isset($firstRow['base_quantity']) && $firstRow['base_quantity'] !== '') $data['quantity'] = $firstRow['base_quantity'];

            // Store ID Logic
            if (Auth::check() && Auth::user()->store_id) {
                // If logged in as Vendor, force their store ID
                $data['store_id'] = Auth::user()->store_id;
            } else {
                // If Admin, use Excel value or null (Admin Product)
                $data['store_id'] = !empty($firstRow['store_id']) ? $firstRow['store_id'] : null;
            }

            if (!empty($firstRow['barcode'])) {
                $data['barcode'] = $firstRow['barcode'];
            }

            // Auto-generate SEO fields if not provided
            $productName = $firstRow['name'];
            $shortDesc = $firstRow['short_description'] ?? '';
            $description = $firstRow['description'] ?? '';
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
                $priceText = isset($firstRow['base_price']) ? 'Starting at ₹' . $firstRow['base_price'] . '.' : '';
                $data['meta_description'] = Str::limit("Buy {$productName}" . ($categoryName ? " in {$categoryName}" : '') . ($brandName ? " from {$brandName}" : '') . ". {$priceText} Shop now!", 155, '...');
            }


            $product->fill($data);

            if (!$product->exists) {
                if (!isset($data['price'])) $product->price = 0;
                if (!isset($data['compare_price'])) $product->compare_price = 0;
                if (!isset($data['quantity'])) $product->quantity = 0;
                if (empty($product->sku)) $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
            $product->save();

            // Handle Image
            if (!empty($firstRow['image_url'])) {
                $this->processImage($product, $firstRow['image_url']);
            }

            // Create Variants
            foreach ($productRows as $row) {
                $flexibleAttrs = (string)($row['attributes'] ?? '');
                
                // Parse comma-separated values (ensure string conversion first)
                $attrValues = array_values(array_filter(array_map('trim', explode(',', $flexibleAttrs))));
                $priceStr = (string)($row['variant_price'] ?? '');
                $mrpStr = (string)($row['variant_mrp'] ?? '');
                $stockStr = (string)($row['variant_stock'] ?? '');
                $skuStr = (string)($row['variant_sku'] ?? '');
                
                $prices = array_values(array_map('trim', explode(',', $priceStr)));
                $mrps = array_values(array_map('trim', explode(',', $mrpStr)));
                $stocks = array_values(array_map('trim', explode(',', $stockStr)));
                $skus = array_values(array_map('trim', explode(',', $skuStr)));

                // If single attribute value, create one variant (original behavior)
                if (count($attrValues) <= 1 && !str_contains($flexibleAttrs, ',')) {
                    // Single variant mode
                    $attributesToAttach = [];
                    $nameParts = [];
                    
                    if (!empty($flexibleAttrs)) {
                        $resolved = null;
                        if (is_numeric(trim($flexibleAttrs))) {
                            $resolved = AttributeValue::find(trim($flexibleAttrs));
                        } elseif (str_contains($flexibleAttrs, ':')) {
                            list($aName, $vName) = explode(':', $flexibleAttrs, 2);
                            $resolved = $this->resolveAttributeValue(null, trim($aName), null, trim($vName));
                        }
                        if ($resolved) {
                            $nameParts[] = $resolved->value;
                            $attributesToAttach[] = $resolved->id;
                        }
                    }

                    $variantName = !empty($nameParts) 
                        ? $product->name . ' - ' . implode(' / ', $nameParts)
                        : $product->name . ' - ' . ($skus[0] ?? 'Default');

                    $variantSearch = ['product_id' => $product->id];
                    if (!empty($skus[0])) {
                        $variantSearch['sku'] = $skus[0];
                    } else {
                        $variantSearch['name'] = $variantName;
                    }

                    // Use first value from parsed arrays, with proper type conversion
                    $price = is_numeric($prices[0] ?? null) ? (float)$prices[0] : (float)($product->price ?? 0);
                    $mrp = is_numeric($mrps[0] ?? null) ? (float)$mrps[0] : (float)($product->compare_price ?? 0);
                    $stock = is_numeric($stocks[0] ?? null) ? (int)$stocks[0] : 0;

                    Log::info('Single variant mode', ['attrs' => $flexibleAttrs, 'price' => $price, 'mrp' => $mrp, 'stock' => $stock, 'prices_array' => $prices]);

                    $variantData = [
                        'name' => $variantName,
                        'selling_price' => $price,
                        'mrp' => $mrp,
                        'quantity' => $stock,
                        'is_active' => true,
                    ];

                    $variant = ProductVariant::updateOrCreate($variantSearch, $variantData);
                    if (!empty($attributesToAttach)) {
                        $variant->attributeValues()->syncWithoutDetaching($attributesToAttach);
                    }
                } else {
                    // Multi-variant mode: create one variant per attribute value
                    foreach ($attrValues as $index => $attrVal) {
                        $attrVal = trim($attrVal);
                        if (empty($attrVal)) continue;

                        $resolved = null;
                        if (is_numeric($attrVal)) {
                            $resolved = AttributeValue::find($attrVal);
                        } elseif (str_contains($attrVal, ':')) {
                            list($aName, $vName) = explode(':', $attrVal, 2);
                            $resolved = $this->resolveAttributeValue(null, trim($aName), null, trim($vName));
                        }

                        if (!$resolved) continue;

                        $variantName = $product->name . ' - ' . $resolved->value;

                        // Get corresponding price/mrp/stock by index, fallback to first or default
                        $priceVal = $prices[$index] ?? ($prices[0] ?? null);
                        $mrpVal = $mrps[$index] ?? ($mrps[0] ?? null);
                        $stockVal = $stocks[$index] ?? ($stocks[0] ?? null);
                        $sku = isset($skus[$index]) && !empty($skus[$index]) ? $skus[$index] : null;

                        // Convert to proper types
                        $price = is_numeric($priceVal) ? (float)$priceVal : (float)($product->price ?? 0);
                        $mrp = is_numeric($mrpVal) ? (float)$mrpVal : (float)($product->compare_price ?? 0);
                        $stock = is_numeric($stockVal) ? (int)$stockVal : 0;

                        $variantSearch = ['product_id' => $product->id];
                        if ($sku) {
                            $variantSearch['sku'] = $sku;
                        } else {
                            $variantSearch['name'] = $variantName;
                        }

                        $variantData = [
                            'name' => $variantName,
                            'selling_price' => $price,
                            'mrp' => $mrp,
                            'quantity' => $stock,
                            'is_active' => true,
                        ];

                        Log::info('Creating variant', ['product_id' => $product->id, 'name' => $variantName, 'price' => $price, 'mrp' => $mrp, 'stock' => $stock, 'attr_id' => $resolved->id]);

                        $variant = ProductVariant::updateOrCreate($variantSearch, $variantData);
                        Log::info('Variant saved', ['variant_id' => $variant->id, 'product_id' => $variant->product_id]);
                        $variant->attributeValues()->syncWithoutDetaching([$resolved->id]);
                    }
                }
            }
        }
    }

    private function resolveAttributeValue($attrId, $attrName, $valId, $valName)
    {
        $attribute = null;
        $attributeValue = null;

        // Resolve Attribute (Strict: Match Existing Only)
        if (!empty($attrId)) {
            $attribute = Attribute::find($attrId);
        }
        
        if (!$attribute && !empty($attrName)) {
            // Find by name only (Don't create)
            $attribute = Attribute::where('name', $attrName)->first();
        }

        if (!$attribute) return null; // Attribute must exist in system

        // Resolve Attribute Value (Strict: Match Existing Only)
        if (!empty($valId)) {
            $attributeValue = AttributeValue::where('attribute_id', $attribute->id)->find($valId);
        }

        if (!$attributeValue && !empty($valName)) {
            // Find by value only (Don't create)
            $attributeValue = AttributeValue::where('attribute_id', $attribute->id)
                ->where('value', $valName)
                ->first();
        }

        return $attributeValue;
    }

    private function processImage($product, $url)
    {
         $path = str_replace(url('/storage') . '/', '', $url);
         $path = str_replace('/storage/', '', $path);
         
         ProductImage::firstOrCreate([
             'product_id' => $product->id,
             'image' => $path
         ], ['is_primary' => true]);
    }
}

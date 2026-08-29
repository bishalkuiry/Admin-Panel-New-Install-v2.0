<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SampleVariantProductsSheet implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        return [
            // One row creates MULTIPLE variants (Hot Water + Cold Water)
            [
                'name' => 'Mineral Water', 'sku' => 'WATER-001', 'base_price' => 100, 'base_mrp' => 120, 'barcode' => '', 'store_id' => 1, 'category_id' => 1, 'brand_id' => 1, 'base_quantity' => 100, 
                'description' => 'Premium Mineral Water', 'short_description' => 'Pure Water', 'image_url' => '', 'is_featured' => 0, 'is_active' => 1, 'status' => 1,
                // Multiple Value IDs with corresponding prices/stocks
                'attributes' => '1, 2',           // Value ID 1 = Hot Water, Value ID 2 = Cold Water
                'variant_sku' => 'WATER-HOT, WATER-COLD',
                'variant_mrp' => '150, 120',      // Hot=150, Cold=120
                'variant_price' => '100, 80',     // Hot=100, Cold=80
                'variant_stock' => '50, 30'       // Hot=50 units, Cold=30 units
            ],
            // Another product with single variant per row (old format also works)
            [
                'name' => 'T-Shirt', 'sku' => 'TSHIRT-001', 'base_price' => 500, 'base_mrp' => 600, 'barcode' => '', 'store_id' => 1, 'category_id' => 1, 'brand_id' => 1, 'base_quantity' => 50, 
                'description' => 'Cotton T-Shirt', 'short_description' => 'Comfortable', 'image_url' => '', 'is_featured' => 0, 'is_active' => 1, 'status' => 1,
                'attributes' => '3',              // Single Value ID 3 = Red Color
                'variant_sku' => 'TSHIRT-RED',
                'variant_mrp' => '600',
                'variant_price' => '500',
                'variant_stock' => '25'
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'name', 'sku', 'base_price', 'base_mrp', 'barcode', 'store_id', 'category_id', 'brand_id', 'base_quantity', 'description', 'short_description', 'image_url', 'is_featured', 'is_active', 'status',
            'attributes',
            'variant_sku', 'variant_mrp', 'variant_price', 'variant_stock'
        ];
    }

    public function title(): string
    {
        return 'Variant Products';
    }
}

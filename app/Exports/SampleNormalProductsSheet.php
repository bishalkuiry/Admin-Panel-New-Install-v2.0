<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SampleNormalProductsSheet implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        return [
            ['Sample Product', 'SMPL-001', 100, 120, '123456789', 1, 1, 1, 50, 'Full description here', 'Short sumary', 'https://example.com/image.jpg', 0, 1, 1],
        ];
    }

    public function headings(): array
    {
        return ['name', 'sku', 'selling_price', 'mrp', 'barcode', 'store_id', 'category_id', 'brand_id', 'quantity', 'description', 'short_description', 'image_url', 'is_featured', 'is_active', 'status'];
    }

    public function title(): string
    {
        return 'Normal Products';
    }
}

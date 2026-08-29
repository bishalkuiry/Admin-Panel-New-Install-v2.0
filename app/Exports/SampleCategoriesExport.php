<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SampleCategoriesExport implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        return [
            // Parent category (no parent_id = root category)
            [
                'name' => 'Electronics',
                'parent_id' => '',  // Empty = parent/root category
                'image' => '',
                'is_active' => 1,
                'is_featured' => 1,
            ],
            // Another parent
            [
                'name' => 'Fashion',
                'parent_id' => '',
                'image' => '',
                'is_active' => 1,
                'is_featured' => 0,
            ],
            // Subcategory (has parent_id)
            [
                'name' => 'Mobile Phones',
                'parent_id' => 1,  // ID of parent category (Electronics)
                'image' => '',
                'is_active' => 1,
                'is_featured' => 0,
            ],
            // Another subcategory
            [
                'name' => 'Laptops',
                'parent_id' => 1,  // ID of parent category (Electronics)
                'image' => '',
                'is_active' => 1,
                'is_featured' => 0,
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'parent_id',
            'image',
            'is_active',
            'is_featured',
        ];
    }

    public function title(): string
    {
        return 'Categories';
    }
}



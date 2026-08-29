<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function collection()
    {
        return Category::orderBy('parent_id')->orderBy('name')->get();
    }

    public function map($category): array
    {
        return [
            $category->id,
            $category->name,
            $category->parent_id,
            $category->image,
            $category->is_active ? 1 : 0,
            $category->is_featured ? 1 : 0,
        ];
    }

    public function headings(): array
    {
        return [
            'id',
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



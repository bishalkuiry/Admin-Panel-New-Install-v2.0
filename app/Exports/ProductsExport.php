<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $context;

    public function __construct($context = null)
    {
        $this->context = $context;
    }

    public function collection()
    {
        $query = Product::with('category', 'brandRelation', 'store');

        if ($this->context === 'seller') {
            $query->whereNotNull('store_id');
        } elseif ($this->context === 'inhouse') {
            $query->whereNull('store_id');
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'SKU', 'BarCode', 'Price', 'Compare Price', 'Quantity', 
            'Category', 'Brand', 'Store', 'Commission', 'Status', 'Active'
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->sku,
            $product->barcode,
            $product->price,
            $product->compare_price,
            $product->quantity,
            $product->category ? $product->category->name : '',
            $product->brandRelation ? $product->brandRelation->name : '',
            $product->store ? $product->store->name : 'In-house',
            $product->commission,
            $product->status?->value ?? '',
            $product->is_active ? 'Yes' : 'No',
        ];
    }
}

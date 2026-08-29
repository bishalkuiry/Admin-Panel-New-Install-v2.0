<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class ProductDTO
{
    public function __construct(
        public string $name,
        public string $sku,
        public float $price,
        public ?string $shortDescription = null,
        public ?string $description = null,
        public ?float $comparePrice = null,
        public int $quantity = 0,
        public int $lowStockThreshold = 5,
        public string $unit = 'piece',
        public ?float $weight = null,
        public ?int $categoryId = null,
        public ?string $brand = null,
        public ?string $barcode = null,
        public bool $isActive = true,
        public bool $isFeatured = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            sku: $request->input('sku'),
            price: (float) $request->input('price'),
            shortDescription: $request->input('short_description'),
            description: $request->input('description'),
            comparePrice: $request->input('compare_price') ? (float) $request->input('compare_price') : null,
            quantity: (int) $request->input('quantity', 0),
            lowStockThreshold: (int) $request->input('low_stock_threshold', 5),
            unit: $request->input('unit', 'piece'),
            weight: $request->input('weight') ? (float) $request->input('weight') : null,
            categoryId: $request->input('category_id') ? (int) $request->input('category_id') : null,
            brand: $request->input('brand'),
            barcode: $request->input('barcode'),
            isActive: (bool) $request->input('is_active', true),
            isFeatured: (bool) $request->input('is_featured', false),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'short_description' => $this->shortDescription,
            'description' => $this->description,
            'compare_price' => $this->comparePrice,
            'quantity' => $this->quantity,
            'low_stock_threshold' => $this->lowStockThreshold,
            'unit' => $this->unit,
            'weight' => $this->weight,
            'category_id' => $this->categoryId,
            'brand' => $this->brand,
            'barcode' => $this->barcode,
            'is_active' => $this->isActive,
            'is_featured' => $this->isFeatured,
        ];
    }
}

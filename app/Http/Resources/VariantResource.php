<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'pricing' => [
                'mrp' => $this->mrp,
                'selling_price' => $this->selling_price,
                'tax_rate' => $this->tax_rate,
                'discount_percent' => $this->discount_percent,
            ],
            'unit' => [
                'id' => $this->unit_id,
                'value' => $this->unit_value,
                'name' => $this->whenLoaded('unit', fn() => $this->unit->name),
                'short_name' => $this->whenLoaded('unit', fn() => $this->unit->short_name),
            ],
            'inventory' => [
                'quantity' => $this->quantity,
                'low_stock_threshold' => $this->low_stock_threshold,
                'in_stock' => $this->quantity > 0,
                'is_low_stock' => $this->isLowStock(),
            ],
            'dimensions' => [
                'weight' => $this->weight,
                'weight_unit' => $this->weight_unit,
                'length' => $this->length,
                'width' => $this->width,
                'height' => $this->height,
            ],
            'image' => storage_url($this->image),
            'is_active' => $this->is_active,
            'is_default' => $this->is_default,
            'sort_order' => $this->sort_order,
            'attribute_values' => AttributeValueResource::collection($this->whenLoaded('attributeValues')),
            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'product_name' => $this->product_name,
            'product_sku' => $this->product_sku,
            'variant_id' => $this->product_variant_id,
            'variant_name' => $this->variant_name,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total' => $this->total,
            'formatted_total' => \App\Helpers\CurrencyHelper::format($this->total),
            'options' => $this->options,
            'prescription_image' => storage_url($this->prescription_image),
        ];
    }
}

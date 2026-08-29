<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => storage_url($this->image),
            'thumbnail' => storage_url($this->image),
            'is_primary' => $this->is_primary,
            'sort_order' => $this->sort_order,
            'alt' => $this->alt ?? $this->whenLoaded('product', fn() => $this->product->name),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'location' => [
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'coordinates' => $this->coordinates,
                'center' => [
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                ],
                'radius_km' => $this->radius_km,
            ],
            'delivery' => [
                'base_fee' => $this->base_delivery_fee,
                'per_km_fee' => $this->per_km_fee,
                'min_order_amount' => $this->min_order_amount,
            ],
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'stores_count' => $this->whenCounted('stores'),
            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}

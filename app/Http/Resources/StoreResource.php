<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'logo' => storage_url($this->logo),
            'banner' => storage_url($this->banner),
            'contact' => [
                'email' => $this->email,
                'phone' => $this->phone,
                'alternate_phone' => $this->alternate_phone,
            ],
            'address' => [
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
                'coordinates' => [
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                ],
            ],
            'business' => [
                'type' => $this->business_type,
                'gst_number' => $this->gst_number,
                'pan_number' => $this->pan_number,
                'fssai_license' => $this->fssai_license,
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'kyc_status' => [
                'value' => $this->kyc_status->value,
                'label' => $this->kyc_status->label(),
                'color' => $this->kyc_status->color(),
            ],
            'settings' => [
                'is_online' => $this->is_online,
                'is_featured' => $this->is_featured,
                'opening_hours' => $this->opening_hours,
                'preparation_time' => $this->preparation_time,
                'delivery_radius_km' => $this->delivery_radius_km,
                'pickup_enabled' => $this->pickup_enabled,
                'delivery_enabled' => $this->delivery_enabled,
            ],
            'pricing' => [
                'packing_charge' => $this->packing_charge,
                'min_order_amount' => $this->min_order_amount,
                'delivery_type' => $this->delivery_type,
                'delivery_method' => $this->delivery_method,
                'delivery_flat_rate' => $this->delivery_flat_rate,
                'delivery_percentage' => $this->delivery_percentage,
                'delivery_per_km_rate' => $this->delivery_per_km_rate,
                'store_free_delivery' => $this->store_free_delivery,
            ],
            'stats' => [
                'rating' => $this->rating,
                'rating_count' => $this->rating_count,
                'order_count' => $this->order_count,
            ],
            'owner' => new UserResource($this->whenLoaded('owner')),
            'zones' => ZoneResource::collection($this->whenLoaded('zones')),
            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
                'approved_at' => $this->approved_at?->toISOString(),
            ],
        ];
    }
}

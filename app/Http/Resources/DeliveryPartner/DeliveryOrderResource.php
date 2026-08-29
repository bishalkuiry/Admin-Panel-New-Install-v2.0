<?php

namespace App\Http\Resources\DeliveryPartner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\OrderItemResource;

class DeliveryOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'order_number' => (string) $this->order_number,
            'status' => $this->status->value,
            'customer' => [
                'id' => (string) ($this->user?->id ?? ''),
                'name' => $this->user?->name ?? 'Customer',
                'phone' => $this->user?->phone ?? '',
                'address' => [
                    'street' => $this->address?->address_line_1 ?? $this->address?->address ?? 'Address not specified',
                    'city' => $this->address?->city ?? '',
                    'state' => $this->address?->state ?? '',
                    'zip_code' => $this->address?->postal_code ?? '',
                    'latitude' => (float) ($this->address?->latitude ?? 0),
                    'longitude' => (float) ($this->address?->longitude ?? 0),
                ],
            ],
            'store' => [
                'id' => (string) ($this->store?->id ?? ''),
                'name' => $this->store?->name ?? 'Store',
                'phone' => $this->store?->phone ?? '',
                'logo_url' => $this->store?->logo ? storage_url($this->store->logo) : '',
                'address' => [
                    'street' => $this->store?->address_line_1 ?? $this->store?->address ?? 'Store Address',
                    'city' => $this->store?->city ?? '',
                    'state' => $this->store?->state ?? '',
                    'zip_code' => $this->store?->postal_code ?? '',
                    'latitude' => (float) ($this->store?->latitude ?? 0),
                    'longitude' => (float) ($this->store?->longitude ?? 0),
                ],
            ],
            'items' => OrderItemResource::collection($this->items),
            'payment' => [
                'method' => $this->payment_method ?? 'COD',
                'status' => $this->payment_status ?? 'pending',
                'transaction_id' => $this->payment_id,
                'wallet_amount' => (float) ($this->wallet_amount ?? 0),
            ],
            'delivery_fee' => (float) ($this->delivery_fee > 0 ? $this->delivery_fee : ($this->store?->zone?->base_delivery_fee ?? (float) \App\Models\Setting::get('default_delivery_fee', 30))),
            'driver_tip' => (float) ($this->driver_tip ?? 0),
            'driver_earnings' => (float) ($this->driver_earning > 0 ? $this->driver_earning : (($this->delivery_fee ?? 0) + ($this->driver_tip ?? 0))),
            'total_amount' => (float) ($this->total ?? 0),
            'delivery_instructions' => $this->notes,
            'delivery_otp' => (string) $this->delivery_otp,
            'created_at' => $this->created_at?->toISOString(),
            'estimated_delivery_at' => $this->estimated_delivery_at?->toISOString(),
            'distance_to_store' => $this->calculateDistance(
                $this->address?->latitude, $this->address?->longitude,
                $this->store?->latitude, $this->store?->longitude
            ),
            'distance_to_customer' => $this->calculateDistance(
                $this->store?->latitude, $this->store?->longitude,
                $this->address?->latitude, $this->address?->longitude
            ),
            // Use eager-loaded orderChats to avoid N+1 queries
            'firebase_chat_id' => $this->whenLoaded('orderChats', function () {
                return $this->orderChats
                    ->firstWhere('chat_type', 'customer_delivery')
                    ?->firebase_chat_id;
            }, fn() => \App\Models\OrderChat::where('order_id', $this->id)
                ->where('chat_type', 'customer_delivery')
                ->value('firebase_chat_id')),
        ];
    }

    /**
     * Haversine formula — straight-line distance in km between two lat/lng points.
     * Returns null if coordinates are missing or zero.
     */
    private function calculateDistance(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?float
    {
        if (!$lat1 || !$lng1 || !$lat2 || !$lng2) {
            return null;
        }

        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}

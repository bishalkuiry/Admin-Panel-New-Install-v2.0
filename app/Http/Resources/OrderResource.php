<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currencyCode = $this->currency ?? ($this->store?->zone?->currency ?? ($this->zone?->currency ?? \App\Helpers\CurrencyHelper::getDefault()));
        $currencySymbol = \App\Helpers\CurrencyHelper::getSymbol($currencyCode);

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'order_type' => $this->order_type ?? 'delivery',
            'is_pickup' => ($this->order_type === 'pickup'),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'customer' => new UserResource($this->whenLoaded('user')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'pricing' => [
                'subtotal' => $this->subtotal,
                'discount' => $this->discount,
                'delivery_fee' => $this->delivery_fee,
                'driver_tip' => (float) ($this->driver_tip ?? 0),
                'tax' => $this->tax,
                'total' => $this->total,
                'store_earning' => (float) ($this->store_earning ?? 0),
                'admin_commission' => (float) ($this->admin_commission ?? 0),
                'driver_earning' => (float) ($this->driver_earning ?? 0),
                'coupon_funded_by' => $this->coupon_funded_by ?? 'admin',
                'formatted_subtotal' => \App\Helpers\CurrencyHelper::format($this->subtotal, $currencyCode),
                'formatted_discount' => \App\Helpers\CurrencyHelper::format($this->discount, $currencyCode),
                'formatted_delivery_fee' => \App\Helpers\CurrencyHelper::format($this->delivery_fee, $currencyCode),
                'formatted_driver_tip' => \App\Helpers\CurrencyHelper::format($this->driver_tip ?? 0, $currencyCode),
                'formatted_tax' => \App\Helpers\CurrencyHelper::format($this->tax, $currencyCode),
                'formatted_total' => \App\Helpers\CurrencyHelper::format($this->total, $currencyCode),
                'formatted_store_earning' => \App\Helpers\CurrencyHelper::format($this->store_earning ?? 0, $currencyCode),
                'formatted_admin_commission' => \App\Helpers\CurrencyHelper::format($this->admin_commission ?? 0, $currencyCode),
                'formatted_driver_earning' => \App\Helpers\CurrencyHelper::format($this->driver_earning ?? 0, $currencyCode),
                'currency' => $currencyCode,
                'currency_symbol' => $currencySymbol,
            ],
            'address' => new AddressResource($this->whenLoaded('address')),
            'payment' => [
                'method' => $this->payment_method,
                'status' => $this->payment_status,
                'transaction_id' => $this->payment_id,
                'wallet_amount' => $this->wallet_amount,
            ],
            'notes' => $this->notes,
            'prescription_image' => $this->prescription_image ? storage_url($this->prescription_image) : null,
            'delivery_otp' => (string) $this->delivery_otp,
            'delivery_partner' => new UserResource($this->whenLoaded('deliveryPartner')),
            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
                'confirmed_at' => $this->confirmed_at?->toISOString(),
                'packed_at' => $this->packed_at?->toISOString(),
                'picked_up_at' => $this->picked_up_at?->toISOString(),
                'out_for_delivery_at' => $this->out_for_delivery_at?->toISOString(),
                'delivered_at' => $this->delivered_at?->toISOString(),
            ],
        ];
    }
}

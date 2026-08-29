<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'constraints' => [
                'min_order_amount' => $this->min_order_amount,
                'max_discount' => $this->max_discount,
                'usage_limit' => $this->usage_limit,
                'usage_limit_per_user' => $this->usage_limit_per_user,
                'used_count' => $this->used_count,
            ],
            'validity' => [
                'starts_at' => $this->starts_at?->toISOString(),
                'expires_at' => $this->expires_at?->toISOString(),
                'is_valid' => $this->isValid(),
            ],
            'flags' => [
                'is_active' => $this->is_active,
                'is_first_order_only' => $this->is_first_order_only,
            ],
            'applicable_to' => [
                'categories' => $this->applicable_categories,
                'products' => $this->applicable_products,
                'stores' => $this->applicable_stores,
            ],
            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}

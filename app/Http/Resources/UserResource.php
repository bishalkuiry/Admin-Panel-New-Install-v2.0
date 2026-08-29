<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeMembership = \App\Models\UserMembership::with('plan')
            ->where('user_id', $this->id)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->latest()
            ->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => storage_url($this->avatar),
            'role' => $this->role,
            'is_vip' => $activeMembership !== null,
            'vip_membership' => $activeMembership ? [
                'plan_name' => $activeMembership->plan?->name ?? 'VIP Member',
                'badge_icon' => $activeMembership->plan?->badge_icon ?? '👑',
                'expires_at' => $activeMembership->expires_at?->toIso8601String(),
            ] : null,
            'email_verified' => $this->email_verified_at !== null,
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
            'orders_count' => $this->whenCounted('orders'),
            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}

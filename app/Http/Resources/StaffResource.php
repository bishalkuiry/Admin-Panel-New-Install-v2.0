<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'store_id' => $this->store_id,
            'role' => $this->role,
            'permissions' => $this->permissions,
            'employee_id' => $this->employee_id,
            'designation' => $this->designation,
            'salary' => $this->salary,
            'is_active' => $this->is_active,
            'timestamps' => [
                'joined_at' => $this->joined_at?->toISOString(),
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}

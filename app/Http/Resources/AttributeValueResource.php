<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attribute' => [
                'id' => $this->attribute?->id,
                'name' => $this->attribute?->name,
                'type' => $this->attribute?->type,
            ],
            'value' => $this->value,
            'display_value' => $this->display_value ?? $this->value,
            'color_code' => $this->color_code,
        ];
    }
}

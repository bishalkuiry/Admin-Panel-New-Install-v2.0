<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'vendor_sku' => $this->vendor_sku,
            'barcode' => $this->barcode,
            'hsn_code' => $this->hsn_code,
            'tax_rate' => $this->tax_rate,
            'tax_class' => $this->tax_class,
            'price' => [
                'amount' => $this->price,
                'formatted' => \App\Helpers\CurrencyHelper::format($this->price),
                'compare_price' => $this->compare_price,
                'formatted_compare_price' => $this->compare_price ? \App\Helpers\CurrencyHelper::format($this->compare_price) : null,
                'discount_percent' => $this->discount_percentage,
                'currency' => \App\Helpers\CurrencyHelper::getDefault(),
                'currency_symbol' => \App\Helpers\CurrencyHelper::getSymbol(),
            ],
            'description' => [
                'short' => $this->short_description,
                'full' => $this->description,
            ],
            'inventory' => [
                'quantity' => $this->quantity,
                'low_stock_threshold' => $this->low_stock_threshold,
                'in_stock' => $this->quantity > 0,
                'is_low_stock' => $this->quantity <= $this->low_stock_threshold && $this->quantity > 0,
                'track_inventory' => (bool) $this->track_inventory,
                'allow_backorder' => (bool) $this->allow_backorder,
            ],
            'unit' => $this->unit,
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit ?? 'g',
            'is_prescription_required' => (bool) $this->is_prescription_required,
            'dimensions' => [
                'length' => $this->length,
                'width' => $this->width,
                'height' => $this->height,
                'unit' => $this->dimension_unit ?? 'cm',
            ],
            'brand' => $this->brand,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'dates' => [
                'manufacture_date' => $this->manufacture_date ? $this->manufacture_date->format('Y-m-d') : null,
                'expiry_date' => $this->expiry_date ? $this->expiry_date->format('Y-m-d') : null,
                'shelf_life_days' => $this->shelf_life_days,
            ],
            'health_info' => [
                'is_veg' => $this->is_veg === null ? null : (bool) $this->is_veg,
                'is_halal' => (bool) $this->is_halal,
                'is_prescription_required' => (bool) $this->is_prescription_required,
                'generic_name' => $this->generic_name,
                'nutrition_info' => $this->nutrition_info ?? (is_array($this->nutritional_info) ? json_encode($this->nutritional_info) : $this->nutritional_info),
            ],
            'prep_time' => [
                'min' => $this->prep_time_min,
                'max' => $this->prep_time_max,
                'unit' => $this->prep_time_unit ?? 'minutes',
            ],
            'available_time' => [
                'starts' => $this->available_time_starts,
                'ends' => $this->available_time_ends,
            ],
            'search_tags' => $this->search_tags,
            'tags' => $this->whenLoaded('tags', function() {
                return $this->tags->pluck('name')->toArray();
            }),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'attributes' => AttributeValueResource::collection($this->whenLoaded('attributes')),
            'variants' => VariantResource::collection($this->relationLoaded('variants') ? $this->variants : $this->variants()->get()),
            'variations' => VariantResource::collection($this->relationLoaded('variants') ? $this->variants : $this->variants()->get()),
            'rating' => $this->reviews_avg_rating ? round((float) $this->reviews_avg_rating, 1) : null,
            'review_count' => $this->reviews_count ?? 0,
            'food_variation_groups' => ($this->relationLoaded('foodVariationGroups') ? $this->foodVariationGroups : $this->foodVariationGroups()->with('options')->get())->map(function($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'is_required' => (bool)$group->is_required,
                    'selection_type' => $group->selection_type,
                    'min_selection' => (int)$group->min_selection,
                    'max_selection' => (int)$group->max_selection,
                    'options' => $group->options->map(function($opt) {
                        return [
                            'id' => $opt->id,
                            'name' => $opt->option_name,
                            'option_name' => $opt->option_name,
                            'price' => (float)$opt->price,
                        ];
                    })->toArray(),
                ];
            }),
            'video' => [
                'type' => $this->video_type,
                'url' => $this->video_url,
            ],
            'policy' => [
                'return_period_days' => $this->return_period_days ?? 0,
                'replacement_period_days' => $this->replacement_period_days ?? 0,
                'warranty_summary' => $this->warranty_summary,
                'guarantee_summary' => $this->guarantee_summary,
                'delivered_by_lead_hours' => $this->delivered_by_lead_hours ?? 24,
            ],
            'store' => ($this->relationLoaded('store') || $this->store_id) ? (function() {
                $store = $this->relationLoaded('store') ? $this->store : $this->store()->first();
                if (!$store) return null;
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'logo' => storage_url($store->logo),
                    'slug' => $store->slug,
                ];
            })() : null,
            'flags' => [
                'is_active' => $this->is_active,
                'is_featured' => $this->is_featured,
            ],
            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }

    public function with(Request $request): array
    {
        return [
            'success' => true,
        ];
    }
}

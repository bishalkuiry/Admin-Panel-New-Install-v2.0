<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodVariationGroup extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'is_required',
        'selection_type',
        'min_selection',
        'max_selection',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'min_selection' => 'integer',
        'max_selection' => 'integer',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(FoodVariationOption::class, 'food_variation_group_id')->orderBy('sort_order');
    }
}

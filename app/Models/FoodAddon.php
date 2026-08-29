<?php

namespace App\Models;

use App\Traits\ScopeByModule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FoodAddon extends Model
{
    use ScopeByModule;

    protected $fillable = [
        'module_id',
        'name',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(HomeHeaderTab::class, 'module_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'food_addon_product', 'food_addon_id', 'product_id');
    }
}

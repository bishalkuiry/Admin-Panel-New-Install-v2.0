<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 'product_id', 'is_visible', 'quantity', 'price_override', 'is_featured'
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_featured' => 'boolean',
        'price_override' => 'decimal:2',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get effective price (override or product price)
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->price_override ?? $this->product->price;
    }

    /**
     * Get effective quantity (override or product quantity)
     */
    public function getEffectiveQuantityAttribute(): int
    {
        return $this->quantity ?? $this->product->quantity;
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'session_id', 'store_id', 'coupon_id', 'coupon_code', 'subtotal', 'discount', 'total'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Compute subtotal from items in memory when the relation is already loaded,
     * otherwise fall back to the persisted column value.
     *
     * This prevents the accessor from silently returning 0 when items are not
     * eager-loaded, and avoids the accessor/column drift bug where the stored
     * `subtotal` column could diverge from the live item sum.
     */
    public function getSubtotalAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum(fn ($item) => $item->price * $item->quantity);
        }

        // Fall back to the persisted column (set by CartService::updateCartTotals).
        return (float) ($this->attributes['subtotal'] ?? 0);
    }

    /**
     * Derive total from the (possibly computed) subtotal minus the stored discount.
     * Always reads discount from the raw column to avoid accessor recursion.
     */
    public function getTotalAttribute(): float
    {
        $subtotal = $this->subtotal;
        $discount = (float) ($this->attributes['discount'] ?? 0);
        return max(0, $subtotal - $discount);
    }

    public function getItemCountAttribute(): int
    {
        return $this->relationLoaded('items')
            ? $this->items->sum('quantity')
            : 0;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id', 'product_id', 'product_variant_id', 'quantity', 'price', 'options'];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'options' => 'array',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Subtotal uses the price captured at the time the item was added to the
     * cart (the `price` column), NOT the live product price.
     *
     * Using the live product price here caused a silent inconsistency: the
     * accessor would return a different value than what CartService stores in
     * the `subtotal` column, making the cart total appear to drift whenever a
     * product price changed between add and checkout.
     *
     * Price drift is surfaced explicitly via CartService::validateCartItems()
     * so the customer is informed before payment — not silently recalculated.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->price * $this->quantity;
    }
}

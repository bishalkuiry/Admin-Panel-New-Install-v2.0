<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'delivery_rating',
        'comment',
        'images',
        'is_verified_purchase',
        'is_approved',
        'reviewable_type',
        'reviewable_id',
        'status',
    ];

    protected $casts = [
        'rating'               => 'integer',
        'delivery_rating'      => 'integer',
        'images'               => 'array',
        'is_verified_purchase' => 'boolean',
        'is_approved'          => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getStatusAttribute(): string
    {
        return $this->attributes['is_approved'] ?? false ? 'approved' : 'pending';
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', 1);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', 0);
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }
}

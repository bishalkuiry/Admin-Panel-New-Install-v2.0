<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerAdPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'store_id',
        'user_id',
        'product_id',
        'title',
        'image',
        'target_url',
        'payment_status',
        'status',
        'rejection_reason',
        'starts_at',
        'expires_at',
        'impressions_count',
        'clicks_count',
        'amount_paid',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
        'amount_paid' => 'float',
    ];

    public function plan()
    {
        return $this->belongsTo(SellerAdPlan::class, 'plan_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

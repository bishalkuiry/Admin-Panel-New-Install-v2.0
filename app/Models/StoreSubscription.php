<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'plan_id',
        'starts_at',
        'expires_at',
        'is_trial',
        'payment_method',
        'payment_id',
        'price_paid',
        'auto_renew',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_trial' => 'boolean',
        'auto_renew' => 'boolean',
        'price_paid' => 'float',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function plan()
    {
        return $this->belongsTo(StoreSubscriptionPlan::class, 'plan_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'duration_days',
        'max_products',
        'commission_rate',
        'features',
        'trial_period_days',
        'auto_renew',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'duration_days' => 'integer',
        'max_products' => 'integer',
        'commission_rate' => 'float',
        'features' => 'array',
        'trial_period_days' => 'integer',
        'auto_renew' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(StoreSubscription::class, 'plan_id');
    }
}

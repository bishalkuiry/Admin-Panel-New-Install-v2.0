<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'full_description',
        'logo',
        'badge_icon',
        'banner_image',
        'price',
        'monthly_price',
        'yearly_price',
        'duration_days',
        'trial_period_days',
        'auto_renewal',
        'cashback_percentage',
        'max_cashback_per_month',
        'free_delivery',
        'max_free_deliveries_per_month',
        'min_order_for_free_delivery',
        'max_delivery_distance_km',
        'extra_discount_percentage',
        'max_discount_per_order',
        'priority_support',
        'eligible_stores',
        'eligible_categories',
        'eligible_zones',
        'benefits_config',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'duration_days' => 'integer',
        'trial_period_days' => 'integer',
        'auto_renewal' => 'boolean',
        'cashback_percentage' => 'decimal:2',
        'max_cashback_per_month' => 'decimal:2',
        'free_delivery' => 'boolean',
        'max_free_deliveries_per_month' => 'integer',
        'min_order_for_free_delivery' => 'decimal:2',
        'max_delivery_distance_km' => 'decimal:2',
        'extra_discount_percentage' => 'decimal:2',
        'max_discount_per_order' => 'decimal:2',
        'priority_support' => 'boolean',
        'eligible_stores' => 'array',
        'eligible_categories' => 'array',
        'eligible_zones' => 'array',
        'benefits_config' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function userMemberships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserMembership::class, 'membership_plan_id');
    }
}

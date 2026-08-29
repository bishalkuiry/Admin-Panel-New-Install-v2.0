<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerAdPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ad_type',
        'description',
        'price',
        'billing_type',
        'duration_days',
        'placement',
        'impression_limit',
        'click_limit',
        'priority_level',
        'targeting_options',
        'banner_size',
        'max_items',
        'auto_renew',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
        'duration_days' => 'integer',
        'impression_limit' => 'integer',
        'click_limit' => 'integer',
        'priority_level' => 'integer',
        'targeting_options' => 'array',
        'max_items' => 'integer',
        'auto_renew' => 'boolean',
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function purchases()
    {
        return $this->hasMany(SellerAdPurchase::class, 'plan_id');
    }
}

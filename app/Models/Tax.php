<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tax extends Model
{
    protected $fillable = [
        'name',
        'applies_to',
        'calculation_type',
        'value',
        'min_order_value',
        'max_order_value',
        'store_id',
        'store_category_id',
        'is_active',
        'is_inclusive',
    ];

    protected $casts = [
        'value'           => 'decimal:4',
        'min_order_value' => 'decimal:2',
        'max_order_value' => 'decimal:2',
        'is_active'       => 'boolean',
        'is_inclusive'    => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function storeCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'store_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCustomer($query)
    {
        return $query->where('applies_to', 'customer');
    }

    public function scopeForStore($query)
    {
        return $query->where('applies_to', 'store');
    }

    public function scopeForDeliveryPartner($query)
    {
        return $query->where('applies_to', 'delivery_partner');
    }

    /**
     * Calculate the tax amount for a given base amount.
     */
    public function calculate(float $amount): float
    {
        if ($this->calculation_type === 'percentage') {
            return round($amount * ($this->value / 100), 2);
        }

        return (float) $this->value;
    }

    /**
     * Get a human-readable label for the tax value.
     */
    public function getValueLabelAttribute(): string
    {
        $decimals = (int) Setting::get('currency_decimal_places', 2);
        
        if ($this->calculation_type === 'percentage') {
            // Trim trailing zeros for cleaner percentage display, e.g. 5% instead of 5.00%
            $val = number_format($this->value, $decimals);
            return (strpos($val, '.') !== false ? rtrim(rtrim($val, '0'), '.') : $val) . '%';
        }
        
        return \App\Helpers\CurrencyHelper::format((float) $this->value);
    }
}

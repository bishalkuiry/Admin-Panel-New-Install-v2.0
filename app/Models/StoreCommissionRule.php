<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreCommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'category_id',
        'commission_type',
        'commission_value',
        'is_active',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Calculate commission for amount
     */
    public function calculate(float $amount): float
    {
        return $this->commission_type === 'percent'
            ? $amount * ($this->commission_value / 100)
            : $this->commission_value;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\ScopeByModule;

class Unit extends Model
{
    use HasFactory, ScopeByModule;

    protected $fillable = [
        'module_id', 'name', 'short_name', 'type', 'conversion_factor', 'base_unit', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Convert value to base unit
     */
    public function toBaseUnit(float $value): float
    {
        return $value * $this->conversion_factor;
    }

    /**
     * Convert value from base unit
     */
    public function fromBaseUnit(float $value): float
    {
        return $value / $this->conversion_factor;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}

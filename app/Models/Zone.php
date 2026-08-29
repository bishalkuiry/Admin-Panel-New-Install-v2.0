<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Zone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'city',
        'state',
        'country',
        'currency',
        'coordinates', // Keeping this for reference/admin UI if needed, but 'area' is the source of truth
        'area', // Spatial column (GEOMETRY, SRID 0 — MariaDB default)
        'latitude',
        'longitude',
        // 'radius_km', // Deprecated
        'base_delivery_fee',
        'per_km_fee',
        'min_order_amount',
        'is_active',
        'sort_order',
        'surge_status',
        'surge_type',
        'surge_value',
        'surge_message',
    ];

    protected $casts = [
        'coordinates' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'base_delivery_fee' => 'decimal:2',
        'per_km_fee' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'surge_status' => 'boolean',
        'surge_value' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($zone) {
            if (empty($zone->slug)) {
                $zone->slug = Str::slug($zone->name . '-' . $zone->city);
            }
        });
    }

    /**
     * Stores serving this zone
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_zone')
            ->withPivot(['delivery_fee_override', 'delivery_time_override', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Active stores in this zone
     */
    public function activeStores(): BelongsToMany
    {
        return $this->stores()
            ->wherePivot('is_active', true)
            ->where('stores.status', 'active')
            ->where('stores.is_online', true);
    }

    /**
     * Calculate delivery fee for distance with Surge Pricing
     */
    public function calculateDeliveryFee(float $distanceKm): array
    {
        $baseFee = $this->base_delivery_fee + ($this->per_km_fee * $distanceKm);
        $surgeAmount = 0;
        $surgeApplied = false;

        if ($this->surge_status) {
            $surgeApplied = true;
            if ($this->surge_type === 'percent') {
                $surgeAmount = ($baseFee * $this->surge_value) / 100;
            } else {
                $surgeAmount = $this->surge_value;
            }
        }

        return [
            'base_cost' => $baseFee,
            'surge_amount' => $surgeAmount,
            'total_cost' => $baseFee + $surgeAmount,
            'surge_applied' => $surgeApplied,
            'surge_message' => $this->surge_message
        ];
    }

    /**
     * Scope to find zones covering a specific point using Spatial SQL.
     *
     * Dynamically passes ST_SRID(area) into ST_GeomFromText to match the SRID
     * of the area column (4326 in MySQL 8.0 vs 0 in MariaDB/MySQL 5.7).
     */
    public function scopeCovering($query, float $lat, float $lng)
    {
        return $query->whereRaw(
            "ST_Contains(area, ST_GeomFromText(?, ST_SRID(area)))",
            ["POINT($lng $lat)"]
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Check if a given lat/lng point falls within this zone's area
     */
    public function containsPoint(float $lat, float $lng): bool
    {
        if (!$this->area) {
            return false;
        }

        return self::where('id', $this->id)
            ->whereRaw(
                "ST_Contains(area, ST_GeomFromText(?, ST_SRID(area)))",
                ["POINT($lng $lat)"]
            )
            ->exists();
    }

}

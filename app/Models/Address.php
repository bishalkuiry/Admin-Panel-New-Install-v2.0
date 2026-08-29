<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'name', 'phone',
        'address_line_1', 'address_line_2', 'city', 'state',
        'postal_code', 'country', 'latitude', 'longitude', 'is_default'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code
        ]);
        return implode(', ', $parts);
    }

    /**
     * Find the zone that contains this address using a single spatial SQL query.
     */
    public function findZone()
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        return Zone::active()
            ->covering((float) $this->latitude, (float) $this->longitude)
            ->first();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryTracking extends Model
{
    use HasFactory;

    protected $table = 'delivery_tracking';

    protected $fillable = [
        'order_id',
        'delivery_partner_id',
        'latitude',
        'longitude',
        'speed',
        'accuracy',
        'status',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'accuracy' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    /**
     * Get the order for this tracking point
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the delivery partner for this tracking point
     */
    public function deliveryPartner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_partner_id');
    }

    /**
     * Scope for recent tracking points
     */
    public function scopeRecent($query, int $minutes = 30)
    {
        return $query->where('recorded_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope for moving status
     */
    public function scopeMoving($query)
    {
        return $query->where('status', 'moving');
    }

    /**
     * Scope for stopped status
     */
    public function scopeStopped($query)
    {
        return $query->where('status', 'stopped');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryCalculationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'cart_id',
        'user_id',
        'store_id',
        'zone_id',
        'address_id',
        'strategy_used',
        'calculation_method',
        'subtotal',
        'distance_km',
        'calculation_steps',
        'metadata',
        'base_fee',
        'distance_fee',
        'zone_fee',
        'final_charge',
        'was_free_delivery',
        'was_cached',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'calculation_steps' => 'array',
        'metadata' => 'array',
        'base_fee' => 'decimal:2',
        'distance_fee' => 'decimal:2',
        'zone_fee' => 'decimal:2',
        'final_charge' => 'decimal:2',
        'was_free_delivery' => 'boolean',
        'was_cached' => 'boolean',
    ];

    /**
     * Boot method to add model validation
     */
    protected static function boot()
    {
        parent::boot();
        
        // Ensure at least one of order_id or cart_id is present
        static::creating(function ($log) {
            if (empty($log->order_id) && empty($log->cart_id)) {
                throw new \InvalidArgumentException(
                    'DeliveryCalculationLog must have either order_id or cart_id'
                );
            }
        });
    }

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    // Scopes
    public function scopeByStrategy($query, string $strategy)
    {
        return $query->where('strategy_used', $strategy);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeFreeDelivery($query)
    {
        return $query->where('was_free_delivery', true);
    }

    // Helper methods
    public function getBreakdownText(): string
    {
        $parts = [];
        
        if ($this->base_fee > 0) {
            $parts[] = "Base Fee: \${$this->base_fee}";
        }
        
        if ($this->distance_fee > 0) {
            $parts[] = "Distance Fee: \${$this->distance_fee}";
        }
        
        if ($this->zone_fee > 0) {
            $parts[] = "Zone Fee: \${$this->zone_fee}";
        }
        
        if ($this->was_free_delivery) {
            $parts[] = "Free Delivery Applied";
        }
        
        return implode(' + ', $parts) . " = \${$this->final_charge}";
    }
}

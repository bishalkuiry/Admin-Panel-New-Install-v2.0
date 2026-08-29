<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'idempotency_key', 'user_id', 'store_id', 'address_id', 'delivery_partner_id', 'status', 'order_type',
        'subtotal', 'discount', 'delivery_fee', 'driver_tip', 'tax', 'total',
        'store_earning', 'admin_commission', 'driver_earning', 'coupon_funded_by',
        'payment_method', 'payment_status', 'payment_id', 'wallet_amount', 'notes', 'prescription',
        'delivery_otp', 'otp_verified_at',
        'cancellation_reason',
        'confirmed_at', 'packed_at', 'picked_up_at', 'out_for_delivery_at', 'delivered_at', 'cancelled_at'
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'driver_tip' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'store_earning' => 'decimal:2',
        'admin_commission' => 'decimal:2',
        'driver_earning' => 'decimal:2',
        'wallet_amount' => 'decimal:2',
        'otp_verified_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'packed_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->delivery_otp)) {
                $order->delivery_otp = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function getDeliveryOtpAttribute($value): string
    {
        if (!empty($value)) {
            return (string) $value;
        }
        $hash = abs(crc32('order_delivery_otp_seed_' . $this->id));
        return str_pad((string) (($hash % 9000) + 1000), 4, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function deliveryPartner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_partner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveryTracking()
    {
        return $this->hasOne(DeliveryTracking::class);
    }

    public function orderChats(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\OrderChat::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status instanceof \App\Enums\OrderStatus) {
            return $this->status->label();
        }
        
        // Handle raw string value if enum cast failed (e.g. legacy 'processing')
        $raw = $this->getRawOriginal('status');
        return match($raw) {
            'processing' => 'Processing',
             default => ucfirst(str_replace('_', ' ', $raw ?? 'Unknown'))
        };
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [OrderStatus::PENDING, OrderStatus::CONFIRMED]);
    }
}

<?php

namespace App\Models;

use App\Enums\StoreStatus;
use App\Enums\KycStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Traits\ScopeByModule;

class Store extends Model
{
    use HasFactory, SoftDeletes, ScopeByModule;

    protected $fillable = [
        'module_id', 'store_id', 'owner_id', 'name', 'slug', 'description', 'logo', 'banner',
        'email', 'phone', 'alternate_phone',
        'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country', 'latitude', 'longitude',
        'business_type', 'gst_number', 'pan_number', 'fssai_license', 'trade_license',
        'status', 'kyc_status', 'is_online', 'is_featured',
        'opening_hours', 'preparation_time', 'delivery_radius_km', 'pickup_enabled', 'delivery_enabled',
        'packing_charge', 'min_order_amount',
        'delivery_type', 'delivery_method', 'delivery_flat_rate', 'delivery_percentage', 'delivery_per_km_rate', 'store_free_delivery',
        'commission_percent', 'commission_flat', 'commission_type',
        'tax_enabled', 'tax_percent',
        'discount_text',
        'payment_methods', 'bank_name', 'bank_account_number', 'bank_ifsc', 'bank_account_holder',
        'rating', 'rating_count', 'order_count', 'total_revenue', 'payout_balance',
        'approved_by', 'approved_at', 'rejection_reason', 'admin_notes',
    ];

    protected $casts = [
        'status' => StoreStatus::class,
        'kyc_status' => KycStatus::class,
        'opening_hours' => 'array',
        'payment_methods' => 'array',
        'is_online' => 'boolean',
        'is_featured' => 'boolean',
        'pickup_enabled' => 'boolean',
        'delivery_enabled' => 'boolean',
        'tax_enabled' => 'boolean',
        'store_free_delivery' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'packing_charge' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'delivery_flat_rate' => 'decimal:2',
        'delivery_percentage' => 'decimal:2',
        'delivery_per_km_rate' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'commission_flat' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'rating' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'payout_balance' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($store) {
            $store->store_id = 'STR-' . strtoupper(Str::random(6));
            if (empty($store->slug)) {
                $store->slug = Str::slug($store->name);
            }
        });
    }

    // Relationships
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Brands assigned to this store (admin assigns)
     */
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'store_brands')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * Active brands for this store
     */
    public function activeBrands(): BelongsToMany
    {
        return $this->brands()->wherePivot('is_active', true)->where('brands.is_active', true);
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'store_zone')
            ->withPivot(['delivery_fee_override', 'delivery_time_override', 'is_active'])
            ->withTimestamps();
    }

    public function activeZones(): BelongsToMany
    {
        return $this->zones()->wherePivot('is_active', true);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(StoreStaff::class);
    }

    public function activeStaff(): HasMany
    {
        return $this->staff()->where('is_active', true);
    }

    public function kycDocuments(): HasMany
    {
        return $this->hasMany(StoreKycDocument::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(StorePayout::class);
    }

    public function commissionRules(): HasMany
    {
        return $this->hasMany(StoreCommissionRule::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(StoreActivityLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', StoreStatus::ACTIVE);
    }

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', StoreStatus::PENDING);
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeInZone($query, int $zoneId)
    {
        return $query->whereHas('zones', fn($q) => $q->where('zones.id', $zoneId));
    }

    // Methods
    public function canAcceptOrders(): bool
    {
        return $this->status->canAcceptOrders() && $this->is_online;
    }

    public function isOpen(): bool
    {
        if (!$this->opening_hours) return true;

        $day = strtolower(now()->format('D'));
        $hours = $this->opening_hours[$day] ?? null;

        if (!$hours || !isset($hours['open'], $hours['close'])) return false;

        $now = now()->format('H:i');
        return $now >= $hours['open'] && $now <= $hours['close'];
    }

    public function calculateCommission(float $orderAmount, ?int $categoryId = null, ?Product $product = null): float
    {
        // 1. Check Product Level Commission (Highest Priority)
        if ($product && !is_null($product->commission) && $product->commission > 0) {
            return $orderAmount * ($product->commission / 100);
        }

        // 2. Check Store-specific Category Rule next
        if ($categoryId) {
            $rule = $this->commissionRules()
                ->where('category_id', $categoryId)
                ->where('is_active', true)
                ->first();

            if ($rule) {
                return $rule->commission_type === 'percent'
                    ? $orderAmount * ($rule->commission_value / 100)
                    : $rule->commission_value;
            }
        }

        // 3. Store-wide Level Commission
        if ($this->commission_percent > 0 || $this->commission_flat > 0) {
            return $this->commission_type === 'percent'
                ? $orderAmount * ($this->commission_percent / 100)
                : $this->commission_flat;
        }

        // 4. Global Admin Commission Fallback
        $globalPercent = (float) Setting::get('default_commission_percent', 0);
        return $orderAmount * ($globalPercent / 100);
    }

    public function logActivity(string $action, ?User $user = null, array $data = []): void
    {
        $this->activityLogs()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function getAddressAttribute()
    {
        return $this->address_line_1;
    }

    /**
     * Payout Stats Accessors
     */
    public function getAvailableBalanceAttribute()
    {
        return max(0, (float) $this->payout_balance);
    }

    public function getPendingBalanceAttribute()
    {
        return $this->payouts()->where('status', 'pending')->sum('net_amount');
    }

    public function getTotalPaidAttribute()
    {
        return $this->payouts()->where('status', 'completed')->sum('net_amount');
    }

    /**
     * Calculate delivery charge for this store
     * 
     * @param float $orderTotal Order subtotal
     * @param float|null $distance Distance in kilometers (for per_km calculation)
     * @return float Calculated delivery charge
     */
    public function calculateDeliveryCharge(float $orderTotal, ?float $distance = null): float
    {
        // Check store-specific free delivery first
        if ($this->store_free_delivery) {
            return 0.00;
        }

        // Check global free delivery setting
        $globalFreeDelivery = Setting::where('key', 'global_free_delivery')->value('value') ?? '0';
        if ($globalFreeDelivery == '1') {
            return 0.00;
        }

        // Determine delivery charge based on delivery type
        switch ($this->delivery_type) {
            case 'self':
                // Store self-delivery - no charge
                return 0.00;

            case 'custom':
                // Custom store delivery calculation
                return $this->calculateCustomDelivery($orderTotal, $distance);

            case 'global':
            default:
                // Use global delivery charge
                $globalCharge = Setting::where('key', 'global_delivery_charge')->value('value') ?? '5.99';
                return (float) $globalCharge;
        }
    }

    /**
     * Calculate custom delivery charge based on store's delivery method
     * 
     * @param float $orderTotal Order subtotal
     * @param float|null $distance Distance in kilometers
     * @return float Calculated delivery charge
     */
    private function calculateCustomDelivery(float $orderTotal, ?float $distance = null): float
    {
        switch ($this->delivery_method) {
            case 'flat':
                // Flat rate delivery
                return $this->delivery_flat_rate ?? 0.00;

            case 'percentage':
                // Percentage of order total
                $percentage = $this->delivery_percentage ?? 0.00;
                return $orderTotal * ($percentage / 100);

            case 'per_km':
                // Per kilometer rate
                if ($distance === null || $distance <= 0) {
                    // If distance not provided, use flat rate as fallback
                    return $this->delivery_flat_rate ?? 0.00;
                }
                $perKmRate = $this->delivery_per_km_rate ?? 0.00;
                return $distance * $perKmRate;

            default:
                // Fallback to global delivery charge
                $globalCharge = Setting::where('key', 'global_delivery_charge')->value('value') ?? '5.99';
                return (float) $globalCharge;
        }
    }
}

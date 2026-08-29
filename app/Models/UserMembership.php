<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'membership_plan_id',
        'starts_at',
        'expires_at',
        'status',
        'payment_method',
        'payment_status',
        'payment_id',
        'amount_paid',
        'free_deliveries_used_this_month',
        'cashback_earned_this_month',
        'total_discount_saved',
        'auto_renew',
        'renewal_count',
        'cancelled_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'amount_paid' => 'decimal:2',
        'free_deliveries_used_this_month' => 'integer',
        'cashback_earned_this_month' => 'decimal:2',
        'total_discount_saved' => 'decimal:2',
        'auto_renew' => 'boolean',
        'renewal_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at && $this->expires_at->isFuture();
    }
}

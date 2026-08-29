<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REWARDED = 'rewarded';

    protected $fillable = [
        'referrer_id',
        'referee_id',
        'referral_code',
        'status',
        'referrer_reward_amount',
        'referrer_reward_paid',
        'referrer_rewarded_at',
        'referee_reward_amount',
        'referee_reward_paid',
        'referee_rewarded_at',
        'referee_free_deliveries',
        'referee_free_deliveries_used',
    ];

    protected $casts = [
        'referrer_reward_amount' => 'decimal:2',
        'referrer_reward_paid' => 'boolean',
        'referrer_rewarded_at' => 'datetime',
        'referee_reward_amount' => 'decimal:2',
        'referee_reward_paid' => 'boolean',
        'referee_rewarded_at' => 'datetime',
        'referee_free_deliveries' => 'integer',
        'referee_free_deliveries_used' => 'integer',
    ];

    /**
     * Relationship: User who shared the referral code
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Relationship: User who used the referral code
     */
    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    /**
     * Check if referee has remaining free deliveries
     */
    public function hasRemainingFreeDeliveries(): bool
    {
        return $this->referee_free_deliveries_used < $this->referee_free_deliveries;
    }

    /**
     * Get remaining free deliveries count
     */
    public function getRemainingFreeDeliveriesAttribute(): int
    {
        return max(0, $this->referee_free_deliveries - $this->referee_free_deliveries_used);
    }

    /**
     * Consume one free delivery
     */
    public function consumeFreeDelivery(): bool
    {
        if (!$this->hasRemainingFreeDeliveries()) {
            return false;
        }

        $this->increment('referee_free_deliveries_used');
        return true;
    }

    /**
     * Check if referrer reward is pending
     */
    public function isReferrerRewardPending(): bool
    {
        return $this->referrer_reward_amount > 0 && !$this->referrer_reward_paid;
    }

    /**
     * Check if referee reward is pending
     */
    public function isRefereeRewardPending(): bool
    {
        return $this->referee_reward_amount > 0 && !$this->referee_reward_paid;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointTransaction extends Model
{
    // Transaction types
    public const TYPE_EARNED = 'earned';
    public const TYPE_REDEEMED = 'redeemed';
    public const TYPE_EXPIRED = 'expired';
    public const TYPE_ADMIN_CREDIT = 'admin_credit';
    public const TYPE_ADMIN_DEBIT = 'admin_debit';

    protected $fillable = [
        'user_points_id',
        'type',
        'points',
        'points_before',
        'points_after',
        'order_id',
        'description',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'points' => 'integer',
        'points_before' => 'integer',
        'points_after' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Relationship: User points account
     */
    public function userPoints(): BelongsTo
    {
        return $this->belongsTo(UserPoints::class);
    }

    /**
     * Relationship: Associated order (if any)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship: Admin who created the transaction (if any)
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this is a credit (positive) transaction
     */
    public function isCredit(): bool
    {
        return in_array($this->type, [self::TYPE_EARNED, self::TYPE_ADMIN_CREDIT]);
    }

    /**
     * Check if this is a debit (negative) transaction
     */
    public function isDebit(): bool
    {
        return in_array($this->type, [self::TYPE_REDEEMED, self::TYPE_EXPIRED, self::TYPE_ADMIN_DEBIT]);
    }
}

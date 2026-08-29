<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPoints extends Model
{
    protected $fillable = [
        'user_id',
        'total_points',
        'available_points',
        'redeemed_points',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'available_points' => 'integer',
        'redeemed_points' => 'integer',
    ];

    /**
     * Relationship: User who owns these points
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: All point transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    /**
     * Check if user has enough points
     */
    public function hasPoints(int $amount): bool
    {
        return $this->available_points >= $amount;
    }

    /**
     * Get points value in currency
     */
    public function getValueAttribute(): float
    {
        $pointsPerCurrency = (int) Setting::get('cashback_points_per_currency', 100);
        if ($pointsPerCurrency <= 0) {
            $pointsPerCurrency = 100;
        }
        return $this->available_points / $pointsPerCurrency;
    }
}

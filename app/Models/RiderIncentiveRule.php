<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderIncentiveRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'target_deliveries',
        'bonus_amount',
        'period_type',
        'is_active',
    ];

    protected $casts = [
        'target_deliveries' => 'integer',
        'bonus_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}

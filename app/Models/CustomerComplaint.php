<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerComplaint extends Model
{
    protected $fillable = [
        'ticket_number',
        'user_id',
        'order_id',
        'store_id',
        'driver_id',
        'category',
        'description',
        'attachments',
        'status',
        'action_taken',
        'penalty_amount',
        'admin_response',
    ];

    protected $casts = [
        'attachments' => 'array',
        'penalty_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->ticket_number)) {
                $model->ticket_number = 'CMP-' . strtoupper(uniqid());
            }
        });
    }
}

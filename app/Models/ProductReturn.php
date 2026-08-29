<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReturn extends Model
{
    protected $fillable = [
        'return_number',
        'order_id',
        'order_item_id',
        'user_id',
        'store_id',
        'type',
        'reason',
        'comments',
        'attachments',
        'status',
        'refund_amount',
        'admin_notes',
    ];

    protected $casts = [
        'attachments' => 'array',
        'refund_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->return_number)) {
                $model->return_number = 'RET-' . strtoupper(uniqid());
            }
        });
    }
}

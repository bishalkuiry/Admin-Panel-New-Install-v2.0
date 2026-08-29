<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class SellerStaff extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $table = 'seller_staff';

    protected $fillable = [
        'store_id',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'permissions',
        'is_active',
        'payout_balance',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'bank_account_holder',
        'kyc_status',
        'kyc_rejection_reason',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'permissions'       => 'array',
            'is_active'         => 'boolean',
            'payout_balance'    => 'decimal:2',
            'last_login_at'     => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}

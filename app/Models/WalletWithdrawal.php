<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class WalletWithdrawal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'wallet_id',
        'amount',
        'bank_name',
        'account_number',
        'account_holder_name',
        'ifsc_code',
        'status',
        'notes',
        'processed_by',
        'processed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the wallet that owns the withdrawal
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the user who owns the wallet (through wallet relationship)
     */
    public function user(): HasOneThrough
    {
        // wallet_withdrawals.wallet_id -> wallets.id -> wallets.user_id -> users.id
        return $this->hasOneThrough(
            User::class,      // Final model
            Wallet::class,    // Intermediate model
            'id',             // Foreign key on wallets table (wallets.id)
            'id',             // Foreign key on users table (users.id)
            'wallet_id',      // Local key on wallet_withdrawals table
            'user_id'         // Local key on wallets table
        );
    }

    /**
     * Get the admin who processed the withdrawal
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}

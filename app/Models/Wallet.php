<?php

namespace App\Models;

use App\Exceptions\InsufficientBalanceException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'balance',
        'currency',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the wallet
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transactions for this wallet
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Credit the wallet balance and create a transaction record
     *
     * @param float $amount Amount to credit
     * @param string $type Transaction type
     * @param string $description Transaction description
     * @param array|null $metadata Additional metadata
     * @return WalletTransaction
     */
    public function credit(float $amount, string $type, string $description, ?array $metadata = null): WalletTransaction
    {
        $balanceBefore = $this->balance;
        $this->balance += $amount;
        $this->save();

        return $this->transactions()->create([
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Debit the wallet balance and create a transaction record
     *
     * @param float $amount Amount to debit
     * @param string $type Transaction type
     * @param string $description Transaction description
     * @param array|null $metadata Additional metadata
     * @return WalletTransaction
     * @throws InsufficientBalanceException If insufficient balance
     */
    public function debit(float $amount, string $type, string $description, ?array $metadata = null): WalletTransaction
    {
        if (!$this->hasBalance($amount)) {
            throw new InsufficientBalanceException($amount, $this->balance);
        }

        $balanceBefore = $this->balance;
        $this->balance -= $amount;
        $this->save();

        return $this->transactions()->create([
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Check if wallet has sufficient balance
     *
     * @param float $amount Amount to check
     * @return bool
     */
    public function hasBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Lock the wallet for update to prevent concurrent modifications
     * This method should be called on a query builder, not the model instance
     * Example: Wallet::where('id', $wallet->id)->lockForUpdate()->first()
     *
     * @return self
     */
    public function lockForUpdate(): self
    {
        return static::query()->where('id', $this->id)->lockForUpdate()->first() ?? $this;
    }
}

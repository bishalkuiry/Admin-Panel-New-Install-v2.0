<?php

namespace Database\Factories;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $balanceBefore = $this->faker->randomFloat(2, 0, 10000);
        $amount = $this->faker->randomFloat(2, 1, 1000);
        $isCredit = $this->faker->boolean();
        
        return [
            'wallet_id' => Wallet::factory(),
            'type' => $this->faker->randomElement([
                WalletTransaction::TYPE_CREDIT,
                WalletTransaction::TYPE_DEBIT,
                WalletTransaction::TYPE_ORDER_PAYMENT,
                WalletTransaction::TYPE_TOP_UP,
                WalletTransaction::TYPE_SIGNUP_BONUS,
                WalletTransaction::TYPE_ADMIN_CREDIT,
                WalletTransaction::TYPE_ADMIN_DEBIT,
                WalletTransaction::TYPE_REFUND,
                WalletTransaction::TYPE_WITHDRAWAL,
                WalletTransaction::TYPE_WITHDRAWAL_REVERSAL,
            ]),
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $isCredit ? $balanceBefore + $amount : $balanceBefore - $amount,
            'description' => $this->faker->sentence(),
            'reference_type' => null,
            'reference_id' => null,
            'metadata' => null,
            'created_by' => null,
        ];
    }

    /**
     * Indicate that the transaction is a credit.
     */
    public function credit(float $amount = null): static
    {
        return $this->state(function (array $attributes) use ($amount) {
            $amt = $amount ?? $this->faker->randomFloat(2, 1, 1000);
            return [
                'type' => WalletTransaction::TYPE_CREDIT,
                'amount' => $amt,
                'balance_after' => $attributes['balance_before'] + $amt,
            ];
        });
    }

    /**
     * Indicate that the transaction is a debit.
     */
    public function debit(float $amount = null): static
    {
        return $this->state(function (array $attributes) use ($amount) {
            $amt = $amount ?? $this->faker->randomFloat(2, 1, 1000);
            return [
                'type' => WalletTransaction::TYPE_DEBIT,
                'amount' => $amt,
                'balance_after' => $attributes['balance_before'] - $amt,
            ];
        });
    }

    /**
     * Indicate that the transaction is for a specific wallet.
     */
    public function forWallet(Wallet $wallet): static
    {
        return $this->state(fn (array $attributes) => [
            'wallet_id' => $wallet->id,
        ]);
    }

    /**
     * Indicate that the transaction has specific balance values.
     */
    public function withBalances(float $before, float $after): static
    {
        return $this->state(fn (array $attributes) => [
            'balance_before' => $before,
            'balance_after' => $after,
            'amount' => abs($after - $before),
        ]);
    }
}

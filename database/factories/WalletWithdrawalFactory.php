<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletWithdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WalletWithdrawal>
 */
class WalletWithdrawalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = WalletWithdrawal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'bank_name' => fake()->company() . ' Bank',
            'account_number' => fake()->numerify('##########'),
            'account_holder_name' => fake()->name(),
            'ifsc_code' => fake()->regexify('[A-Z]{4}0[A-Z0-9]{6}'),
            'status' => WalletWithdrawal::STATUS_PENDING,
            'notes' => null,
            'processed_by' => null,
            'processed_at' => null,
        ];
    }

    /**
     * Indicate that the withdrawal is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WalletWithdrawal::STATUS_PENDING,
            'processed_by' => null,
            'processed_at' => null,
        ]);
    }

    /**
     * Indicate that the withdrawal is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WalletWithdrawal::STATUS_APPROVED,
            'processed_by' => User::factory(),
            'processed_at' => now(),
        ]);
    }

    /**
     * Indicate that the withdrawal is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WalletWithdrawal::STATUS_REJECTED,
            'processed_by' => User::factory(),
            'processed_at' => now(),
            'notes' => fake()->sentence(),
        ]);
    }
}

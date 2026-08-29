<?php

namespace Tests\Unit;

use App\Exceptions\InsufficientBalanceException;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Test suite to verify Wallet model meets all requirements for task 2.1
 * Requirements: 1.1, 1.2, 1.3
 */
class WalletRequirementsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Requirement 1.1: Wallet stores user ID, current balance, and timestamps
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function wallet_stores_required_fields()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 150.50,
        ]);

        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertEquals('150.50', $wallet->balance);
        $this->assertNotNull($wallet->created_at);
        $this->assertNotNull($wallet->updated_at);
    }

    /**
     * Requirement 1.2: Wallet has user() relationship
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function wallet_has_user_relationship()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $wallet->user);
        $this->assertEquals($user->id, $wallet->user->id);
    }

    /**
     * Requirement 1.2: Wallet has transactions() relationship
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function wallet_has_transactions_relationship()
    {
        $wallet = Wallet::factory()->create();
        
        // Create some transactions
        $wallet->credit(50.00, WalletTransaction::TYPE_CREDIT, 'Test credit 1');
        $wallet->credit(30.00, WalletTransaction::TYPE_CREDIT, 'Test credit 2');

        $this->assertCount(2, $wallet->transactions);
        $this->assertInstanceOf(WalletTransaction::class, $wallet->transactions->first());
    }

    /**
     * Requirement 1.3: Balance field is cast to decimal with 2 places
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function balance_is_cast_to_decimal_with_two_places()
    {
        $wallet = Wallet::factory()->create(['balance' => 123.456]);

        // Should be rounded to 2 decimal places
        $this->assertEquals('123.46', $wallet->balance);
    }

    /**
     * Requirement 1.2: credit() method adds balance and creates transaction
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function credit_method_increases_balance_and_creates_transaction()
    {
        $wallet = Wallet::factory()->create(['balance' => 100.00]);

        $transaction = $wallet->credit(
            50.00,
            WalletTransaction::TYPE_CREDIT,
            'Test credit',
            ['test' => 'metadata']
        );

        $this->assertEquals(150.00, $wallet->fresh()->balance);
        $this->assertInstanceOf(WalletTransaction::class, $transaction);
        $this->assertEquals(100.00, $transaction->balance_before);
        $this->assertEquals(150.00, $transaction->balance_after);
        $this->assertEquals(50.00, $transaction->amount);
        $this->assertEquals(['test' => 'metadata'], $transaction->metadata);
    }

    /**
     * Requirement 1.2: debit() method subtracts balance and creates transaction
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function debit_method_decreases_balance_and_creates_transaction()
    {
        $wallet = Wallet::factory()->create(['balance' => 100.00]);

        $transaction = $wallet->debit(
            30.00,
            WalletTransaction::TYPE_DEBIT,
            'Test debit',
            ['test' => 'metadata']
        );

        $this->assertEquals(70.00, $wallet->fresh()->balance);
        $this->assertInstanceOf(WalletTransaction::class, $transaction);
        $this->assertEquals(100.00, $transaction->balance_before);
        $this->assertEquals(70.00, $transaction->balance_after);
        $this->assertEquals(30.00, $transaction->amount);
        $this->assertEquals(['test' => 'metadata'], $transaction->metadata);
    }

    /**
     * Requirement 1.2: debit() throws exception when insufficient balance
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function debit_throws_exception_when_insufficient_balance()
    {
        $wallet = Wallet::factory()->create(['balance' => 50.00]);

        $this->expectException(InsufficientBalanceException::class);
        $wallet->debit(100.00, WalletTransaction::TYPE_DEBIT, 'Test debit');

        // Balance should remain unchanged
        $this->assertEquals(50.00, $wallet->fresh()->balance);
    }

    /**
     * Requirement 1.2: hasBalance() method checks sufficient balance
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function hasBalance_method_checks_sufficient_balance()
    {
        $wallet = Wallet::factory()->create(['balance' => 100.00]);

        $this->assertTrue($wallet->hasBalance(50.00));
        $this->assertTrue($wallet->hasBalance(100.00));
        $this->assertFalse($wallet->hasBalance(100.01));
        $this->assertFalse($wallet->hasBalance(150.00));
    }

    /**
     * Requirement 1.2: lockForUpdate() method locks wallet for concurrent access
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function lockForUpdate_method_locks_wallet_for_update()
    {
        $wallet = Wallet::factory()->create(['balance' => 100.00]);

        DB::beginTransaction();
        
        try {
            $lockedWallet = $wallet->lockForUpdate();
            
            $this->assertInstanceOf(Wallet::class, $lockedWallet);
            $this->assertEquals($wallet->id, $lockedWallet->id);
            $this->assertEquals(100.00, $lockedWallet->balance);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Requirement 1.3: Wallet supports all user roles
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function wallet_supports_all_user_roles()
    {
        $roles = ['customer', 'delivery_partner', 'store_owner', 'admin'];

        foreach ($roles as $role) {
            $user = User::factory()->create(['role' => $role]);
            $wallet = Wallet::factory()->create(['user_id' => $user->id]);

            $this->assertEquals($user->id, $wallet->user_id);
            $this->assertEquals($role, $wallet->user->role->value);
        }
    }

    /**
     * MySQL Compatibility: Decimal precision is maintained
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function mysql_decimal_precision_is_maintained()
    {
        $wallet = Wallet::factory()->create(['balance' => 0.00]);

        // Test various decimal operations
        $wallet->credit(10.50, WalletTransaction::TYPE_CREDIT, 'Test 1');
        $this->assertEquals('10.50', $wallet->fresh()->balance);

        $wallet->credit(0.01, WalletTransaction::TYPE_CREDIT, 'Test 2');
        $this->assertEquals('10.51', $wallet->fresh()->balance);

        $wallet->debit(0.01, WalletTransaction::TYPE_DEBIT, 'Test 3');
        $this->assertEquals('10.50', $wallet->fresh()->balance);
    }

    /**
     * MySQL Compatibility: Foreign key constraints work correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function mysql_foreign_key_constraints_work()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $walletId = $wallet->id;

        // Create a transaction
        $wallet->credit(50.00, WalletTransaction::TYPE_CREDIT, 'Test');

        // Delete user should cascade delete wallet and transactions
        $user->delete();

        $this->assertDatabaseMissing('wallets', ['id' => $walletId]);
        $this->assertDatabaseMissing('wallet_transactions', ['wallet_id' => $walletId]);
    }
}

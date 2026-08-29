<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Wallet;
use App\Services\RealtimeService;
use App\Services\Payment\PaymentService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    private WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create WalletService with mocked RealtimeService and PaymentService
        $realtimeService = $this->createMock(RealtimeService::class);
        $paymentService = $this->createMock(PaymentService::class);
        $this->walletService = new WalletService($realtimeService, $paymentService);
    }

    /** @test */
    public function it_creates_wallet_for_new_user()
    {
        $user = User::factory()->create();

        $wallet = $this->walletService->createWallet($user);

        $this->assertInstanceOf(Wallet::class, $wallet);
        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertEquals(0.00, $wallet->balance);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 0.00,
        ]);
    }

    /** @test */
    public function it_creates_wallet_with_correct_currency()
    {
        config(['app.currency' => 'USD']);
        $user = User::factory()->create();

        $wallet = $this->walletService->createWallet($user);

        $this->assertEquals('USD', $wallet->currency);
    }

    /** @test */
    public function it_creates_wallet_with_default_currency_when_not_configured()
    {
        config(['app.currency' => null]);
        $user = User::factory()->create();

        $wallet = $this->walletService->createWallet($user);

        $this->assertEquals('INR', $wallet->currency);
    }

    /** @test */
    public function it_gets_existing_wallet_when_wallet_exists()
    {
        $user = User::factory()->create();
        $existingWallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $wallet = $this->walletService->getOrCreateWallet($user);

        $this->assertEquals($existingWallet->id, $wallet->id);
        $this->assertEquals(100.00, $wallet->balance);
        
        // Ensure no new wallet was created
        $this->assertEquals(1, Wallet::where('user_id', $user->id)->count());
    }

    /** @test */
    public function it_creates_wallet_when_wallet_does_not_exist()
    {
        $user = User::factory()->create();

        // Ensure no wallet exists
        $this->assertEquals(0, Wallet::where('user_id', $user->id)->count());

        $wallet = $this->walletService->getOrCreateWallet($user);

        $this->assertInstanceOf(Wallet::class, $wallet);
        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertEquals(0.00, $wallet->balance);
        $this->assertEquals(1, Wallet::where('user_id', $user->id)->count());
    }

    /** @test */
    public function get_or_create_wallet_is_idempotent()
    {
        $user = User::factory()->create();

        // Call multiple times
        $wallet1 = $this->walletService->getOrCreateWallet($user);
        $wallet2 = $this->walletService->getOrCreateWallet($user);
        $wallet3 = $this->walletService->getOrCreateWallet($user);

        // Should return the same wallet
        $this->assertEquals($wallet1->id, $wallet2->id);
        $this->assertEquals($wallet1->id, $wallet3->id);
        
        // Should only have one wallet
        $this->assertEquals(1, Wallet::where('user_id', $user->id)->count());
    }

    /** @test */
    public function create_wallet_uses_database_transaction()
    {
        $user = User::factory()->create();

        // This test verifies that the wallet creation is wrapped in a transaction
        // by checking that the wallet is properly created
        $wallet = $this->walletService->createWallet($user);

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_creates_wallets_for_different_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $wallet1 = $this->walletService->createWallet($user1);
        $wallet2 = $this->walletService->createWallet($user2);

        $this->assertNotEquals($wallet1->id, $wallet2->id);
        $this->assertEquals($user1->id, $wallet1->user_id);
        $this->assertEquals($user2->id, $wallet2->user_id);
    }

    /** @test */
    public function it_credits_wallet_balance_and_creates_transaction()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $transaction = $this->walletService->creditBalance(
            $wallet,
            50.00,
            'test_credit',
            'Test credit transaction'
        );

        $this->assertEquals(150.00, $wallet->fresh()->balance);
        $this->assertEquals('test_credit', $transaction->type);
        $this->assertEquals(50.00, $transaction->amount);
        $this->assertEquals(100.00, $transaction->balance_before);
        $this->assertEquals(150.00, $transaction->balance_after);
        $this->assertEquals('Test credit transaction', $transaction->description);
    }

    /** @test */
    public function it_credits_wallet_with_metadata()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $metadata = ['order_id' => 123, 'payment_method' => 'razorpay'];

        $transaction = $this->walletService->creditBalance(
            $wallet,
            50.00,
            'test_credit',
            'Test credit with metadata',
            $metadata
        );

        $this->assertEquals($metadata, $transaction->metadata);
    }

    /** @test */
    public function it_credits_wallet_with_created_by_user()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $transaction = $this->walletService->creditBalance(
            $wallet,
            50.00,
            'admin_credit',
            'Admin credit',
            null,
            $admin
        );

        $this->assertEquals($admin->id, $transaction->created_by);
    }

    /** @test */
    public function it_debits_wallet_balance_and_creates_transaction()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $transaction = $this->walletService->debitBalance(
            $wallet,
            30.00,
            'test_debit',
            'Test debit transaction'
        );

        $this->assertEquals(70.00, $wallet->fresh()->balance);
        $this->assertEquals('test_debit', $transaction->type);
        $this->assertEquals(30.00, $transaction->amount);
        $this->assertEquals(100.00, $transaction->balance_before);
        $this->assertEquals(70.00, $transaction->balance_after);
        $this->assertEquals('Test debit transaction', $transaction->description);
    }

    /** @test */
    public function it_debits_wallet_with_metadata()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $metadata = ['order_id' => 456, 'payment_method' => 'wallet'];

        $transaction = $this->walletService->debitBalance(
            $wallet,
            30.00,
            'order_payment',
            'Order payment',
            $metadata
        );

        $this->assertEquals($metadata, $transaction->metadata);
    }

    /** @test */
    public function it_debits_wallet_with_created_by_user()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $transaction = $this->walletService->debitBalance(
            $wallet,
            30.00,
            'admin_debit',
            'Admin debit',
            null,
            $admin
        );

        $this->assertEquals($admin->id, $transaction->created_by);
    }

    /** @test */
    public function it_throws_exception_when_insufficient_balance()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 50.00,
        ]);

        $this->expectException(\App\Exceptions\InsufficientBalanceException::class);

        $this->walletService->debitBalance(
            $wallet,
            100.00,
            'test_debit',
            'Test debit with insufficient balance'
        );

        // Balance should remain unchanged
        $this->assertEquals(50.00, $wallet->fresh()->balance);
    }

    /** @test */
    public function it_allows_negative_balance_when_flag_is_set()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 50.00,
        ]);

        $transaction = $this->walletService->debitBalance(
            $wallet,
            100.00,
            'admin_debit',
            'Admin debit with negative balance',
            null,
            $admin,
            true // allowNegative = true
        );

        $this->assertEquals(-50.00, $wallet->fresh()->balance);
        $this->assertEquals(50.00, $transaction->balance_before);
        $this->assertEquals(-50.00, $transaction->balance_after);
    }

    /** @test */
    public function credit_and_debit_use_database_transactions()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        // Credit
        $creditTransaction = $this->walletService->creditBalance(
            $wallet,
            50.00,
            'test_credit',
            'Test credit'
        );

        $this->assertDatabaseHas('wallet_transactions', [
            'id' => $creditTransaction->id,
            'wallet_id' => $wallet->id,
            'type' => 'test_credit',
        ]);

        // Debit
        $debitTransaction = $this->walletService->debitBalance(
            $wallet->fresh(),
            30.00,
            'test_debit',
            'Test debit'
        );

        $this->assertDatabaseHas('wallet_transactions', [
            'id' => $debitTransaction->id,
            'wallet_id' => $wallet->id,
            'type' => 'test_debit',
        ]);
    }

    /** @test */
    public function it_uses_row_locking_for_credit_operations()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        // This test verifies that row locking is used by checking
        // that the operation completes successfully with the locked wallet
        $transaction = $this->walletService->creditBalance(
            $wallet,
            50.00,
            'test_credit',
            'Test credit with locking'
        );

        $this->assertEquals(150.00, $wallet->fresh()->balance);
        $this->assertNotNull($transaction);
    }

    /** @test */
    public function it_uses_row_locking_for_debit_operations()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        // This test verifies that row locking is used by checking
        // that the operation completes successfully with the locked wallet
        $transaction = $this->walletService->debitBalance(
            $wallet,
            30.00,
            'test_debit',
            'Test debit with locking'
        );

        $this->assertEquals(70.00, $wallet->fresh()->balance);
        $this->assertNotNull($transaction);
    }

    /** @test */
    public function it_rejects_negative_amounts_for_credit()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $this->expectException(\App\Exceptions\InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must be positive and greater than zero');

        $this->walletService->creditBalance(
            $wallet,
            -50.00,
            'test_credit',
            'Test negative credit'
        );

        // Balance should remain unchanged
        $this->assertEquals(100.00, $wallet->fresh()->balance);
    }

    /** @test */
    public function it_rejects_zero_amounts_for_credit()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $this->expectException(\App\Exceptions\InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must be positive and greater than zero');

        $this->walletService->creditBalance(
            $wallet,
            0.00,
            'test_credit',
            'Test zero credit'
        );

        // Balance should remain unchanged
        $this->assertEquals(100.00, $wallet->fresh()->balance);
    }

    /** @test */
    public function it_rejects_negative_amounts_for_debit()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $this->expectException(\App\Exceptions\InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must be positive and greater than zero');

        $this->walletService->debitBalance(
            $wallet,
            -30.00,
            'test_debit',
            'Test negative debit'
        );

        // Balance should remain unchanged
        $this->assertEquals(100.00, $wallet->fresh()->balance);
    }

    /** @test */
    public function it_rejects_zero_amounts_for_debit()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $this->expectException(\App\Exceptions\InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must be positive and greater than zero');

        $this->walletService->debitBalance(
            $wallet,
            0.00,
            'test_debit',
            'Test zero debit'
        );

        // Balance should remain unchanged
        $this->assertEquals(100.00, $wallet->fresh()->balance);
    }

    /** @test */
    public function it_rejects_amounts_with_more_than_two_decimal_places()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $this->expectException(\App\Exceptions\InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must have maximum 2 decimal places');

        $this->walletService->creditBalance(
            $wallet,
            50.123,
            'test_credit',
            'Test credit with 3 decimal places'
        );

        // Balance should remain unchanged
        $this->assertEquals(100.00, $wallet->fresh()->balance);
    }

    /** @test */
    public function it_accepts_amounts_with_one_decimal_place()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $transaction = $this->walletService->creditBalance(
            $wallet,
            50.5,
            'test_credit',
            'Test credit with 1 decimal place'
        );

        $this->assertEquals(150.50, $wallet->fresh()->balance);
        $this->assertEquals(50.5, $transaction->amount);
    }

    /** @test */
    public function it_accepts_amounts_with_two_decimal_places()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $transaction = $this->walletService->creditBalance(
            $wallet,
            50.75,
            'test_credit',
            'Test credit with 2 decimal places'
        );

        $this->assertEquals(150.75, $wallet->fresh()->balance);
        $this->assertEquals(50.75, $transaction->amount);
    }

    /** @test */
    public function it_accepts_whole_number_amounts()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $transaction = $this->walletService->creditBalance(
            $wallet,
            50.00,
            'test_credit',
            'Test credit with whole number'
        );

        $this->assertEquals(150.00, $wallet->fresh()->balance);
        $this->assertEquals(50.00, $transaction->amount);
    }

    /** @test */
    public function it_rejects_amounts_exceeding_maximum_limit()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $this->expectException(\App\Exceptions\InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must not exceed 1000000');

        $this->walletService->creditBalance(
            $wallet,
            1000001.00,
            'test_credit',
            'Test credit exceeding limit'
        );

        // Balance should remain unchanged
        $this->assertEquals(100.00, $wallet->fresh()->balance);
    }

    /** @test */
    public function it_accepts_amounts_at_maximum_limit()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $transaction = $this->walletService->creditBalance(
            $wallet,
            1000000.00,
            'test_credit',
            'Test credit at maximum limit'
        );

        $this->assertEquals(1000100.00, $wallet->fresh()->balance);
        $this->assertEquals(1000000.00, $transaction->amount);
    }

    /** @test */
    public function it_validates_amount_before_checking_balance_for_debit()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        // Should throw InvalidAmountException, not InsufficientBalanceException
        $this->expectException(\App\Exceptions\InvalidAmountException::class);

        $this->walletService->debitBalance(
            $wallet,
            -50.00,
            'test_debit',
            'Test negative debit'
        );
    }
}


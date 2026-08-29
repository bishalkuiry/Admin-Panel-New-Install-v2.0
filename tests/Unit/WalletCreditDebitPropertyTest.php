<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\RealtimeService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Tests for Wallet Credit and Debit Operations
 * 
 * Feature: wallet-system
 * Property 6: Admin Credit Operation
 * Property 7: Admin Debit Operation
 * Property 22: Balance Change Creates Transaction
 * 
 * Validates: Requirements 3.1, 3.2, 6.1
 */
class WalletCreditDebitPropertyTest extends TestCase
{
    use RefreshDatabase;

    private WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create WalletService with mocked RealtimeService
        $realtimeService = $this->createMock(RealtimeService::class);
        $this->walletService = new WalletService($realtimeService);
    }

    /**
     * Data provider for credit amounts
     * Tests various positive amounts to ensure credit operations work correctly
     * 
     * @return array<string, array<float>>
     */
    public static function creditAmountProvider(): array
    {
        return [
            'small_amount' => [10.00],
            'medium_amount' => [100.50],
            'large_amount' => [1000.00],
            'very_large_amount' => [10000.99],
            'decimal_precision' => [123.45],
            'single_cent' => [0.01],
            'round_number' => [500.00],
        ];
    }

    /**
     * Data provider for debit amounts with initial balances
     * Tests various debit scenarios with sufficient balance
     * 
     * @return array<string, array<float, float>>
     */
    public static function debitAmountProvider(): array
    {
        return [
            'small_debit_from_large_balance' => [1000.00, 10.00],
            'medium_debit' => [500.00, 100.50],
            'large_debit' => [10000.00, 5000.00],
            'exact_balance_debit' => [100.00, 100.00],
            'decimal_precision_debit' => [200.00, 123.45],
            'single_cent_debit' => [10.00, 0.01],
            'almost_full_debit' => [1000.00, 999.99],
        ];
    }

    /**
     * Data provider for transaction types
     * Tests various transaction types for credit and debit operations
     * 
     * @return array<string, array<string>>
     */
    public static function transactionTypeProvider(): array
    {
        return [
            'admin_credit' => ['admin_credit'],
            'admin_debit' => ['admin_debit'],
            'top_up' => ['top_up'],
            'order_payment' => ['order_payment'],
            'refund' => ['refund'],
            'signup_bonus' => ['signup_bonus'],
            'withdrawal' => ['withdrawal'],
            'withdrawal_reversal' => ['withdrawal_reversal'],
        ];
    }

    /**
     * Data provider for admin users
     * Tests credit/debit operations with different admin roles
     * 
     * @return array<string, array<string>>
     */
    public static function adminRoleProvider(): array
    {
        return [
            'admin' => [UserRole::ADMIN->value],
            'super_admin' => [UserRole::SUPER_ADMIN->value],
        ];
    }

    /**
     * Property Test: Admin Credit Operation
     * 
     * **Validates: Requirements 3.1**
     * 
     * For any wallet and positive amount, when an admin credits the wallet with a reason,
     * the wallet balance should increase by that amount and a transaction record with type
     * "admin_credit" should be created with the admin's user ID and reason.
     * 
     * @test
     * @dataProvider creditAmountProvider
     */
    public function admin_credit_increases_balance_and_creates_transaction_for_any_amount(float $amount): void
    {
        // Arrange: Create user, wallet, and admin
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $initialBalance = $wallet->balance;
        $reason = 'Admin credit for testing - amount: ' . $amount;

        // Act: Admin credits the wallet
        $transaction = $this->walletService->creditBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            $reason,
            ['reason' => $reason],
            $admin
        );

        // Assert: Balance should increase by the amount
        $expectedBalance = $initialBalance + $amount;
        $this->assertEquals($expectedBalance, $wallet->fresh()->balance);

        // Assert: Transaction record should be created with correct details
        $this->assertInstanceOf(WalletTransaction::class, $transaction);
        $this->assertEquals(WalletTransaction::TYPE_ADMIN_CREDIT, $transaction->type);
        $this->assertEquals($amount, $transaction->amount);
        $this->assertEquals($initialBalance, $transaction->balance_before);
        $this->assertEquals($expectedBalance, $transaction->balance_after);
        $this->assertEquals($reason, $transaction->description);
        $this->assertEquals($admin->id, $transaction->created_by);
        $this->assertArrayHasKey('reason', $transaction->metadata);
        $this->assertEquals($reason, $transaction->metadata['reason']);

        // Assert: Transaction is persisted in database
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_ADMIN_CREDIT,
            'amount' => $amount,
            'balance_before' => $initialBalance,
            'balance_after' => $expectedBalance,
            'created_by' => $admin->id,
        ]);
    }

    /**
     * Property Test: Admin Credit Operation with Different Admin Roles
     * 
     * **Validates: Requirements 3.1, 3.5**
     * 
     * For any admin role (admin or super_admin), credit operations should work correctly
     * and record the admin's user ID.
     * 
     * @test
     * @dataProvider adminRoleProvider
     */
    public function admin_credit_works_for_any_admin_role(string $adminRole): void
    {
        // Arrange: Create user, wallet, and admin with specified role
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);
        $admin = User::factory()->create(['role' => $adminRole]);
        $amount = 50.00;
        $reason = 'Admin credit by ' . $adminRole;

        // Act: Admin credits the wallet
        $transaction = $this->walletService->creditBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            $reason,
            null,
            $admin
        );

        // Assert: Transaction should record the admin's user ID
        $this->assertEquals($admin->id, $transaction->created_by);
        $this->assertEquals(150.00, $wallet->fresh()->balance);
    }

    /**
     * Property Test: Admin Debit Operation
     * 
     * **Validates: Requirements 3.2**
     * 
     * For any wallet with sufficient balance and positive amount, when an admin debits
     * the wallet with a reason, the wallet balance should decrease by that amount and
     * a transaction record with type "admin_debit" should be created with the admin's
     * user ID and reason.
     * 
     * @test
     * @dataProvider debitAmountProvider
     */
    public function admin_debit_decreases_balance_and_creates_transaction_for_any_amount(
        float $initialBalance,
        float $debitAmount
    ): void {
        // Arrange: Create user, wallet with initial balance, and admin
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => $initialBalance,
        ]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $reason = 'Admin debit for testing - amount: ' . $debitAmount;

        // Act: Admin debits the wallet
        $transaction = $this->walletService->debitBalance(
            $wallet,
            $debitAmount,
            WalletTransaction::TYPE_ADMIN_DEBIT,
            $reason,
            ['reason' => $reason],
            $admin
        );

        // Assert: Balance should decrease by the amount
        $expectedBalance = $initialBalance - $debitAmount;
        $this->assertEqualsWithDelta($expectedBalance, $wallet->fresh()->balance, 0.001);

        // Assert: Transaction record should be created with correct details
        $this->assertInstanceOf(WalletTransaction::class, $transaction);
        $this->assertEquals(WalletTransaction::TYPE_ADMIN_DEBIT, $transaction->type);
        $this->assertEquals($debitAmount, $transaction->amount);
        $this->assertEquals($initialBalance, $transaction->balance_before);
        $this->assertEqualsWithDelta($expectedBalance, $transaction->balance_after, 0.001);
        $this->assertEquals($reason, $transaction->description);
        $this->assertEquals($admin->id, $transaction->created_by);
        $this->assertArrayHasKey('reason', $transaction->metadata);
        $this->assertEquals($reason, $transaction->metadata['reason']);

        // Assert: Transaction is persisted in database
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_ADMIN_DEBIT,
            'amount' => $debitAmount,
            'balance_before' => $initialBalance,
            'created_by' => $admin->id,
        ]);
    }

    /**
     * Property Test: Admin Debit Operation with Different Admin Roles
     * 
     * **Validates: Requirements 3.2, 3.5**
     * 
     * For any admin role (admin or super_admin), debit operations should work correctly
     * and record the admin's user ID.
     * 
     * @test
     * @dataProvider adminRoleProvider
     */
    public function admin_debit_works_for_any_admin_role(string $adminRole): void
    {
        // Arrange: Create user, wallet, and admin with specified role
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 200.00,
        ]);
        $admin = User::factory()->create(['role' => $adminRole]);
        $amount = 50.00;
        $reason = 'Admin debit by ' . $adminRole;

        // Act: Admin debits the wallet
        $transaction = $this->walletService->debitBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_DEBIT,
            $reason,
            null,
            $admin
        );

        // Assert: Transaction should record the admin's user ID
        $this->assertEquals($admin->id, $transaction->created_by);
        $this->assertEquals(150.00, $wallet->fresh()->balance);
    }

    /**
     * Property Test: Balance Change Creates Transaction (Credit)
     * 
     * **Validates: Requirements 6.1**
     * 
     * For any wallet balance change (credit), a corresponding transaction record should
     * be created with the correct type, amount, balance_before, and balance_after values.
     * 
     * @test
     * @dataProvider creditAmountProvider
     */
    public function credit_operation_creates_transaction_with_correct_balance_tracking(float $amount): void
    {
        // Arrange: Create user and wallet with known balance
        $user = User::factory()->create();
        $initialBalance = 250.75;
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => $initialBalance,
        ]);

        // Act: Credit the wallet
        $transaction = $this->walletService->creditBalance(
            $wallet,
            $amount,
            'test_credit',
            'Test credit operation'
        );

        // Assert: Transaction should track balance changes correctly
        $expectedBalance = $initialBalance + $amount;
        $this->assertEquals($initialBalance, $transaction->balance_before);
        $this->assertEquals($expectedBalance, $transaction->balance_after);
        $this->assertEquals($amount, $transaction->amount);
        
        // Assert: Wallet balance should match transaction's balance_after
        $this->assertEquals($transaction->balance_after, $wallet->fresh()->balance);
        
        // Assert: Balance change calculation is correct
        $calculatedChange = $transaction->balance_after - $transaction->balance_before;
        $this->assertEqualsWithDelta($amount, $calculatedChange, 0.001);
    }

    /**
     * Property Test: Balance Change Creates Transaction (Debit)
     * 
     * **Validates: Requirements 6.1**
     * 
     * For any wallet balance change (debit), a corresponding transaction record should
     * be created with the correct type, amount, balance_before, and balance_after values.
     * 
     * @test
     * @dataProvider debitAmountProvider
     */
    public function debit_operation_creates_transaction_with_correct_balance_tracking(
        float $initialBalance,
        float $debitAmount
    ): void {
        // Arrange: Create user and wallet with specified balance
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => $initialBalance,
        ]);

        // Act: Debit the wallet
        $transaction = $this->walletService->debitBalance(
            $wallet,
            $debitAmount,
            'test_debit',
            'Test debit operation'
        );

        // Assert: Transaction should track balance changes correctly
        $expectedBalance = $initialBalance - $debitAmount;
        $this->assertEquals($initialBalance, $transaction->balance_before);
        $this->assertEqualsWithDelta($expectedBalance, $transaction->balance_after, 0.001);
        $this->assertEquals($debitAmount, $transaction->amount);
        
        // Assert: Wallet balance should match transaction's balance_after
        $this->assertEquals($transaction->balance_after, $wallet->fresh()->balance);
        
        // Assert: Balance change calculation is correct (negative for debit)
        $calculatedChange = $transaction->balance_before - $transaction->balance_after;
        $this->assertEqualsWithDelta($debitAmount, $calculatedChange, 0.001);
    }

    /**
     * Property Test: Multiple Credits Accumulate Correctly
     * 
     * **Validates: Requirements 3.1, 6.1**
     * 
     * For any sequence of credit operations, the final balance should equal
     * the initial balance plus the sum of all credits, and each operation
     * should create a transaction record.
     * 
     * @test
     */
    public function multiple_credits_accumulate_correctly_with_transaction_records(): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $initialBalance = 100.00;
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => $initialBalance,
        ]);

        // Define multiple credit amounts
        $creditAmounts = [10.00, 25.50, 100.00, 0.01, 500.00];
        $expectedFinalBalance = $initialBalance + array_sum($creditAmounts);

        // Act: Perform multiple credit operations
        $transactions = [];
        foreach ($creditAmounts as $amount) {
            $transactions[] = $this->walletService->creditBalance(
                $wallet->fresh(),
                $amount,
                WalletTransaction::TYPE_ADMIN_CREDIT,
                'Credit amount: ' . $amount,
                null,
                $admin
            );
        }

        // Assert: Final balance should be correct
        $this->assertEquals($expectedFinalBalance, $wallet->fresh()->balance);

        // Assert: All transactions should be created
        $this->assertCount(count($creditAmounts), $transactions);

        // Assert: Each transaction should have correct balance tracking
        $runningBalance = $initialBalance;
        foreach ($transactions as $index => $transaction) {
            $amount = $creditAmounts[$index];
            $this->assertEquals($runningBalance, $transaction->balance_before);
            $runningBalance += $amount;
            $this->assertEquals($runningBalance, $transaction->balance_after);
        }
    }

    /**
     * Property Test: Multiple Debits Accumulate Correctly
     * 
     * **Validates: Requirements 3.2, 6.1**
     * 
     * For any sequence of debit operations with sufficient balance, the final balance
     * should equal the initial balance minus the sum of all debits, and each operation
     * should create a transaction record.
     * 
     * @test
     */
    public function multiple_debits_accumulate_correctly_with_transaction_records(): void
    {
        // Arrange: Create user and wallet with sufficient balance
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $initialBalance = 1000.00;
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => $initialBalance,
        ]);

        // Define multiple debit amounts (total less than initial balance)
        $debitAmounts = [10.00, 25.50, 100.00, 50.00, 200.00];
        $expectedFinalBalance = $initialBalance - array_sum($debitAmounts);

        // Act: Perform multiple debit operations
        $transactions = [];
        foreach ($debitAmounts as $amount) {
            $transactions[] = $this->walletService->debitBalance(
                $wallet->fresh(),
                $amount,
                WalletTransaction::TYPE_ADMIN_DEBIT,
                'Debit amount: ' . $amount,
                null,
                $admin
            );
        }

        // Assert: Final balance should be correct
        $this->assertEquals($expectedFinalBalance, $wallet->fresh()->balance);

        // Assert: All transactions should be created
        $this->assertCount(count($debitAmounts), $transactions);

        // Assert: Each transaction should have correct balance tracking
        $runningBalance = $initialBalance;
        foreach ($transactions as $index => $transaction) {
            $amount = $debitAmounts[$index];
            $this->assertEquals($runningBalance, $transaction->balance_before);
            $runningBalance -= $amount;
            $this->assertEquals($runningBalance, $transaction->balance_after);
        }
    }

    /**
     * Property Test: Mixed Credit and Debit Operations
     * 
     * **Validates: Requirements 3.1, 3.2, 6.1**
     * 
     * For any sequence of mixed credit and debit operations, the final balance
     * should equal the initial balance plus credits minus debits, and each operation
     * should create a transaction record with correct balance tracking.
     * 
     * @test
     */
    public function mixed_credit_and_debit_operations_maintain_correct_balance(): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $initialBalance = 500.00;
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => $initialBalance,
        ]);

        // Define mixed operations: [amount, isCredit]
        $operations = [
            [100.00, true],   // Credit 100
            [50.00, false],   // Debit 50
            [200.00, true],   // Credit 200
            [75.50, false],   // Debit 75.50
            [25.00, true],    // Credit 25
        ];

        // Calculate expected final balance
        $expectedFinalBalance = $initialBalance;
        foreach ($operations as [$amount, $isCredit]) {
            $expectedFinalBalance += $isCredit ? $amount : -$amount;
        }

        // Act: Perform mixed operations
        $transactions = [];
        foreach ($operations as [$amount, $isCredit]) {
            if ($isCredit) {
                $transactions[] = $this->walletService->creditBalance(
                    $wallet->fresh(),
                    $amount,
                    WalletTransaction::TYPE_ADMIN_CREDIT,
                    'Credit: ' . $amount,
                    null,
                    $admin
                );
            } else {
                $transactions[] = $this->walletService->debitBalance(
                    $wallet->fresh(),
                    $amount,
                    WalletTransaction::TYPE_ADMIN_DEBIT,
                    'Debit: ' . $amount,
                    null,
                    $admin
                );
            }
        }

        // Assert: Final balance should be correct
        $this->assertEquals($expectedFinalBalance, $wallet->fresh()->balance);

        // Assert: All transactions should be created
        $this->assertCount(count($operations), $transactions);

        // Assert: Each transaction should have correct balance tracking
        $runningBalance = $initialBalance;
        foreach ($transactions as $index => $transaction) {
            [$amount, $isCredit] = $operations[$index];
            $this->assertEquals($runningBalance, $transaction->balance_before);
            $runningBalance += $isCredit ? $amount : -$amount;
            $this->assertEquals($runningBalance, $transaction->balance_after);
        }
    }

    /**
     * Property Test: Transaction Type Variety
     * 
     * **Validates: Requirements 6.1, 6.3**
     * 
     * For any transaction type, credit and debit operations should create
     * transaction records with the correct type.
     * 
     * @test
     * @dataProvider transactionTypeProvider
     */
    public function operations_create_transactions_with_correct_type_for_any_type(string $type): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 500.00,
        ]);

        // Act: Perform credit operation with specified type
        $creditTransaction = $this->walletService->creditBalance(
            $wallet,
            100.00,
            $type,
            'Credit with type: ' . $type
        );

        // Assert: Credit transaction should have correct type
        $this->assertEquals($type, $creditTransaction->type);
        $this->assertDatabaseHas('wallet_transactions', [
            'id' => $creditTransaction->id,
            'type' => $type,
            'wallet_id' => $wallet->id,
        ]);

        // Act: Perform debit operation with specified type (if not a credit-only type)
        if (!in_array($type, ['signup_bonus', 'top_up', 'refund', 'withdrawal_reversal'])) {
            $debitTransaction = $this->walletService->debitBalance(
                $wallet->fresh(),
                50.00,
                $type,
                'Debit with type: ' . $type
            );

            // Assert: Debit transaction should have correct type
            $this->assertEquals($type, $debitTransaction->type);
            $this->assertDatabaseHas('wallet_transactions', [
                'id' => $debitTransaction->id,
                'type' => $type,
                'wallet_id' => $wallet->id,
            ]);
        }
    }

    /**
     * Property Test: Transaction Metadata Preservation
     * 
     * **Validates: Requirements 6.1, 6.2**
     * 
     * For any credit or debit operation with metadata, the transaction record
     * should preserve the metadata correctly.
     * 
     * @test
     */
    public function operations_preserve_metadata_in_transaction_records(): void
    {
        // Arrange: Create user, wallet, and admin
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 500.00,
        ]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);

        // Define various metadata structures
        $metadataVariations = [
            ['order_id' => 123, 'payment_method' => 'razorpay'],
            ['reason' => 'Customer support adjustment', 'ticket_id' => 'TICKET-456'],
            ['gateway_transaction_id' => 'txn_abc123', 'gateway' => 'stripe'],
            ['notes' => 'Promotional credit', 'campaign_id' => 789],
        ];

        // Act & Assert: Test each metadata variation
        foreach ($metadataVariations as $metadata) {
            // Credit operation
            $creditTransaction = $this->walletService->creditBalance(
                $wallet->fresh(),
                50.00,
                WalletTransaction::TYPE_ADMIN_CREDIT,
                'Credit with metadata',
                $metadata,
                $admin
            );

            $this->assertEquals($metadata, $creditTransaction->metadata);
            foreach ($metadata as $key => $value) {
                $this->assertArrayHasKey($key, $creditTransaction->metadata);
                $this->assertEquals($value, $creditTransaction->metadata[$key]);
            }

            // Debit operation
            $debitTransaction = $this->walletService->debitBalance(
                $wallet->fresh(),
                25.00,
                WalletTransaction::TYPE_ADMIN_DEBIT,
                'Debit with metadata',
                $metadata,
                $admin
            );

            $this->assertEquals($metadata, $debitTransaction->metadata);
            foreach ($metadata as $key => $value) {
                $this->assertArrayHasKey($key, $debitTransaction->metadata);
                $this->assertEquals($value, $debitTransaction->metadata[$key]);
            }
        }
    }

    /**
     * Property Test: Admin Debit with Negative Balance Permission
     * 
     * **Validates: Requirements 3.7**
     * 
     * For any admin debit operation with allowNegative flag set to true,
     * the operation should succeed even if it results in negative balance.
     * 
     * @test
     */
    public function admin_debit_allows_negative_balance_when_explicitly_authorized(): void
    {
        // Arrange: Create user, wallet with small balance, and admin
        $user = User::factory()->create();
        $initialBalance = 50.00;
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => $initialBalance,
        ]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $debitAmount = 150.00; // More than available balance

        // Act: Admin debits with allowNegative = true
        $transaction = $this->walletService->debitBalance(
            $wallet,
            $debitAmount,
            WalletTransaction::TYPE_ADMIN_DEBIT,
            'Admin debit with negative balance authorization',
            null,
            $admin,
            true // allowNegative = true
        );

        // Assert: Balance should be negative
        $expectedBalance = $initialBalance - $debitAmount;
        $this->assertEquals($expectedBalance, $wallet->fresh()->balance);
        $this->assertLessThan(0, $wallet->fresh()->balance);

        // Assert: Transaction should be created correctly
        $this->assertEquals($initialBalance, $transaction->balance_before);
        $this->assertEquals($expectedBalance, $transaction->balance_after);
        $this->assertEquals($admin->id, $transaction->created_by);
    }

    /**
     * Property Test: Transaction Timestamps
     * 
     * **Validates: Requirements 6.2**
     * 
     * For any credit or debit operation, the transaction record should have
     * valid timestamps (created_at and updated_at).
     * 
     * @test
     */
    public function operations_create_transactions_with_valid_timestamps(): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 500.00,
        ]);

        // Act: Perform credit operation
        $creditTransaction = $this->walletService->creditBalance(
            $wallet,
            100.00,
            'test_credit',
            'Test credit for timestamp validation'
        );

        // Assert: Credit transaction should have valid timestamps
        $this->assertNotNull($creditTransaction->created_at);
        $this->assertNotNull($creditTransaction->updated_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $creditTransaction->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $creditTransaction->updated_at);

        // Act: Perform debit operation
        $debitTransaction = $this->walletService->debitBalance(
            $wallet->fresh(),
            50.00,
            'test_debit',
            'Test debit for timestamp validation'
        );

        // Assert: Debit transaction should have valid timestamps
        $this->assertNotNull($debitTransaction->created_at);
        $this->assertNotNull($debitTransaction->updated_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $debitTransaction->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $debitTransaction->updated_at);
    }

    /**
     * Property Test: Operations Use Database Transactions
     * 
     * **Validates: Requirements 10.3**
     * 
     * For any credit or debit operation, the operation should be wrapped in a
     * database transaction to ensure atomicity.
     * 
     * @test
     */
    public function operations_are_atomic_and_use_database_transactions(): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        // Act: Perform credit operation
        $creditTransaction = $this->walletService->creditBalance(
            $wallet,
            50.00,
            'test_credit',
            'Test credit atomicity'
        );

        // Assert: Both wallet balance and transaction should be persisted
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 150.00,
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'id' => $creditTransaction->id,
            'wallet_id' => $wallet->id,
            'amount' => 50.00,
        ]);

        // Act: Perform debit operation
        $debitTransaction = $this->walletService->debitBalance(
            $wallet->fresh(),
            30.00,
            'test_debit',
            'Test debit atomicity'
        );

        // Assert: Both wallet balance and transaction should be persisted
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 120.00,
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'id' => $debitTransaction->id,
            'wallet_id' => $wallet->id,
            'amount' => 30.00,
        ]);
    }
}

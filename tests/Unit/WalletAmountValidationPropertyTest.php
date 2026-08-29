<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Exceptions\InvalidAmountException;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\RealtimeService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Tests for Wallet Amount Validation
 * 
 * Feature: wallet-system
 * Property 44: Amount Format Validation
 * Property 45: Amount Limit Validation
 * 
 * Validates: Requirements 11.3, 11.4
 */
class WalletAmountValidationPropertyTest extends TestCase
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
     * Data provider for invalid amounts with more than 2 decimal places
     * Tests that amounts with excessive decimal precision are rejected
     * 
     * @return array<string, array<float>>
     */
    public static function invalidDecimalPlacesProvider(): array
    {
        return [
            'three_decimal_places' => [10.123],
            'four_decimal_places' => [100.1234],
            'five_decimal_places' => [50.12345],
            'six_decimal_places' => [25.123456],
            'many_decimal_places' => [99.999999999],
            'small_amount_three_decimals' => [0.123],
            'large_amount_three_decimals' => [9999.999],
        ];
    }

    /**
     * Data provider for negative amounts
     * Tests that negative amounts are rejected
     * 
     * @return array<string, array<float>>
     */
    public static function negativeAmountProvider(): array
    {
        return [
            'negative_small' => [-0.01],
            'negative_medium' => [-10.00],
            'negative_large' => [-100.50],
            'negative_very_large' => [-10000.00],
            'negative_with_decimals' => [-123.45],
        ];
    }

    /**
     * Data provider for zero amount
     * Tests that zero amount is rejected
     * 
     * @return array<string, array<float>>
     */
    public static function zeroAmountProvider(): array
    {
        return [
            'exact_zero' => [0.00],
            'zero_with_decimals' => [0.0],
        ];
    }

    /**
     * Data provider for amounts exceeding the maximum limit
     * Tests that amounts over 1,000,000 are rejected
     * 
     * @return array<string, array<float>>
     */
    public static function excessiveAmountProvider(): array
    {
        return [
            'just_over_limit' => [1000000.01],
            'slightly_over_limit' => [1000001.00],
            'moderately_over_limit' => [1500000.00],
            'significantly_over_limit' => [5000000.00],
            'extremely_over_limit' => [10000000.00],
            'massive_amount' => [99999999.99],
        ];
    }

    /**
     * Data provider for valid amounts
     * Tests that properly formatted amounts within limits are accepted
     * 
     * @return array<string, array<float>>
     */
    public static function validAmountProvider(): array
    {
        return [
            'minimum_amount' => [0.01],
            'small_amount' => [1.00],
            'medium_amount' => [100.50],
            'large_amount' => [10000.00],
            'very_large_amount' => [500000.00],
            'at_maximum_limit' => [1000000.00],
            'two_decimal_places' => [123.45],
            'one_decimal_place' => [50.5],
            'no_decimal_places' => [100.0],
        ];
    }

    /**
     * Property Test: Amount Format Validation - Decimal Places (Credit)
     * 
     * **Validates: Requirements 11.3**
     * 
     * For any wallet credit operation, amounts with more than 2 decimal places
     * should be rejected with a validation error.
     * 
     * @test
     * @dataProvider invalidDecimalPlacesProvider
     */
    public function credit_rejects_amounts_with_more_than_two_decimal_places(float $amount): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);
        $initialBalance = $wallet->balance;

        // Act & Assert: Attempt to credit with invalid decimal places should throw exception
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must have maximum 2 decimal places');

        $this->walletService->creditBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            'Test credit with invalid decimal places'
        );

        // Assert: Balance should remain unchanged
        $this->assertEquals($initialBalance, $wallet->fresh()->balance);
    }

    /**
     * Property Test: Amount Format Validation - Decimal Places (Debit)
     * 
     * **Validates: Requirements 11.3**
     * 
     * For any wallet debit operation, amounts with more than 2 decimal places
     * should be rejected with a validation error.
     * 
     * @test
     * @dataProvider invalidDecimalPlacesProvider
     */
    public function debit_rejects_amounts_with_more_than_two_decimal_places(float $amount): void
    {
        // Arrange: Create user and wallet with sufficient balance
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 10000.00,
        ]);
        $initialBalance = $wallet->balance;

        // Act & Assert: Attempt to debit with invalid decimal places should throw exception
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must have maximum 2 decimal places');

        $this->walletService->debitBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_DEBIT,
            'Test debit with invalid decimal places'
        );

        // Assert: Balance should remain unchanged
        $this->assertEquals($initialBalance, $wallet->fresh()->balance);
    }

    /**
     * Property Test: Amount Format Validation - Negative Values (Credit)
     * 
     * **Validates: Requirements 11.3**
     * 
     * For any wallet credit operation, negative amounts should be rejected
     * with a validation error.
     * 
     * @test
     * @dataProvider negativeAmountProvider
     */
    public function credit_rejects_negative_amounts(float $amount): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);
        $initialBalance = $wallet->balance;

        // Act & Assert: Attempt to credit with negative amount should throw exception
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must be positive and greater than zero');

        $this->walletService->creditBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            'Test credit with negative amount'
        );

        // Assert: Balance should remain unchanged
        $this->assertEquals($initialBalance, $wallet->fresh()->balance);
    }

    /**
     * Property Test: Amount Format Validation - Negative Values (Debit)
     * 
     * **Validates: Requirements 11.3**
     * 
     * For any wallet debit operation, negative amounts should be rejected
     * with a validation error.
     * 
     * @test
     * @dataProvider negativeAmountProvider
     */
    public function debit_rejects_negative_amounts(float $amount): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 10000.00,
        ]);
        $initialBalance = $wallet->balance;

        // Act & Assert: Attempt to debit with negative amount should throw exception
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must be positive and greater than zero');

        $this->walletService->debitBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_DEBIT,
            'Test debit with negative amount'
        );

        // Assert: Balance should remain unchanged
        $this->assertEquals($initialBalance, $wallet->fresh()->balance);
    }

    /**
     * Property Test: Amount Format Validation - Zero Amount (Credit)
     * 
     * **Validates: Requirements 11.3**
     * 
     * For any wallet credit operation, zero amount should be rejected
     * with a validation error.
     * 
     * @test
     * @dataProvider zeroAmountProvider
     */
    public function credit_rejects_zero_amount(float $amount): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);
        $initialBalance = $wallet->balance;

        // Act & Assert: Attempt to credit with zero amount should throw exception
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must be positive and greater than zero');

        $this->walletService->creditBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            'Test credit with zero amount'
        );

        // Assert: Balance should remain unchanged
        $this->assertEquals($initialBalance, $wallet->fresh()->balance);
    }

    /**
     * Property Test: Amount Format Validation - Zero Amount (Debit)
     * 
     * **Validates: Requirements 11.3**
     * 
     * For any wallet debit operation, zero amount should be rejected
     * with a validation error.
     * 
     * @test
     * @dataProvider zeroAmountProvider
     */
    public function debit_rejects_zero_amount(float $amount): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 10000.00,
        ]);
        $initialBalance = $wallet->balance;

        // Act & Assert: Attempt to debit with zero amount should throw exception
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must be positive and greater than zero');

        $this->walletService->debitBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_DEBIT,
            'Test debit with zero amount'
        );

        // Assert: Balance should remain unchanged
        $this->assertEquals($initialBalance, $wallet->fresh()->balance);
    }

    /**
     * Property Test: Amount Limit Validation - Excessive Amounts (Credit)
     * 
     * **Validates: Requirements 11.4**
     * 
     * For any wallet credit operation with an amount exceeding the reasonable
     * limit (1,000,000), the operation should be rejected with a validation error.
     * 
     * @test
     * @dataProvider excessiveAmountProvider
     */
    public function credit_rejects_amounts_exceeding_maximum_limit(float $amount): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);
        $initialBalance = $wallet->balance;

        // Act & Assert: Attempt to credit with excessive amount should throw exception
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must not exceed 1000000');

        $this->walletService->creditBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            'Test credit with excessive amount'
        );

        // Assert: Balance should remain unchanged
        $this->assertEquals($initialBalance, $wallet->fresh()->balance);
    }

    /**
     * Property Test: Amount Limit Validation - Excessive Amounts (Debit)
     * 
     * **Validates: Requirements 11.4**
     * 
     * For any wallet debit operation with an amount exceeding the reasonable
     * limit (1,000,000), the operation should be rejected with a validation error.
     * 
     * @test
     * @dataProvider excessiveAmountProvider
     */
    public function debit_rejects_amounts_exceeding_maximum_limit(float $amount): void
    {
        // Arrange: Create user and wallet with very large balance
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 50000000.00, // Large enough to cover any debit
        ]);
        $initialBalance = $wallet->balance;

        // Act & Assert: Attempt to debit with excessive amount should throw exception
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must not exceed 1000000');

        $this->walletService->debitBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_DEBIT,
            'Test debit with excessive amount',
            null,
            null,
            true // Allow negative to bypass insufficient balance check
        );

        // Assert: Balance should remain unchanged
        $this->assertEquals($initialBalance, $wallet->fresh()->balance);
    }

    /**
     * Property Test: Valid Amounts Are Accepted (Credit)
     * 
     * **Validates: Requirements 11.3, 11.4**
     * 
     * For any wallet credit operation with a valid amount (positive, max 2 decimal
     * places, within limit), the operation should succeed.
     * 
     * @test
     * @dataProvider validAmountProvider
     */
    public function credit_accepts_valid_amounts(float $amount): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);
        $initialBalance = $wallet->balance;

        // Act: Credit with valid amount
        $transaction = $this->walletService->creditBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            'Test credit with valid amount'
        );

        // Assert: Operation should succeed
        $this->assertInstanceOf(WalletTransaction::class, $transaction);
        $this->assertEquals($amount, $transaction->amount);
        
        // Assert: Balance should increase by the amount
        $expectedBalance = $initialBalance + $amount;
        $this->assertEquals($expectedBalance, $wallet->fresh()->balance);
        
        // Assert: Transaction should be persisted
        $this->assertDatabaseHas('wallet_transactions', [
            'id' => $transaction->id,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
        ]);
    }

    /**
     * Property Test: Valid Amounts Are Accepted (Debit)
     * 
     * **Validates: Requirements 11.3, 11.4**
     * 
     * For any wallet debit operation with a valid amount (positive, max 2 decimal
     * places, within limit), the operation should succeed.
     * 
     * @test
     * @dataProvider validAmountProvider
     */
    public function debit_accepts_valid_amounts(float $amount): void
    {
        // Arrange: Create user and wallet with sufficient balance
        $user = User::factory()->create();
        $initialBalance = 2000000.00; // Large enough to cover any valid amount
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => $initialBalance,
        ]);

        // Act: Debit with valid amount
        $transaction = $this->walletService->debitBalance(
            $wallet,
            $amount,
            WalletTransaction::TYPE_ADMIN_DEBIT,
            'Test debit with valid amount'
        );

        // Assert: Operation should succeed
        $this->assertInstanceOf(WalletTransaction::class, $transaction);
        $this->assertEquals($amount, $transaction->amount);
        
        // Assert: Balance should decrease by the amount
        $expectedBalance = $initialBalance - $amount;
        $this->assertEquals($expectedBalance, $wallet->fresh()->balance);
        
        // Assert: Transaction should be persisted
        $this->assertDatabaseHas('wallet_transactions', [
            'id' => $transaction->id,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
        ]);
    }

    /**
     * Property Test: Amount Validation Prevents Transaction Creation
     * 
     * **Validates: Requirements 11.3, 11.4**
     * 
     * For any invalid amount (negative, excessive decimals, or over limit),
     * no transaction record should be created in the database.
     * 
     * @test
     */
    public function invalid_amounts_do_not_create_transaction_records(): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 10000.00,
        ]);

        // Get initial transaction count
        $initialTransactionCount = WalletTransaction::where('wallet_id', $wallet->id)->count();

        // Test various invalid amounts
        $invalidAmounts = [
            -10.00,           // Negative
            0.00,             // Zero
            10.123,           // Too many decimals
            1000000.01,       // Over limit
        ];

        foreach ($invalidAmounts as $invalidAmount) {
            try {
                $this->walletService->creditBalance(
                    $wallet->fresh(),
                    $invalidAmount,
                    WalletTransaction::TYPE_ADMIN_CREDIT,
                    'Test with invalid amount: ' . $invalidAmount
                );
                
                // If we reach here, the test should fail
                $this->fail('Expected InvalidAmountException was not thrown for amount: ' . $invalidAmount);
            } catch (InvalidAmountException $e) {
                // Expected exception - continue to next invalid amount
            }
        }

        // Assert: No new transactions should have been created
        $finalTransactionCount = WalletTransaction::where('wallet_id', $wallet->id)->count();
        $this->assertEquals($initialTransactionCount, $finalTransactionCount);
    }

    /**
     * Property Test: Amount Validation Is Consistent Across Transaction Types
     * 
     * **Validates: Requirements 11.3, 11.4**
     * 
     * For any transaction type, amount validation rules should be applied
     * consistently.
     * 
     * @test
     */
    public function amount_validation_is_consistent_across_transaction_types(): void
    {
        // Arrange: Create user, wallet, and admin
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 10000.00,
        ]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);

        // Test various transaction types with invalid amount
        $transactionTypes = [
            WalletTransaction::TYPE_ADMIN_CREDIT,
            WalletTransaction::TYPE_ADMIN_DEBIT,
            WalletTransaction::TYPE_TOP_UP,
            WalletTransaction::TYPE_ORDER_PAYMENT,
            WalletTransaction::TYPE_REFUND,
        ];

        $invalidAmount = 10.123; // Too many decimal places

        foreach ($transactionTypes as $type) {
            try {
                $this->walletService->creditBalance(
                    $wallet->fresh(),
                    $invalidAmount,
                    $type,
                    'Test ' . $type . ' with invalid amount',
                    null,
                    $admin
                );
                
                // If we reach here, the test should fail
                $this->fail('Expected InvalidAmountException was not thrown for type: ' . $type);
            } catch (InvalidAmountException $e) {
                // Expected exception - validation is consistent
                $this->assertStringContainsString('Amount must have maximum 2 decimal places', $e->getMessage());
            }
        }
    }

    /**
     * Property Test: Maximum Limit Boundary Testing
     * 
     * **Validates: Requirements 11.4**
     * 
     * For amounts at and around the maximum limit (1,000,000), validation
     * should correctly accept amounts at or below the limit and reject
     * amounts above the limit.
     * 
     * @test
     */
    public function maximum_limit_boundary_is_correctly_enforced(): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        // Test: Amount exactly at limit should be accepted
        $atLimit = 1000000.00;
        $transaction = $this->walletService->creditBalance(
            $wallet,
            $atLimit,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            'Test at maximum limit'
        );
        $this->assertEquals($atLimit, $transaction->amount);

        // Test: Amount just below limit should be accepted
        $belowLimit = 999999.99;
        $transaction = $this->walletService->creditBalance(
            $wallet->fresh(),
            $belowLimit,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            'Test below maximum limit'
        );
        $this->assertEquals($belowLimit, $transaction->amount);

        // Test: Amount just above limit should be rejected
        $aboveLimit = 1000000.01;
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must not exceed 1000000');
        
        $this->walletService->creditBalance(
            $wallet->fresh(),
            $aboveLimit,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            'Test above maximum limit'
        );
    }

    /**
     * Property Test: Decimal Precision Boundary Testing
     * 
     * **Validates: Requirements 11.3**
     * 
     * For amounts with different decimal precisions, validation should
     * correctly accept amounts with 0, 1, or 2 decimal places and reject
     * amounts with 3 or more decimal places.
     * 
     * @test
     */
    public function decimal_precision_boundary_is_correctly_enforced(): void
    {
        // Arrange: Create user and wallet
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        // Test: No decimal places should be accepted
        $noDecimals = 100.0;
        $transaction = $this->walletService->creditBalance(
            $wallet,
            $noDecimals,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            'Test no decimals'
        );
        $this->assertNotNull($transaction);

        // Test: One decimal place should be accepted
        $oneDecimal = 100.5;
        $transaction = $this->walletService->creditBalance(
            $wallet->fresh(),
            $oneDecimal,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            'Test one decimal'
        );
        $this->assertNotNull($transaction);

        // Test: Two decimal places should be accepted
        $twoDecimals = 100.55;
        $transaction = $this->walletService->creditBalance(
            $wallet->fresh(),
            $twoDecimals,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            'Test two decimals'
        );
        $this->assertNotNull($transaction);

        // Test: Three decimal places should be rejected
        $threeDecimals = 100.555;
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Amount must have maximum 2 decimal places');
        
        $this->walletService->creditBalance(
            $wallet->fresh(),
            $threeDecimals,
            WalletTransaction::TYPE_ADMIN_CREDIT,
            'Test three decimals'
        );
    }
}

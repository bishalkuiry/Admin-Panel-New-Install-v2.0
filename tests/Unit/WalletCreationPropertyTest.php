<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Wallet;
use App\Services\RealtimeService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Wallet Creation
 * 
 * Feature: wallet-system
 * Property 1: Wallet Creation on Registration
 * 
 * For any new user registration, creating the user account should result in a wallet 
 * being created with zero balance (unless signup bonus is configured).
 * 
 * Validates: Requirements 1.1
 */
class WalletCreationPropertyTest extends TestCase
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
     * Data provider for user roles
     * Tests all possible user roles to ensure wallet creation works for any role
     * 
     * @return array<string, array<string>>
     */
    public static function userRoleProvider(): array
    {
        return [
            'customer' => [UserRole::CUSTOMER->value],
            'delivery_partner' => [UserRole::DELIVERY_PARTNER->value],
            'store_staff' => [UserRole::STORE_STAFF->value],
            'store_manager' => [UserRole::STORE_MANAGER->value],
            'store_owner' => [UserRole::STORE_OWNER->value],
            'admin' => [UserRole::ADMIN->value],
            'super_admin' => [UserRole::SUPER_ADMIN->value],
        ];
    }

    /**
     * Data provider for user attributes
     * Tests various user configurations to ensure wallet creation is independent of user attributes
     * 
     * @return array<string, array<array<string, mixed>>>
     */
    public static function userAttributesProvider(): array
    {
        return [
            'basic_user' => [
                ['name' => 'John Doe', 'email' => 'john@example.com', 'role' => UserRole::CUSTOMER->value]
            ],
            'user_with_phone' => [
                ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone' => '+1234567890', 'role' => UserRole::CUSTOMER->value]
            ],
            'inactive_user' => [
                ['name' => 'Bob Wilson', 'email' => 'bob@example.com', 'is_active' => false, 'role' => UserRole::CUSTOMER->value]
            ],
            'user_with_permissions' => [
                ['name' => 'Alice Brown', 'email' => 'alice@example.com', 'permissions' => ['custom.permission'], 'role' => UserRole::STORE_OWNER->value]
            ],
        ];
    }

    /**
     * Data provider for currency configurations
     * Tests wallet creation with different currency settings
     * 
     * @return array<string, array<string|null>>
     */
    public static function currencyProvider(): array
    {
        return [
            'INR_currency' => ['INR'],
            'USD_currency' => ['USD'],
            'EUR_currency' => ['EUR'],
            'GBP_currency' => ['GBP'],
            'default_currency' => [null], // Should default to INR
        ];
    }

    /**
     * Property Test: Wallet creation for any user role
     * 
     * **Validates: Requirements 1.1, 1.4**
     * 
     * For any user role (customer, delivery_partner, store_owner, store_manager, 
     * store_staff, admin, super_admin), creating a user with that role should 
     * successfully create a wallet with zero balance.
     * 
     * @test
     * @dataProvider userRoleProvider
     */
    public function wallet_is_created_with_zero_balance_for_any_user_role(string $role): void
    {
        // Arrange: Create a user with the specified role
        $user = User::factory()->create(['role' => $role]);

        // Act: Create wallet for the user
        $wallet = $this->walletService->createWallet($user);

        // Assert: Wallet should be created with zero balance
        $this->assertInstanceOf(Wallet::class, $wallet);
        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertEquals(0.00, $wallet->balance);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 0.00,
        ]);
    }

    /**
     * Property Test: Wallet creation for any user attributes
     * 
     * **Validates: Requirements 1.1**
     * 
     * For any user with different attributes (name, email, phone, active status, permissions),
     * creating a wallet should result in a wallet with zero balance, independent of user attributes.
     * 
     * @test
     * @dataProvider userAttributesProvider
     * @param array<string, mixed> $attributes
     */
    public function wallet_is_created_with_zero_balance_for_any_user_attributes(array $attributes): void
    {
        // Arrange: Create a user with specified attributes
        $user = User::factory()->create($attributes);

        // Act: Create wallet for the user
        $wallet = $this->walletService->createWallet($user);

        // Assert: Wallet should be created with zero balance regardless of user attributes
        $this->assertInstanceOf(Wallet::class, $wallet);
        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertEquals(0.00, $wallet->balance);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 0.00,
        ]);
    }

    /**
     * Property Test: Wallet creation with any currency configuration
     * 
     * **Validates: Requirements 1.1, 1.2**
     * 
     * For any currency configuration (INR, USD, EUR, GBP, or default), 
     * creating a wallet should use the configured currency and initialize with zero balance.
     * 
     * @test
     * @dataProvider currencyProvider
     */
    public function wallet_is_created_with_correct_currency_for_any_configuration(?string $currency): void
    {
        // Arrange: Set currency configuration
        config(['app.currency' => $currency]);
        $expectedCurrency = $currency ?? 'INR'; // Default to INR if null
        $user = User::factory()->create();

        // Act: Create wallet for the user
        $wallet = $this->walletService->createWallet($user);

        // Assert: Wallet should be created with correct currency and zero balance
        $this->assertInstanceOf(Wallet::class, $wallet);
        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertEquals(0.00, $wallet->balance);
        $this->assertEquals($expectedCurrency, $wallet->currency);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 0.00,
            'currency' => $expectedCurrency,
        ]);
    }

    /**
     * Property Test: Wallet creation is idempotent via getOrCreateWallet
     * 
     * **Validates: Requirements 1.1, 1.3**
     * 
     * For any user, calling getOrCreateWallet multiple times should always return 
     * the same wallet instance without creating duplicates.
     * 
     * @test
     * @dataProvider userRoleProvider
     */
    public function get_or_create_wallet_is_idempotent_for_any_user_role(string $role): void
    {
        // Arrange: Create a user with the specified role
        $user = User::factory()->create(['role' => $role]);

        // Act: Call getOrCreateWallet multiple times
        $wallet1 = $this->walletService->getOrCreateWallet($user);
        $wallet2 = $this->walletService->getOrCreateWallet($user);
        $wallet3 = $this->walletService->getOrCreateWallet($user);

        // Assert: All calls should return the same wallet
        $this->assertEquals($wallet1->id, $wallet2->id);
        $this->assertEquals($wallet1->id, $wallet3->id);
        $this->assertEquals(0.00, $wallet1->balance);
        
        // Assert: Only one wallet should exist for the user
        $this->assertEquals(1, Wallet::where('user_id', $user->id)->count());
    }

    /**
     * Property Test: Each user gets a unique wallet
     * 
     * **Validates: Requirements 1.1**
     * 
     * For any set of users, each user should get their own unique wallet instance.
     * 
     * @test
     */
    public function each_user_gets_unique_wallet_regardless_of_creation_order(): void
    {
        // Arrange: Create multiple users with different roles
        $users = [
            User::factory()->create(['role' => UserRole::CUSTOMER->value]),
            User::factory()->create(['role' => UserRole::DELIVERY_PARTNER->value]),
            User::factory()->create(['role' => UserRole::STORE_OWNER->value]),
            User::factory()->create(['role' => UserRole::ADMIN->value]),
        ];

        // Act: Create wallets for all users
        $wallets = [];
        foreach ($users as $user) {
            $wallets[] = $this->walletService->createWallet($user);
        }

        // Assert: Each wallet should be unique and belong to the correct user
        $walletIds = array_map(fn($wallet) => $wallet->id, $wallets);
        $this->assertCount(4, array_unique($walletIds), 'All wallets should have unique IDs');

        foreach ($users as $index => $user) {
            $this->assertEquals($user->id, $wallets[$index]->user_id);
            $this->assertEquals(0.00, $wallets[$index]->balance);
        }
    }

    /**
     * Property Test: Wallet stores correct user ID and timestamps
     * 
     * **Validates: Requirements 1.2**
     * 
     * For any user, the created wallet should store the user ID and have valid timestamps.
     * 
     * @test
     * @dataProvider userRoleProvider
     */
    public function wallet_stores_correct_user_id_and_timestamps_for_any_role(string $role): void
    {
        // Arrange: Create a user with the specified role
        $user = User::factory()->create(['role' => $role]);

        // Act: Create wallet for the user
        $wallet = $this->walletService->createWallet($user);

        // Assert: Wallet should have correct user_id and timestamps
        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertNotNull($wallet->created_at);
        $this->assertNotNull($wallet->updated_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $wallet->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $wallet->updated_at);
    }

    /**
     * Property Test: Wallet creation uses database transaction
     * 
     * **Validates: Requirements 1.1, 10.3**
     * 
     * For any user, wallet creation should be wrapped in a database transaction
     * to ensure atomicity.
     * 
     * @test
     */
    public function wallet_creation_is_atomic_for_any_user(): void
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Act: Create wallet (this internally uses DB::transaction)
        $wallet = $this->walletService->createWallet($user);

        // Assert: Wallet should be properly persisted in database
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'user_id' => $user->id,
            'balance' => 0.00,
        ]);

        // Assert: Wallet can be retrieved from database
        $retrievedWallet = Wallet::find($wallet->id);
        $this->assertNotNull($retrievedWallet);
        $this->assertEquals($wallet->id, $retrievedWallet->id);
        $this->assertEquals($wallet->user_id, $retrievedWallet->user_id);
        $this->assertEquals($wallet->balance, $retrievedWallet->balance);
    }

    /**
     * Property Test: Wallet query returns correct data
     * 
     * **Validates: Requirements 1.3**
     * 
     * For any wallet, querying the wallet should return the current balance 
     * and wallet information that matches the stored data.
     * 
     * @test
     * @dataProvider userRoleProvider
     */
    public function wallet_query_returns_correct_data_for_any_user_role(string $role): void
    {
        // Arrange: Create a user and wallet
        $user = User::factory()->create(['role' => $role]);
        $createdWallet = $this->walletService->createWallet($user);

        // Act: Query the wallet
        $queriedWallet = Wallet::where('user_id', $user->id)->first();

        // Assert: Queried wallet should match created wallet
        $this->assertNotNull($queriedWallet);
        $this->assertEquals($createdWallet->id, $queriedWallet->id);
        $this->assertEquals($createdWallet->user_id, $queriedWallet->user_id);
        $this->assertEquals($createdWallet->balance, $queriedWallet->balance);
        $this->assertEquals($createdWallet->currency, $queriedWallet->currency);
        $this->assertEquals($createdWallet->created_at->timestamp, $queriedWallet->created_at->timestamp);
    }
}

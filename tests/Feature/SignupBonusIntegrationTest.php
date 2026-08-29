<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SignupBonusIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed wallet settings
        Setting::create([
            'group' => 'wallet',
            'key' => 'wallet_signup_bonus_enabled',
            'value' => '1',
            'type' => 'boolean',
        ]);
        
        Setting::create([
            'group' => 'wallet',
            'key' => 'wallet_signup_bonus_amount',
            'value' => '100.00',
            'type' => 'decimal',
        ]);
    }

    /** @test */
    public function customer_registration_creates_wallet_and_applies_signup_bonus()
    {
        // Act: Register a new customer
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
        ]);

        // Assert: Registration successful
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'token',
                'token_type',
            ],
        ]);

        // Assert: User was created
        $user = User::where('email', 'customer@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('customer', $user->role->value);

        // Assert: Wallet was created
        $wallet = Wallet::where('user_id', $user->id)->first();
        $this->assertNotNull($wallet);

        // Assert: Signup bonus was applied
        $this->assertEquals(100.00, $wallet->balance);

        // Assert: Transaction record exists
        $transaction = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('type', WalletTransaction::TYPE_SIGNUP_BONUS)
            ->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(100.00, $transaction->amount);
        $this->assertEquals(0.00, $transaction->balance_before);
        $this->assertEquals(100.00, $transaction->balance_after);
    }

    /** @test */
    public function seller_registration_creates_wallet_but_no_signup_bonus()
    {
        // Enable multi-vendor mode (check if already exists first)
        if (!Setting::where('key', 'vendor_mode')->exists()) {
            Setting::create([
                'group' => 'general',
                'key' => 'vendor_mode',
                'value' => 'multi',
                'type' => 'string',
            ]);
        } else {
            Setting::where('key', 'vendor_mode')->update(['value' => 'multi']);
        }
        
        if (!Setting::where('key', 'seller_registration')->exists()) {
            Setting::create([
                'group' => 'general',
                'key' => 'seller_registration',
                'value' => '1',
                'type' => 'boolean',
            ]);
        } else {
            Setting::where('key', 'seller_registration')->update(['value' => '1']);
        }

        // Act: Register a new seller
        $response = $this->postJson('/api/v1/seller/auth/register', [
            'name' => 'Test Seller',
            'email' => 'seller@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'store_name' => 'Test Store',
            'business_type' => 'grocery',
            'address' => '123 Test St',
            'city' => 'Test City',
            'postal_code' => '12345',
        ]);

        // Assert: Registration successful
        $response->assertStatus(201);

        // Assert: User was created
        $user = User::where('email', 'seller@test.com')->first();
        $this->assertNotNull($user);

        // Assert: Wallet was created
        $wallet = Wallet::where('user_id', $user->id)->first();
        $this->assertNotNull($wallet);

        // Assert: NO signup bonus was applied (sellers don't get bonus)
        $this->assertEquals(0.00, $wallet->balance);

        // Assert: No signup bonus transaction exists
        $transaction = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('type', WalletTransaction::TYPE_SIGNUP_BONUS)
            ->first();
        $this->assertNull($transaction);
    }

    /** @test */
    public function signup_bonus_not_applied_when_disabled()
    {
        // Disable signup bonus
        Setting::where('key', 'wallet_signup_bonus_enabled')->update(['value' => '0']);

        // Act: Register a new customer
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Assert: Registration successful
        $response->assertStatus(201);

        // Assert: User and wallet created
        $user = User::where('email', 'customer@test.com')->first();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $this->assertNotNull($wallet);

        // Assert: NO signup bonus was applied (feature disabled)
        $this->assertEquals(0.00, $wallet->balance);

        // Assert: No signup bonus transaction exists
        $transaction = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('type', WalletTransaction::TYPE_SIGNUP_BONUS)
            ->first();
        $this->assertNull($transaction);
    }

    /** @test */
    public function admin_created_customer_gets_signup_bonus()
    {
        // Create admin user
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN->value,
        ]);

        // Act: Admin creates a customer
        $response = $this->actingAs($admin)->postJson('/admin/users', [
            'name' => 'Admin Created Customer',
            'email' => 'admincustomer@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
            'is_active' => true,
        ]);

        // Assert: User was created
        $user = User::where('email', 'admincustomer@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('customer', $user->role->value);

        // Assert: Wallet was created
        $wallet = Wallet::where('user_id', $user->id)->first();
        $this->assertNotNull($wallet);

        // Assert: Signup bonus was applied
        $this->assertEquals(100.00, $wallet->balance);

        // Assert: Transaction record exists
        $transaction = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('type', WalletTransaction::TYPE_SIGNUP_BONUS)
            ->first();
        $this->assertNotNull($transaction);
    }

    /** @test */
    public function admin_created_non_customer_does_not_get_signup_bonus()
    {
        // Create admin user
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN->value,
        ]);

        // Act: Admin creates a delivery partner
        $response = $this->actingAs($admin)->postJson('/admin/users', [
            'name' => 'Delivery Partner',
            'email' => 'delivery@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'delivery_partner',
            'is_active' => true,
        ]);

        // Assert: User was created
        $user = User::where('email', 'delivery@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('delivery_partner', $user->role->value);

        // Assert: Wallet was created
        $wallet = Wallet::where('user_id', $user->id)->first();
        $this->assertNotNull($wallet);

        // Assert: NO signup bonus (not a customer)
        $this->assertEquals(0.00, $wallet->balance);
    }
}

<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTransactionModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_all_required_transaction_type_constants()
    {
        $this->assertEquals('credit', WalletTransaction::TYPE_CREDIT);
        $this->assertEquals('debit', WalletTransaction::TYPE_DEBIT);
        $this->assertEquals('order_payment', WalletTransaction::TYPE_ORDER_PAYMENT);
        $this->assertEquals('top_up', WalletTransaction::TYPE_TOP_UP);
        $this->assertEquals('signup_bonus', WalletTransaction::TYPE_SIGNUP_BONUS);
        $this->assertEquals('admin_credit', WalletTransaction::TYPE_ADMIN_CREDIT);
        $this->assertEquals('admin_debit', WalletTransaction::TYPE_ADMIN_DEBIT);
        $this->assertEquals('refund', WalletTransaction::TYPE_REFUND);
        $this->assertEquals('withdrawal', WalletTransaction::TYPE_WITHDRAWAL);
        $this->assertEquals('withdrawal_reversal', WalletTransaction::TYPE_WITHDRAWAL_REVERSAL);
    }

    /** @test */
    public function it_belongs_to_a_wallet()
    {
        $wallet = Wallet::factory()->create();
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
        ]);

        $this->assertInstanceOf(Wallet::class, $transaction->wallet);
        $this->assertEquals($wallet->id, $transaction->wallet->id);
    }

    /** @test */
    public function it_has_polymorphic_reference_relationship()
    {
        $wallet = Wallet::factory()->create();
        $user = User::factory()->create();
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'reference_type' => User::class,
            'reference_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $transaction->reference);
        $this->assertEquals($user->id, $transaction->reference->id);
    }

    /** @test */
    public function it_can_have_null_reference()
    {
        $wallet = Wallet::factory()->create();
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'reference_type' => null,
            'reference_id' => null,
        ]);

        $this->assertNull($transaction->reference_type);
        $this->assertNull($transaction->reference_id);
        $this->assertNull($transaction->reference);
    }

    /** @test */
    public function it_belongs_to_created_by_user()
    {
        $wallet = Wallet::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'created_by' => $admin->id,
        ]);

        $this->assertInstanceOf(User::class, $transaction->createdBy);
        $this->assertEquals($admin->id, $transaction->createdBy->id);
    }

    /** @test */
    public function it_can_have_null_created_by()
    {
        $wallet = Wallet::factory()->create();
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'created_by' => null,
        ]);

        $this->assertNull($transaction->created_by);
        $this->assertNull($transaction->createdBy);
    }

    /** @test */
    public function it_casts_amount_to_decimal_with_two_places()
    {
        $wallet = Wallet::factory()->create();
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'amount' => 123.456,
        ]);

        $this->assertEquals('123.46', $transaction->amount);
    }

    /** @test */
    public function it_casts_balance_before_to_decimal_with_two_places()
    {
        $wallet = Wallet::factory()->create();
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'balance_before' => 999.999,
        ]);

        $this->assertEquals('1000.00', $transaction->balance_before);
    }

    /** @test */
    public function it_casts_balance_after_to_decimal_with_two_places()
    {
        $wallet = Wallet::factory()->create();
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'balance_after' => 555.555,
        ]);

        $this->assertEquals('555.56', $transaction->balance_after);
    }

    /** @test */
    public function it_casts_metadata_to_array()
    {
        $wallet = Wallet::factory()->create();
        $metadata = [
            'order_id' => 123,
            'gateway' => 'razorpay',
            'transaction_id' => 'txn_abc123',
        ];
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'metadata' => $metadata,
        ]);

        $this->assertIsArray($transaction->metadata);
        $this->assertEquals($metadata, $transaction->metadata);
    }

    /** @test */
    public function it_can_store_null_metadata()
    {
        $wallet = Wallet::factory()->create();
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'metadata' => null,
        ]);

        $this->assertNull($transaction->metadata);
    }

    /** @test */
    public function it_stores_all_required_transaction_fields()
    {
        $wallet = Wallet::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_ADMIN_CREDIT,
            'amount' => 100.50,
            'balance_before' => 50.00,
            'balance_after' => 150.50,
            'description' => 'Admin credit for testing',
            'reference_type' => null,
            'reference_id' => null,
            'metadata' => ['reason' => 'test'],
            'created_by' => $admin->id,
        ]);

        $this->assertEquals($wallet->id, $transaction->wallet_id);
        $this->assertEquals(WalletTransaction::TYPE_ADMIN_CREDIT, $transaction->type);
        $this->assertEquals('100.50', $transaction->amount);
        $this->assertEquals('50.00', $transaction->balance_before);
        $this->assertEquals('150.50', $transaction->balance_after);
        $this->assertEquals('Admin credit for testing', $transaction->description);
        $this->assertEquals(['reason' => 'test'], $transaction->metadata);
        $this->assertEquals($admin->id, $transaction->created_by);
    }

    /** @test */
    public function it_can_create_transaction_with_each_type()
    {
        $wallet = Wallet::factory()->create();
        
        $types = [
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
        ];

        foreach ($types as $type) {
            $transaction = WalletTransaction::factory()->create([
                'wallet_id' => $wallet->id,
                'type' => $type,
            ]);

            $this->assertEquals($type, $transaction->type);
        }

        $this->assertCount(10, $wallet->transactions);
    }
}

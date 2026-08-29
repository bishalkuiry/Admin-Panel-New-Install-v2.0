<?php

namespace Tests\Unit;

use App\Exceptions\InsufficientBalanceException;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $wallet->user);
        $this->assertEquals($user->id, $wallet->user->id);
    }

    /** @test */
    public function it_has_many_transactions()
    {
        $wallet = Wallet::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $wallet->transactions);
    }

    /** @test */
    public function it_casts_balance_to_decimal_with_two_places()
    {
        $wallet = Wallet::factory()->create(['balance' => 100.50]);

        $this->assertEquals('100.50', $wallet->balance);
    }

    /** @test */
    public function it_can_credit_balance()
    {
        $wallet = Wallet::factory()->create(['balance' => 100.00]);

        $transaction = $wallet->credit(50.00, WalletTransaction::TYPE_CREDIT, 'Test credit');

        $this->assertEquals(150.00, $wallet->fresh()->balance);
        $this->assertInstanceOf(WalletTransaction::class, $transaction);
        $this->assertEquals(100.00, $transaction->balance_before);
        $this->assertEquals(150.00, $transaction->balance_after);
        $this->assertEquals(50.00, $transaction->amount);
        $this->assertEquals(WalletTransaction::TYPE_CREDIT, $transaction->type);
        $this->assertEquals('Test credit', $transaction->description);
    }

    /** @test */
    public function it_can_debit_balance_when_sufficient()
    {
        $wallet = Wallet::factory()->create(['balance' => 100.00]);

        $transaction = $wallet->debit(30.00, WalletTransaction::TYPE_DEBIT, 'Test debit');

        $this->assertEquals(70.00, $wallet->fresh()->balance);
        $this->assertInstanceOf(WalletTransaction::class, $transaction);
        $this->assertEquals(100.00, $transaction->balance_before);
        $this->assertEquals(70.00, $transaction->balance_after);
        $this->assertEquals(30.00, $transaction->amount);
        $this->assertEquals(WalletTransaction::TYPE_DEBIT, $transaction->type);
        $this->assertEquals('Test debit', $transaction->description);
    }

    /** @test */
    public function it_throws_exception_when_debiting_with_insufficient_balance()
    {
        $wallet = Wallet::factory()->create(['balance' => 50.00]);

        $this->expectException(InsufficientBalanceException::class);
        $this->expectExceptionMessage('Insufficient wallet balance');

        $wallet->debit(100.00, WalletTransaction::TYPE_DEBIT, 'Test debit');

        // Balance should remain unchanged
        $this->assertEquals(50.00, $wallet->fresh()->balance);
    }

    /** @test */
    public function it_can_check_if_has_sufficient_balance()
    {
        $wallet = Wallet::factory()->create(['balance' => 100.00]);

        $this->assertTrue($wallet->hasBalance(50.00));
        $this->assertTrue($wallet->hasBalance(100.00));
        $this->assertFalse($wallet->hasBalance(150.00));
    }

    /** @test */
    public function credit_stores_metadata_when_provided()
    {
        $wallet = Wallet::factory()->create(['balance' => 100.00]);
        $metadata = ['order_id' => 123, 'gateway' => 'razorpay'];

        $transaction = $wallet->credit(50.00, WalletTransaction::TYPE_TOP_UP, 'Top up', $metadata);

        $this->assertEquals($metadata, $transaction->metadata);
    }

    /** @test */
    public function debit_stores_metadata_when_provided()
    {
        $wallet = Wallet::factory()->create(['balance' => 100.00]);
        $metadata = ['order_id' => 456, 'reason' => 'order payment'];

        $transaction = $wallet->debit(30.00, WalletTransaction::TYPE_ORDER_PAYMENT, 'Order payment', $metadata);

        $this->assertEquals($metadata, $transaction->metadata);
    }
}

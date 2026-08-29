<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletWithdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletWithdrawalModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_status_constants()
    {
        $this->assertEquals('pending', WalletWithdrawal::STATUS_PENDING);
        $this->assertEquals('approved', WalletWithdrawal::STATUS_APPROVED);
        $this->assertEquals('rejected', WalletWithdrawal::STATUS_REJECTED);
    }

    /** @test */
    public function it_belongs_to_a_wallet()
    {
        $wallet = Wallet::factory()->create();
        $withdrawal = WalletWithdrawal::factory()->create([
            'wallet_id' => $wallet->id,
        ]);

        $this->assertInstanceOf(Wallet::class, $withdrawal->wallet);
        $this->assertEquals($wallet->id, $withdrawal->wallet->id);
    }

    /** @test */
    public function it_can_access_user_through_wallet()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $withdrawal = WalletWithdrawal::factory()->create([
            'wallet_id' => $wallet->id,
        ]);

        $this->assertInstanceOf(User::class, $withdrawal->user);
        $this->assertEquals($user->id, $withdrawal->user->id);
    }

    /** @test */
    public function it_belongs_to_processed_by_user()
    {
        $admin = User::factory()->create();
        $withdrawal = WalletWithdrawal::factory()->create([
            'processed_by' => $admin->id,
            'status' => WalletWithdrawal::STATUS_APPROVED,
        ]);

        $this->assertInstanceOf(User::class, $withdrawal->processedBy);
        $this->assertEquals($admin->id, $withdrawal->processedBy->id);
    }

    /** @test */
    public function it_can_be_created_with_pending_status()
    {
        $withdrawal = WalletWithdrawal::factory()->pending()->create();

        $this->assertEquals(WalletWithdrawal::STATUS_PENDING, $withdrawal->status);
        $this->assertNull($withdrawal->processed_by);
        $this->assertNull($withdrawal->processed_at);
    }

    /** @test */
    public function it_can_be_created_with_approved_status()
    {
        $withdrawal = WalletWithdrawal::factory()->approved()->create();

        $this->assertEquals(WalletWithdrawal::STATUS_APPROVED, $withdrawal->status);
        $this->assertNotNull($withdrawal->processed_by);
        $this->assertNotNull($withdrawal->processed_at);
    }

    /** @test */
    public function it_can_be_created_with_rejected_status()
    {
        $withdrawal = WalletWithdrawal::factory()->rejected()->create();

        $this->assertEquals(WalletWithdrawal::STATUS_REJECTED, $withdrawal->status);
        $this->assertNotNull($withdrawal->processed_by);
        $this->assertNotNull($withdrawal->processed_at);
        $this->assertNotNull($withdrawal->notes);
    }

    /** @test */
    public function it_casts_amount_to_decimal()
    {
        $withdrawal = WalletWithdrawal::factory()->create([
            'amount' => 100.50,
        ]);

        $this->assertIsString($withdrawal->amount);
        $this->assertEquals('100.50', $withdrawal->amount);
    }

    /** @test */
    public function it_casts_processed_at_to_datetime()
    {
        $withdrawal = WalletWithdrawal::factory()->approved()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $withdrawal->processed_at);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'wallet_id',
            'amount',
            'bank_name',
            'account_number',
            'account_holder_name',
            'ifsc_code',
            'status',
            'notes',
            'processed_by',
            'processed_at',
        ];

        $withdrawal = new WalletWithdrawal();
        $this->assertEquals($fillable, $withdrawal->getFillable());
    }

    /** @test */
    public function it_stores_bank_details_correctly()
    {
        $withdrawal = WalletWithdrawal::factory()->create([
            'bank_name' => 'Test Bank',
            'account_number' => '1234567890',
            'account_holder_name' => 'John Doe',
            'ifsc_code' => 'TEST0001234',
        ]);

        $this->assertEquals('Test Bank', $withdrawal->bank_name);
        $this->assertEquals('1234567890', $withdrawal->account_number);
        $this->assertEquals('John Doe', $withdrawal->account_holder_name);
        $this->assertEquals('TEST0001234', $withdrawal->ifsc_code);
    }

    /** @test */
    public function ifsc_code_can_be_null()
    {
        $withdrawal = WalletWithdrawal::factory()->create([
            'ifsc_code' => null,
        ]);

        $this->assertNull($withdrawal->ifsc_code);
    }
}

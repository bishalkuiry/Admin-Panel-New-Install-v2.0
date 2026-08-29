<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WalletLockTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_lock_wallet_for_update()
    {
        $wallet = Wallet::factory()->create(['balance' => 100.00]);

        // Start a transaction to test locking
        DB::beginTransaction();
        
        try {
            // Lock the wallet for update
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            
            $this->assertNotNull($lockedWallet);
            $this->assertEquals($wallet->id, $lockedWallet->id);
            $this->assertEquals(100.00, $lockedWallet->balance);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lockForUpdate_method_returns_locked_instance()
    {
        $wallet = Wallet::factory()->create(['balance' => 100.00]);

        DB::beginTransaction();
        
        try {
            // Use the model's lockForUpdate method
            $lockedWallet = $wallet->lockForUpdate();
            
            $this->assertInstanceOf(Wallet::class, $lockedWallet);
            $this->assertEquals($wallet->id, $lockedWallet->id);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

<?php

namespace App\Services;

use App\Models\User;
use App\Models\Referral;
use App\Models\Setting;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralService
{
    public function __construct(
        private WalletService $walletService,
        private RealtimeService $realtimeService
    ) {}

    /**
     * Generate a unique referral code for a user
     */
    public function generateReferralCode(User $user): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        $user->update(['referral_code' => $code]);
        return $code;
    }

    /**
     * Apply a referral code to a new user
     */
    public function applyReferralCode(User $newUser, string $code): ?Referral
    {
        $enabled = Setting::get('referral_enabled', '0');
        if ($enabled !== '1' && $enabled !== true && $enabled !== 1) {
            return null;
        }

        $referrer = User::where('referral_code', $code)->first();

        if (!$referrer || $referrer->id === $newUser->id) {
            return null;
        }

        // Get reward amounts from settings
        $referrerReward = (float) Setting::get('referral_referrer_reward', 50);
        $refereeReward = (float) Setting::get('referral_referee_reward', 25);
        $freeDeliveries = (int) Setting::get('referral_free_deliveries', 3);

        return Referral::create([
            'referrer_id' => $referrer->id,
            'referee_id' => $newUser->id,
            'referral_code' => $code,
            'status' => Referral::STATUS_PENDING,
            'referrer_reward_amount' => $referrerReward,
            'referee_reward_amount' => $refereeReward,
            'referee_free_deliveries' => $freeDeliveries,
        ]);
    }

    /**
     * Process referral rewards after referee completes first order
     */
    public function processReferralRewards(User $referee): void
    {
        // Use a transaction with a row lock to prevent double-payout on concurrent requests
        DB::transaction(function () use ($referee) {
            $referral = Referral::where('referee_id', $referee->id)
                ->where('status', Referral::STATUS_PENDING)
                ->lockForUpdate()
                ->first();

            if (!$referral) {
                return;
            }

            $referral->update(['status' => Referral::STATUS_COMPLETED]);

            // 1. Pay Referrer
            if ($referral->referrer_reward_amount > 0) {
                $wallet = $this->walletService->getOrCreateWallet($referral->referrer);
                $this->walletService->creditBalance(
                    $wallet,
                    $referral->referrer_reward_amount,
                    WalletTransaction::TYPE_REFERRAL_REWARD,
                    "Referral reward for inviting {$referral->referee->name}",
                    ['referee_id' => $referral->referee_id]
                );
                
                $referral->update([
                    'referrer_reward_paid' => true,
                    'referrer_rewarded_at' => now(),
                ]);

                // Notify Referrer
                $this->realtimeService->notifyUser($referral->referrer_id, 'referral_earned', [
                    'amount' => $referral->referrer_reward_amount,
                    'referee_name' => $referral->referee->name
                ]);
            }

            // 2. Pay Referee (Sign-up credit if any)
            if ($referral->referee_reward_amount > 0) {
                $wallet = $this->walletService->getOrCreateWallet($referral->referee);
                $this->walletService->creditBalance(
                    $wallet,
                    $referral->referee_reward_amount,
                    WalletTransaction::TYPE_REFERRAL_REWARD,
                    "Welcome reward for joining via referral",
                    ['referrer_id' => $referral->referrer_id]
                );

                $referral->update([
                    'referee_reward_paid' => true,
                    'referee_rewarded_at' => now(),
                ]);
            }

            $referral->update(['status' => Referral::STATUS_REWARDED]);
        });
    }

    /**
     * Check if user has free delivery reward
     */
    public function hasFreeDelivery(User $user): bool
    {
        $enabled = Setting::get('referral_enabled', '0');
        if ($enabled !== '1' && $enabled !== true && $enabled !== 1) {
            return false;
        }

        $referral = Referral::where('referee_id', $user->id)->first();
        
        return $referral && $referral->hasRemainingFreeDeliveries();
    }

    /**
     * Consume one free delivery session
     */
    public function consumeFreeDelivery(User $user): bool
    {
        $referral = Referral::where('referee_id', $user->id)->first();
        
        if ($referral && $referral->consumeFreeDelivery()) {
            return true;
        }

        return false;
    }

    /**
     * Get referral stats for a user
     */
    public function getStats(User $user): array
    {
        $referrals = Referral::where('referrer_id', $user->id)->get();
        
        return [
            'total_referrals' => $referrals->count(),
            'successful_referrals' => $referrals->where('status', Referral::STATUS_REWARDED)->count(),
            'total_earned' => $referrals->where('referrer_reward_paid', true)->sum('referrer_reward_amount'),
            'referral_code' => $user->referral_code,
        ];
    }
}

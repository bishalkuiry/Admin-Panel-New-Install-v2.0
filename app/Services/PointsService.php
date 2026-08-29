<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\UserPoints;
use App\Models\PointTransaction;
use App\Models\Setting;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class PointsService
{
    public function __construct(
        private WalletService $walletService
    ) {}

    /**
     * Get or create user points balance
     */
    public function getOrCreateUserPoints(User $user): UserPoints
    {
        $points = UserPoints::where('user_id', $user->id)->first();
        
        if (!$points) {
            $points = UserPoints::create([
                'user_id' => $user->id,
                'total_points' => 0,
                'available_points' => 0,
                'redeemed_points' => 0,
            ]);
        }
        
        return $points;
    }

    /**
     * Check if cashback system is enabled
     */
    public function isEnabled(): bool
    {
        $enabled = Setting::get('cashback_enabled', '0');
        return $enabled === '1' || $enabled === true;
    }

    /**
     * Earn points for a completed order
     * Called when order status changes to DELIVERED
     */
    public function earnPointsForOrder(Order $order): ?PointTransaction
    {
        // Check if cashback is enabled
        if (!$this->isEnabled()) {
            return null;
        }

        // Check minimum order amount
        $minOrderAmount = (float) Setting::get('cashback_min_order_amount', 500);
        if ($order->total < $minOrderAmount) {
            return null;
        }

        // Check if points already earned for this order
        $existingTransaction = PointTransaction::where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_EARNED)
            ->first();
        
        if ($existingTransaction) {
            Log::warning('Points already earned for order', [
                'order_id' => $order->id,
                'transaction_id' => $existingTransaction->id,
            ]);
            return $existingTransaction;
        }

        // Calculate points to earn
        $cashbackPercentage = (float) Setting::get('cashback_percentage', 10);
        $pointsToEarn = (int) floor($order->total * ($cashbackPercentage / 100));
        
        if ($pointsToEarn <= 0) {
            return null;
        }

        return DB::transaction(function () use ($order, $pointsToEarn) {
            $userPoints = $this->getOrCreateUserPoints($order->user);
            
            // Lock for update
            $lockedPoints = UserPoints::where('id', $userPoints->id)
                ->lockForUpdate()
                ->first();
            
            $pointsBefore = $lockedPoints->available_points;
            
            // Credit points
            $lockedPoints->total_points += $pointsToEarn;
            $lockedPoints->available_points += $pointsToEarn;
            $lockedPoints->save();
            
            // Create transaction record
            $transaction = PointTransaction::create([
                'user_points_id' => $lockedPoints->id,
                'type' => PointTransaction::TYPE_EARNED,
                'points' => $pointsToEarn,
                'points_before' => $pointsBefore,
                'points_after' => $lockedPoints->available_points,
                'order_id' => $order->id,
                'description' => "Earned {$pointsToEarn} points for order #{$order->order_number}",
                'metadata' => [
                    'order_total' => $order->total,
                    'cashback_percentage' => Setting::get('cashback_percentage', 10),
                ],
            ]);
            
            Log::info('Points earned for order', [
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'points_earned' => $pointsToEarn,
            ]);
            
            return $transaction;
        });
    }

    /**
     * Redeem points to wallet
     */
    public function redeemPointsToWallet(User $user, int $points): WalletTransaction
    {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Points must be positive');
        }

        $userPoints = $this->getOrCreateUserPoints($user);
        
        if (!$userPoints->hasPoints($points)) {
            throw new \App\Exceptions\InsufficientBalanceException(
                $points,
                $userPoints->available_points,
                'Insufficient points balance'
            );
        }

        // Calculate money value
        $pointsPerCurrency = (int) Setting::get('cashback_points_per_currency', 100);
        if ($pointsPerCurrency <= 0) {
            $pointsPerCurrency = 100;
        }
        
        $moneyValue = $points / $pointsPerCurrency;
        
        if ($moneyValue < 0.01) {
            throw new \InvalidArgumentException('Points value too low to redeem');
        }

        return DB::transaction(function () use ($user, $userPoints, $points, $moneyValue, $pointsPerCurrency) {
            // Lock points for update
            $lockedPoints = UserPoints::where('id', $userPoints->id)
                ->lockForUpdate()
                ->first();
            
            // Double-check balance
            if (!$lockedPoints->hasPoints($points)) {
                throw new \App\Exceptions\InsufficientBalanceException(
                    $points,
                    $lockedPoints->available_points,
                    'Insufficient points balance'
                );
            }
            
            $pointsBefore = $lockedPoints->available_points;
            
            // Deduct points
            $lockedPoints->available_points -= $points;
            $lockedPoints->redeemed_points += $points;
            $lockedPoints->save();
            
            // Create points transaction
            PointTransaction::create([
                'user_points_id' => $lockedPoints->id,
                'type' => PointTransaction::TYPE_REDEEMED,
                'points' => -$points,
                'points_before' => $pointsBefore,
                'points_after' => $lockedPoints->available_points,
                'description' => "Redeemed {$points} points for " . \App\Helpers\CurrencyHelper::format($moneyValue),
                'metadata' => [
                    'money_value' => $moneyValue,
                    'points_per_currency' => $pointsPerCurrency,
                ],
            ]);
            
            // Credit wallet
            $wallet = $this->walletService->getOrCreateWallet($user);
            $walletTransaction = $this->walletService->creditBalance(
                $wallet,
                $moneyValue,
                WalletTransaction::TYPE_POINTS_REDEMPTION ?? 'points_redemption',
                "Points redemption: {$points} points",
                [
                    'points_redeemed' => $points,
                    'conversion_rate' => $pointsPerCurrency,
                ]
            );
            
            Log::info('Points redeemed to wallet', [
                'user_id' => $user->id,
                'points_redeemed' => $points,
                'money_value' => $moneyValue,
                'wallet_transaction_id' => $walletTransaction->id,
            ]);
            
            return $walletTransaction;
        });
    }

    /**
     * Get points transaction history
     */
    public function getPointsHistory(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $userPoints = UserPoints::where('user_id', $user->id)->first();
        
        if (!$userPoints) {
            return new LengthAwarePaginator([], 0, $perPage);
        }
        
        return PointTransaction::where('user_points_id', $userPoints->id)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Calculate money value of points
     */
    public function calculatePointsValue(int $points): float
    {
        $pointsPerCurrency = (int) Setting::get('cashback_points_per_currency', 100);
        if ($pointsPerCurrency <= 0) {
            $pointsPerCurrency = 100;
        }
        return $points / $pointsPerCurrency;
    }

    /**
     * Admin credit points to user
     */
    public function adminCreditPoints(User $user, int $points, string $reason, ?User $admin = null): PointTransaction
    {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Points must be positive');
        }

        return DB::transaction(function () use ($user, $points, $reason, $admin) {
            $userPoints = $this->getOrCreateUserPoints($user);
            
            $lockedPoints = UserPoints::where('id', $userPoints->id)
                ->lockForUpdate()
                ->first();
            
            $pointsBefore = $lockedPoints->available_points;
            
            $lockedPoints->total_points += $points;
            $lockedPoints->available_points += $points;
            $lockedPoints->save();
            
            return PointTransaction::create([
                'user_points_id' => $lockedPoints->id,
                'type' => PointTransaction::TYPE_ADMIN_CREDIT,
                'points' => $points,
                'points_before' => $pointsBefore,
                'points_after' => $lockedPoints->available_points,
                'description' => "Admin credit: {$reason}",
                'created_by' => $admin?->id,
            ]);
        });
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\PointsService;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function __construct(
        private PointsService $pointsService
    ) {}

    /**
     * Get user's points balance
     */
    public function getBalance(Request $request)
    {
        $points = $this->pointsService->getOrCreateUserPoints($request->user());
        
        return response()->json([
            'success' => true,
            'data' => [
                'available_points' => $points->available_points,
                'total_earned' => $points->total_points,
                'total_redeemed' => $points->redeemed_points,
                'currency_value' => $this->pointsService->calculatePointsValue($points->available_points),
                'conversion_rate' => (int) Setting::get('cashback_points_per_currency', 100),
            ]
        ]);
    }

    /**
     * Get points transaction history
     */
    public function getHistory(Request $request)
    {
        $history = $this->pointsService->getPointsHistory($request->user());
        
        return response()->json([
            'success' => true,
            'data' => [
                'data' => $history->items(),
                'meta' => [
                    'current_page' => $history->currentPage(),
                    'last_page'    => $history->lastPage(),
                    'per_page'     => $history->perPage(),
                    'total'        => $history->total(),
                ],
            ],
        ]);
    }

    /**
     * Redeem points to wallet
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'points' => 'required|integer|min:1'
        ]);

        try {
            $transaction = $this->pointsService->redeemPointsToWallet($request->user(), $request->points);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully redeemed {$request->points} points to wallet",
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

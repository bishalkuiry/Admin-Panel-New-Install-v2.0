<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserPoints;
use App\Models\PointTransaction;
use App\Models\Referral;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    /**
     * Show loyalty dashboard
     */
    public function index()
    {
        $stats = [
            'total_points_earned' => PointTransaction::where('type', 'earned')->sum('points'),
            'total_points_redeemed' => PointTransaction::where('type', 'redeemed')->sum('points'),
            'total_referrals' => Referral::count(),
            'successful_referrals' => Referral::where('status', 'rewarded')->count(),
            'referral_rewards_paid' => Referral::where('referrer_reward_paid', true)->sum('referrer_reward_amount'),
        ];

        $recentTransactions = PointTransaction::with('userPoints.user', 'order')
            ->latest()
            ->take(10)
            ->get();

        $recentReferrals = Referral::with('referrer', 'referee')
            ->latest()
            ->take(10)
            ->get();

        // Points trend for last 7 days
        $pointsTrend = PointTransaction::where('type', 'earned')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(points) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.loyalty.index', compact('stats', 'recentTransactions', 'recentReferrals', 'pointsTrend'));
    }
}

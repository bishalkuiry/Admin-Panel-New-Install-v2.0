<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $store = $request->get('current_store');
        $period = $request->get('period', '30days');

        $startDate = match($period) {
            '7days' => now()->subDays(7),
            '30days' => now()->subDays(30),
            '90days' => now()->subDays(90),
            default => now()->subDays(30),
        };

        // Revenue over time
        $revenueData = Order::where('store_id', $store->id)
            ->where('status', OrderStatus::DELIVERED)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top selling products
        $topProducts = OrderItem::whereHas('order', function($q) use ($store) {
                $q->where('store_id', $store->id)->where('status', OrderStatus::DELIVERED);
            })
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total) as total_revenue'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product')
            ->take(5)
            ->get();

        // Order Status Distribution
        $orderStatus = Order::where('store_id', $store->id)
            ->where('created_at', '>=', $startDate)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // Real Performance Metrics (Using string comparison to be safe against legacy/invalid status data)
        $deliveredCount = $orderStatus->filter(fn($item) => ($item->status?->value ?? $item->status) === 'delivered')->first()?->count ?? 0;
        $cancelledCount = $orderStatus->filter(fn($item) => ($item->status?->value ?? $item->status) === 'cancelled')->first()?->count ?? 0;
        
        $totalRelevant = $deliveredCount + $cancelledCount;
        $fulfillmentRate = $totalRelevant > 0 ? ($deliveredCount / $totalRelevant) * 100 : 100;

        // Score: (Rating * 10) + (FulfillmentRate * 0.5) -> Max 100
        // e.g. 5.0 rating (50) + 100% fulfillment (50) = 100
        $ratingScore = ($store->rating ?? 0) * 10;
        $fulfillmentScore = $fulfillmentRate * 0.5;
        $storeScore = round($ratingScore + $fulfillmentScore);

        return view('seller.analytics.index', compact(
            'store', 'revenueData', 'topProducts', 'orderStatus', 'period', 
            'fulfillmentRate', 'storeScore'
        ));
    }
}

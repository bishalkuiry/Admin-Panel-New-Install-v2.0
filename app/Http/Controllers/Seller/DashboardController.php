<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

use App\Enums\OrderStatus;

class DashboardController extends Controller
{
    /**
     * Show seller dashboard
     */
    public function index(Request $request)
    {
        $store = $request->get('current_store');
        
        // Basic stats for dashboard
        $stats = [
            'total_orders' => Order::where('store_id', $store->id)->count(),
            'pending_orders' => Order::where('store_id', $store->id)->where('status', OrderStatus::PENDING)->count(),
            'total_products' => Product::where('store_id', $store->id)->count(),
            'total_revenue' => $store->total_revenue ?? 0,
        ];

        $recent_orders = Order::where('store_id', $store->id)
            ->latest()
            ->take(5)
            ->get();

        return view('seller.dashboard', compact('stats', 'recent_orders', 'store'));
    }
}

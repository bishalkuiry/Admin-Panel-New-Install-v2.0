<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class TopbarStatsController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();

        $ordersToday = Order::whereDate('created_at', $today)->count();

        $revenueRaw = Order::whereDate('created_at', $today)
            ->whereNotIn('status', [OrderStatus::CANCELLED])
            ->sum('total');

        $pending = Order::where('status', OrderStatus::PENDING)->count();

        $newCustomers = User::where('role', 'customer')
            ->whereDate('created_at', $today)
            ->count();

        $symbol = Setting::where('key', 'currency_symbol')->value('value') ?? '$';

        $revenue = $revenueRaw >= 1000
            ? $symbol . number_format($revenueRaw / 1000, 1) . 'k'
            : $symbol . number_format($revenueRaw, 0);

        return response()->json([
            'orders_today'  => $ordersToday,
            'revenue_today' => $revenue,
            'pending'       => $pending,
            'new_customers' => $newCustomers,
        ]);
    }
}

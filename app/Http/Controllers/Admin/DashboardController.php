<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\Store;
use App\Models\OrderItem;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Enums\StoreStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::active()->count(),
            'low_stock' => Product::lowStock()->count(),
            'categories' => Category::count(),
            
            'total_revenue' => Order::where('status', OrderStatus::DELIVERED)->sum('total'),
            'total_commission' => OrderItem::whereHas('order', function($q) {
                $q->where('status', OrderStatus::DELIVERED);
            })->sum('commission'),
            
            'pending_orders' => Order::where('status', OrderStatus::PENDING)->count(),
            'processing_orders' => Order::whereIn('status', [OrderStatus::CONFIRMED, OrderStatus::PACKED])->count(),
            
            'total_stores' => Store::count(),
            'pending_stores' => Store::where('status', StoreStatus::PENDING)->count(),
            'active_stores' => Store::where('status', StoreStatus::ACTIVE)->count(),
            
            'total_customers' => User::where('role', 'customer')->count(),
        ];

        // Revenue Chart Data (Last 7 Days)
        $chartData = $this->getRevenueChartData();

        $recentProducts = Product::with('category', 'primaryImage')
            ->latest()
            ->take(5)
            ->get();

        $recentOrders = Order::with('user', 'store')
            ->latest()
            ->take(5)
            ->get();

        $pendingStores = Store::where('status', StoreStatus::PENDING)
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentProducts', 'recentOrders', 'pendingStores', 'chartData'));
    }

    public function downloadReport()
    {
        $stats = [
            'total_revenue' => Order::where('status', OrderStatus::DELIVERED)->sum('total'),
            'total_commission' => OrderItem::whereHas('order', function($q) {
                $q->where('status', OrderStatus::DELIVERED);
            })->sum('commission'),
            'total_products' => Product::count(),
            'active_products' => Product::active()->count(),
            'active_stores' => Store::where('status', StoreStatus::ACTIVE)->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'pending_orders' => Order::where('status', OrderStatus::PENDING)->count(),
            'processing_orders' => Order::whereIn('status', [OrderStatus::CONFIRMED, OrderStatus::PACKED])->count(),
        ];

        $chartData = $this->getRevenueChartData();
        
        $data = [
            'stats' => $stats,
            'chartData' => $chartData,
            'reportDate' => now()->format('F d, Y'),
            'generatedAt' => now()->format('h:i A'),
        ];

        $pdf = Pdf::loadView('admin.reports.dashboard', $data);
        return $pdf->download('dashboard-report-' . now()->format('Y-m-d') . '.pdf');
    }

    private function getRevenueChartData()
    {
        $data = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('D, M d');
            
            $revenue = Order::whereDate('created_at', $date)
                ->where('status', OrderStatus::DELIVERED)
                ->sum('total');
            
            $data[] = (float)$revenue;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}

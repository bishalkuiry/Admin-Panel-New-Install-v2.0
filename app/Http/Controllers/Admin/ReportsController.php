<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->query('type', 'sales');
        $startDate = $request->query('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $format = $request->query('format', 'view');

        $data = $this->generateReportData($reportType, $startDate, $endDate);

        if ($format === 'pdf') {
            return view('admin.reports.pdf', compact('reportType', 'startDate', 'endDate', 'data'));
        }

        return view('admin.reports.index', compact('reportType', 'startDate', 'endDate', 'data'));
    }

    private function generateReportData(string $reportType, string $startDate, string $endDate)
    {
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';

        return match ($reportType) {
            'sales' => Order::whereBetween('created_at', [$start, $end])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(subtotal) as gross_sales'), DB::raw('SUM(discount) as total_discount'), DB::raw('SUM(tax) as total_tax'), DB::raw('SUM(total) as net_sales'))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get(),

            'orders' => Order::with(['user', 'store'])
                ->whereBetween('created_at', [$start, $end])
                ->latest()
                ->paginate(25),

            'admin_earnings' => Order::whereBetween('created_at', [$start, $end])
                ->where('status', 'delivered')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total) as gross_volume'), DB::raw('SUM(admin_commission) as admin_commission'), DB::raw('SUM(tax) as tax_collected'))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get(),

            'vendor_earnings' => Store::withCount(['orders' => function ($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                }])
                ->get()
                ->map(function ($store) use ($start, $end) {
                    $storeOrders = Order::where('store_id', $store->id)
                        ->whereBetween('created_at', [$start, $end])
                        ->where('status', 'delivered');
                    $totalSales = (float) $storeOrders->sum('total');
                    $storeEarning = (float) $storeOrders->sum('store_earning');
                    $adminCommission = (float) $storeOrders->sum('admin_commission');

                    return [
                        'store_name' => $store->name,
                        'total_orders' => $store->orders_count,
                        'total_sales' => $totalSales,
                        'vendor_earning' => round($storeEarning, 2),
                        'admin_commission' => round($adminCommission, 2),
                    ];
                }),

            'rider_earnings' => User::where('role', 'delivery_partner')
                ->get()
                ->map(function ($driver) use ($start, $end) {
                    $deliveries = Order::where('delivery_partner_id', $driver->id)
                        ->whereBetween('created_at', [$start, $end])
                        ->where('status', 'delivered');
                    return [
                        'driver_name' => $driver->name,
                        'phone' => $driver->phone,
                        'deliveries_count' => $deliveries->count(),
                        'delivery_fee_sum' => $deliveries->sum('delivery_fee'),
                        'driver_tip_sum' => $deliveries->sum('driver_tip'),
                        'total_earnings' => (float) $deliveries->sum('driver_earning'),
                    ];
                }),

            'transactions' => WalletTransaction::with('user')
                ->whereBetween('created_at', [$start, $end])
                ->latest()
                ->paginate(30),

            'store_wise' => Store::get()->map(function ($s) use ($start, $end) {
                $orders = Order::where('store_id', $s->id)->whereBetween('created_at', [$start, $end]);
                return [
                    'store_name' => $s->name,
                    'orders_count' => $orders->count(),
                    'revenue' => $orders->where('status', 'delivered')->sum('total'),
                ];
            }),

            'product_wise' => OrderItem::select('product_id', 'product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total) as total_revenue'))
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('product_id', 'product_name')
                ->orderBy('total_qty', 'desc')
                ->limit(50)
                ->get(),

            'provider_wise' => Schema::hasTable('rides')
                ? DB::table('rides')
                    ->leftJoin('users', 'rides.rider_id', '=', 'users.id')
                    ->whereBetween('rides.created_at', [$start, $end])
                    ->select('users.name as driver_name', DB::raw('COUNT(*) as total_rides'), DB::raw('SUM(rides.total_fare) as total_fare'))
                    ->groupBy('users.id', 'users.name')
                    ->get()
                : collect(),

            'vehicle_reports' => Schema::hasTable('ride_drivers')
                ? DB::table('ride_drivers')
                    ->leftJoin('users', 'ride_drivers.user_id', '=', 'users.id')
                    ->select(
                        'users.name as owner_name',
                        DB::raw("CONCAT(COALESCE(ride_drivers.vehicle_make, ''), ' ', COALESCE(ride_drivers.vehicle_model, '')) as vehicle_model"),
                        'ride_drivers.vehicle_plate_number as vehicle_number',
                        'ride_drivers.availability_status as is_online',
                        'ride_drivers.total_rides as total_trips'
                    )
                    ->get()
                : collect(),

            'tax_reports' => Order::whereBetween('created_at', [$start, $end])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(subtotal) as taxable_amount'), DB::raw('SUM(tax) as gst_collected'))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get(),

            default => [],
        };
    }

    public function exportCsv(Request $request)
    {
        $reportType = $request->query('type', 'sales');
        $startDate = $request->query('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $isExcel = $request->query('excel', false);

        $fileName = "InAllCart-{$reportType}-report-{$startDate}-to-{$endDate}." . ($isExcel ? 'xls' : 'csv');

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($reportType, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            $data = $this->generateReportData($reportType, $startDate, $endDate);

            if ($reportType === 'sales') {
                fputcsv($file, ['Date', 'Total Orders', 'Gross Sales (₹)', 'Discounts (₹)', 'Tax (₹)', 'Net Sales (₹)']);
                foreach ($data as $row) {
                    fputcsv($file, [$row->date, $row->total_orders, $row->gross_sales, $row->total_discount, $row->total_tax, $row->net_sales]);
                }
            } elseif ($reportType === 'admin_earnings') {
                fputcsv($file, ['Date', 'Orders Completed', 'Gross Sales Volume (₹)', 'Admin Commission (15%) (₹)', 'Tax Collected (₹)']);
                foreach ($data as $row) {
                    fputcsv($file, [$row->date, $row->orders_count, $row->gross_volume, $row->admin_commission, $row->tax_collected]);
                }
            } elseif ($reportType === 'vendor_earnings') {
                fputcsv($file, ['Store Name', 'Total Orders', 'Total Sales (₹)', 'Vendor Net Payout (85%) (₹)', 'Admin Commission (15%) (₹)']);
                foreach ($data as $row) {
                    fputcsv($file, [$row['store_name'], $row['total_orders'], $row['total_sales'], $row['vendor_earning'], $row['admin_commission']]);
                }
            } elseif ($reportType === 'rider_earnings') {
                fputcsv($file, ['Rider Name', 'Phone', 'Completed Deliveries', 'Total Delivery Fee Earnings (₹)']);
                foreach ($data as $row) {
                    fputcsv($file, [$row['driver_name'], $row['phone'], $row['deliveries_count'], $row['total_earnings']]);
                }
            } else {
                fputcsv($file, ['Report Type', 'Start Date', 'End Date', 'Export Status']);
                fputcsv($file, [$reportType, $startDate, $endDate, 'Completed']);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

@extends('admin.layouts.app')

@section('title', 'Advanced Analytical Reports')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Advanced Analytical Reports</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Multi-format reports across Sales, Admin & Vendor Earnings, Rider Earnings, Products, Vehicles & Taxes</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.reports.index', array_merge(request()->all(), ['format' => 'pdf'])) }}" target="_blank" class="btn btn-secondary text-xs flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Export PDF</span>
            </a>
            <a href="{{ route('admin.reports.export', array_merge(request()->all(), ['excel' => 1])) }}" class="btn btn-secondary text-xs flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel</span>
            </a>
            <a href="{{ route('admin.reports.export', request()->all()) }}" class="btn btn-primary text-xs flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="card p-4 sm:p-5">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="label text-xs font-bold">Select Report Category</label>
                <select name="type" class="input text-xs font-semibold">
                    <option value="sales" {{ $reportType === 'sales' ? 'selected' : '' }}>📊 Sales & Volume Report</option>
                    <option value="admin_earnings" {{ $reportType === 'admin_earnings' ? 'selected' : '' }}>💰 Admin Commission Earnings</option>
                    <option value="vendor_earnings" {{ $reportType === 'vendor_earnings' ? 'selected' : '' }}>🏪 Vendor Payout & Net Earnings</option>
                    <option value="rider_earnings" {{ $reportType === 'rider_earnings' ? 'selected' : '' }}>🛵 Delivery Partner Payouts</option>
                    <option value="transactions" {{ $reportType === 'transactions' ? 'selected' : '' }}>💳 Wallet & Payment Transactions</option>
                    <option value="store_wise" {{ $reportType === 'store_wise' ? 'selected' : '' }}>🏬 Store-wise Performance</option>
                    <option value="product_wise" {{ $reportType === 'product_wise' ? 'selected' : '' }}>📦 Product-wise Sales Analytics</option>
                    <option value="provider_wise" {{ $reportType === 'provider_wise' ? 'selected' : '' }}>🚗 Provider & Driver Rides</option>
                    <option value="vehicle_reports" {{ $reportType === 'vehicle_reports' ? 'selected' : '' }}>🚘 Vehicle & fleet Registry</option>
                    <option value="tax_reports" {{ $reportType === 'tax_reports' ? 'selected' : '' }}>🧾 Tax & GST Collected</option>
                </select>
            </div>
            <div>
                <label class="label text-xs font-bold">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="input text-xs">
            </div>
            <div>
                <label class="label text-xs font-bold">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="input text-xs">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary text-xs w-full">Generate Report</button>
            </div>
        </form>
    </div>

    <!-- Report Output Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-xs sm:text-sm text-gray-900 uppercase tracking-wider">{{ str_replace('_', ' ', $reportType) }} Report</h3>
            <span class="text-[10px] sm:text-xs text-gray-500 font-mono">{{ $startDate }} to {{ $endDate }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-xs min-w-[600px]">
                @if($reportType === 'sales')
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Date</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Orders</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Gross Sales</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Discounts</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Tax</th>
                            <th class="text-right py-3 px-4 font-bold text-gray-700">Net Sales Volume</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($data as $row)
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3 px-4 font-bold text-gray-900">{{ $row->date }}</td>
                                <td class="py-3 px-4 font-semibold text-gray-800">{{ $row->total_orders }} orders</td>
                                <td class="py-3 px-4 text-gray-600 font-mono">₹{{ number_format($row->gross_sales, 2) }}</td>
                                <td class="py-3 px-4 text-red-600 font-mono">-₹{{ number_format($row->total_discount, 2) }}</td>
                                <td class="py-3 px-4 text-gray-600 font-mono">₹{{ number_format($row->total_tax, 2) }}</td>
                                <td class="py-3 px-4 text-right font-extrabold text-green-700">₹{{ number_format($row->net_sales, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-gray-400">No records found for selected date range.</td></tr>
                        @endforelse
                    </tbody>
                @elseif($reportType === 'admin_earnings')
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Date</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Orders Delivered</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Gross Sales Volume</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Tax Collected</th>
                            <th class="text-right py-3 px-4 font-bold text-gray-700">Admin Commission (15%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($data as $row)
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3 px-4 font-bold text-gray-900">{{ $row->date }}</td>
                                <td class="py-3 px-4 text-gray-800">{{ $row->orders_count }} orders</td>
                                <td class="py-3 px-4 font-semibold text-gray-800">₹{{ number_format($row->gross_volume, 2) }}</td>
                                <td class="py-3 px-4 text-gray-600 font-mono">₹{{ number_format($row->tax_collected, 2) }}</td>
                                <td class="py-3 px-4 text-right font-extrabold text-orange-600">₹{{ number_format($row->admin_commission, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-gray-400">No records found.</td></tr>
                        @endforelse
                    </tbody>
                @elseif($reportType === 'vendor_earnings')
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Store Name</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Completed Orders</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Gross Sales</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Admin Fee (15%)</th>
                            <th class="text-right py-3 px-4 font-bold text-gray-700">Vendor Net Payout (85%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($data as $row)
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3 px-4 font-bold text-gray-900">{{ $row['store_name'] }}</td>
                                <td class="py-3 px-4 text-gray-700">{{ $row['total_orders'] }} orders</td>
                                <td class="py-3 px-4 text-gray-800 font-semibold">₹{{ number_format($row['total_sales'], 2) }}</td>
                                <td class="py-3 px-4 text-orange-600 font-mono">₹{{ number_format($row['admin_commission'], 2) }}</td>
                                <td class="py-3 px-4 text-right font-extrabold text-green-700">₹{{ number_format($row['vendor_earning'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                @elseif($reportType === 'rider_earnings')
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Rider / Delivery Partner</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Phone</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Deliveries Completed</th>
                            <th class="text-right py-3 px-4 font-bold text-gray-700">Delivery Fee</th>
                            <th class="text-right py-3 px-4 font-bold text-gray-700">Customer Tip</th>
                            <th class="text-right py-3 px-4 font-bold text-gray-700">Total Rider Payout</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($data as $row)
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3 px-4 font-bold text-gray-900">{{ $row['driver_name'] }}</td>
                                <td class="py-3 px-4 text-gray-500 font-mono">{{ $row['phone'] ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-gray-800 font-semibold">{{ $row['deliveries_count'] }} deliveries</td>
                                <td class="py-3 px-4 text-right font-semibold text-gray-800">₹{{ number_format($row['delivery_fee_sum'] ?? 0, 2) }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-700">+₹{{ number_format($row['driver_tip_sum'] ?? 0, 2) }}</td>
                                <td class="py-3 px-4 text-right font-extrabold text-green-700">₹{{ number_format($row['total_earnings'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                @elseif($reportType === 'product_wise')
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Product Name</th>
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Units Sold</th>
                            <th class="text-right py-3 px-4 font-bold text-gray-700">Total Revenue Generated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($data as $row)
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3 px-4 font-bold text-gray-900">{{ $row->product_name }}</td>
                                <td class="py-3 px-4 font-semibold text-gray-800">{{ $row->total_qty }} units</td>
                                <td class="py-3 px-4 text-right font-extrabold text-green-700">₹{{ number_format($row->total_revenue, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                @else
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left py-3 px-4 font-bold text-gray-700">Summary</th>
                            <th class="text-right py-3 px-4 font-bold text-gray-700">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-4 px-4 font-bold text-gray-800">{{ str_replace('_', ' ', $reportType) }} report generated.</td>
                            <td class="py-4 px-4 text-right text-gray-500 font-mono">{{ count($data) }} rows loaded</td>
                        </tr>
                    </tbody>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

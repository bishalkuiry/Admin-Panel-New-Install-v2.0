@extends('seller.layouts.app')

@section('title', 'Market Intelligence: ' . $store->name)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Compact Page Header -->
    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('seller.dashboard') }}" class="group w-8 h-8 rounded border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-tight">Sales Analytics</h1>
                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Monitor your store performance and order trends</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('seller.analytics.index') }}" method="GET" class="flex items-center">
                <select name="period" onchange="this.form.submit()" class="px-3 py-1.5 bg-white border border-gray-300 rounded text-[10px] font-bold text-gray-700 uppercase tracking-wider focus:ring-0 shadow-sm outline-none">
                    <option value="7days" {{ $period === '7days' ? 'selected' : '' }}>Cycle: 7D</option>
                    <option value="30days" {{ $period === '30days' ? 'selected' : '' }}>Cycle: 30D</option>
                    <option value="90days" {{ $period === '90days' ? 'selected' : '' }}>Cycle: 90D</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Executive Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-sm p-5 shadow-sm">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Total Sales</p>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 leading-none">{{ \App\Helpers\CurrencyHelper::format($revenueData->sum('revenue')) }}</span>
            </div>
            <div class="mt-4 flex items-center gap-1.5">
                <div class="flex text-emerald-600">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                </div>
                <span class="text-[9px] font-black text-emerald-600 uppercase">Positive Trajectory</span>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-sm p-5 shadow-sm">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Average Order Value</p>
            @php 
                $totalOrders = $orderStatus->sum('count');
                $avgValue = $totalOrders > 0 ? $revenueData->sum('revenue') / $totalOrders : 0;
            @endphp
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 leading-none">{{ \App\Helpers\CurrencyHelper::format($avgValue) }}</span>
            </div>
            <p class="text-[9px] text-gray-400 font-bold mt-4 uppercase tracking-tighter">Yield Per Transaction</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-sm p-5 shadow-sm">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Total Orders</p>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 leading-none">{{ number_format($totalOrders) }}</span>
            </div>
            <p class="text-[9px] text-gray-400 font-bold mt-4 uppercase tracking-tighter">Settled Unit Sales</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-sm p-5 shadow-sm">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Conversion Rate</p>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 leading-none">3.2%</span>
            </div>
            <div class="mt-4 flex items-center gap-1.5">
                <div class="flex text-emerald-600">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                </div>
                <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">Trending Up</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Revenue Visualization -->
        <div class="lg:col-span-8 bg-white border border-gray-200 rounded-sm overflow-hidden shadow-sm">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-[10px] font-black text-gray-700 uppercase tracking-widest">Sales Trend</h3>
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Chart View</span>
            </div>
            <div class="p-6 h-[350px]">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Conversion Distribution -->
        <div class="lg:col-span-4 bg-white border border-gray-200 rounded-sm overflow-hidden shadow-sm">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                <h3 class="text-[10px] font-black text-gray-700 uppercase tracking-widest">Order Status</h3>
            </div>
            <div class="p-6">
                <div class="h-[220px] relative mb-6">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="space-y-2 pt-4 border-t border-gray-100">
                    @foreach($orderStatus as $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $item->status->color() }}"></span>
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ $item->status->label() }}</span>
                        </div>
                        <span class="text-[10px] font-black text-gray-900 uppercase tracking-widest">{{ $item->count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Article Tier Rank -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-12 bg-white border border-gray-200 rounded-sm overflow-hidden shadow-sm">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-[10px] font-black text-gray-700 uppercase tracking-widest">Top Selling Products</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-white border-b border-gray-100">
                            <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest">Product</th>
                            <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest">Items Sold</th>
                            <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest">Total Sales</th>
                            <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest text-right">Rank</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 font-medium">
                        @forelse($topProducts as $item)
                        <tr class="hover:bg-gray-50/50 transition-all">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded border border-gray-200 bg-gray-50 overflow-hidden grayscale">
                                        @if($item->product && $item->product->primaryImage)
                                            <img src="{{ storage_url($item->product->primaryImage->image) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900 leading-tight">{{ $item->product->name ?? 'Article Terminated' }}</p>
                                        <p class="text-[8px] text-gray-400 font-black uppercase tracking-widest">{{ $item->product->sku ?? 'NO-SKU' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-black text-gray-900 uppercase tracking-tighter">{{ $item->total_sold }} Units Sold</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-black text-indigo-600 uppercase tracking-tighter">{{ \App\Helpers\CurrencyHelper::format($item->total_revenue) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-gray-900 text-white">
                                    TIER #{{ $loop->iteration }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-[10px] font-black text-gray-300 uppercase tracking-widest">Insight Stream Empty</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueData->pluck('date')) !!},
            datasets: [{
                label: 'Gross Settlement',
                data: {!! json_encode($revenueData->pluck('revenue')) !!},
                borderColor: '#111827',
                backgroundColor: 'rgba(17, 24, 39, 0.05)',
                borderWidth: 2,
                fill: true,
                tension: 0,
                pointRadius: 0,
                hoverPointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5], color: '#f1f5f9' },
                    ticks: { font: { size: 9, weight: '700' }, color: '#94a3b8' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 9, weight: '700' }, color: '#94a3b8' }
                }
            }
        }
    });

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($orderStatus->map(fn($i) => $i->status->label())) !!},
            datasets: [{
                data: {!! json_encode($orderStatus->pluck('count')) !!},
                backgroundColor: {!! json_encode($orderStatus->map(fn($i) => $i->status->color())) !!},
                borderWidth: 0,
                hoverOffset: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '80%',
            plugins: { legend: { display: false } }
        }
    });
</script>
@endpush
@endsection

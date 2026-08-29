@extends('seller.layouts.app')

@section('title', 'Execution Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Compact Professional Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-100 pb-4 mb-2">
        <div>
            <h1 class="text-xl font-bold text-gray-900 leading-tight">Operational Overview</h1>
            <p class="text-[10px] text-gray-500 font-medium uppercase tracking-widest mt-0.5">Performance metrics for <span class="text-indigo-600 font-bold">{{ $store->name }}</span></p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-2">
            <a href="{{ route('seller.products.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded text-[10px] font-black text-white hover:bg-indigo-700 uppercase tracking-widest transition-all shadow-sm">
                Add Product
            </a>
            <div class="inline-flex items-center px-3 py-2 bg-white border border-gray-200 rounded text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                Active Period: Last 30 Days
            </div>
        </div>
    </div>

    <!-- Metrics Grid: Clean & Functional -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('seller.orders.index') }}" class="bg-white border border-gray-200 rounded-sm p-5 hover:border-indigo-300 hover:shadow-sm transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Gross Orders</span>
                <div class="w-8 h-8 rounded bg-gray-50 flex items-center justify-center text-gray-400 group-hover:text-indigo-600 group-hover:bg-indigo-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 leading-none">{{ number_format($stats['total_orders']) }}</span>
                <span class="text-[9px] font-bold text-emerald-600 uppercase">Unit Volume</span>
            </div>
        </a>

        <a href="{{ route('seller.orders.index', ['status' => 'pending']) }}" class="bg-white border border-gray-200 rounded-sm p-5 hover:border-amber-300 hover:shadow-sm transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pending Fulfilment</span>
                <div class="w-8 h-8 rounded bg-gray-50 flex items-center justify-center text-gray-400 group-hover:text-amber-600 group-hover:bg-amber-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 leading-none">{{ number_format($stats['pending_orders']) }}</span>
                <span class="text-[9px] font-bold text-amber-600 uppercase">Awaiting Action</span>
            </div>
        </a>

        <a href="{{ route('seller.analytics.index') }}" class="bg-white border border-gray-200 rounded-sm p-5 hover:border-indigo-300 hover:shadow-sm transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Settled Revenue</span>
                <div class="w-8 h-8 rounded bg-gray-50 flex items-center justify-center text-gray-400 group-hover:text-indigo-600 group-hover:bg-indigo-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 leading-none">₹{{ number_format($stats['total_revenue'], 2) }}</span>
                <span class="text-[9px] font-bold text-emerald-600 uppercase">Gross Sales</span>
            </div>
        </a>

        <a href="{{ route('seller.products.index') }}" class="bg-white border border-gray-200 rounded-sm p-5 hover:border-indigo-300 hover:shadow-sm transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Active Stock Items</span>
                <div class="w-8 h-8 rounded bg-gray-50 flex items-center justify-center text-gray-400 group-hover:text-indigo-600 group-hover:bg-indigo-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-gray-900 leading-none">{{ number_format($stats['total_products']) }}</span>
                <span class="text-[9px] font-bold text-indigo-600 uppercase">SKU Count</span>
            </div>
        </a>
    </div>

    <!-- Secondary Data Row -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Recent Orders Entry Table -->
        <div class="lg:col-span-8">
            <div class="bg-white border border-gray-200 rounded-sm overflow-hidden shadow-sm">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-[10px] font-black text-gray-700 uppercase tracking-widest">Recent Orders</h2>
                    <a href="{{ route('seller.orders.index') }}" class="text-[9px] font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-widest">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-white border-b border-gray-100">
                                <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest">Entity ID</th>
                                <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest">Counterparty</th>
                                <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                                <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest">Net Value</th>
                                <th class="px-4 py-3 text-[9px] font-black text-gray-400 uppercase tracking-widest text-right">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($recent_orders as $order)
                            <tr class="hover:bg-gray-50/50 transition-all cursor-pointer" onclick="window.location='{{ route('seller.orders.show', $order) }}'">
                                <td class="px-4 py-3">
                                    <span class="text-xs font-bold text-gray-900">#{{ $order->order_number }}</span>
                                    <p class="text-[8px] text-gray-400 font-bold uppercase tracking-tighter">{{ $order->created_at->format('M d, H:i') }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-xs font-semibold text-gray-700 truncate max-w-[120px]">{{ $order->user->name ?? 'Direct Counterparty' }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest border 
                                        {{ $order->status->value === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-100' : 
                                           ($order->status->value === 'delivered' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-gray-50 text-gray-500 border-gray-100') }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-black text-gray-900">₹{{ number_format($order->total, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center justify-center w-6 h-6 rounded border border-gray-200 text-gray-400 group-hover:border-indigo-200 group-hover:text-indigo-600 transition-all">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]">Data stream empty</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Store Profile & Health -->
        <div class="lg:col-span-4">
            <div class="bg-white border border-gray-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-[10px] font-black text-gray-700 uppercase tracking-widest">Store Profile</h2>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-4 mb-6">
                        @if($store->logo)
                            <img src="{{ storage_url($store->logo) }}" class="w-12 h-12 rounded bg-gray-50 border border-gray-100 object-cover grayscale brightness-90">
                        @else
                            <div class="w-12 h-12 rounded bg-gray-900 flex items-center justify-center text-white text-base font-black">
                                {{ substr($store->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">{{ $store->name }}</h3>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <div class="flex text-amber-500">
                                    <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </div>
                                <span class="text-[10px] font-black text-gray-700">{{ $store->rating ?? '0.0' }}</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">/ Index Score</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Network Status</p>
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest {{ $store->is_online ? 'text-emerald-600' : 'text-rose-600' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $store->is_online ? 'bg-emerald-600' : 'bg-rose-600' }} animate-pulse"></span>
                                {{ $store->is_online ? 'Live' : 'Standby' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tax Provision</p>
                            <p class="text-[10px] font-black text-gray-900 uppercase tracking-widest">Standard {{ $store->commission_percent }}%</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Catalog Depth</p>
                            <p class="text-[10px] font-black text-gray-900 uppercase tracking-widest">{{ $stats['total_products'] }} SKU</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('seller.settings.edit') }}" class="block w-full text-center py-2.5 bg-gray-900 text-white rounded-sm text-[10px] font-black uppercase tracking-[0.2em] hover:bg-black transition-all shadow-md">
                            Store Configuration
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

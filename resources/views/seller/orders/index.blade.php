@extends('seller.layouts.app')

@section('title', 'Manage Orders')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-indigo-950">Store Orders</h1>
            <p class="text-gray-500 text-sm mt-1">Manage and track your customer orders here.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="bg-white p-2.5 rounded-xl border border-gray-100 shadow-sm text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </button>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card p-4">
        <form action="{{ route('seller.orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2 relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, Customer Name..." class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm">
            </div>
            
            <select name="status" class="block w-full px-3 py-2 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm appearance-none cursor-pointer">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="bg-indigo-600 text-white rounded-xl py-2 font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                Apply Filters
            </button>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Order Details</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Customer</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Amount</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Date</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-indigo-600">#{{ $order->order_number }}</span>
                                <span class="text-[10px] text-gray-400 font-medium tracking-tight uppercase">{{ $order->items->count() }} items</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500 text-xs font-bold">
                                    {{ substr($order->user->name ?? 'G', 0, 1) }}
                                </div>
                                <div class="flex flex-col leading-none">
                                    <span class="text-sm font-semibold text-gray-700">{{ $order->user->name ?? 'Guest' }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $order->user->email ?? '' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                @php $rawStatus = $order->getRawOriginal('status'); @endphp
                                @if($rawStatus === 'pending') bg-amber-100 text-amber-600 
                                @elseif($rawStatus === 'confirmed' || $rawStatus === 'packed') bg-blue-100 text-blue-600
                                @elseif($order->status->value === 'delivered') bg-emerald-100 text-emerald-600
                                @elseif($order->status->value === 'cancelled') bg-rose-100 text-rose-600
                                @else bg-gray-100 text-gray-600 @endif">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-indigo-950">₹{{ number_format($order->total, 2) }}</span>
                                <span class="text-[10px] text-gray-400 font-medium uppercase">{{ $order->payment_method }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 font-medium">
                            {{ $order->created_at->format('M d, Y') }}
                            <p class="text-[10px] text-gray-300">{{ $order->created_at->format('h:i A') }}</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('seller.orders.show', $order) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-xs font-bold hover:bg-indigo-600 hover:text-white transition-all">
                                View Details
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="w-20 h-20 rounded-full bg-gray-50 flex items-center justify-center text-gray-300 mx-auto mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-indigo-950">No orders found</h3>
                            <p class="text-sm text-gray-400 mt-1">Try adjusting your search or filters.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($orders->count() > 0)
        <div class="px-6 py-4 border-t border-gray-50">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

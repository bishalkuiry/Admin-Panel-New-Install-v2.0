@extends('admin.layouts.app')
@section('title', 'Orders')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Orders</h1>
            <p class="text-sm text-gray-500 mt-1">Manage customer orders</p>
        </div>
        <x-permission-btn 
            permission="orders.view" 
            href="#" 
            onclick="alert('Export functionality coming soon or not implemented yet.')"
            class="btn-secondary" 
            label="Export"
            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>'
        />
    </div>


    <form method="GET" action="{{ route('admin.orders.index') }}" class="card">
        <div class="p-4 flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order number or customer..." class="input pl-10">
            </div>
            <select name="status" class="input lg:w-40" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="packed" {{ request('status') === 'packed' ? 'selected' : '' }}>Packed</option>
                <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                <option value="out_for_delivery" {{ request('status') === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="input lg:w-40" placeholder="From">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="input lg:w-40" placeholder="To">
            <button type="submit" class="btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
            <a href="{{ route('admin.orders.index') }}" class="btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Order</th>
                        <th class="table-header">Customer</th>
                        <th class="table-header">Items</th>
                        <th class="table-header">Total</th>
                        <th class="table-header">Payment</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Date</th>
                        <th class="table-header text-center w-24">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <p class="font-mono font-semibold text-gray-900">#{{ $order->order_number }}</p>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm bg-gray-200 text-gray-600 text-xs font-semibold">
                                    {{ substr($order->user->name ?? 'G', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $order->user->name ?? 'Guest' }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->user->email ?? $order->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <span class="badge badge-gray">{{ $order->items_count ?? $order->items->count() }} items</span>
                        </td>
                        <td class="table-cell">
                            <span class="font-semibold text-gray-900">{{ \App\Helpers\CurrencyHelper::format($order->total) }}</span>
                        </td>
                        <td class="table-cell">
                            @if($order->payment_status === 'paid')
                            <span class="badge badge-green">Paid</span>
                            @elseif($order->payment_status === 'pending')
                            <span class="badge badge-orange">Pending</span>
                            @else
                            <span class="badge badge-red">{{ ucfirst($order->payment_status) }}</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @php
                            $statusColors = [
                                'pending' => 'badge-orange',
                                'confirmed' => 'badge-blue',
                                'packed' => 'badge-blue',
                                'picked_up' => 'badge-purple',
                                'out_for_delivery' => 'badge-purple',
                                'delivered' => 'badge-green',
                                'cancelled' => 'badge-red',
                            ];
                            $statusValue = $order->getRawOriginal('status');
                            @endphp
                            <span class="badge {{ $statusColors[$statusValue] ?? 'badge-gray' }}">{{ $order->status instanceof \App\Enums\OrderStatus ? $order->status->label() : ucfirst($statusValue) }}</span>
                        </td>
                        <td class="table-cell">
                            <span class="text-gray-500 text-sm">{{ \App\Helpers\DateHelper::format($order->created_at) }}</span>
                            <p class="text-xs text-gray-400">{{ \App\Helpers\DateHelper::formatTime($order->created_at) }}</p>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <x-permission-btn 
                                    permission="orders.view" 
                                    href="{{ route('admin.orders.show', $order) }}" 
                                    class="action-btn action-btn-view" 
                                    label=""
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
                                />
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="empty-icon mx-auto"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg></div>
                            <p class="font-medium text-gray-900 mt-4">No orders found</p>
                            <p class="text-sm text-gray-500 mt-1">Orders will appear here when customers place them</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $orders->appends(request()->query())->links() }}</div>@endif
    </div>
</div>
@endsection

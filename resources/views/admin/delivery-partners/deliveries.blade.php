@extends('admin.layouts.app')
@section('title', $deliveryPartner->name . ' - Deliveries')
@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.delivery-partners.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-wide">{{ $deliveryPartner->name }}</h1>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mt-1">Delivery History</p>
            </div>
        </div>
    </div>

    @include('admin.delivery-partners._tabs', ['deliveryPartner' => $deliveryPartner])

    <div class="card bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-white border-b border-gray-100 text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Order ID</th>
                        <th class="px-6 py-4">Store</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Revenue</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($deliveries as $order)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-gray-900 text-xs text-primary-600">
                            #{{ $order->order_number }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-gray-900">{{ $order->store->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ $order->user->name ?? 'Guest' }}</div>
                            <div class="text-xs text-gray-500">{{ $order->user->phone ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            <x-currency :amount="$order->delivery_fee" />
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded border border-gray-100 text-[10px] font-bold uppercase tracking-wider bg-gray-50 text-gray-600">
                                {{ $order->status->value }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500">
                            {{ \App\Helpers\DateHelper::format($order->created_at, true) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                             <a href="{{ route('admin.orders.show', $order) }}" class="text-primary-600 hover:text-primary-700 text-[10px] font-black uppercase tracking-wider hover:underline">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-xs text-gray-500 italic">
                            No delivery history found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deliveries->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $deliveries->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

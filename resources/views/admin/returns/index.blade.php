@extends('admin.layouts.app')

@section('title', 'Product Return & Replacement Requests')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Product-Wise Return &amp; Replacement Requests</h1>
            <p class="text-xs text-gray-500 mt-1">Manage customer item return requests, replacement orders, and wallet refunds</p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center justify-between">
        <span>✓ {{ session('success') }}</span>
    </div>
    @endif

    <!-- Filters Bar -->
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.returns.index') }}" class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <input type="text" name="search" class="input text-xs max-w-xs" placeholder="Search return # or reason..." value="{{ request('search') }}">
                <select name="status" class="input text-xs max-w-xs">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="pickup_scheduled" {{ request('status') == 'pickup_scheduled' ? 'selected' : '' }}>Pickup Scheduled</option>
                    <option value="picked_up" {{ request('status') == 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
                <button type="submit" class="btn-primary text-xs py-2 px-4">Filter</button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="p-3">Return #</th>
                        <th class="p-3">Order #</th>
                        <th class="p-3">Customer</th>
                        <th class="p-3">Store</th>
                        <th class="p-3">Type &amp; Reason</th>
                        <th class="p-3 text-right">Refund / Amount</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Date</th>
                        <th class="p-3 text-right">Update Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($returns as $return)
                    <tr class="hover:bg-gray-50/50">
                        <td class="p-3 font-bold font-mono text-indigo-600">{{ $return->return_number ?? ('RET-'.$return->id) }}</td>
                        <td class="p-3 font-semibold text-gray-900">#{{ $return->order_id }}</td>
                        <td class="p-3">
                            <span class="font-semibold text-gray-900 block">{{ $return->user->name ?? 'Customer' }}</span>
                            <span class="text-gray-400 text-[11px] block">{{ $return->user->phone ?? '' }}</span>
                        </td>
                        <td class="p-3 font-medium text-gray-700">{{ $return->store->name ?? 'Main Store' }}</td>
                        <td class="p-3">
                            <span class="badge badge-gray mb-1 inline-block">{{ ucfirst($return->type ?? 'Return') }}</span>
                            <span class="text-gray-600 block text-[11px]">{{ $return->reason }}</span>
                        </td>
                        <td class="p-3 text-right font-bold text-emerald-600">₹{{ number_format($return->refund_amount ?? 0, 2) }}</td>
                        <td class="p-3 text-center">
                            @switch($return->status)
                                @case('pending') <span class="badge badge-orange">Pending</span> @break
                                @case('approved') <span class="badge badge-blue">Approved</span> @break
                                @case('rejected') <span class="badge badge-red">Rejected</span> @break
                                @case('picked_up') <span class="badge badge-blue">Picked Up</span> @break
                                @case('refunded') <span class="badge badge-green">Refunded to Wallet</span> @break
                                @default <span class="badge badge-gray">{{ ucfirst($return->status) }}</span>
                            @endswitch
                        </td>
                        <td class="p-3 text-center text-gray-500">{{ $return->created_at ? $return->created_at->format('d M Y, h:i A') : '' }}</td>
                        <td class="p-3 text-right">
                            <form method="POST" action="{{ route('admin.returns.update', $return->id) }}" class="inline-block">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="input text-xs py-1 px-2 cursor-pointer font-bold">
                                    <option value="pending" {{ $return->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ in_array($return->status, ['approved', 'refunded']) ? 'selected' : '' }}>Approve &amp; Refund</option>
                                    <option value="rejected" {{ $return->status === 'rejected' ? 'selected' : '' }}>Reject</option>
                                    <option value="picked_up" {{ $return->status === 'picked_up' ? 'selected' : '' }}>Mark Picked Up</option>
                                    <option value="refunded" {{ $return->status === 'refunded' ? 'selected' : '' }}>Refunded to Wallet</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-12 text-center text-gray-500">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                            <p class="font-semibold text-sm text-gray-700">No Return or Replacement Requests Found</p>
                        </td>
                    </tr>
                    @endempty
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

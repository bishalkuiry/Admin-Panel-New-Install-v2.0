@extends('admin.layouts.app')

@section('title', 'Seller Ad Purchases & Campaign Analytics')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Seller Campaign Purchases &amp; Analytics</h1>
            <p class="text-xs text-gray-500 mt-1">Review active ad campaigns, approval requests, payment statuses, impressions, and clicks.</p>
        </div>
        <a href="{{ route('admin.ad-plans.index') }}" class="btn-secondary">Back to Ad Plans</a>
    </div>

    <!-- Purchases Table -->
    <div class="card overflow-hidden">
        <div class="card-header"><h3 class="card-title">Purchased Advertisement Campaigns</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold uppercase tracking-wider border-b border-gray-200">
                    <tr>
                        <th class="p-3">Store Name</th>
                        <th class="p-3">Ad Package</th>
                        <th class="p-3">Ad Title / Target</th>
                        <th class="p-3">Amount</th>
                        <th class="p-3">Dates</th>
                        <th class="p-3">Impressions / Clicks</th>
                        <th class="p-3">Payment</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Approval Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($purchases as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-bold text-gray-900">{{ $p->store->name ?? 'Store #'.$p->store_id }}</td>
                        <td class="p-3">
                            <span class="badge badge-purple uppercase font-bold">{{ $p->plan->name ?? 'Custom Ad' }}</span>
                        </td>
                        <td class="p-3">
                            <p class="font-bold text-gray-900">{{ $p->title ?? 'Store Promotion' }}</p>
                            @if($p->product)
                                <p class="text-[10px] text-blue-600 font-bold mt-0.5">Product: {{ $p->product->name }}</p>
                            @endif
                        </td>
                        <td class="p-3 font-bold text-emerald-700">{{ \App\Helpers\CurrencyHelper::format($p->amount_paid) }}</td>
                        <td class="p-3 text-[11px]">
                            @if($p->starts_at)
                                <p>Start: {{ $p->starts_at->format('M d, Y') }}</p>
                                <p class="text-gray-400">End: {{ $p->expires_at ? $p->expires_at->format('M d, Y') : 'N/A' }}</p>
                            @else
                                <span class="text-amber-600 font-bold">Pending Start</span>
                            @endif
                        </td>
                        <td class="p-3 font-bold text-gray-800">
                            👁️ {{ number_format($p->impressions_count) }} | 🖱️ {{ number_format($p->clicks_count) }}
                        </td>
                        <td class="p-3">
                            <span class="badge {{ $p->payment_status === 'paid' ? 'badge-green' : 'badge-orange' }} uppercase font-bold">
                                {{ $p->payment_status }}
                            </span>
                        </td>
                        <td class="p-3">
                            @if($p->status === 'active')
                                <span class="badge badge-green font-bold">Active</span>
                            @elseif($p->status === 'pending')
                                <span class="badge badge-orange font-bold">Awaiting Approval</span>
                            @elseif($p->status === 'rejected')
                                <span class="badge badge-red font-bold">Rejected</span>
                            @else
                                <span class="badge badge-gray font-bold">{{ ucfirst($p->status) }}</span>
                            @endif
                        </td>
                        <td class="p-3 text-right space-y-1">
                            @if($p->status === 'pending')
                                <form action="{{ route('admin.ad-plans.purchases.update-status', $p) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="px-2 py-1 bg-green-600 text-white font-bold rounded text-[10px]">Approve &amp; Activate</button>
                                </form>
                                <form action="{{ route('admin.ad-plans.purchases.update-status', $p) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="px-2 py-1 bg-rose-600 text-white font-bold rounded text-[10px]">Reject</button>
                                </form>
                            @elseif($p->status === 'active')
                                <form action="{{ route('admin.ad-plans.purchases.update-status', $p) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="expired">
                                    <button type="submit" class="px-2 py-1 bg-gray-700 text-white font-bold rounded text-[10px]">Deactivate</button>
                                </form>
                            @else
                                <span class="text-gray-400 text-[10px]">No Actions</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-6 text-center text-gray-400">No seller campaign purchases recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $purchases->links() }}
        </div>
    </div>
</div>
@endsection

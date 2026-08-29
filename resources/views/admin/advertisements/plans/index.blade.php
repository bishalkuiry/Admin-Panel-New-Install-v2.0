@extends('admin.layouts.app')

@section('title', 'Seller Advertisement Plans')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Paid Seller Advertisement Plans</h1>
            <p class="text-xs text-gray-500 mt-1">Manage advertisement packages (Banner Ads, Featured Stores, Sponsored Products) for sellers.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.ad-plans.purchases') }}" class="btn-secondary flex items-center gap-1">
                📊 Seller Purchases &amp; Analytics
            </a>
            <a href="{{ route('admin.ad-plans.create') }}" class="btn-primary flex items-center gap-1">
                + Create Ad Plan
            </a>
        </div>
    </div>

    <!-- Analytics Stats Bar -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card p-4 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200">
            <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Total Ad Revenue</p>
            <h2 class="text-2xl font-black text-emerald-900 mt-1">{{ \App\Helpers\CurrencyHelper::format($totalRevenue) }}</h2>
        </div>
        <div class="card p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200">
            <p class="text-xs font-bold text-blue-800 uppercase tracking-wider">Active Campaigns</p>
            <h2 class="text-2xl font-black text-blue-900 mt-1">{{ $activeCampaigns }} Live Ads</h2>
        </div>
        <div class="card p-4 bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200">
            <p class="text-xs font-bold text-purple-800 uppercase tracking-wider">Configured Ad Plans</p>
            <h2 class="text-2xl font-black text-purple-900 mt-1">{{ $plans->total() }} Packages</h2>
        </div>
    </div>

    <!-- Ad Plans Table -->
    <div class="card overflow-hidden">
        <div class="card-header"><h3 class="card-title">Available Seller Ad Plans</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold uppercase tracking-wider border-b border-gray-200">
                    <tr>
                        <th class="p-3">Plan Name</th>
                        <th class="p-3">Ad Type</th>
                        <th class="p-3">Billing</th>
                        <th class="p-3">Price</th>
                        <th class="p-3">Duration</th>
                        <th class="p-3">Placement</th>
                        <th class="p-3">Limits (Imp / Click)</th>
                        <th class="p-3">Purchases</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($plans as $plan)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-bold text-gray-900">{{ $plan->name }}</td>
                        <td class="p-3">
                            <span class="badge badge-purple uppercase font-bold">{{ str_replace('_', ' ', $plan->ad_type) }}</span>
                        </td>
                        <td class="p-3 uppercase font-bold text-gray-500">{{ $plan->billing_type }}</td>
                        <td class="p-3 font-bold text-emerald-700">{{ \App\Helpers\CurrencyHelper::format($plan->price) }}</td>
                        <td class="p-3">{{ $plan->duration_days }} Days</td>
                        <td class="p-3 font-mono text-[11px] text-indigo-700 font-bold">{{ $plan->placement }}</td>
                        <td class="p-3 font-medium">
                            {{ $plan->impression_limit ? number_format($plan->impression_limit).' Imps' : 'Unlimited' }} /
                            {{ $plan->click_limit ? number_format($plan->click_limit).' Clicks' : 'Unlimited' }}
                        </td>
                        <td class="p-3 font-bold text-blue-700">{{ $plan->purchases_count }} Orders</td>
                        <td class="p-3">
                            <span class="badge {{ $plan->status ? 'badge-green' : 'badge-red' }}">
                                {{ $plan->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="p-3 text-right space-x-2">
                            <a href="{{ route('admin.ad-plans.edit', $plan) }}" class="text-blue-600 hover:underline font-bold">Edit</a>
                            <form action="{{ route('admin.ad-plans.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('Delete this ad plan?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:underline font-bold">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="p-6 text-center text-gray-400">No seller ad plans created yet. Click "+ Create Ad Plan" to set one up.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $plans->links() }}
        </div>
    </div>
</div>
@endsection

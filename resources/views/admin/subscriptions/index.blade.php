@extends('admin.layouts.app')

@section('title', 'Store Subscription Plans')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Store Subscription Plans</h1>
            <p class="text-xs text-gray-500 mt-1">Manage monthly, yearly, and trial subscription packages for vendor stores.</p>
        </div>
        <a href="{{ route('admin.subscriptions.create') }}" class="btn-primary flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            + Create New Plan
        </a>
    </div>

    <!-- Subscription Plans Table -->
    <div class="card overflow-hidden">
        <div class="card-header"><h3 class="card-title">Available Subscription Plans</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold uppercase tracking-wider border-b border-gray-200">
                    <tr>
                        <th class="p-3">Plan Name</th>
                        <th class="p-3">Billing Cycle</th>
                        <th class="p-3">Price</th>
                        <th class="p-3">Duration</th>
                        <th class="p-3">Product Limit</th>
                        <th class="p-3">Commission %</th>
                        <th class="p-3">Subscribers</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($plans as $plan)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-bold text-gray-900">{{ $plan->name }}</td>
                        <td class="p-3 uppercase"><span class="badge badge-blue font-bold">{{ $plan->billing_cycle }}</span></td>
                        <td class="p-3 font-bold text-emerald-700">{{ \App\Helpers\CurrencyHelper::format($plan->price) }}</td>
                        <td class="p-3">{{ $plan->duration_days }} Days</td>
                        <td class="p-3 font-bold text-gray-800">{{ $plan->max_products }} Items</td>
                        <td class="p-3 font-bold text-orange-600">{{ number_format($plan->commission_rate, 1) }}%</td>
                        <td class="p-3 font-bold text-indigo-600">{{ $plan->subscriptions_count }} Stores</td>
                        <td class="p-3">
                            <span class="badge {{ $plan->is_active ? 'badge-green' : 'badge-red' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="p-3 text-right space-x-2">
                            <a href="{{ route('admin.subscriptions.edit', $plan) }}" class="text-blue-600 hover:underline font-bold">Edit</a>
                            <form action="{{ route('admin.subscriptions.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('Delete this plan?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:underline font-bold">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-6 text-center text-gray-400">No subscription plans created yet. Click "+ Create New Plan" to add one.</td>
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

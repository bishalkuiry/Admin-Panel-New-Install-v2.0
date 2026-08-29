@extends('seller.layouts.app')

@section('title', 'Store Subscription Plans')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Store Subscription Plans</h1>
            <p class="text-xs text-gray-500 mt-1">Upgrade your store subscription tier to unlock lower commissions and higher product limits.</p>
        </div>
    </div>

    <!-- Current Active Subscription Banner -->
    @if($activeSubscription)
    <div class="card p-6 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <span class="badge badge-green font-bold text-xs uppercase mb-1">Active Store Plan</span>
                <h2 class="text-2xl font-black text-emerald-950">{{ $activeSubscription->plan->name ?? 'Standard Plan' }}</h2>
                <p class="text-xs text-emerald-800 font-medium mt-1">
                    Valid until: <strong>{{ $activeSubscription->expires_at ? $activeSubscription->expires_at->format('M d, Y') : 'Lifetime' }}</strong>
                    ({{ $activeSubscription->expires_at ? $activeSubscription->expires_at->diffForHumans() : 'Active' }})
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs text-emerald-700 font-bold uppercase">Commission Rate</p>
                <p class="text-2xl font-black text-emerald-900">{{ number_format($activeSubscription->plan->commission_rate ?? 10, 1) }}%</p>
                <p class="text-[11px] text-emerald-800">Max Products: {{ $activeSubscription->plan->max_products ?? 100 }} Items</p>
            </div>
        </div>
    </div>
    @else
    <div class="card p-6 bg-amber-50 border border-amber-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-lg">⚠️</div>
            <div>
                <h3 class="font-bold text-amber-950 text-sm">No Active Paid Subscription Plan</h3>
                <p class="text-xs text-amber-800">Your store is running on default platform commission. Choose a subscription package below to lower your rates.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Available Plans Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
        @php
            $isCurrent = $activeSubscription && $activeSubscription->plan_id == $plan->id;
        @endphp
        <div class="card p-6 flex flex-col justify-between relative {{ $isCurrent ? 'ring-2 ring-emerald-500 bg-emerald-50/20' : '' }}">
            @if($isCurrent)
                <span class="absolute top-3 right-3 badge badge-green font-bold text-[10px]">CURRENT PLAN</span>
            @endif
            <div>
                <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                <p class="text-xs text-gray-500 mt-1 min-h-[36px]">{{ $plan->description ?? 'Full store operational package with premium support.' }}</p>
                
                <div class="my-4">
                    <span class="text-3xl font-black text-gray-900">{{ \App\Helpers\CurrencyHelper::format($plan->price) }}</span>
                    <span class="text-xs text-gray-500 font-bold uppercase">/ {{ $plan->billing_cycle }}</span>
                </div>

                <div class="space-y-2 border-t border-gray-100 pt-4 text-xs text-gray-700">
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Product Limit: <strong>{{ $plan->max_products }} Items</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Commission Fee: <strong>{{ number_format($plan->commission_rate, 1) }}%</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Duration: <strong>{{ $plan->duration_days }} Days</strong></span>
                    </div>
                    @if($plan->auto_renew)
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Auto-Renewal Supported</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                @if($isCurrent)
                    <button disabled class="w-full btn-secondary opacity-60 font-bold cursor-default">Active Subscription</button>
                @else
                    <form action="{{ route('seller.subscription.subscribe', $plan) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full btn-primary font-bold" onclick="return confirm('Subscribe to {{ $plan->name }} for {{ \App\Helpers\CurrencyHelper::format($plan->price) }}?')">
                            Subscribe Now
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

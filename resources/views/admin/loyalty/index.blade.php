@extends('admin.layouts.app')

@section('title', 'Loyalty Analytics')

@section('content')
<div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="min-w-0">
            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">Loyalty Analytics</h1>
            <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">Analyze cashback points and referral performance</p>
        </div>
        <a href="{{ route('admin.settings.loyalty') }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Loyalty Settings</span>
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Points Earned -->
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Lifetime</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Total Points Earned</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_points_earned']) }}</p>
        </div>

        <!-- Points Redeemed -->
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Total Points Redeemed</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_points_redeemed'] * -1) }}</p>
        </div>

        <!-- successful_referrals -->
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Successful Referrals</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['successful_referrals']) }}</p>
        </div>

        <!-- Referral Payouts -->
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Referral Payouts</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1"><x-currency>{{ $stats['referral_rewards_paid'] }}</x-currency></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Point Transactions -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                <h3 class="font-bold text-gray-900">Recent Point Activity</h3>
                <span class="text-xs text-blue-600 font-medium">Last 10 items</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">User</th>
                            <th class="px-4 py-3 font-medium">Points</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentTransactions as $tx)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $tx->userPoints->user->name ?? 'User' }}</div>
                                <div class="text-xs text-gray-500">ID: #{{ $tx->userPoints->user_id }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="{{ $tx->points > 0 ? 'text-green-600' : 'text-red-600' }} font-bold">
                                    {{ $tx->points > 0 ? '+' : '' }}{{ $tx->points }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $tx->type == 'earned' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ ucfirst($tx->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $tx->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 italic">No point transactions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Referrals -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                <h3 class="font-bold text-gray-900">Recent Referrals</h3>
                <span class="text-xs text-blue-600 font-medium">Last 10 items</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Referrer</th>
                            <th class="px-4 py-3 font-medium">Referee</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Reward</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentReferrals as $ref)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $ref->referrer->name ?? 'User' }}</div>
                                <div class="text-xs text-gray-500">Code: {{ $ref->referral_code }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $ref->referee->name ?? 'New User' }}</div>
                                <div class="text-xs text-gray-500">{{ \App\Helpers\DateHelper::format($ref->referee->created_at) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                    @if($ref->status == 'rewarded') bg-green-100 text-green-700 
                                    @elseif($ref->status == 'completed') bg-blue-100 text-blue-700 
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ ucfirst($ref->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs {{ $ref->referrer_reward_paid ? 'text-green-600 font-bold' : 'text-gray-400' }}">
                                    Referrer: {{ $ref->referrer_reward_paid ? 'Paid' : 'Pending' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Referee: {{ $ref->referee_free_deliveries_used }}/{{ $ref->referee_free_deliveries }} Free
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 italic">No referrals found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

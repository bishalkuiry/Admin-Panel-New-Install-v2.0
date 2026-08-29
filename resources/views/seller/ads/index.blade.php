@extends('seller.layouts.app')

@section('title', 'Seller Advertising & Marketing')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Paid Advertising & Promotion</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Boost store visibility on Home Banners, Featured Listings & Sponsored Products</p>
        </div>
        <div class="p-3 bg-orange-50 rounded-xl border border-orange-200 flex items-center gap-3">
            <div>
                <span class="text-[10px] text-gray-500 font-bold block uppercase">Seller Wallet Balance</span>
                <span class="font-extrabold text-orange-600 text-sm">₹{{ number_format($wallet->balance, 2) }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 text-green-700 text-xs rounded-xl font-bold border border-green-200">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-xl font-bold border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <!-- Available Ad Plans -->
    <div class="space-y-3">
        <h3 class="font-bold text-sm text-gray-900">Available Advertising Packages (Admin Pricing)</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($plans as $p)
                <div class="card p-5 space-y-3 flex flex-col justify-between border hover:border-orange-300 transition-all">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-orange-100 text-orange-700">
                                {{ str_replace('_', ' ', $p->ad_type) }}
                            </span>
                            <span class="font-mono text-xs text-gray-500">{{ $p->duration_days }} Days</span>
                        </div>
                        <h4 class="font-bold text-base text-gray-900">{{ $p->name }}</h4>
                        <p class="text-xs text-gray-500">{{ $p->description ?? 'Promote your store to thousands of daily active shoppers.' }}</p>
                    </div>

                    <div class="space-y-3 pt-3 border-t">
                        <div class="flex items-baseline justify-between">
                            <span class="text-xs text-gray-500">Package Price:</span>
                            <span class="font-extrabold text-lg text-orange-600">₹{{ number_format($p->price, 2) }}</span>
                        </div>

                        <form action="{{ route('seller.ads.buy', $p->id) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Buy this Ad Plan for ₹{{ number_format($p->price, 2) }}? Amount will be deducted from your Seller Wallet.')" class="w-full btn btn-primary text-xs py-2.5 font-bold">
                                Purchase Plan (Wallet Deduct)
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full card p-8 text-center text-xs text-gray-400">
                    No active advertising plans available. Please check back later.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Active Store Campaigns -->
    <div class="card overflow-hidden p-4 sm:p-5 space-y-3">
        <h3 class="font-bold text-sm text-gray-900 border-b pb-2">My Active Ad Campaigns</h3>
        <div class="overflow-x-auto">
            <table class="table w-full text-xs min-w-[600px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Campaign Title</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Ad Type</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Price Paid</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Validity</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($myAds as $ad)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-4 font-bold text-gray-900">{{ $ad->title }}</td>
                            <td class="py-3 px-4 uppercase font-bold text-[10px] text-gray-600">{{ str_replace('_', ' ', $ad->ad_type) }}</td>
                            <td class="py-3 px-4 font-bold text-green-700">₹{{ number_format($ad->price, 2) }}</td>
                            <td class="py-3 px-4 font-mono text-[10px] text-gray-500">{{ $ad->starts_at?->format('d/m/Y') }} - {{ $ad->ends_at?->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ad->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $ad->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-400">You have no active ad campaigns. Select a package above to promote your store.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

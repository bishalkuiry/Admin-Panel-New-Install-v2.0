@extends('seller.layouts.app')

@section('title', 'Paid Advertisement Plans')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Promote Store &amp; Products</h1>
            <p class="text-xs text-gray-500 mt-1">Purchase paid advertisement packages to boost visibility on home screen banners, search results, and category slots.</p>
        </div>
    </div>

    <!-- Available Ad Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
        <div class="card p-6 flex flex-col justify-between border-t-4 border-t-purple-600">
            <div>
                <span class="badge badge-purple uppercase text-[10px] font-bold mb-2">{{ str_replace('_', ' ', $plan->ad_type) }}</span>
                <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                <p class="text-xs text-gray-500 mt-1 min-h-[36px]">{{ $plan->description ?? 'Target high intent customers across top placement slots.' }}</p>

                <div class="my-4">
                    <span class="text-3xl font-black text-emerald-700">{{ \App\Helpers\CurrencyHelper::format($plan->price) }}</span>
                    <span class="text-xs text-gray-500 font-bold uppercase">/ {{ $plan->duration_days }} Days</span>
                </div>

                <div class="space-y-2 border-t border-gray-100 pt-4 text-xs text-gray-700">
                    <div class="flex items-center gap-2">
                        <span class="text-purple-600 font-bold">📍</span>
                        <span>Placement: <strong class="font-mono text-indigo-700">{{ $plan->placement }}</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-purple-600 font-bold">👁️</span>
                        <span>Impression Limit: <strong>{{ $plan->impression_limit ? number_format($plan->impression_limit) : 'Unlimited' }}</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-purple-600 font-bold">🖱️</span>
                        <span>Click Guarantee: <strong>{{ $plan->click_limit ? number_format($plan->click_limit) : 'Unlimited' }}</strong></span>
                    </div>
                    @if($plan->banner_size)
                    <div class="flex items-center gap-2">
                        <span class="text-purple-600 font-bold">📐</span>
                        <span>Banner Dimensions: <strong>{{ $plan->banner_size }}</strong></span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Apply / Purchase Form Trigger -->
            <div class="mt-6">
                <form action="{{ route('seller.ad-plans.purchase', $plan) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    @if($plan->ad_type === 'sponsored_product')
                        <div>
                            <label class="label text-[11px]">Select Product to Promote</label>
                            <select name="product_id" required class="input text-xs">
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }} ({{ \App\Helpers\CurrencyHelper::format($prod->price) }})</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div>
                            <label class="label text-[11px]">Upload Promotional Banner Image</label>
                            <input type="file" name="image" accept="image/*" class="input text-xs p-1">
                        </div>
                        <div>
                            <label class="label text-[11px]">Target URL (Optional)</label>
                            <input type="url" name="target_url" placeholder="https://..." class="input text-xs">
                        </div>
                    @endif

                    <button type="submit" class="w-full btn-primary font-bold flex items-center justify-center gap-1" onclick="return confirm('Submit campaign application for {{ $plan->name }}?')">
                        🚀 Buy &amp; Launch Campaign
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Active & Past Purchases History Table -->
    <div class="card overflow-hidden mt-8">
        <div class="card-header"><h3 class="card-title">Your Advertisement Campaigns</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold uppercase tracking-wider border-b border-gray-200">
                    <tr>
                        <th class="p-3">Ad Package</th>
                        <th class="p-3">Title / Item</th>
                        <th class="p-3">Amount</th>
                        <th class="p-3">Dates</th>
                        <th class="p-3">Performance</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($myPurchases as $purch)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-bold text-gray-900">{{ $purch->plan->name ?? 'Custom Ad' }}</td>
                        <td class="p-3">
                            <p class="font-bold text-gray-900">{{ $purch->title }}</p>
                            @if($purch->product)
                                <p class="text-[10px] text-blue-600 font-bold mt-0.5">Product: {{ $purch->product->name }}</p>
                            @endif
                        </td>
                        <td class="p-3 font-bold text-emerald-700">{{ \App\Helpers\CurrencyHelper::format($purch->amount_paid) }}</td>
                        <td class="p-3 text-[11px]">
                            @if($purch->starts_at)
                                <p>Start: {{ $purch->starts_at->format('M d, Y') }}</p>
                                <p class="text-gray-400">End: {{ $purch->expires_at ? $purch->expires_at->format('M d, Y') : 'N/A' }}</p>
                            @else
                                <span class="text-amber-600 font-bold">Awaiting Admin Approval</span>
                            @endif
                        </td>
                        <td class="p-3 font-bold text-gray-800">
                            👁️ {{ number_format($purch->impressions_count) }} Impressions | 🖱️ {{ number_format($purch->clicks_count) }} Clicks
                        </td>
                        <td class="p-3">
                            @if($purch->status === 'active')
                                <span class="badge badge-green font-bold">Live Ad</span>
                            @elseif($purch->status === 'pending')
                                <span class="badge badge-orange font-bold">Pending Review</span>
                            @elseif($purch->status === 'rejected')
                                <span class="badge badge-red font-bold">Rejected</span>
                            @else
                                <span class="badge badge-gray font-bold">{{ ucfirst($purch->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-400">You haven't launched any advertisement campaigns yet. Choose a package above to begin promoting.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

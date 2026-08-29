@extends('admin.layouts.app')
@section('title', $store->name)
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.stores.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $store->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $store->email }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($store->status === \App\Enums\StoreStatus::PENDING)
            <form action="{{ route('admin.stores.approve', $store) }}" method="POST" class="inline">@csrf<button type="submit" class="btn-primary">Approve</button></form>
            <form action="{{ route('admin.stores.reject', $store) }}" method="POST" class="inline">@csrf<button type="submit" class="btn-secondary text-red-600">Reject</button></form>
            @elseif($store->status === \App\Enums\StoreStatus::ACTIVE)
            <form action="{{ route('admin.stores.suspend', $store) }}" method="POST" class="inline">@csrf<button type="submit" class="btn-secondary text-red-600">Suspend</button></form>
            @elseif($store->status === \App\Enums\StoreStatus::SUSPENDED)
            <form action="{{ route('admin.stores.reactivate', $store) }}" method="POST" class="inline">@csrf<button type="submit" class="btn-primary">Reactivate</button></form>
            @endif
        </div>
    </div>

    <div class="flex gap-2 border-b border-gray-200">
        <a href="{{ route('admin.stores.show', $store) }}" class="px-4 py-2 text-sm font-medium {{ !request()->routeIs('admin.stores.staff', 'admin.stores.orders', 'admin.stores.products', 'admin.stores.payouts', 'admin.stores.activity') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Overview</a>
        <a href="{{ route('admin.stores.products', $store) }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.stores.products') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Products</a>
        <a href="{{ route('admin.stores.orders', $store) }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.stores.orders') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Orders</a>
        <a href="{{ route('admin.stores.staff', $store) }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.stores.staff') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Staff</a>
        <a href="{{ route('admin.stores.payouts', $store) }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.stores.payouts') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Payouts</a>
        <a href="{{ route('admin.stores.activity', $store) }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.stores.activity') ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700' }}">Activity</a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card"><p class="stat-value text-xl">{{ $store->products_count ?? 0 }}</p><p class="stat-label">Products</p></div>
        <div class="stat-card"><p class="stat-value text-xl">{{ $store->orders_count ?? 0 }}</p><p class="stat-label">Orders</p></div>
        <div class="stat-card"><p class="stat-value text-xl"><x-currency :amount="$store->total_revenue ?? 0" /></p><p class="stat-label">Revenue</p></div>
        <div class="stat-card"><p class="stat-value text-xl">{{ number_format($store->rating ?? 0, 1) }}</p><p class="stat-label">Rating</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Store Details</h3></div>
                <div class="card-body">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-gray-500">Status</dt><dd class="mt-1">
                            @php $statusValue = $store->status instanceof \App\Enums\StoreStatus ? $store->status->value : $store->status; @endphp
                            <span class="badge badge-{{ $statusValue === 'active' ? 'green' : ($statusValue === 'pending' ? 'orange' : 'red') }}">{{ $store->status instanceof \App\Enums\StoreStatus ? $store->status->label() : ucfirst($statusValue) }}</span>
                        </dd></div>
                        <div><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900 mt-1">{{ $store->phone ?: '—' }}</dd></div>
                        <div><dt class="text-gray-500">Address</dt><dd class="font-medium text-gray-900 mt-1">{{ $store->address ?: '—' }}</dd></div>
                        <div><dt class="text-gray-500">Commission Rate</dt><dd class="font-medium text-gray-900 mt-1">{{ $store->commission_rate ?? 10 }}%</dd></div>
                        <div><dt class="text-gray-500">Created</dt><dd class="font-medium text-gray-900 mt-1">{{ \App\Helpers\DateHelper::format($store->created_at) }}</dd></div>
                        <div><dt class="text-gray-500">Zones</dt><dd class="mt-1">@forelse($store->zones ?? [] as $zone)<span class="badge badge-blue mr-1">{{ $zone->name }}</span>@empty<span class="text-gray-400">None</span>@endforelse</dd></div>
                    </dl>
                </div>
            </div>

            @if($store->description)
            <div class="card">
                <div class="card-header"><h3 class="card-title">Description</h3></div>
                <div class="card-body text-sm text-gray-600">{{ $store->description }}</div>
            </div>
            @endif

            <!-- Store Customer Ratings & Reviews -->
            <div class="card p-5">
                <div class="card-header border-b pb-3 flex items-center justify-between">
                    <h3 class="card-title font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <span>Customer Ratings & Reviews</span>
                    </h3>
                    <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">★ {{ $avgRating }} / 5.0 ({{ $totalReviews }} Reviews)</span>
                </div>
                <div class="card-body pt-4 space-y-4">
                    @forelse($reviews as $rev)
                        <div class="border-b border-gray-100 pb-3 last:border-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-gray-900">{{ $rev->user->name ?? 'Buyer #'.$rev->user_id }}</span>
                                <span class="text-amber-500 text-xs font-bold">{{ str_repeat('★', max(1, min(5, (int)$rev->rating))) }}</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">{{ $rev->comment ?? 'No written comment' }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $rev->product->name ?? 'Product #'.$rev->product_id }} • {{ $rev->created_at ? $rev->created_at->format('M d, Y') : '' }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 py-4 text-center">No reviews submitted for this store yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Owner</h3></div>
                <div class="card-body">
                    <div class="flex items-center gap-3">
                        <div class="avatar bg-gradient-to-br from-orange-400 to-orange-600 text-white font-semibold">{{ substr($store->owner->name ?? 'O', 0, 1) }}</div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $store->owner->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">{{ $store->owner->email ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">KYC Documents</h3></div>
                <div class="card-body">
                    @forelse($store->kycDocuments ?? [] as $doc)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $doc->document_type }}</p>
                            <p class="text-xs text-gray-500">{{ \App\Helpers\DateHelper::format($doc->created_at) }}</p>
                        </div>
                        <span class="badge badge-{{ $doc->status === 'verified' ? 'green' : ($doc->status === 'pending' ? 'orange' : 'red') }}">{{ ucfirst($doc->status) }}</span>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400">No documents uploaded</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

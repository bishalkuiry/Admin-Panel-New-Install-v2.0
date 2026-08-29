@extends('admin.layouts.app')
@section('title', 'Sellers')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Sellers</h1>
            <p class="text-sm text-gray-500 mt-1">Manage store owners and sellers</p>
        </div>
        <x-permission-btn 
            permission="sellers.create" 
            href="{{ route('admin.sellers.create') }}" 
            class="btn-primary" 
            label="Create Seller"
            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>'
        />
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="stat-value text-xl">{{ $stats['total_sellers'] ?? 0 }}</p>
            <p class="stat-label">Total Sellers</p>
        </div>
        <div class="stat-card">
            <p class="stat-value text-xl text-green-600">{{ $stats['active_sellers'] ?? 0 }}</p>
            <p class="stat-label">Active Sellers</p>
        </div>
        <div class="stat-card">
            <p class="stat-value text-xl text-blue-600">{{ $stats['with_stores'] ?? 0 }}</p>
            <p class="stat-label">With Stores</p>
        </div>
        <div class="stat-card">
            <p class="stat-value text-xl text-orange-600">{{ $stats['without_stores'] ?? 0 }}</p>
            <p class="stat-label">Without Stores</p>
        </div>
    </div>

    <form id="filterForm" action="{{ route('admin.sellers.index') }}" class="card">
        <div class="p-4 flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or phone..." class="input pl-10" onkeypress="if(event.key === 'Enter') { event.preventDefault(); applyFilters(); }">
            </div>
            <select name="status" class="input lg:w-40" onchange="applyFilters()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <select name="store_status" class="input lg:w-40" onchange="applyFilters()">
                <option value="">All Store Status</option>
                <option value="active" {{ request('store_status') === 'active' ? 'selected' : '' }}>Active Store</option>
                <option value="pending" {{ request('store_status') === 'pending' ? 'selected' : '' }}>Pending Store</option>
                <option value="suspended" {{ request('store_status') === 'suspended' ? 'selected' : '' }}>Suspended Store</option>
            </select>
            <button type="button" onclick="applyFilters()" class="btn-secondary">Filter</button>
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Seller</th>
                        <th class="table-header">Store</th>
                        <th class="table-header">Wallet Balance</th>
                        <th class="table-header">Orders</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Joined Date</th>
                        <th class="table-header text-center w-24">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sellers as $seller)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm bg-gray-200 text-gray-600 text-xs font-semibold">
                                    {{ substr($seller->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $seller->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $seller->email }}</p>
                                    @if($seller->phone)<p class="text-xs text-gray-500">{{ $seller->phone }}</p>@endif
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            @if($seller->ownedStore)
                            <div>
                                <p class="font-medium text-gray-900">{{ $seller->ownedStore->name }}</p>
                                <p class="text-xs text-gray-500">{{ $seller->ownedStore->city ?? 'N/A' }}</p>
                            </div>
                            @else
                            <span class="text-gray-400 text-sm">No store assigned</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($seller->wallet)
                            <span class="font-semibold text-gray-900"><x-currency :amount="$seller->wallet->balance" /></span>
                            @else
                            <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <span class="badge badge-gray">{{ $seller->orders_count ?? 0 }}</span>
                        </td>
                        <td class="table-cell">
                            @if($seller->is_active)
                            <span class="badge badge-green">Active</span>
                            @else
                            <span class="badge badge-red">Inactive</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <span class="text-gray-500 text-sm">{{ \App\Helpers\DateHelper::format($seller->created_at) }}</span>
                            <p class="text-xs text-gray-400">{{ \App\Helpers\DateHelper::formatTime($seller->created_at) }}</p>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.sellers.show', $seller) }}" class="action-btn action-btn-view">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="empty-icon mx-auto">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <p class="font-medium text-gray-900 mt-4">No sellers found</p>
                            <p class="text-sm text-gray-500 mt-1">Create your first seller to get started</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sellers->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $sellers->appends(request()->query())->links() }}</div>@endif
    </div>
</div>

<script>
function applyFilters() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (const [key, value] of formData.entries()) {
        if (value && value.trim() !== '') {
            params.append(key, value);
        }
    }

    const queryString = params.toString();
    const cleanUrl = form.action.split('?')[0];

    window.location.href = cleanUrl + (queryString ? '?' + queryString : '');
}
</script>
@endsection

@extends('admin.layouts.app')
@section('title', 'Stores')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Stores</h1>
            <p class="text-sm text-gray-500 mt-1">Manage vendor stores</p>
        </div>
        <div class="flex gap-2">
            <x-permission-btn 
                permission="stores.create" 
                href="{{ route('admin.stores.create') }}" 
                class="btn-primary" 
                label="Create Store"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>'
            />
            <x-permission-btn 
                permission="stores.approve" 
                href="{{ route('admin.stores.pending') }}" 
                class="btn-secondary" 
                label="Pending Approvals"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            />
            @if(($pendingCount ?? 0) > 0)<span class="ml-1 px-2 py-0.5 bg-orange-500 text-white text-xs rounded-full">{{ $pendingCount }}</span>@endif
        </div>
    </div>

    <form id="filterForm" action="{{ route('admin.stores.index') }}" class="card">
        <div class="p-4 flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search stores..." class="input pl-10" onkeypress="if(event.key === 'Enter') { event.preventDefault(); applyFilters(); }">
            </div>
            <select name="status" class="input lg:w-40" onchange="applyFilters()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
            <select name="zone" class="input lg:w-40" onchange="applyFilters()">
                <option value="">All Zones</option>
                @foreach($zones ?? [] as $zone)
                <option value="{{ $zone->id }}" {{ request('zone') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                @endforeach
            </select>
            <button type="button" onclick="applyFilters()" class="btn-secondary">Filter</button>
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Store</th>
                        <th class="table-header">Owner</th>
                        <th class="table-header">Products</th>
                        <th class="table-header">Orders</th>
                        <th class="table-header">Revenue</th>
                        <th class="table-header">Status</th>
                        <th class="table-header text-center w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($stores as $store)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                                    @if($store->logo)
                                    <img src="{{ storage_url($store->logo) }}" class="w-full h-full object-cover">
                                    @else
                                    <span class="text-sm font-semibold text-gray-500">{{ substr($store->name, 0, 2) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $store->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $store->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <p class="text-gray-900">{{ $store->owner->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $store->owner->email ?? '' }}</p>
                        </td>
                        <td class="table-cell"><span class="badge badge-blue">{{ $store->products_count ?? 0 }}</span></td>
                        <td class="table-cell"><span class="text-gray-900">{{ $store->orders_count ?? 0 }}</span></td>
                        <td class="table-cell"><span class="font-semibold text-gray-900"><x-currency :amount="$store->total_revenue ?? 0" /></span></td>
                        <td class="table-cell">
                            @php
                            $statusColors = ['active' => 'badge-green', 'pending' => 'badge-orange', 'under_review' => 'badge-blue', 'suspended' => 'badge-red', 'rejected' => 'badge-gray', 'closed' => 'badge-gray'];
                            $statusValue = $store->status instanceof \App\Enums\StoreStatus ? $store->status->value : $store->status;
                            @endphp
                            <span class="badge {{ $statusColors[$statusValue] ?? 'badge-gray' }}">{{ $store->status instanceof \App\Enums\StoreStatus ? $store->status->label() : ucfirst($statusValue) }}</span>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <x-permission-btn 
                                    permission="stores.update" 
                                    href="{{ route('admin.stores.edit', $store) }}" 
                                    class="action-btn action-btn-edit" 
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                                />
                                <a href="{{ route('admin.stores.show', $store) }}" class="action-btn action-btn-view" title="View Store">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="empty-icon mx-auto"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
                            <p class="font-medium text-gray-900 mt-4">No stores found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stores->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $stores->appends(request()->query())->links() }}</div>@endif
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

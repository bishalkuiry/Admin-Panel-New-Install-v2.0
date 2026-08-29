@extends('admin.layouts.app')
@section('title', 'Zones')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Delivery Zones</h1>
            <p class="text-sm text-gray-500 mt-1">Manage delivery zones and coverage areas</p>
        </div>
        <x-permission-btn 
            permission="zones.create" 
            href="{{ route('admin.zones.create') }}" 
            class="btn-primary" 
            label="Add Zone"
            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
        />
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Zone</th>
                        <th class="table-header">Stores</th>
                        <th class="table-header">Delivery Fee</th>
                        <th class="table-header">Min Order</th>
                        <th class="table-header">Status</th>
                        <th class="table-header text-center w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($zones as $zone)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div>
                                <p class="font-medium text-gray-900">{{ $zone->name }}</p>
                                <p class="text-xs text-gray-500">{{ $zone->slug }}</p>
                            </div>
                        </td>
                        <td class="table-cell">
                            @php
                                $hasStoresView = auth()->user()->hasPermission('stores.view');
                            @endphp
                            @if($hasStoresView)
                                <a href="{{ route('admin.zones.stores', $zone) }}" class="badge badge-blue hover:bg-blue-100">{{ $zone->stores_count ?? 0 }} stores</a>
                            @else
                                <span class="badge badge-blue opacity-50 cursor-not-allowed filter grayscale" onclick="alert('You do not have permission to view stores.')">{{ $zone->stores_count ?? 0 }} stores</span>
                            @endif
                        </td>
                        <td class="table-cell font-semibold text-gray-900"><x-currency :amount="$zone->base_delivery_fee ?? 0" /></td>
                        <td class="table-cell text-gray-600"><x-currency :amount="$zone->min_order_amount ?? 0" /></td>
                        <td class="table-cell">
                            @if(auth()->user()->hasPermission('zones.update'))
                                <label class="toggle" data-url="{{ route('admin.zones.toggle', $zone) }}">
                                    <input type="checkbox" {{ $zone->is_active ? 'checked' : '' }} onchange="toggleStatus(this)">
                                    <span class="toggle-slider"></span>
                                </label>
                            @else
                                <label class="toggle opacity-50 cursor-not-allowed filter grayscale" onclick="alert('You do not have permission to update zones.')">
                                    <input type="checkbox" disabled {{ $zone->is_active ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <x-permission-btn 
                                    permission="stores.view" 
                                    href="{{ route('admin.zones.stores', $zone) }}" 
                                    class="action-btn action-btn-view p-0 bg-transparent border-0 shadow-none hover:bg-gray-100" 
                                    label=""
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'
                                />
                                <x-permission-btn 
                                    permission="zones.update" 
                                    href="{{ route('admin.zones.edit', $zone) }}" 
                                    class="action-btn action-btn-edit p-0 bg-transparent border-0 shadow-none hover:bg-gray-100" 
                                    label=""
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                                />
                                <form action="{{ route('admin.zones.destroy', $zone) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-permission-btn 
                                        permission="zones.delete" 
                                        type="submit"
                                        class="action-btn action-btn-delete p-0 bg-transparent border-0 shadow-none hover:bg-gray-100" 
                                        label=""
                                        onclick="return confirm('Delete this zone?')"
                                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                                    />
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="empty-icon mx-auto"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                            <p class="font-medium text-gray-900 mt-4">No zones found</p>
                            <p class="text-sm text-gray-500 mt-1 mb-4">Create your first delivery zone</p>
                            <x-permission-btn 
                                permission="zones.create" 
                                href="{{ route('admin.zones.create') }}" 
                                class="btn-primary" 
                                label="Add Zone"
                            />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($zones->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $zones->links() }}</div>@endif
    </div>
</div>

<script>
function toggleStatus(checkbox) {
    const toggle = checkbox.closest('.toggle');
    const url = toggle.dataset.url;
    toggle.classList.add('loading');
    fetch(url, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }})
    .then(res => res.json()).then(data => { toggle.classList.remove('loading'); if (!data.success) checkbox.checked = !checkbox.checked; })
    .catch(() => { toggle.classList.remove('loading'); checkbox.checked = !checkbox.checked; });
}
</script>
@endsection

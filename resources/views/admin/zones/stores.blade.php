@extends('admin.layouts.app')
@section('title', $zone->name . ' - Stores')
@section('content')
<div class="space-y-5">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.zones.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ $zone->name }} - Stores</h1>
            <p class="text-sm text-gray-500 mt-1">Stores operating in this zone</p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Store</th>
                        <th class="table-header">Owner</th>
                        <th class="table-header">Products</th>
                        <th class="table-header">Status</th>
                        <th class="table-header text-center w-24">Action</th>
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
                                    <p class="text-xs text-gray-500">{{ $store->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">{{ $store->owner->name ?? 'N/A' }}</td>
                        <td class="table-cell"><span class="badge badge-blue">{{ $store->products_count ?? 0 }}</span></td>
                        <td class="table-cell">
                            <span class="badge badge-{{ $store->status->color() }}">{{ $store->status->label() }}</span>
                        </td>
                        <td class="table-cell">
                            <a href="{{ route('admin.stores.show', $store) }}" class="action-btn action-btn-view">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <p class="font-medium text-gray-900">No stores in this zone</p>
                            <p class="text-sm text-gray-500 mt-1">Stores will appear here when assigned to this zone</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stores->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $stores->links() }}</div>@endif
    </div>
</div>
@endsection

@extends('admin.layouts.app')
@section('title', 'Pending Store Approvals')
@section('content')
<div class="space-y-5">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.stores.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Pending Store Approvals</h1>
            <p class="text-sm text-gray-500 mt-1">Review and approve new store applications</p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Store</th>
                        <th class="table-header">Owner</th>
                        <th class="table-header">Applied</th>
                        <th class="table-header">KYC Status</th>
                        <th class="table-header text-center w-48">Action</th>
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
                        <td class="table-cell">
                            <p class="text-gray-900">{{ $store->owner->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $store->owner->phone ?? '' }}</p>
                        </td>
                        <td class="table-cell">
                            <span class="text-gray-500 text-sm">{{ $store->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="table-cell">
                            @if($store->kycDocuments->count() > 0)
                            <span class="badge badge-blue">{{ $store->kycDocuments->count() }} docs</span>
                            @else
                            <span class="badge badge-gray">No docs</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.stores.show', $store) }}" class="btn-secondary btn-sm">Review</a>
                                <form action="{{ route('admin.stores.approve', $store) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-sm" onclick="return confirm('Approve this store?')">Approve</button>
                                </form>
                                <form action="{{ route('admin.stores.reject', $store) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 text-xs font-medium rounded-lg hover:bg-red-100" onclick="return confirm('Reject this store?')">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="empty-icon mx-auto"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                            <p class="font-medium text-gray-900 mt-4">No pending approvals</p>
                            <p class="text-sm text-gray-500 mt-1">All store applications have been reviewed</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stores->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $stores->appends(request()->query())->links() }}</div>@endif
    </div>
</div>
@endsection

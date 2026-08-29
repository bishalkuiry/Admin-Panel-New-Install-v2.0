@extends('admin.layouts.app')
@section('title', $store->name . ' - Staff')
@section('content')
<div class="space-y-5">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.stores.show', $store) }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ $store->name }} - Staff</h1>
            <p class="text-sm text-gray-500 mt-1">Manage store staff members</p>
        </div>
    </div>

    @include('admin.stores._tabs', ['store' => $store])

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Staff Member</th>
                        <th class="table-header">Role</th>
                        <th class="table-header">Permissions</th>
                        <th class="table-header">Added</th>
                        <th class="table-header">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($staff as $member)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm bg-gray-200 text-gray-600 text-xs font-semibold">{{ substr($member->user->name ?? 'S', 0, 1) }}</div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $member->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $member->user->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell"><span class="badge badge-blue">{{ ucfirst($member->role) }}</span></td>
                        <td class="table-cell">
                            @foreach($member->permissions ?? [] as $perm)
                            <span class="badge badge-gray mr-1 mb-1">{{ $perm }}</span>
                            @endforeach
                        </td>
                        <td class="table-cell text-gray-500 text-sm">{{ \App\Helpers\DateHelper::format($member->created_at) }}</td>
                        <td class="table-cell">
                            @if($member->is_active)<span class="badge badge-green">Active</span>@else<span class="badge badge-red">Inactive</span>@endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <p class="font-medium text-gray-900">No staff members</p>
                            <p class="text-sm text-gray-500 mt-1">This store has no staff assigned</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.app')
@section('title', 'Staff Management')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Staff Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage administrators and staff members</p>
        </div>
        <x-permission-btn
            permission="staff.create"
            href="{{ route('admin.staff.create') }}"
            class="btn-primary"
            label="Add Staff"
            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
        />
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">User</th>
                        <th class="table-header">Role</th>
                        <th class="table-header">Permissions</th>
                        <th class="table-header">Status</th>
                        <th class="table-header text-center w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="avatar bg-gradient-to-br from-blue-400 to-blue-600 text-white font-semibold">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 flex items-center gap-1.5">
                                        {{ $user->name }}
                                        @if($user->isSystemAdmin())
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 uppercase tracking-wider">Default</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            @if($user->isSystemAdmin())
                                <span class="badge badge-purple">All Access</span>
                            @else
                                <span class="badge badge-blue">
                                    {{ $user->role instanceof \App\Enums\UserRole ? $user->role->label() : ($user->role instanceof \App\Support\DynamicRole ? $user->role->label() : ucfirst($user->role)) }}
                                </span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($user->isAdmin())
                                <span class="text-xs text-green-600 font-medium">All Permissions</span>
                            @else
                                <span class="text-xs text-gray-500">{{ count($user->getRolePermissions()) }} Permissions</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($user->is_active)
                                <span class="badge badge-green">Active</span>
                            @else
                                <span class="badge badge-red">Inactive</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <x-permission-btn
                                    permission="staff.update"
                                    href="{{ route('admin.staff.edit', $user) }}"
                                    class="action-btn action-btn-edit p-0 bg-transparent border-0 shadow-none"
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                                />
                                @if(auth()->id() != $user->id && !$user->isSystemAdmin())
                                <form action="{{ route('admin.staff.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf @method('DELETE')
                                    <x-permission-btn
                                        permission="staff.delete"
                                        type="submit"
                                        class="action-btn action-btn-delete p-0 bg-transparent border-0 shadow-none"
                                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                                    />
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="empty-icon mx-auto"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
                            <p class="font-medium text-gray-900 mt-4">No staff members found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $users->appends(request()->query())->links() }}</div>@endif
    </div>
</div>
@endsection

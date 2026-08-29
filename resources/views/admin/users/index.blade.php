@extends('admin.layouts.app')
@section('title', 'Customers')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Customers</h1>
            <p class="text-sm text-gray-500 mt-1">Manage customer accounts (Sellers, Delivery Partners, and Staff are separated in dedicated sections)</p>
        </div>
        <x-permission-btn 
            permission="users.create" 
            href="{{ route('admin.users.create') }}" 
            class="btn-primary" 
            label="Add Customer"
            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
        />
    </div>

    {{-- User Category Section Shortcuts --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 pb-3">
        <a href="{{ route('admin.users.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-orange-500 text-white shadow-xs">
            👤 Customers ({{ number_format($stats['customers'] ?? 0) }})
        </a>
        <a href="{{ route('admin.sellers.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200">
            🏪 Sellers / Stores ({{ number_format($stats['sellers'] ?? 0) }})
        </a>
        <a href="{{ route('admin.delivery-partners.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200">
            🛵 Delivery Partners / Drivers ({{ number_format($stats['drivers'] ?? 0) }})
        </a>
        <a href="{{ route('admin.staff.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200">
            🛡️ Staff &amp; Admins ({{ number_format($stats['admins'] ?? 0) }})
        </a>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="card">
        <div class="p-4 flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="input pl-10">
            </div>
            <!-- Role filter removed as we only show customers -->
            <select name="status" class="input lg:w-36" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'role', 'status']))
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">User</th>
                        <th class="table-header">Role</th>
                        <th class="table-header">Orders</th>
                        <th class="table-header">Last Login</th>
                        <th class="table-header">Status</th>
                        <th class="table-header text-center w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="avatar bg-gradient-to-br from-orange-400 to-orange-600 text-white font-semibold">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            @php
                            $roleColors = [
                                'super_admin' => 'badge-red',
                                'admin' => 'badge-red',
                                'store_owner' => 'badge-purple',
                                'store_manager' => 'badge-purple',
                                'store_staff' => 'badge-purple',
                                'customer' => 'badge-blue'
                            ];
                            $roleValue = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role;
                            @endphp
                            <span class="badge {{ $roleColors[$roleValue] ?? 'badge-gray' }}">{{ $user->role instanceof \App\Enums\UserRole ? $user->role->label() : ucfirst($roleValue) }}</span>
                        </td>
                        <td class="table-cell">
                            <span class="text-gray-900">{{ $user->orders_count ?? 0 }}</span>
                        </td>
                        <td class="table-cell">
                            @if($user->last_login_at)
                            <span class="text-gray-500 text-sm">{{ $user->last_login_at->diffForHumans() }}</span>
                            @else
                            <span class="text-gray-400">Never</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if(auth()->user()->hasPermission('users.update'))
                                <label class="toggle" data-url="{{ route('admin.users.toggle-status', $user) }}">
                                    <input type="checkbox" {{ $user->is_active ? 'checked' : '' }} onchange="toggleStatus(this)">
                                    <span class="toggle-slider"></span>
                                </label>
                            @else
                                <label class="toggle opacity-50 cursor-not-allowed filter grayscale" onclick="alert('You do not have permission to update users.')">
                                    <input type="checkbox" {{ $user->is_active ? 'checked' : '' }} disabled>
                                    <span class="toggle-slider"></span>
                                </label>
                            @endif
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <x-permission-btn 
                                    permission="users.view" 
                                    href="{{ route('admin.users.show', $user) }}" 
                                    class="action-btn action-btn-view" 
                                    label=""
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
                                />
                                <x-permission-btn 
                                    permission="users.update" 
                                    href="{{ route('admin.users.edit', $user) }}" 
                                    class="action-btn action-btn-edit" 
                                    label=""
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                                />
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="empty-icon mx-auto"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
                            <p class="font-medium text-gray-900 mt-4">No customers found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $users->appends(request()->query())->links() }}</div>@endif
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

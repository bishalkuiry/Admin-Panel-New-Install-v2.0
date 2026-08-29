@extends('admin.layouts.app')

@section('title', 'Popups')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">User App Popups</h1>
            <p class="text-sm text-gray-500 mt-1">Manage promotional popups, triggers, frequency controls, and targeting rules</p>
        </div>
        <a href="{{ route('admin.popups.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Popup
        </a>
    </div>

    <!-- Filters & Search Bar -->
    <form method="GET" action="{{ route('admin.popups.index') }}" class="card">
        <div class="p-4 flex flex-col lg:flex-row gap-4 items-center">
            <div class="flex-1 w-full relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by popup name..." class="input pl-10">
            </div>

            <select name="status" class="input lg:w-40" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
            </select>

            <select name="trigger" class="input lg:w-48" onchange="this.form.submit()">
                <option value="">All Triggers</option>
                <option value="first_app_open" {{ request('trigger') == 'first_app_open' ? 'selected' : '' }}>First App Open</option>
                <option value="second_app_open" {{ request('trigger') == 'second_app_open' ? 'selected' : '' }}>2nd App Open</option>
                <option value="every_app_open" {{ request('trigger') == 'every_app_open' ? 'selected' : '' }}>Every App Open</option>
                <option value="once_per_day" {{ request('trigger') == 'once_per_day' ? 'selected' : '' }}>Once Per Day</option>
                <option value="once_per_session" {{ request('trigger') == 'once_per_session' ? 'selected' : '' }}>Once Per Session</option>
                <option value="once_per_user" {{ request('trigger') == 'once_per_user' ? 'selected' : '' }}>Once Per User</option>
                <option value="after_x_seconds" {{ request('trigger') == 'after_x_seconds' ? 'selected' : '' }}>After X Seconds</option>
                <option value="after_x_opens" {{ request('trigger') == 'after_x_opens' ? 'selected' : '' }}>After X App Opens</option>
                <option value="on_app_exit" {{ request('trigger') == 'on_app_exit' ? 'selected' : '' }}>On App Exit</option>
                <option value="after_order_completion" {{ request('trigger') == 'after_order_completion' ? 'selected' : '' }}>After Order Completion</option>
                <option value="after_login" {{ request('trigger') == 'after_login' ? 'selected' : '' }}>After Login</option>
                <option value="before_checkout" {{ request('trigger') == 'before_checkout' ? 'selected' : '' }}>Before Checkout</option>
                <option value="cart_amount_reached" {{ request('trigger') == 'cart_amount_reached' ? 'selected' : '' }}>Cart Amount Reached</option>
            </select>

            <button type="submit" class="btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'status', 'trigger']))
                <a href="{{ route('admin.popups.index') }}" class="btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <!-- Data Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Preview</th>
                        <th class="table-header">Name & Action</th>
                        <th class="table-header">Position</th>
                        <th class="table-header">Trigger</th>
                        <th class="table-header">Audience</th>
                        <th class="table-header">Priority</th>
                        <th class="table-header">Status</th>
                        <th class="table-header text-center w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($popups as $popup)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center">
                                @if(!empty($popup->formatted_media_url))
                                    <img src="{{ $popup->formatted_media_url }}" alt="{{ $popup->name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xs text-gray-400">No Image</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900 text-sm">{{ $popup->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Action: <span class="font-mono text-indigo-600">{{ strtoupper($popup->click_action) }}</span></p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                {{ strtoupper(str_replace('_', ' ', $popup->position)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900 font-medium">{{ ucwords(str_replace('_', ' ', $popup->display_trigger)) }}</p>
                            @if(!empty($popup->trigger_value))
                                <p class="text-xs text-gray-500">Value: {{ $popup->trigger_value }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold text-gray-700 uppercase">{{ $popup->audience_type }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-indigo-600 text-sm">{{ $popup->priority }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="togglePopupStatus({{ $popup->id }}, this)" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer
                                {{ $popup->status == 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ ucfirst($popup->status) }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.popups.edit', $popup->id) }}" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('admin.popups.destroy', $popup->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this popup?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 hover:bg-rose-50 rounded-lg text-rose-600 transition" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500 text-sm">No popups found. Click "Create Popup" above to add one.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($popups->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $popups->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function togglePopupStatus(popupId, btn) {
    fetch(`/admin/popups/${popupId}/toggle`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            btn.innerText = data.status.charAt(0).toUpperCase() + data.status.slice(1);
            if(data.status === 'active') {
                btn.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
            } else {
                btn.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200';
            }
        }
    });
}
</script>
@endsection

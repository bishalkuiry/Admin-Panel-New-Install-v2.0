@extends('admin.layouts.app')
@section('title', 'Coupons')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Coupons</h1>
            <p class="text-sm text-gray-500 mt-1">Manage discount coupons and promotions</p>
        </div>
        <x-permission-btn 
            permission="coupons.create" 
            href="{{ route('admin.coupons.create') }}" 
            class="btn-primary" 
            label="Add Coupon"
            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
        />
    </div>

    <form method="GET" action="{{ route('admin.coupons.index') }}" class="card">
        <div class="p-4 flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by code or name..." class="input pl-10">
            </div>
            <select name="type" class="input lg:w-40" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="percentage" {{ request('type') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                <option value="fixed" {{ request('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                <option value="free_delivery" {{ request('type') === 'free_delivery' ? 'selected' : '' }}>Free Delivery</option>
            </select>
            <select name="status" class="input lg:w-36" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'type', 'status']))
            <a href="{{ route('admin.coupons.index') }}" class="btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Coupon</th>
                        <th class="table-header">Type</th>
                        <th class="table-header">Value</th>
                        <th class="table-header">Usage</th>
                        <th class="table-header">Validity</th>
                        <th class="table-header">Status</th>
                        <th class="table-header text-center w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($coupons as $coupon)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div>
                                <p class="font-mono font-semibold text-gray-900">{{ $coupon->code }}</p>
                                <p class="text-xs text-gray-500">{{ $coupon->name }}</p>
                            </div>
                        </td>
                        <td class="table-cell">
                            @if($coupon->type === 'percentage')
                            <span class="badge badge-blue">Percentage</span>
                            @elseif($coupon->type === 'fixed')
                            <span class="badge badge-green">Fixed</span>
                            @else
                            <span class="badge badge-purple">Free Delivery</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <span class="font-semibold text-gray-900">
                                @if($coupon->type === 'percentage')
                                {{ $coupon->value }}%
                                @elseif($coupon->type === 'fixed')
                                ${{ number_format($coupon->value, 2) }}
                                @else
                                —
                                @endif
                            </span>
                            @if($coupon->max_discount)
                            <p class="text-xs text-gray-500">Max: ${{ number_format($coupon->max_discount, 2) }}</p>
                            @endif
                        </td>
                        <td class="table-cell">
                            <span class="text-gray-900">{{ $coupon->usages_count ?? $coupon->used_count }}</span>
                            @if($coupon->usage_limit)
                            <span class="text-gray-400">/ {{ $coupon->usage_limit }}</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($coupon->expires_at)
                                @if($coupon->expires_at->isPast())
                                <span class="badge badge-red">Expired</span>
                                @elseif($coupon->expires_at->diffInDays() <= 7)
                                <span class="badge badge-orange">{{ $coupon->expires_at->diffForHumans() }}</span>
                                @else
                                <span class="text-sm text-gray-600">{{ \App\Helpers\DateHelper::format($coupon->expires_at) }}</span>
                                @endif
                            @else
                            <span class="text-gray-400">No expiry</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <label class="toggle {{ !auth()->user()->hasPermission('coupons.update') ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}" data-url="{{ route('admin.coupons.toggle-status', $coupon) }}">
                                <input type="checkbox" {{ $coupon->is_active ? 'checked' : '' }} onchange="toggleStatus(this)" {{ !auth()->user()->hasPermission('coupons.update') ? 'disabled' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <x-permission-btn 
                                    permission="coupons.update" 
                                    href="{{ route('admin.coupons.edit', $coupon) }}" 
                                    class="action-btn action-btn-edit" 
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                                />
                                <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" id="delete-form-coupon-{{ $coupon->id }}">
                                    @csrf @method('DELETE')
                                    <x-permission-btn 
                                        permission="coupons.delete" 
                                        type="button"
                                        onclick="if(confirm('Delete this coupon?')) document.getElementById('delete-form-coupon-{{ $coupon->id }}').submit()"
                                        class="action-btn action-btn-delete" 
                                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                                    />
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="empty-icon mx-auto"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg></div>
                            <p class="font-medium text-gray-900 mt-4">No coupons found</p>
                            <p class="text-sm text-gray-500 mt-1 mb-4">Create your first discount coupon</p>
                            <a href="{{ route('admin.coupons.create') }}" class="btn-primary">Add Coupon</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $coupons->appends(request()->query())->links() }}</div>@endif
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

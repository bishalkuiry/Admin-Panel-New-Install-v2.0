@extends('admin.layouts.app')
@section('title', 'Delivery Partners')
@section('content')

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-6 bg-white border border-gray-100 rounded-2xl shadow-xs">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center text-white shadow-md shadow-orange-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Delivery Partners</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Manage delivery fleet, review eKYC verification requests, and monitor activity</p>
            </div>
        </div>
        <div>
            <x-permission-btn 
                permission="delivery_partners.create" 
                href="{{ route('admin.delivery-partners.create') }}" 
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white rounded-xl text-xs font-bold shadow-md shadow-orange-500/20 transition-all transform hover:-translate-y-0.5" 
                label="Add Delivery Partner"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>'
            />
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.delivery-partners.index') }}" class="group bg-white p-5 rounded-2xl border border-gray-100 shadow-xs hover:shadow-md hover:border-gray-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Partners</span>
                <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-gray-600 group-hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-black text-gray-900 mt-2">{{ number_format($stats['total_partners'] ?? 0) }}</p>
            <span class="text-[11px] font-semibold text-gray-400 mt-1 inline-block">Registered fleet count</span>
        </a>

        <a href="{{ route('admin.delivery-partners.index', ['status' => 'pending']) }}" class="group bg-amber-50/60 p-5 rounded-2xl border border-amber-200/80 shadow-xs hover:shadow-md hover:border-amber-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">Pending Drivers</span>
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-black text-amber-700 mt-2">{{ number_format($stats['pending_partners'] ?? 0) }}</p>
            <span class="text-[11px] font-bold text-amber-600 mt-1 inline-block">Awaiting eKYC verification</span>
        </a>

        <a href="{{ route('admin.delivery-partners.index', ['status' => 'active']) }}" class="group bg-emerald-50/60 p-5 rounded-2xl border border-emerald-200/80 shadow-xs hover:shadow-md hover:border-emerald-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Active Partners</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-black text-emerald-700 mt-2">{{ number_format($stats['active_partners'] ?? 0) }}</p>
            <span class="text-[11px] font-bold text-emerald-600 mt-1 inline-block">Approved & active on road</span>
        </a>

        <a href="{{ route('admin.delivery-partners.index', ['status' => 'rejected']) }}" class="group bg-rose-50/60 p-5 rounded-2xl border border-rose-200/80 shadow-xs hover:shadow-md hover:border-rose-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-800 uppercase tracking-wider">Rejected Drivers</span>
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center group-hover:bg-rose-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-black text-rose-700 mt-2">{{ number_format($stats['rejected_partners'] ?? 0) }}</p>
            <span class="text-[11px] font-bold text-rose-600 mt-1 inline-block">Registration rejected</span>
        </a>
    </div>

    <!-- Search & Filter Card -->
    <form id="filterForm" action="{{ route('admin.delivery-partners.index') }}" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs">
        <div class="flex flex-col md:flex-row items-center gap-3">
            <div class="flex-1 relative w-full">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by driver name, email address, or phone number..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all" onkeypress="if(event.key === 'Enter') { event.preventDefault(); applyFilters(); }">
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <select name="status" class="w-full md:w-52 py-2.5 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all" onchange="applyFilters()">
                    <option value="">All Status Filter</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="button" onclick="applyFilters()" class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                    Filter
                </button>
            </div>
        </div>
    </form>

    <!-- Table Container -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100 text-[11px] font-extrabold uppercase text-gray-400 tracking-wider">
                        <th class="py-3.5 px-5">Delivery Partner</th>
                        <th class="py-3.5 px-5">Contact Details</th>
                        <th class="py-3.5 px-5">Deliveries</th>
                        <th class="py-3.5 px-5">Verification Status</th>
                        <th class="py-3.5 px-5">Joined Date</th>
                        <th class="py-3.5 px-5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($partners as $partner)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-orange-400 to-amber-500 text-white font-bold flex items-center justify-center shadow-xs overflow-hidden flex-shrink-0">
                                    @if($partner->avatar)
                                        <img src="{{ storage_url($partner->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr($partner->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('admin.delivery-partners.show', $partner) }}" class="font-bold text-gray-900 hover:text-orange-600 transition-colors text-sm">{{ $partner->name }}</a>
                                    <div class="mt-0.5">
                                        @if($partner->kyc_status === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 text-[10px] font-extrabold">Rejected</span>
                                        @elseif(!$partner->is_active || $partner->kyc_status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-extrabold">Pending Review</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-extrabold">Approved</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-5">
                            <div class="flex flex-col gap-0.5">
                                <span class="font-semibold text-gray-900">{{ $partner->email }}</span>
                                <span class="text-[11px] text-gray-500 font-mono">{{ $partner->phone }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-5">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-100">
                                {{ $partner->deliveries_count }} deliveries
                            </span>
                        </td>
                        <td class="py-4 px-5">
                            @if($partner->kyc_status === 'rejected')
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-rose-100 text-rose-800 text-[11px] font-extrabold">REJECTED</span>
                            @elseif(!$partner->is_active || $partner->kyc_status === 'pending')
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] font-extrabold">IN REVIEW</span>
                            @else
                                <form action="{{ route('admin.delivery-partners.toggle-status', $partner) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $partner->is_active ? 0 : 1 }}">
                                    @php
                                        $hasPerm = auth()->user()->hasPermission('delivery_partners.update');
                                        $alertMsg = "You do not have permission to update delivery partners.";
                                    @endphp
                                    <label class="relative inline-flex items-center {{ $hasPerm ? 'cursor-pointer' : 'cursor-not-allowed opacity-50 grayscale' }}" 
                                           @if(!$hasPerm) onclick="alert('{{ $alertMsg }}'); return false;" @endif>
                                        <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $partner->is_active ? 'checked' : '' }} {{ !$hasPerm ? 'disabled' : '' }}>
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                    </label>
                                </form>
                            @endif
                        </td>
                        <td class="py-4 px-5 text-gray-500 font-medium">
                            {{ \App\Helpers\DateHelper::format($partner->created_at) }}
                        </td>
                        <td class="py-4 px-5">
                            <div class="flex items-center justify-center gap-2">
                                @if(!$partner->is_active || $partner->kyc_status === 'pending')
                                <form action="{{ route('admin.delivery-partners.approve', $partner) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" title="Approve Driver" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[11px] transition-colors shadow-xs">
                                        Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.delivery-partners.reject', $partner) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" title="Reject Driver" onclick="return confirm('Reject driver registration?')" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold text-[11px] transition-colors shadow-xs">
                                        Reject
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('admin.delivery-partners.show', $partner) }}" title="View Profile" class="p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('admin.delivery-partners.edit', $partner) }}" title="Edit Details" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('admin.delivery-partners.destroy', $partner) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Driver" onclick="return confirm('Are you sure you want to delete this delivery partner?')" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <p class="font-bold text-gray-900 text-sm">No delivery partners found</p>
                            <p class="text-xs text-gray-500 mt-0.5">Get started by adding your first delivery partner</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($partners->hasPages())<div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">{{ $partners->links() }}</div>@endif
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

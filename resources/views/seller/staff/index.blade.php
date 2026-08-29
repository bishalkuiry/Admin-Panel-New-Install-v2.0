@extends('seller.layouts.app')

@section('title', 'Employee Management')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Employees</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your store's workforce and permissions</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('seller.staff.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-2xl transition-all shadow-lg shadow-indigo-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Employee
            </a>
        </div>
    </div>

    <!-- Stats Row (Optional, adding for premium look) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-6 flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">Total Staff</p>
                <p class="text-xl font-black text-gray-900">{{ $staffMembers->total() }}</p>
            </div>
        </div>
        <div class="card p-6 flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">Active</p>
                <p class="text-xl font-black text-gray-900">{{ $staffMembers->where('is_active', true)->count() }}</p>
            </div>
        </div>
        <div class="card p-6 flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">Roles</p>
                <p class="text-xl font-black text-gray-900">2 Types</p>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-widest">Employee</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-widest">Role & Designation</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-widest">Joined Date</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-widest text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($staffMembers as $staff)
                    <tr class="hover:bg-gray-50/30 transition-colors group">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                    {{ substr($staff->user->name, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $staff->user->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $staff->user->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-col">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $staff->role === 'store_manager' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700' }} uppercase tracking-wider w-fit mb-1">
                                    {{ str_replace('_', ' ', $staff->role) }}
                                </span>
                                <span class="text-xs text-gray-500">{{ $staff->designation ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <span class="text-xs font-medium text-gray-600">{{ $staff->joined_at ? $staff->joined_at->format('M d, Y') : 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-5">
                            <button @click="toggleStatus({{ $staff->id }})" id="status-btn-{{ $staff->id }}" class="relative inline-flex h-5 w-10 flex-shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 focus:outline-none {{ $staff->is_active ? 'bg-emerald-500' : 'bg-gray-200' }}">
                                <span class="sr-only">Toggle status</span>
                                <span id="status-dot-{{ $staff->id }}" class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition duration-200 ease-in-out {{ $staff->is_active ? 'translate-x-5.5' : 'translate-x-1' }}"></span>
                            </button>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('seller.staff.edit', $staff) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('seller.staff.destroy', $staff) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to remove this employee?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mb-4">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <h3 class="text-sm font-bold text-gray-900">No employees found</h3>
                                <p class="text-xs text-gray-500 mt-1 max-w-[200px] mx-auto">Start by adding your first store employee to help manage your business.</p>
                                <a href="{{ route('seller.staff.create') }}" class="mt-4 text-xs font-bold text-indigo-600 hover:text-indigo-800">Add Employee →</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($staffMembers->hasPages())
        <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
            {{ $staffMembers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(id) {
    const btn = document.getElementById(`status-btn-${id}`);
    const dot = document.getElementById(`status-dot-${id}`);
    
    fetch(`/seller/staff/${id}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_active) {
                btn.classList.add('bg-emerald-500');
                btn.classList.remove('bg-gray-200');
                dot.classList.add('translate-x-5.5');
                dot.classList.remove('translate-x-1');
            } else {
                btn.classList.add('bg-gray-200');
                btn.classList.remove('bg-emerald-500');
                dot.classList.add('translate-x-1');
                dot.classList.remove('translate-x-5.5');
            }
        }
    })
    .catch(error => {
        console.error('Error toggling status:', error);
        alert('Failed to update status. Please try again.');
    });
}
</script>
@endpush

@extends('admin.layouts.app')
@section('title', 'Brands')
@section('content')

<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">All Brands</h1>
            <p class="text-sm text-gray-500 mt-1">Manage product brands</p>
        </div>
        <x-permission-btn 
            permission="brands.create" 
            href="{{ route('admin.brands.create') }}" 
            class="btn-primary" 
            label="Add Brand"
            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
        />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="stat-card">
            <p class="stat-value text-xl">{{ $brands->total() }}</p>
            <p class="stat-label">Total Brands</p>
        </div>
        <div class="stat-card">
            <p class="stat-value text-xl text-green-600">{{ $brands->where('is_active', true)->count() }}</p>
            <p class="stat-label">Active Brands</p>
        </div>
        <div class="stat-card">
            <p class="stat-value text-xl text-blue-600">{{ $brands->where('is_featured', true)->count() }}</p>
            <p class="stat-label">Featured Brands</p>
        </div>
    </div>

    <form id="filterForm" action="{{ route('admin.brands.index') }}" class="card">
        <div class="p-4 flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search brands..." class="input pl-10" onkeypress="if(event.key === 'Enter') { event.preventDefault(); applyFilters(); }">
            </div>
            <select name="is_active" class="input lg:w-40" onchange="applyFilters()">
                <option value="">All Status</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="button" onclick="applyFilters()" class="btn-secondary">Filter</button>
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Brand</th>
                        <th class="table-header">Products</th>
                        <th class="table-header">Featured</th>
                        <th class="table-header">Status</th>
                        <th class="table-header text-center w-24">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($brands as $brand)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-sm bg-gray-200 text-gray-600 text-xs font-semibold overflow-hidden">
                                    @if($brand->logo)
                                    <img src="{{ storage_url($brand->logo) }}" class="w-full h-full object-cover">
                                    @else
                                    {{ substr($brand->name, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $brand->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $brand->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <span class="badge badge-blue">{{ $brand->products_count }} products</span>
                        </td>
                        <td class="table-cell">
                             <label class="relative inline-flex items-center cursor-pointer {{ !auth()->user()->hasPermission('brands.update') ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                                <input type="checkbox" onchange="toggleFeatured(this, '{{ route('admin.brands.toggle-featured', $brand) }}')" {{ $brand->is_featured ? 'checked' : '' }} class="sr-only peer" {{ !auth()->user()->hasPermission('brands.update') ? 'disabled' : '' }}>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </td>
                        <td class="table-cell">
                            <label class="relative inline-flex items-center cursor-pointer {{ !auth()->user()->hasPermission('brands.update') ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                                <input type="checkbox" onchange="toggleStatus(this, '{{ route('admin.brands.toggle-status', $brand) }}')" {{ $brand->is_active ? 'checked' : '' }} class="sr-only peer" {{ !auth()->user()->hasPermission('brands.update') ? 'disabled' : '' }}>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-2">
                                <x-permission-btn 
                                    permission="brands.update" 
                                    href="{{ route('admin.brands.edit', $brand) }}" 
                                    class="action-btn action-btn-edit" 
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                                />
                                <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" id="delete-form-brand-{{ $brand->id }}" class="inline">
                                    @csrf @method('DELETE')
                                    <x-permission-btn 
                                        permission="brands.delete" 
                                        type="button"
                                        onclick="if(confirm('Delete this brand?')) document.getElementById('delete-form-brand-{{ $brand->id }}').submit()"
                                        class="action-btn action-btn-delete" 
                                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                                    />
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="empty-icon mx-auto"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg></div>
                            <p class="font-medium text-gray-900 mt-4">No brands found</p>
                            <p class="text-sm text-gray-500 mt-1 mb-4">Get started by adding your first brand</p>
                            <a href="{{ route('admin.brands.create') }}" class="btn-primary">Add Brand</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($brands->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $brands->appends(request()->query())->links() }}</div>@endif
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

function toggleStatus(checkbox, url) {
    fetch(url, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    })
    .then(res => res.json()).then(data => { if (!data.success) checkbox.checked = !checkbox.checked; })
    .catch(() => checkbox.checked = !checkbox.checked);
}

function toggleFeatured(checkbox, url) {
    fetch(url, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    })
    .then(res => res.json()).then(data => { if (!data.success) checkbox.checked = !checkbox.checked; })
    .catch(() => checkbox.checked = !checkbox.checked);
}
</script>
@endsection

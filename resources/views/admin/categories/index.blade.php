@extends('admin.layouts.app')
@section('title', 'Categories')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">All Categories</h1>
            <p class="text-sm text-gray-500 mt-1">Organize your products with categories</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-permission-btn 
                permission="categories.create" 
                href="{{ route('admin.categories.import') }}" 
                class="btn-secondary" 
                label="Import"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>'
            />
            <x-permission-btn 
                permission="categories.view" 
                href="{{ route('admin.categories.export') }}" 
                class="btn-secondary" 
                label="Export"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>'
            />
            <x-permission-btn 
                permission="categories.create" 
                href="{{ route('admin.categories.create') }}" 
                class="btn-primary" 
                label="Add Category"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
            />
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.categories.index') }}" class="card">
        <div class="p-4 flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories by name..." class="input pl-10">
            </div>
            <select name="status" class="input lg:w-36" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Search
            </button>
            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header w-12"><input type="checkbox" class="checkbox"></th>
                        <th class="table-header">Category</th>
                        <th class="table-header">Parent</th>
                        <th class="table-header">Products</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Sort Order</th>
                        <th class="table-header text-center w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($categories as $category)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell"><input type="checkbox" class="checkbox"></td>
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    @if($category->image)
                                    <img src="{{ storage_url($category->image) }}" class="w-full h-full object-cover">
                                    @else
                                    <span class="text-white text-lg font-medium">{{ substr($category->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $category->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $category->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            @if($category->parent)
                            <span class="badge badge-gray">{{ $category->parent->name }}</span>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <span class="badge badge-blue">{{ $category->products_count }} products</span>
                        </td>
                        <td class="table-cell">
                            <label class="toggle {{ !auth()->user()->hasPermission('categories.update') ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}" data-url="{{ route('admin.categories.toggle-status', $category) }}">
                                <input type="checkbox" {{ $category->is_active ? 'checked' : '' }} onchange="toggleStatus(this)" {{ !auth()->user()->hasPermission('categories.update') ? 'disabled' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td class="table-cell text-gray-600">{{ $category->sort_order }}</td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <button class="action-btn action-btn-view">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>

                                <x-permission-btn 
                                    permission="categories.update" 
                                    href="{{ route('admin.categories.edit', $category) }}" 
                                    class="action-btn action-btn-edit" 
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                                />

                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" id="delete-form-category-{{ $category->id }}">
                                    @csrf @method('DELETE')
                                    <x-permission-btn 
                                        permission="categories.delete" 
                                        type="button"
                                        onclick="if(confirm('Delete this category?')) document.getElementById('delete-form-category-{{ $category->id }}').submit()"
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
                            <div class="empty-icon mx-auto">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <p class="font-medium text-gray-900 mt-4">No categories found</p>
                            <p class="text-sm text-gray-500 mt-1 mb-4">Get started by creating your first category</p>
                            <a href="{{ route('admin.categories.create') }}" class="btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add Category
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $categories->appends(request()->query())->links() }}</div>
        @endif
    </div>
</div>

<script>
function toggleStatus(checkbox) {
    const toggle = checkbox.closest('.toggle');
    const url = toggle.dataset.url;
    
    toggle.classList.add('loading');
    
    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        toggle.classList.remove('loading');
        if (!data.success) {
            checkbox.checked = !checkbox.checked;
        }
    })
    .catch(() => {
        toggle.classList.remove('loading');
        checkbox.checked = !checkbox.checked;
    });
}
</script>
@endsection

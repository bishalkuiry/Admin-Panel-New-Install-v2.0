@extends('admin.layouts.app')
@section('title', 'Attributes')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">All Attributes</h1>
            <p class="text-sm text-gray-500 mt-1">Manage product attributes like Size, Color, Material</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-permission-btn 
                permission="attributes.create" 
                href="{{ route('admin.attributes.create') }}"
                class="btn-primary" 
                label="Add Attribute"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
            />
            <x-permission-btn 
                permission="attributes.view" 
                class="btn-secondary" 
                label="Export"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>'
            />
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.attributes.index') }}" class="card">
        <div class="p-4 flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search attributes by name..." class="input pl-10">
            </div>
            <select name="type" class="input lg:w-40" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="select" {{ request('type') == 'select' ? 'selected' : '' }}>Select</option>
                <option value="color" {{ request('type') == 'color' ? 'selected' : '' }}>Color</option>
                <option value="size" {{ request('type') == 'size' ? 'selected' : '' }}>Size</option>
                <option value="text" {{ request('type') == 'text' ? 'selected' : '' }}>Text</option>
            </select>
            <select name="filterable" class="input lg:w-40" onchange="this.form.submit()">
                <option value="">Filterable</option>
                <option value="yes" {{ request('filterable') == 'yes' ? 'selected' : '' }}>Yes</option>
                <option value="no" {{ request('filterable') == 'no' ? 'selected' : '' }}>No</option>
            </select>
            <button type="submit" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Search
            </button>
            @if(request()->hasAny(['search', 'type', 'filterable']))
            <a href="{{ route('admin.attributes.index') }}" class="btn-secondary">Clear</a>
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
                        <th class="table-header w-16">ID</th>
                        <th class="table-header">Attribute</th>
                        <th class="table-header">Type</th>
                        <th class="table-header">Values (with IDs)</th>
                        <th class="table-header">Filterable</th>
                        <th class="table-header">Visible</th>
                        <th class="table-header text-center w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($attributes as $attribute)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell"><input type="checkbox" class="checkbox"></td>
                        <td class="table-cell">
                            <span class="font-mono text-sm font-semibold text-indigo-600 bg-indigo-50 px-2 py-1 rounded">{{ $attribute->id }}</span>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center flex-shrink-0">
                                    @if($attribute->type === 'color')
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                    @elseif($attribute->type === 'size')
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                    @else
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $attribute->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $attribute->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            @php
                                $typeColors = [
                                    'select' => 'badge-blue',
                                    'color' => 'badge-purple',
                                    'size' => 'badge-orange',
                                    'text' => 'badge-gray'
                                ];
                            @endphp
                            <span class="badge {{ $typeColors[$attribute->type] ?? 'badge-gray' }} capitalize">{{ $attribute->type }}</span>
                        </td>
                        <td class="table-cell">
                            <div class="space-y-1">
                                <span class="badge badge-green">{{ $attribute->values_count }} values</span>
                                @if($attribute->values_count > 0)
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($attribute->values->take(6) as $value)
                                    <span class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded" title="Value ID: {{ $value->id }}">
                                        @if($attribute->type === 'color' && $value->color_code)
                                        <span class="w-3 h-3 rounded-full border" style="background-color: {{ $value->color_code }}"></span>
                                        @endif
                                        <span class="font-mono text-indigo-600 font-semibold">#{{ $value->id }}</span>
                                        {{ $value->value }}
                                    </span>
                                    @endforeach
                                    @if($attribute->values_count > 6)
                                    <span class="text-xs text-gray-500">+{{ $attribute->values_count - 6 }} more</span>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="table-cell">
                            @php $hasUpdatePerm = auth()->user()->hasPermission('attributes.update'); @endphp
                            <label class="toggle {{ !$hasUpdatePerm ? 'opacity-50 cursor-not-allowed grayscale' : '' }}" 
                                   data-url="{{ $hasUpdatePerm ? route('admin.attributes.toggle-filterable', $attribute) : '#' }}">
                                <input type="checkbox" {{ $attribute->is_filterable ? 'checked' : '' }} 
                                       {{ $hasUpdatePerm ? 'onchange=toggleStatus(this)' : 'onclick=alert("You_do_not_have_permission_to_edit_attributes.");return_false;' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td class="table-cell">
                            <label class="toggle {{ !$hasUpdatePerm ? 'opacity-50 cursor-not-allowed grayscale' : '' }}" 
                                   data-url="{{ $hasUpdatePerm ? route('admin.attributes.toggle-visible', $attribute) : '#' }}">
                                <input type="checkbox" {{ $attribute->is_visible ? 'checked' : '' }} 
                                       {{ $hasUpdatePerm ? 'onchange=toggleStatus(this)' : 'onclick=alert("You_do_not_have_permission_to_edit_attributes.");return_false;' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <button class="action-btn action-btn-view">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <x-permission-btn 
                                    permission="attributes.update" 
                                    href="{{ route('admin.attributes.edit', $attribute) }}"
                                    class="action-btn action-btn-edit" 
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                                />
                                <form action="{{ route('admin.attributes.destroy', $attribute) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <x-permission-btn 
                                        permission="attributes.delete" 
                                        type="submit"
                                        class="action-btn action-btn-delete" 
                                        onclick="return confirm('Delete this attribute?')"
                                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                                    />
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="empty-icon mx-auto">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            </div>
                            <p class="font-medium text-gray-900 mt-4">No attributes found</p>
                            <p class="text-sm text-gray-500 mt-1 mb-4">Create attributes like Size, Color, Material</p>
                            <x-permission-btn 
                                permission="attributes.create" 
                                href="{{ route('admin.attributes.create') }}"
                                class="btn-primary mx-auto" 
                                label="Add Attribute"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
                            />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attributes->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $attributes->appends(request()->query())->links() }}</div>
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

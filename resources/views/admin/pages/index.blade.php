@extends('admin.layouts.app')

@section('title', 'Static Pages')

@section('content')
<div class="page-header flex justify-between items-center">
    <div>
        <h1 class="page-title">Static Pages</h1>
        <p class="page-subtitle">Manage your website's static pages (About, Privacy Policy, Terms, etc.)</p>
    </div>
    <x-permission-btn 
        permission="pages.create" 
        href="{{ route('admin.pages.create') }}" 
        class="btn-primary" 
        label="Add Page"
        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
    />
</div>

<div class="card">
    <table class="w-full">
        <thead>
            <tr>
                <th class="table-header">Title</th>
                <th class="table-header">Slug</th>
                <th class="table-header">Status</th>
                <th class="table-header">Order</th>
                <th class="table-header text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pages as $page)
                <tr class="table-row">
                    <td class="table-cell">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">{{ $page->icon }}</span>
                            <span class="font-medium text-gray-900">{{ $page->title }}</span>
                        </div>
                    </td>
                    <td class="table-cell">
                        <a href="{{ url($page->slug) }}" target="_blank" class="text-orange-600 hover:text-orange-700 underline font-medium">
                            /{{ $page->slug }}
                        </a>
                    </td>
                    <td class="table-cell">
                        @if($page->is_active)
                            <span class="badge badge-green">Active</span>
                        @else
                            <span class="badge badge-gray">Hidden</span>
                        @endif
                    </td>
                    <td class="table-cell text-gray-500">{{ $page->order }}</td>
                    <td class="table-cell">
                        <div class="flex items-center justify-end gap-2">
                            <form action="{{ route('admin.pages.toggle', $page) }}" method="POST" id="toggle-form-page-{{ $page->id }}" class="inline">
                                @csrf @method('PATCH')
                                <x-permission-btn 
                                    permission="pages.update" 
                                    type="button"
                                    onclick="document.getElementById('toggle-form-page-{{ $page->id }}').submit()"
                                    class="action-btn {{ $page->is_active ? 'action-btn-view' : 'action-btn-view' }}" 
                                    title="{{ $page->is_active ? 'Hide' : 'Show' }}"
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
                                />
                            </form>
                            <x-permission-btn 
                                permission="pages.update" 
                                href="{{ route('admin.pages.edit', $page) }}" 
                                class="action-btn action-btn-edit" 
                                title="Edit"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                            />
                            <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" id="delete-form-page-{{ $page->id }}" class="inline">
                                @csrf @method('DELETE')
                                <x-permission-btn 
                                    permission="pages.delete" 
                                    type="button"
                                    onclick="if(confirm('Delete this page?')) document.getElementById('delete-form-page-{{ $page->id }}').submit()"
                                    class="action-btn action-btn-delete" 
                                    title="Delete"
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                                />
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="table-cell">
                        <div class="empty-state">
                            <div class="empty-icon">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <h3 class="empty-title">No pages yet</h3>
                            <p class="empty-text">Create static pages like About, Privacy Policy, Terms of Service</p>
                            <a href="{{ route('admin.pages.create') }}" class="btn-primary">Create First Page</a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

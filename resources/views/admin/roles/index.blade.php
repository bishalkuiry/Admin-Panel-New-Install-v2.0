@extends('admin.layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Roles & Permissions</h1>
            <p class="text-sm text-gray-500 mt-1">Manage user roles and access control</p>
        </div>
        <x-permission-btn
            permission="roles.create"
            href="{{ route('admin.roles.create') }}"
            class="btn-primary flex items-center gap-2"
            label="Create New Role"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>'
        />
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                        <th class="px-6 py-4">Role Name</th>
                        <th class="px-6 py-4">Slug</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4 text-center">Permissions</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $role->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $role->slug }}</code>
                            </td>
                            <td class="px-6 py-4">
                                @if($role->is_system)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        System
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Custom
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm text-gray-500">{{ count($role->permissions ?? []) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <x-permission-btn
                                    permission="roles.update"
                                    href="{{ route('admin.roles.edit', $role) }}"
                                    class="text-blue-600 hover:text-blue-800 font-medium text-sm p-0 bg-transparent border-0 shadow-none inline-flex items-center"
                                    label="Edit"
                                />
                                
                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this role? This will not remove the role from assigned users but will remove its permission configuration.');">
                                    @csrf
                                    @method('DELETE')
                                    <x-permission-btn
                                        permission="roles.delete"
                                        type="submit"
                                        class="text-red-600 hover:text-red-800 font-medium text-sm p-0 bg-transparent border-0 shadow-none inline-flex items-center"
                                        label="Delete"
                                    />
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No roles found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

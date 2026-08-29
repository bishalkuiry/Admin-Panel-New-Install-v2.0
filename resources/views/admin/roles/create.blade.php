@extends('admin.layouts.app')

@section('title', 'Create New Role')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Create New Role</h1>
            <p class="text-sm text-gray-500 mt-1">Define a new role and assign permissions</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="text-gray-500 hover:text-gray-700 font-medium">
            &larr; Back to Roles
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Role Name</label>
                <input type="text" name="name" id="name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('name') }}" required placeholder="e.g. Support Manager">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($permissions as $group => $perms)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200" x-data="{ 
                            all: [{{ collect($perms)->keys()->map(function($k) { return "'perm_$k'"; })->implode(',') }}],
                            get isAllSelected() {
                                return this.all.every(id => document.getElementById(id).checked);
                            },
                            toggleAll() {
                                const target = !this.isAllSelected;
                                this.all.forEach(id => {
                                    document.getElementById(id).checked = target;
                                });
                            }
                        }">
                            <div class="flex items-center justify-between mb-3 border-b border-gray-200 pb-2">
                                <h4 class="font-semibold text-gray-700">{{ $group }}</h4>
                                <button type="button" @click="toggleAll()" class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-800 transition-colors">
                                    <span x-text="isAllSelected ? 'Deselect All' : 'Select All'"></span>
                                </button>
                            </div>
                            <div class="space-y-2">
                                @foreach($perms as $key => $label)
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="perm_{{ $key }}" name="permissions[]" type="checkbox" value="{{ $key }}" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                            {{ in_array($key, old('permissions', [])) ? 'checked' : '' }}>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="perm_{{ $key }}" class="font-medium text-gray-700 cursor-pointer">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-gray-100">
                <x-permission-btn
                    permission="roles.create"
                    type="submit"
                    class="btn-primary px-6 py-2.5"
                    label="Create Role"
                />
            </div>
        </form>
    </div>
</div>
@endsection

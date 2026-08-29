@extends('admin.layouts.app')
@section('title', $user->role === \App\Enums\UserRole::CUSTOMER || $user->role->value === 'customer' ? 'Edit Customer' : 'Edit User')
@section('content')
<div class="space-y-5">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Edit {{ $user->role === \App\Enums\UserRole::CUSTOMER || $user->role->value === 'customer' ? 'Customer' : 'User' }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $user->name }}</p>
        </div>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="card">
        @csrf @method('PUT')
        <div class="card-body space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input" required>
                </div>
                <div class="form-group">
                    <label class="label">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="label">New Password</label>
                    <input type="password" name="password" class="input">
                    <p class="form-hint">Leave blank to keep current password</p>
                </div>
                <div class="form-group">
                    <label class="label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="input">
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="input">
                </div>
                @php $roleValue = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role; @endphp
                @if($roleValue === 'customer')
                <div class="form-group hidden">
                    <input type="hidden" name="role" value="customer">
                </div>
                @else
                <div class="form-group">
                    <label class="label">Role <span class="text-red-500">*</span></label>
                    <select name="role" class="input" required>
                        <option value="customer" {{ $roleValue === 'customer' ? 'selected' : '' }}>Customer</option>
                        <option value="store_owner" {{ $roleValue === 'store_owner' ? 'selected' : '' }}>Store Owner</option>
                        <option value="store_manager" {{ $roleValue === 'store_manager' ? 'selected' : '' }}>Store Manager</option>
                        <option value="admin" {{ $roleValue === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ $roleValue === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>
                @endif
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="checkbox">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            <x-permission-btn 
                permission="users.update" 
                type="submit"
                class="btn-primary" 
                label="Update {{ $roleValue === 'customer' ? 'Customer' : 'User' }}"
            />
        </div>
    </form>
</div>
@endsection

@extends('admin.layouts.app')
@section('title', 'Edit Staff Member')
@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.staff.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900 tracking-wide">Edit Staff</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $staff->name }}</p>
        </div>
    </div>

    <form action="{{ route('admin.staff.update', $staff) }}" method="POST">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Personal Details -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Personal Information</h3>
                    
                    <div class="space-y-5">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $staff->name) }}" required class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-gray-400">
                            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $staff->email) }}" required class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-gray-400">
                            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Security -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Security</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="password" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-dots" placeholder="Leave blank to keep current">
                            <p class="text-xs text-gray-500 mt-1">Min 8 characters</p>
                            @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-dots">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Role & Status -->
                @if($staff->isSystemAdmin())
                    <input type="hidden" name="role" value="super_admin">
                    <input type="hidden" name="is_active" value="1">
                @else
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Access Control</h3>
                    
                    <div class="space-y-6">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assign Role <span class="text-red-500">*</span></label>
                            @php $staffRole = $staff->role instanceof \App\Enums\UserRole ? $staff->role->value : ($staff->role instanceof \App\Support\DynamicRole ? $staff->role->slug : $staff->role); @endphp
                            <select name="role" required class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer">
                                <option value="">-- Select Role --</option>
                                @forelse($roles as $role)
                                    <option value="{{ $role->slug }}" {{ (old('role', $staffRole) === $role->slug) ? 'selected' : '' }}>{{ $role->name }}</option>
                                @empty
                                    <option value="" disabled>No roles created yet — go to Roles page first</option>
                                @endforelse
                            </select>
                            <p class="text-xs text-gray-500 mt-2">Roles define what actions the user can perform.</p>
                            @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="border-t border-gray-100 pt-6"></div>

                        <!-- Status Toggle -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-900 block">Active Status</label>
                                <p class="text-xs text-gray-500 mt-0.5">Allow login access</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="flex flex-col gap-3">
                    <x-permission-btn
                        permission="staff.update"
                        type="submit"
                        class="w-full py-3 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-widest hover:bg-black transition-all shadow-md active:scale-[0.98] justify-center"
                        label="Update Staff"
                    />
                    <a href="{{ route('admin.staff.index') }}" class="w-full py-3 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-bold uppercase tracking-widest text-center transition-all">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

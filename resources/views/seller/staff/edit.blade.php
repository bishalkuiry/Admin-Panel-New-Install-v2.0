@extends('seller.layouts.app')

@section('title', 'Edit Employee - ' . $staff->user->name)

@section('content')
<div class="space-y-6 animate-fade-in">
    <div class="flex items-center gap-4">
        <a href="{{ route('seller.staff.index') }}" class="p-2 bg-white border border-gray-100 rounded-xl text-gray-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Employee</h1>
            <p class="text-sm text-gray-500 mt-1">Update profile and permissions for {{ $staff->user->name }}</p>
        </div>
    </div>

    <form action="{{ route('seller.staff.update', $staff) }}" method="POST" class="max-w-4xl">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Column: Basic Info -->
            <div class="md:col-span-2 space-y-6">
                <div class="card p-6 sm:p-8 space-y-6">
                    <h2 class="text-xs font-bold text-indigo-600 uppercase tracking-[0.2em] mb-4">Identity & Contact</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label for="name" class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Full Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $staff->user->name) }}" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                            @error('name')<p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-2">
                            <label for="email" class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $staff->user->email) }}" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                            @error('email')<p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-2">
                            <label for="phone" class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $staff->user->phone) }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                            @error('phone')<p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-2">
                            <label for="employee_id" class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Employee ID</label>
                            <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id', $staff->employee_id) }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                            @error('employee_id')<p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-50">
                        <h2 class="text-xs font-bold text-indigo-600 uppercase tracking-[0.2em] mb-4">Security Credentials</h2>
                        <p class="text-[10px] text-gray-400 font-medium mb-4 italic uppercase ml-1">Leave blank to keep existing password</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label for="password" class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">New Password</label>
                                <input type="password" name="password" id="password"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                    placeholder="••••••••">
                                @error('password')<p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p>@enderror
                            </div>

                            <div class="space-y-2">
                                <label for="password_confirmation" class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                    placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings -->
            <div class="space-y-6">
                <div class="card p-6 space-y-6">
                    <h2 class="text-xs font-bold text-indigo-600 uppercase tracking-[0.2em] mb-4">Work Settings</h2>
                    
                    <div class="space-y-2">
                        <label for="role" class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Role</label>
                        <select name="role" id="role" required
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                            <option value="store_staff" {{ old('role', $staff->role) == 'store_staff' ? 'selected' : '' }}>Store Staff</option>
                            <option value="store_manager" {{ old('role', $staff->role) == 'store_manager' ? 'selected' : '' }}>Store Manager</option>
                        </select>
                        @error('role')<p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-2">
                        <label for="designation" class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Designation</label>
                        <input type="text" name="designation" id="designation" value="{{ old('designation', $staff->designation) }}"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    </div>

                    <div class="space-y-2">
                        <label for="salary" class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Monthly Salary</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">₹</span>
                            <input type="number" name="salary" id="salary" value="{{ old('salary', $staff->salary) }}" step="0.01"
                                class="w-full pl-8 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                placeholder="0.00">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-50 flex flex-col gap-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" name="is_active" value="1" {{ $staff->is_active ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </div>
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Account Active</span>
                        </label>
                    </div>
                </div>

                <div class="card p-6 space-y-6">
                    <h2 class="text-xs font-bold text-indigo-600 uppercase tracking-[0.2em] mb-4">Permissions</h2>
                    <p class="text-[10px] text-gray-400 font-medium -mt-2 uppercase tracking-wider">Define what this employee can access</p>
                    
                    <div class="space-y-4">
                        @php
                            $permissionGroups = [
                                'General' => [
                                    'store.view' => 'View Dashboard',
                                    'store.edit' => 'Edit Store Settings',
                                ],
                                'Products' => [
                                    'store.products.view' => 'View Products',
                                    'store.products.manage' => 'Manage Products (Add/Edit/Delete)',
                                ],
                                'Orders' => [
                                    'store.orders.view' => 'View Orders',
                                    'store.orders.manage' => 'Manage Orders (Status/Assign)',
                                ],
                                'Employees' => [
                                    'store.staff.view' => 'View Employees',
                                    'store.staff.manage' => 'Manage Employees',
                                ],
                                'Finance' => [
                                    'store.reports.view' => 'View Reports',
                                    'store.payouts.view' => 'View Payouts',
                                ],
                            ];
                            $userPermissions = $staff->user->permissions ?? [];
                        @endphp

                        @foreach($permissionGroups as $group => $permissions)
                            <div class="space-y-2">
                                <h3 class="text-[10px] font-black text-gray-900 uppercase tracking-widest">{{ $group }}</h3>
                                <div class="grid grid-cols-1 gap-2">
                                    @foreach($permissions as $value => $label)
                                        <label class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer border border-transparent hover:border-gray-100">
                                            <input type="checkbox" name="permissions[]" value="{{ $value }}" {{ in_array($value, $userPermissions) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500/20">
                                            <span class="text-xs font-medium text-gray-700">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-5 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-extrabold rounded-2xl transition-all shadow-lg shadow-indigo-200">
                        Update Employee
                    </button>
                    <a href="{{ route('seller.staff.index') }}" class="px-5 py-3.5 bg-white border border-gray-100 text-gray-400 hover:text-gray-600 text-sm font-bold rounded-2xl transition-all shadow-sm">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

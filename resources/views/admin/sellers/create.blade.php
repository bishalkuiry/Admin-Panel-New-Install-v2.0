@extends('admin.layouts.app')
@section('title', 'Onboard New Seller')
@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.sellers.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900 tracking-wide">Create New Seller</h1>
            <p class="text-sm text-gray-500 mt-1">Add a new seller to your platform</p>
        </div>
    </div>

    <form action="{{ route('admin.sellers.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Seller Details -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Seller Information</h3>
                    
                    <div class="space-y-5">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-gray-400" placeholder="Ex: John Doe">
                            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="form-group">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-gray-400" placeholder="Ex: seller@example.com">
                                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-group">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-gray-400" placeholder="Ex: +1234567890">
                                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Security</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="password" required class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-dots" placeholder="••••••••">
                            <p class="text-xs text-gray-500 mt-1">Min 8 characters</p>
                            @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" required class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-dots" placeholder="••••••••">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Status & Assignment -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Account Settings</h3>
                    
                    <div class="space-y-6">
                        <!-- Status Toggle -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-900 block">Active Status</label>
                                <p class="text-xs text-gray-500 mt-0.5">Allow seller to login immediately</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>

                        <div class="border-t border-gray-100 pt-6"></div>

                        <!-- Store Assignment -->
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assign Store</label>
                            <div class="relative">
                                <select name="store_id" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer">
                                    <option value="">-- Assign Later --</option>
                                    @foreach($availableStores as $store)
                                    <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }} ({{ $store->city }})
                                    </option>
                                    @endforeach
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Optional: You can link a store now or assign one later from the seller details page.</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-3">
                <x-permission-btn 
                        permission="sellers.create" 
                        type="submit"
                        class="w-full py-3 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-widest hover:bg-black transition-all shadow-md active:scale-[0.98]" 
                        label="Create Seller"
                    />
                    <a href="{{ route('admin.sellers.index') }}" class="w-full py-3 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-bold uppercase tracking-widest text-center transition-all">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

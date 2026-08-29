@extends('seller.layouts.app')

@section('title', 'Identity Access: ' . $user->name)

@section('content')
<div class="max-w-screen-2xl mx-auto space-y-8 px-4 sm:px-6 lg:px-8">
    <!-- Compact Page Header -->
    <div class="flex items-center justify-between mb-8 border-b border-gray-100 pb-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('seller.dashboard') }}" class="group w-9 h-9 border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition-all rounded">
                <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 leading-tight">Profile Settings</h1>
                <p class="text-[11px] text-slate-500 font-medium uppercase tracking-widest mt-1">Manage personal identity and security</p>
            </div>
        </div>
    </div>

    <form action="{{ route('seller.profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Personal Business Identity -->
        <div class="bg-slate-900 rounded-lg p-8 shadow-xl flex items-center justify-between mb-10 overflow-hidden relative">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full -mr-16 -mt-16 blur-3xl"></div>
            
            <div class="flex items-center gap-8">
                <div class="w-20 h-20 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 relative z-10">
                    <span class="text-3xl font-bold text-white uppercase">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <div class="relative z-10">
                    <h2 class="text-2xl font-bold text-white tracking-tight">{{ $user->name }}</h2>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="flex items-center gap-1.5 text-[10px] font-bold text-emerald-400 uppercase tracking-widest">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Verified Global Administrator
                        </span>
                    </div>
                </div>
            </div>

            <div class="relative z-10">
                <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-lg text-sm font-bold tracking-tight hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20 active:scale-95">
                    Update Details
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Identity Credentials -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-900">Personal Information</h3>
                </div>
                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Full Legal Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        @error('phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Security Thresholds -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-900">Security & Password</h3>
                </div>
                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Current Password</label>
                        <input type="password" name="current_password"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                            placeholder="Required to change password">
                        @error('current_password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">New Password</label>
                        <input type="password" name="password"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                            placeholder="Leave blank to keep current">
                        @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Confirm New Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    </div>
                    
                    <p class="text-[10px] text-slate-400 italic">Notice: Password fields are optional. Only fill them if you wish to update your security keys.</p>
                </div>
            </div>
        </div>
    </form>

    </div>

</div>
@endsection

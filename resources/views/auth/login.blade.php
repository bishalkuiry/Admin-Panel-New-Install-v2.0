@extends('layouts.base')

@section('title', 'Login - ' . \App\Models\Setting::where('key', 'app_name')->value('value') ?? config('app.name', 'InAllCart'))

@section('content')
@php
    $appSetting = fn(string $key, string $default = '') => \App\Models\Setting::where('key',$key)->value('value') ?? $default;
    $adminAppName = $appSetting('app_name', config('app.name','InAllCart'));
    $adminLogo = $appSetting('admin_login_logo', '') ?: $appSetting('admin_sidebar_logo', '') ?: $appSetting('app_logo', '');
    $adminFavicon = $appSetting('app_favicon', '');
@endphp

<div class="min-h-screen bg-[#f1f5f9] flex items-center justify-center p-4 sm:p-6 lg:p-8">

    <div class="max-w-5xl w-full bg-white rounded-3xl shadow-xl overflow-hidden flex flex-col md:flex-row border border-gray-100">
        
        <!-- Left Side: Branding & Info -->
        <div class="w-full md:w-5/12 bg-white p-10 md:p-12 lg:p-16 flex flex-col items-center justify-center border-r border-gray-100 relative">
            
            <div class="relative z-10 flex flex-col items-center text-center">
                @if($adminLogo)
                    <img src="{{ storage_url($adminLogo) }}" alt="{{ $adminAppName }}" class="max-h-32 w-auto mb-8 object-contain">
                @else
                    <div class="w-20 h-20 bg-gradient-to-br from-orange-400 to-orange-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-orange-500/30">
                        <svg class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                @endif
                
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $adminAppName }}</h1>
                <p class="mt-3 text-sm font-medium text-orange-600 uppercase tracking-wider font-bold">{{ $portalTitle ?? 'Admin & Seller Portal' }}</p>
                <p class="mt-4 text-gray-400 text-sm max-w-xs leading-relaxed">
                    {{ $portalSubtitle ?? 'Manage your store, process orders, and control your application settings all in one place.' }}
                </p>
            </div>
            
            <!-- Subtle background decoration -->
            <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
                <div class="absolute -top-24 -left-24 w-64 h-64 bg-orange-50 rounded-full mix-blend-multiply filter blur-3xl opacity-70"></div>
                <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-blue-50 rounded-full mix-blend-multiply filter blur-3xl opacity-70"></div>
            </div>
        </div>
        
        <!-- Right Side: Login Form -->
        <div class="w-full md:w-7/12 bg-gray-50/30 p-8 md:p-12 lg:p-16 flex flex-col justify-center">
            
            <div class="max-w-md w-full mx-auto">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $portalTitle ?? 'Welcome Back' }}</h2>
                    <p class="text-gray-500 text-sm">Please sign in to access your panel.</p>
                </div>
                
                @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-red-700 text-sm font-medium">{{ session('error') }}</span>
                </div>
                @endif

                @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-green-700 text-sm font-medium">{{ session('success') }}</span>
                </div>
                @endif
                
                @php
                    $isDemoMode = filter_var(env('DEMO_MODE', config('app.demo_mode', true)), FILTER_VALIDATE_BOOLEAN);
                    $defaultEmail = 'admin@inallcart.com';
                    if (request()->is('store*') || request()->is('seller*')) {
                        $defaultEmail = 'vivekstore8@gmail.com';
                    } elseif (request()->is('employee*') || request()->is('staff*')) {
                        $defaultEmail = 'manager@gmail.com';
                    }
                @endphp

                @if($isDemoMode)
                <div class="mb-5 p-3.5 bg-amber-50 border border-amber-200/80 rounded-xl text-xs text-amber-900 shadow-sm space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><strong>Demo Mode:</strong> Pre-filled for {{ $portalTitle ?? 'Portal' }}</span>
                        </div>
                        <span class="px-2 py-0.5 bg-amber-200/70 text-amber-900 rounded font-semibold text-[11px]">{{ $defaultEmail }}</span>
                    </div>

                    <div class="pt-2 border-t border-amber-200/60 flex flex-wrap items-center gap-1.5 text-[11px]">
                        <span class="text-amber-700 font-medium">Quick Credentials:</span>
                        <button type="button" onclick="fillCredentials('admin@inallcart.com', '12345678')" class="px-2 py-1 bg-white hover:bg-amber-100 border border-amber-300 rounded font-medium transition-colors">Admin</button>
                        <button type="button" onclick="fillCredentials('vivekstore8@gmail.com', '12345678')" class="px-2 py-1 bg-white hover:bg-amber-100 border border-amber-300 rounded font-medium transition-colors">Store Panel</button>
                        <button type="button" onclick="fillCredentials('manager@gmail.com', '12345678')" class="px-2 py-1 bg-white hover:bg-amber-100 border border-amber-300 rounded font-medium transition-colors">Employee Panel</button>
                    </div>
                </div>
                @endif
                
                <form method="POST" action="{{ request()->is('store*') || request()->is('seller*') ? route('store.login.post') : (request()->is('employee*') || request()->is('staff*') ? route('employee.login.post') : route('admin.login.post')) }}" class="space-y-5">
                    @csrf
                    
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email', $isDemoMode ? $defaultEmail : '') }}"
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all shadow-sm"
                            placeholder="name@example.com"
                            required
                            autofocus
                        >
                        @error('email')
                        <p class="mt-1.5 text-sm font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <div class="mb-1.5">
                            <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                        </div>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                value="{{ $isDemoMode ? '12345678' : '' }}"
                                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all shadow-sm"
                                placeholder="Enter your password"
                                required
                            >
                            <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 rounded-lg">
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                        <p class="mt-1.5 text-sm font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex items-center py-1">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500 transition-all cursor-pointer">
                            <span class="text-sm font-medium text-gray-600 group-hover:text-gray-900 transition-colors">Remember my email</span>
                        </label>
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full py-3.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl font-bold hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg shadow-orange-500/20 flex items-center justify-center gap-2 mt-2"
                    >
                        <span>Sign In to Dashboard</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>
                
                
                
            </div>
        </div>
    </div>
</div>

<script>
function fillCredentials(email, password) {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    if (emailInput) emailInput.value = email;
    if (passwordInput) passwordInput.value = password;
}

function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const rememberCheckbox = document.getElementById('remember');
    const form = emailInput.closest('form');

    const savedEmail = localStorage.getItem('remembered_admin_email');
    if (savedEmail) {
        emailInput.value = savedEmail;
        rememberCheckbox.checked = true;
    }

    form.addEventListener('submit', function() {
        if (rememberCheckbox.checked) {
            localStorage.setItem('remembered_admin_email', emailInput.value);
        } else {
            localStorage.removeItem('remembered_admin_email');
        }
    });
});
</script>
@endsection

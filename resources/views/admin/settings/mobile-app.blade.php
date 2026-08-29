@extends('admin.layouts.app')

@section('title', 'Mobile App Settings')

@push('styles')
<style>
    .tab-btn { transition: all 0.2s ease; }
    .tab-btn.active { color: var(--primary-600); border-color: var(--primary-600); background: var(--primary-50); }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
</style>
@endpush

@section('content')
@php
    $decimals = (int)($settings['currency_decimal_places'] ?? 0);
    $step = $decimals > 0 ? pow(10, -$decimals) : 1;
@endphp
<div>

    {{-- ── Settings Section Navigation ── --}}
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <nav class="flex min-w-max sm:min-w-0">
                <a href="{{ route('admin.settings.app-settings') }}"
                   class="flex items-center gap-2 px-4 sm:px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap
                          {{ request()->routeIs('admin.settings.app-settings') ? 'border-orange-500 text-orange-600 bg-orange-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Business Settings</span>
                </a>
                <a href="{{ route('admin.settings.mobile-app') }}"
                   class="flex items-center gap-2 px-4 sm:px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap
                          {{ request()->routeIs('admin.settings.mobile-app') ? 'border-orange-500 text-orange-600 bg-orange-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>App Settings</span>
                </a>
                <a href="{{ route('admin.settings.index') }}"
                   class="flex items-center gap-2 px-4 sm:px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap
                          {{ request()->routeIs('admin.settings.index') ? 'border-orange-500 text-orange-600 bg-orange-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>System Settings</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="min-w-0">
            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">Mobile App Settings</h1>
            <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">Configure your mobile application</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.home-header.index') }}" class="btn-secondary text-sm flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                <span class="hidden sm:inline">Home Header</span>
            </a>
            <a href="{{ route('admin.settings.onboarding') }}" class="btn-secondary text-sm flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="hidden sm:inline">Onboarding</span>
            </a>
        </div>
    </div>

    <form action="{{ route('admin.settings.mobile-app.update') }}" method="POST" id="settingsForm" onsubmit="return false;">
        @csrf
        @method('PUT')

        <!-- Horizontal Tabs -->
        <div class="bg-white border border-gray-200 rounded-lg mb-4 overflow-hidden">
            <div class="flex overflow-x-auto scrollbar-hide border-b border-gray-200">
                <button type="button" onclick="switchTab('general')" class="tab-btn active flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="general">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>General</span>
                </button>
                <button type="button" onclick="switchTab('maps')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="maps">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Maps</span>
                </button>
                <button type="button" onclick="switchTab('notifications')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="notifications">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span>Push</span>
                </button>
                <button type="button" onclick="switchTab('firebase')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="firebase">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3.89 15.673L6.255.461A.542.542 0 017.27.289l2.543 4.771zm16.794 3.692l-2.25-14a.54.54 0 00-.919-.295L3.316 19.365l7.856 4.427a1.621 1.621 0 001.588 0zM14.3 7.148l-1.82-3.482a.542.542 0 00-.96 0L3.53 17.984z"/></svg>
                    <span>Firebase</span>
                </button>
                <button type="button" onclick="switchTab('cloudflare')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="cloudflare">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M23.53 11.23a.47.47 0 00-.09-.17c-.38-.45-1.07-.48-1.54-.1l-1.12.89c-.19-.55-.45-1.07-.75-1.57l1.04-.97a.47.47 0 00.08-.18c.2-.56-.1-1.17-.66-1.37l-1.33-.48c.08-.57.12-1.15.12-1.74s-.04-1.17-.12-1.74l1.33-.48c.56-.2.86-.81.66-1.37a.47.47 0 00-.08-.18l-1.04-.97c.3-.5.56-1.02.75-1.57l1.12.89c.47.38 1.16.35 1.54-.1a.47.47 0 00.09-.17c.31-.51.15-1.18-.35-1.49L21.37.5c-.53-.33-1.21-.18-1.55.35l-.75 1.15c-.51-.3-1.05-.56-1.6-.78L17.47.05a.47.47 0 00-.18-.08c-.56-.19-1.17.1-1.37.66l-.48 1.33c-.57-.08-1.15-.12-1.74-.12s-1.17.04-1.74.12l-.48-1.33a.47.47 0 00-.18-.08c-.56-.2-1.17.1-1.37.66l-.01.03-.47 1.17c-.55.22-1.09.48-1.6.78l-.75-1.15c-.34-.53-1.02-.68-1.55-.35l-1.14.71c-.5.31-.66.98-.35 1.49a.47.47 0 00.09.17c.38.45 1.07.48 1.54.1l1.12-.89c.19.55.45 1.07.75 1.57l-1.04.97a.47.47 0 00-.08.18c-.2.56.1 1.17.66 1.37l1.33.48c-.08.57-.12 1.15-.12 1.74s.04 1.17.12 1.74l-1.33.48c-.56.2-.86.81-.66 1.37a.47.47 0 00.08.18l1.04.97c-.3.5-.56 1.02-.75 1.57l-1.12-.89c-.47-.38-1.16-.35-1.54.1a.47.47 0 00-.09.17c-.31.51-.15 1.18.35 1.49l1.14.71c.53.33 1.21.18 1.55-.35l.75-1.15c.51.3 1.05.56 1.6.78l.47 1.17.01.03c.2.56.81.86 1.37.66a.47.47 0 00.18-.08l.48-1.33c.57.08 1.15.12 1.74.12s 1.17-.04 1.74-.12l.48 1.33a.47.47 0 00.18.08c.56.2 1.17-.1 1.37-.66l.47-1.17c.55-.22 1.09-.48 1.6-.78l.75 1.15c.34.53 1.02.68 1.55.35l1.14-.71c.5-.31.66-.98.35-1.49zM12 16.5c-2.48 0-4.5-2.02-4.5-4.5s2.02-4.5 4.5-4.5 4.5 2.02 4.5 4.5-2.02 4.5-4.5 4.5z"/></svg>
                    <span>Cloudflare</span>
                </button>
                <button type="button" onclick="switchTab('payments')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="payments">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span>Payments</span>
                </button>
                <button type="button" onclick="switchTab('currency')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="currency">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Currency & Time</span>
                </button>
                <button type="button" onclick="switchTab('email')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="email">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Email & SMTP</span>
                </button>
                <button type="button" onclick="switchTab('compliance')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="compliance">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Compliance</span>
                </button>
            </div>
        </div>

        <!-- Tab Contents -->
        <div class="space-y-4">

            
            <!-- General Tab -->
            <div id="tab-general" class="tab-content active">
                <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 text-lg">General Settings</h3>
                        <p class="text-sm text-gray-500">Configure core application settings</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Vendor Mode -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Vendor Mode</label>
                            <div class="space-y-3">
                                <!-- Single Vendor -->
                                <label class="cursor-pointer">
                                    <input type="radio" name="vendor_mode" value="single" class="peer sr-only" {{ ($settings['vendor_mode'] ?? 'single') == 'single' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-medium text-gray-900">Single Vendor</span>
                                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded">SIMPLE</span>
                                                </div>
                                                <p class="text-sm text-gray-600">Admin manages all products and orders centrally</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <!-- Multi Vendor -->
                                <label class="cursor-pointer">
                                    <input type="radio" name="vendor_mode" value="multi" class="peer sr-only" {{ ($settings['vendor_mode'] ?? 'single') == 'multi' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-medium text-gray-900">Multi-Vendor</span>
                                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-medium rounded">MARKETPLACE</span>
                                                </div>
                                                <p class="text-sm text-gray-600">Multiple sellers manage their own products and orders</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Order Confirmation Mode -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Order Confirmation</label>
                            <div class="space-y-3">
                                <!-- Store Confirmation -->
                                <label class="cursor-pointer">
                                    <input type="radio" name="order_confirmation_by" value="store" class="peer sr-only" {{ ($settings['order_confirmation_by'] ?? 'store') == 'store' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-medium text-gray-900">Store Confirmation</span>
                                                    <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-medium rounded">DEFAULT</span>
                                                </div>
                                                <p class="text-sm text-gray-600">Stores must accept orders before drivers are assigned</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <!-- Driver Confirmation -->
                                <label class="cursor-pointer">
                                    <input type="radio" name="order_confirmation_by" value="driver" class="peer sr-only" {{ ($settings['order_confirmation_by'] ?? 'store') == 'driver' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-medium text-gray-900">Driver Confirmation</span>
                                                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-medium rounded">FAST</span>
                                                </div>
                                                <p class="text-sm text-gray-600">Drivers accept orders directly (Auto-confirm for store)</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Global Delivery Charge -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Global Delivery Charge</label>
                            <div class="relative max-w-xs">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                </div>
                                <input type="number" name="global_delivery_charge" step="{{ $step }}" min="0" class="w-full h-10 pl-10 pr-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" value="{{ number_format((float)($settings['global_delivery_charge'] ?? 5.99), $decimals, '.', '') }}" placeholder="{{ number_format(5.99, $decimals, '.', '') }}">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Default delivery fee when store-specific charges are not set</p>
                        </div>


                        <!-- Single Store Cart -->
                        <div>
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700">Single Store Cart</label>
                                    <p class="text-xs text-gray-500 mt-1">Restrict customers to add items from only one store at a time</p>
                                </div>
                                <label class="toggle">
                                    <input type="checkbox" name="single_store_cart" value="1" {{ ($settings['single_store_cart'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <p class="text-xs text-gray-600">
                                    <strong>ON:</strong> Users can only add items from ONE store at a time.<br>
                                    <strong>OFF:</strong> Users can add items from MULTIPLE stores (separate orders per store).
                                </p>
                            </div>
                        </div>

                        <!-- Global Service Access (Strict Zone Mode) -->
                        <div>
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700">Global Service Access</label>
                                    <p class="text-xs text-gray-500 mt-1">Control visibility for users outside defined zones</p>
                                </div>
                                <label class="toggle">
                                    <input type="checkbox" name="global_zone_enabled" value="1" {{ ($settings['global_zone_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <p class="text-xs text-gray-600">
                                    <strong>ON (Default):</strong> App is visible to everyone. Delivery availability depends on zones.<br>
                                    <strong>OFF (Strict Mode):</strong> Stores/Products are HIDDEN for users outside active zones.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


<!-- Maps Tab -->
            <div id="tab-maps" class="tab-content">
                <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-900">Map Provider</h3>
                        <p class="text-sm text-gray-500">Choose your preferred map service</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                        <!-- OSM -->
                        <label class="cursor-pointer">
                            <input type="radio" name="map_provider" value="osm" class="peer sr-only" {{ ($settings['map_provider'] ?? 'osm') == 'osm' ? 'checked' : '' }} onchange="toggleGoogleKey()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-300 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900">OpenStreetMap</span>
                                            <span class="px-1.5 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded">FREE</span>
                                        </div>
                                        <p class="text-xs text-gray-500">No API key required</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <!-- Google -->
                        <label class="cursor-pointer">
                            <input type="radio" name="map_provider" value="google" class="peer sr-only" {{ ($settings['map_provider'] ?? 'osm') == 'google' ? 'checked' : '' }} onchange="toggleGoogleKey()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900">Google Maps</span>
                                            <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-xs font-medium rounded">PAID</span>
                                        </div>
                                        <p class="text-xs text-gray-500">Premium features</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <!-- Google API Key -->
                    <div id="google_key_section" class="{{ ($settings['map_provider'] ?? 'osm') == 'google' ? '' : 'hidden' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Google Maps API Key</label>
                        <input type="text" name="google_maps_api_key" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" value="{{ $settings['google_maps_api_key'] ?? '' }}" placeholder="AIzaSy...">
                        <p class="text-xs text-gray-500 mt-1"><a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-primary-600 hover:underline">Get API key</a> from Google Cloud Console</p>
                    </div>
                </div>
            </div>

            <!-- Notifications Tab -->
            <div id="tab-notifications" class="tab-content">
                <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-gray-900">Push Notifications</h3>
                            <p class="text-sm text-gray-500">Configure notification types</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="push_enabled" value="1" id="push_master" {{ ($settings['push_enabled'] ?? '1') == '1' ? 'checked' : '' }} onchange="togglePushOptions()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div id="push_options_section" class="space-y-3">
                        <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">Order Updates</p>
                                    <p class="text-xs text-gray-500">Status & delivery</p>
                                </div>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="push_order_updates" value="1" {{ ($settings['push_order_updates'] ?? '1') == '1' ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </label>
                        <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">Promotions</p>
                                    <p class="text-xs text-gray-500">Deals & offers</p>
                                </div>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="push_promotions" value="1" {{ ($settings['push_promotions'] ?? '1') == '1' ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </label>
                        <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">New Products</p>
                                    <p class="text-xs text-gray-500">Latest arrivals</p>
                                </div>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="push_new_products" value="1" {{ ($settings['push_new_products'] ?? '0') == '1' ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Firebase Tab -->
            <div id="tab-firebase" class="tab-content">
                <!-- Auto-Setup Section -->
                <div class="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-6 mb-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">🚀 One-Click Firebase Setup</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                No Firebase Console or CLI needed! Just upload your service account JSON and click setup.
                                Perfect for CodeCanyon buyers - everything automated!
                            </p>
                            
                            <!-- Setup Status -->
                            <div id="firebase-setup-status" class="mb-4">
                                <div class="flex items-center gap-2 text-sm">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-pulse"></div>
                                    <span class="text-gray-600">Checking configuration...</span>
                                </div>
                            </div>

                            <!-- Auto Setup Button -->
                            <x-permission-btn 
                                permission="mobile_app.manage" 
                                type="button"
                                id="auto-setup-btn"
                                onclick="autoSetupFirebase()" 
                                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white font-medium rounded-lg hover:from-purple-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed" 
                                label="Auto-Setup Firebase Database"
                                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
                            />

                            <!-- Manual Setup Link -->
                            <button type="button" onclick="toggleManualSetup()" class="ml-3 text-sm text-gray-600 hover:text-gray-900 underline">
                                Or setup manually
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Manual Setup Section (Hidden by default) -->
                <div id="manual-setup-section" class="hidden">
                <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-900">Firebase Cloud Messaging (v1 API)</h3>
                        <p class="text-sm text-gray-500">Configure server-side push notifications using OAuth 2.0</p>
                    </div>

                    <!-- Info Banner -->
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium mb-1">Using Firebase Cloud Messaging HTTP v1 API</p>
                                <p class="text-xs">This implementation uses OAuth 2.0 with service account credentials for enhanced security. The legacy server key method is deprecated.</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Project ID -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Firebase Project ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="firebase_project_id" id="firebase_project_id" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" value="{{ $settings['firebase_project_id'] ?? '' }}" placeholder="your-project-id" required>
                            <p class="text-xs text-gray-500 mt-1">Found in Firebase Console → Project Settings → General</p>
                        </div>

                        <!-- Service Account JSON -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Service Account JSON <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <textarea name="firebase_service_account" id="firebase_service_account" rows="8" class="w-full px-3 py-2 text-xs font-mono border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" placeholder='{"type": "service_account", "project_id": "...", "private_key_id": "...", ...}'>{{ $settings['firebase_service_account'] ?? '' }}</textarea>
                                <div class="absolute top-2 right-2">
                                    <button type="button" onclick="document.getElementById('firebase_json_file').click()" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        Upload JSON
                                    </button>
                                    <input type="file" id="firebase_json_file" accept=".json" class="hidden" onchange="loadFirebaseJSON(this)">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Download from Firebase Console → Project Settings → Service Accounts → Generate New Private Key
                            </p>
                        </div>

                        <!-- Database URL (Auto-detected) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Database URL (Auto-detected)
                            </label>
                            <input type="text" name="firebase_database_url" id="firebase_database_url" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg bg-gray-50" value="{{ $settings['firebase_database_url'] ?? '' }}" placeholder="https://your-project.firebaseio.com" readonly>
                            <p class="text-xs text-gray-500 mt-1">This will be auto-filled after setup</p>
                        </div>

                        <!-- Web SDK Config (for admin live chat) -->
                        <div class="border border-blue-100 bg-blue-50 rounded-lg p-4 space-y-3">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                <span class="text-sm font-semibold text-blue-900">Web SDK Config (for Admin Live Chat)</span>
                            </div>
                            <p class="text-xs text-blue-700">Found in Firebase Console → Project Settings → General → Your apps → Web app → SDK setup and configuration.</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">API Key <span class="text-red-500">*</span></label>
                                    <input type="text" name="firebase_api_key" id="firebase_api_key"
                                           class="w-full h-9 px-3 text-xs border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                           value="{{ $settings['firebase_api_key'] ?? '' }}"
                                           placeholder="AIzaSy...">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">App ID <span class="text-red-500">*</span></label>
                                    <input type="text" name="firebase_app_id" id="firebase_app_id"
                                           class="w-full h-9 px-3 text-xs border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                           value="{{ $settings['firebase_app_id'] ?? '' }}"
                                           placeholder="1:123456789:web:abc123">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Messaging Sender ID</label>
                                    <input type="text" name="firebase_messaging_sender_id" id="firebase_messaging_sender_id"
                                           class="w-full h-9 px-3 text-xs border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                           value="{{ $settings['firebase_messaging_sender_id'] ?? '' }}"
                                           placeholder="123456789">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Auth Domain</label>
                                    <input type="text" name="firebase_auth_domain" id="firebase_auth_domain"
                                           class="w-full h-9 px-3 text-xs border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                           value="{{ $settings['firebase_auth_domain'] ?? '' }}"
                                           placeholder="your-project.firebaseapp.com">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Storage Bucket</label>
                                    <input type="text" name="firebase_storage_bucket" id="firebase_storage_bucket"
                                           class="w-full h-9 px-3 text-xs border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                           value="{{ $settings['firebase_storage_bucket'] ?? '' }}"
                                           placeholder="your-project.firebasestorage.app">
                                </div>
                            </div>
                            <p class="text-xs text-blue-600 mt-1">💡 Tip: You can also paste your full <code class="bg-blue-100 px-1 rounded">firebaseConfig</code> JS object and click <button type="button" onclick="parseFirebaseWebConfig()" class="underline font-medium">Auto-Parse</button>.</p>
                            <textarea id="firebase_web_config_paste" rows="3" placeholder='Paste firebaseConfig object here, e.g. { apiKey: "...", appId: "...", ... }'
                                      class="w-full px-3 py-2 text-xs font-mono border border-blue-200 rounded-lg focus:border-primary-500 outline-none bg-white"></textarea>
                        </div>

                        <!-- Configuration Status -->
                        <div id="firebase_status" class="hidden p-3 rounded-lg">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium"></span>
                            </div>
                        </div>

                        <!-- Setup Instructions -->
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <p class="text-sm font-medium text-gray-900 mb-2">Setup Instructions:</p>
                            <ol class="text-xs text-gray-600 space-y-1 list-decimal list-inside">
                                <li>Go to <a href="https://console.firebase.google.com" target="_blank" class="text-primary-600 hover:underline">Firebase Console</a></li>
                                <li>Select your project → Project Settings (gear icon)</li>
                                <li>Copy the <strong>Project ID</strong> and paste above</li>
                                <li>Go to <strong>Service Accounts</strong> tab</li>
                                <li>Click <strong>"Generate New Private Key"</strong></li>
                                <li>Upload the downloaded JSON file or paste its content</li>
                                <li>Save settings and test notifications</li>
                            </ol>
                        </div>

                        <!-- Test Notification Button -->
                        <div class="flex items-center gap-3">
                            <x-permission-btn 
                                permission="mobile_app.manage" 
                                type="button"
                                onclick="testFirebaseNotification()" 
                                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500" 
                                label="Test Notification"
                                icon='<svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>'
                            />
                            <span id="test_notification_result" class="text-sm"></span>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <!-- Payments Tab -->
            <div id="tab-payments" class="tab-content">
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 text-lg">Payment Gateway Settings</h3>
                    <p class="text-sm text-gray-500">Configure payment methods for your mobile app</p>
                </div>

                <!-- Payment Gateway Accordion -->
                <div class="space-y-3">
                    <!-- Razorpay -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="togglePaymentGateway('razorpay')" class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M22.436 0l-11.91 7.773-1.174 4.276 6.625-4.297L11.65 24h4.391l6.395-24z"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">Razorpay</p>
                                    <p class="text-xs text-gray-500">UPI, Cards, Wallets & Net Banking</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($paymentConfig['gateways']['razorpay']['enabled'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ($paymentConfig['gateways']['razorpay']['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform gateway-chevron" data-gateway="razorpay" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>
                        <div id="gateway-razorpay" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            @include('admin.settings.partials._razorpay-form')
                        </div>
                    </div>

                    <!-- Stripe -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="togglePaymentGateway('stripe')" class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 24 24"><path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.594-7.305h.003z"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">Stripe</p>
                                    <p class="text-xs text-gray-500">Global payment processing</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($paymentConfig['gateways']['stripe']['enabled'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ($paymentConfig['gateways']['stripe']['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform gateway-chevron" data-gateway="stripe" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>
                        <div id="gateway-stripe" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            @include('admin.settings.partials._stripe-form')
                        </div>
                    </div>

                    <!-- PayPal -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="togglePaymentGateway('paypal')" class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106z"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">PayPal</p>
                                    <p class="text-xs text-gray-500">Worldwide payment solution</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($paymentConfig['gateways']['paypal']['enabled'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ($paymentConfig['gateways']['paypal']['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform gateway-chevron" data-gateway="paypal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="gateway-paypal" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            @include('admin.settings.partials._paypal-form')
                        </div>
                    </div>

                    <!-- Paystack -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="togglePaymentGateway('paystack')" class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-cyan-600" fill="currentColor" viewBox="0 0 24 24"><path d="M0 12.5C0 9.462 2.462 7 5.5 7h13C21.538 7 24 9.462 24 12.5S21.538 18 18.5 18h-13C2.462 18 0 15.538 0 12.5z"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">Paystack</p>
                                    <p class="text-xs text-gray-500">Payments across Africa</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($paymentConfig['gateways']['paystack']['enabled'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ($paymentConfig['gateways']['paystack']['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform gateway-chevron" data-gateway="paystack" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="gateway-paystack" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            @include('admin.settings.partials._paystack-form')
                        </div>
                    </div>

                    <!-- PhonePe -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="togglePaymentGateway('phonepe')" class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 24 24"><path d="M19.5 3h-15A1.5 1.5 0 003 4.5v15A1.5 1.5 0 004.5 21h15a1.5 1.5 0 001.5-1.5v-15A1.5 1.5 0 0019.5 3z"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">PhonePe</p>
                                    <p class="text-xs text-gray-500">UPI & Digital payments (India)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($paymentConfig['gateways']['phonepe']['enabled'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ($paymentConfig['gateways']['phonepe']['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform gateway-chevron" data-gateway="phonepe" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="gateway-phonepe" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            @include('admin.settings.partials._phonepe-form')
                        </div>
                    </div>

                    <!-- Paytm -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="togglePaymentGateway('paytm')" class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">Paytm</p>
                                    <p class="text-xs text-gray-500">Wallet & UPI (India)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($paymentConfig['gateways']['paytm']['enabled'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ($paymentConfig['gateways']['paytm']['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform gateway-chevron" data-gateway="paytm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="gateway-paytm" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            @include('admin.settings.partials._paytm-form')
                        </div>
                    </div>

                    <!-- Flutterwave -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="togglePaymentGateway('flutterwave')" class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">Flutterwave</p>
                                    <p class="text-xs text-gray-500">African payment gateway</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($paymentConfig['gateways']['flutterwave']['enabled'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ($paymentConfig['gateways']['flutterwave']['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform gateway-chevron" data-gateway="flutterwave" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="gateway-flutterwave" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            @include('admin.settings.partials._flutterwave-form')
                        </div>
                    </div>

                    <!-- Midtrans -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="togglePaymentGateway('midtrans')" class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-teal-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">Midtrans</p>
                                    <p class="text-xs text-gray-500">Indonesia payment gateway</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($paymentConfig['gateways']['midtrans']['enabled'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ($paymentConfig['gateways']['midtrans']['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform gateway-chevron" data-gateway="midtrans" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="gateway-midtrans" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            @include('admin.settings.partials._midtrans-form')
                        </div>
                    </div>

                    <!-- MyFatoorah -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="togglePaymentGateway('myfatoorah')" class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">MyFatoorah</p>
                                    <p class="text-xs text-gray-500">Middle East payments</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($paymentConfig['gateways']['myfatoorah']['enabled'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ($paymentConfig['gateways']['myfatoorah']['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform gateway-chevron" data-gateway="myfatoorah" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="gateway-myfatoorah" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            @include('admin.settings.partials._myfatoorah-form')
                        </div>
                    </div>

                    <!-- Instamojo -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="togglePaymentGateway('instamojo')" class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-pink-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">Instamojo</p>
                                    <p class="text-xs text-gray-500">India payment gateway</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($paymentConfig['gateways']['instamojo']['enabled'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ($paymentConfig['gateways']['instamojo']['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform gateway-chevron" data-gateway="instamojo" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="gateway-instamojo" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            @include('admin.settings.partials._instamojo-form')
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-3 bg-gray-50 text-gray-500 font-medium">Offline Payment Methods</span>
                        </div>
                    </div>

                    <!-- Cash on Delivery -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="togglePaymentGateway('cod')" class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">Cash on Delivery</p>
                                    <p class="text-xs text-gray-500">Pay with cash upon delivery</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($paymentConfig['cod']['enabled'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ($paymentConfig['cod']['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform gateway-chevron" data-gateway="cod" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="gateway-cod" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            @include('admin.settings.partials._cod-form')
                        </div>
                    </div>

                    <!-- Bank Transfer -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="togglePaymentGateway('bank-transfer')" class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">Bank Transfer</p>
                                    <p class="text-xs text-gray-500">Direct bank deposit</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($paymentConfig['bank_transfer']['enabled'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ($paymentConfig['bank_transfer']['enabled'] ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform gateway-chevron" data-gateway="bank-transfer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="gateway-bank-transfer" class="hidden border-t border-gray-200 p-4 bg-gray-50">
                            @include('admin.settings.partials._bank-transfer-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currency & Timezone Tab -->
        <div id="tab-currency" class="tab-content">
            @include('admin.settings.partials._currency-timezone-form')
        </div>

        <!-- Cloudflare Tab -->
        <div id="tab-cloudflare" class="tab-content">
            <div class="bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 rounded-lg p-6 mb-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">☁️ One-Click Cloudflare Optimization</h3>
                            @if(!empty($settings['cloudflare_zone_id']))
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                    ✓ Connected
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            Automatically configure Cloudflare "Cache Everything" rules for your API.
                            This will make your app handle millions of hits by offloading traffic to Cloudflare's global edge network.
                        </p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cloudflare Email</label>
                                <input type="email" id="cf_email" name="cloudflare_email" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg outline-none focus:border-orange-500" value="{{ $settings['cloudflare_email'] ?? '' }}" placeholder="user@example.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cloudflare Global API Key</label>
                                <input type="password" id="cf_api_key" name="cloudflare_api_key" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg outline-none focus:border-orange-500" value="{{ $settings['cloudflare_api_key'] ?? '' }}" placeholder="••••••••••••••••">
                                <p class="text-[10px] text-gray-500 mt-1">Found in Cloudflare Dashboard → My Profile → API Tokens → Global API Key</p>
                            </div>
                        </div>

                        <!-- Auto Setup Button -->
                        <div class="flex flex-wrap gap-4">
                            <x-permission-btn 
                                permission="mobile_app.manage" 
                                type="button"
                                id="cf-setup-btn"
                                onclick="autoSetupCloudflare()" 
                                class="px-6 py-3 bg-gradient-to-r from-orange-600 to-red-600 text-white font-medium rounded-lg hover:from-orange-700 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all disabled:opacity-50" 
                                label="Optimize Cloudflare Now"
                                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
                            />
                            

                        </div>

                        <div class="mt-6 flex items-start gap-2 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                            <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs text-blue-700">
                                <strong>Safety Mode:</strong> We only cache public paths (products, categories, config). 
                                Personal user data (cart, profile, orders) is always served directly from your server to ensure absolute privacy and security.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="font-semibold text-gray-900 mb-4">What this does:</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <div class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold text-sm">1</div>
                        <p class="font-medium text-sm text-gray-900">Auto-Detection</p>
                        <p class="text-xs text-gray-500">Detects your domain's Zone ID in Cloudflare automatically based on your current host.</p>
                    </div>
                    <div class="space-y-2">
                        <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm">2</div>
                        <p class="font-medium text-sm text-gray-900">Cache Rules</p>
                        <p class="text-xs text-gray-500">Creates a "Cache Everything" rule for /api/v1/* to allow edge caching of your content.</p>
                    </div>
                    <div class="space-y-2">
                        <div class="w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold text-sm">3</div>
                        <p class="font-medium text-sm text-gray-900">TTL Sync</p>
                        <p class="text-xs text-gray-500">Syncs the edge cache TTL to match our Internal Shield (60 seconds) for real-time updates.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance Tab -->
        <div id="tab-compliance" class="tab-content">
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Compliance & Privacy</h3>
                        <p class="text-sm text-gray-600">
                            Configure user-facing policies and platform compliance requirements here.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="space-y-6">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Account Deletion</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Account Deletion Grace Period (Days)</label>
                                <div class="relative max-w-xs">
                                    <input type="number" name="auth_account_deletion_grace_period" min="0" max="365" class="w-full h-10 px-3 pr-12 text-sm border border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none" value="{{ $settings['auth_account_deletion_grace_period'] ?? '7' }}">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-xs text-indigo-600 font-semibold">Days</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">
                                    Number of days before an account is permanently deleted after the user initiates deletion. 
                                    During this period, logging back in will restore the account.
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <div class="flex items-center gap-3 mb-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-sm font-medium text-gray-900">Why this matters?</span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed">
                                    Both Apple App Store and Google Play Store require a clear and accessible account deletion process. Providing a grace period helps prevent accidental data loss and reduces support tickets for account recovery.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Settings Tab -->
        <div id="tab-email" class="tab-content">
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Email Configuration & Templates</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Configure SMTP settings, sending domains, and manage email content templates for system notifications.
                        </p>
                        
                        <div class="flex flex-wrap gap-4">
                            <a href="{{ route('admin.settings.email') }}" class="btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Configure SMTP Settings
                            </a>
                            <a href="{{ route('admin.email-templates.index') }}" class="btn-secondary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                Manage Email Templates
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div id="globalSaveButton" class="mt-4">
            <x-permission-btn 
                permission="mobile_app.manage" 
                type="button"
                onclick="handleSaveClick()" 
                class="btn-primary inline-flex items-center gap-2" 
                label="Save Changes"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
            />
        </div>
    </form>
</div>

@push('scripts')
<script>
function handleSaveClick() {
    const form = document.getElementById('settingsForm');
    const activeTab = sessionStorage.getItem('activeTab') || 'general';
    const formData = new FormData(form);
    const filteredData = new FormData();
    
    // Always include CSRF token and method
    filteredData.append('_token', formData.get('_token'));
    filteredData.append('_method', formData.get('_method'));
    
    // Only include fields relevant to the active tab
    if (activeTab === 'general') {
        // General settings fields
        const vendorMode = form.querySelector('input[name="vendor_mode"]:checked');
        if (vendorMode) {
            filteredData.append('vendor_mode', vendorMode.value);
        }
        const deliveryCharge = formData.get('global_delivery_charge');
        if (deliveryCharge !== null) {
            filteredData.append('global_delivery_charge', deliveryCharge);
        }
        const singleStoreCart = form.querySelector('input[name="single_store_cart"]');
        filteredData.append('single_store_cart', singleStoreCart && singleStoreCart.checked ? '1' : '0');
        
        const orderConfirmationBy = form.querySelector('input[name="order_confirmation_by"]:checked');
        if (orderConfirmationBy) {
            filteredData.append('order_confirmation_by', orderConfirmationBy.value);
        }

        const globalZoneEnabled = form.querySelector('input[name="global_zone_enabled"]');
        filteredData.append('global_zone_enabled', globalZoneEnabled && globalZoneEnabled.checked ? '1' : '0');
    } else if (activeTab === 'currency') {
        // Currency and timezone fields
        const currencyFields = [
            'multi_currency_enabled', 'default_currency', 'currency_symbol_position',
            'currency_decimal_places', 'currency_thousand_separator',
            'auto_exchange_rate_update', 'exchange_rate_update_frequency',
            'system_timezone', 'date_format', 'time_format'
        ];
        
        currencyFields.forEach(field => {
            // Handle checkboxes
            if (field === 'multi_currency_enabled' || field === 'auto_exchange_rate_update') {
                const checkbox = form.querySelector(`input[name="${field}"]`);
                filteredData.append(field, checkbox && checkbox.checked ? '1' : '0');
            }
            // Handle radio buttons
            else if (field === 'currency_symbol_position' || field === 'currency_thousand_separator' || field === 'time_format') {
                const radio = form.querySelector(`input[name="${field}"]:checked`);
                if (radio) {
                    filteredData.append(field, radio.value);
                }
            }
            // Handle regular inputs and selects
            else {
                const value = formData.get(field);
                if (value !== null) {
                    filteredData.append(field, value);
                }
            }
        });
    } else if (activeTab === 'onboarding') {
        const onboardingEnabled = formData.get('onboarding_enabled');
        filteredData.append('onboarding_enabled', onboardingEnabled || '0');
    } else if (activeTab === 'maps') {
        const mapProvider = formData.get('map_provider');
        filteredData.append('map_provider', mapProvider || 'osm');
        const googleKey = formData.get('google_maps_api_key');
        if (googleKey) {
            filteredData.append('google_maps_api_key', googleKey);
        }
    } else if (activeTab === 'notifications') {
        const pushFields = ['push_enabled', 'push_order_updates', 'push_promotions', 'push_new_products'];
        pushFields.forEach(field => {
            const value = formData.get(field);
            filteredData.append(field, value || '0');
        });
    } else if (activeTab === 'firebase') {
        const serverKey = formData.get('firebase_server_key');
        const projectId = formData.get('firebase_project_id');
        const serviceAccount = formData.get('firebase_service_account');
        filteredData.append('firebase_server_key', serverKey || '');
        filteredData.append('firebase_project_id', projectId || '');
        filteredData.append('firebase_service_account', serviceAccount || '');
    } else if (activeTab === 'cloudflare') {
        const cfEmail = formData.get('cloudflare_email');
        const cfApiKey = formData.get('cloudflare_api_key');
        filteredData.append('cloudflare_email', cfEmail || '');
        filteredData.append('cloudflare_api_key', cfApiKey || '');
    } else if (activeTab === 'compliance') {
        const gracePeriod = formData.get('auth_account_deletion_grace_period');
        if (gracePeriod !== null) {
            filteredData.append('auth_account_deletion_grace_period', gracePeriod);
        }
    } else if (activeTab === 'payments') {
        // Don't submit anything for payments tab - gateways are saved individually
        showNotification('Payment gateways are saved automatically when you toggle them', 'info');
        return;
    }
    
    // Show loading state
    const submitButton = document.querySelector('#globalSaveButton button');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';
    }
    
    // Debug: Log what we're sending
    console.log('Active tab:', activeTab);
    console.log('Sending data:');
    for (let pair of filteredData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Submit filtered data
    fetch(form.action, {
        method: 'POST',
        body: filteredData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Server error response:', text);
                throw new Error(`Server returned ${response.status}: ${response.statusText}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success response:', data);
        showNotification(data.message || 'Settings saved successfully', 'success');
        // Reload page after 1 second to show updated values
        setTimeout(() => {
            location.reload();
        }, 1000);
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to save settings: ' + error.message, 'error');
        // Re-enable button
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save Changes';
        }
    });
}

function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.add('text-gray-500');
    });
    
    // Show selected tab
    document.getElementById('tab-' + tabName).classList.add('active');
    document.querySelector('[data-tab="' + tabName + '"]').classList.add('active');
    document.querySelector('[data-tab="' + tabName + '"]').classList.remove('text-gray-500');
    
    // Store current active tab
    sessionStorage.setItem('activeTab', tabName);
    
    // Hide/show save button based on tab
    const saveButton = document.getElementById('globalSaveButton');
    
    // Hide save button on payments, onboarding, and email tabs
    if (tabName === 'payments' || tabName === 'onboarding' || tabName === 'email') {
        if (saveButton) saveButton.style.display = 'none';
    } else {
        // Show save button on other tabs (general, maps, notifications, firebase, currency)
        if (saveButton) saveButton.style.display = 'block';
    }
}

function togglePaymentGateway(gateway) {
    const content = document.getElementById('gateway-' + gateway);
    const chevron = document.querySelector('.gateway-chevron[data-gateway="' + gateway + '"]');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

function toggleGoogleKey() {
    const section = document.getElementById('google_key_section');
    const isGoogle = document.querySelector('input[name="map_provider"][value="google"]').checked;
    section.classList.toggle('hidden', !isGoogle);
}

function togglePushOptions() {
    const section = document.getElementById('push_options_section');
    const isEnabled = document.getElementById('push_master').checked;
    section.style.opacity = isEnabled ? '1' : '0.5';
    section.style.pointerEvents = isEnabled ? 'auto' : 'none';
}

// Real-time payment gateway toggle - saves entire gateway form
function updatePaymentGatewayStatus(gateway, checkbox) {
    const isEnabled = checkbox.checked;
    const statusBadge = document.querySelector(`[data-gateway="${gateway}"]`).closest('button').querySelector('.rounded-full');
    
    // Show loading state
    const originalText = statusBadge.textContent.trim();
    const originalClass = statusBadge.className;
    statusBadge.textContent = 'Saving...';
    statusBadge.className = 'px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700';
    
    // Prepare form data - collect ALL fields for this gateway
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PUT');
    
    const prefix = gateway.replaceAll('-', '_') + '_';
    const form = document.getElementById('settingsForm');
    const inputs = form.querySelectorAll(`[name^="${prefix}"]`);
    
    // Collect all fields for this gateway
    inputs.forEach(input => {
        if (input.type === 'checkbox') {
            formData.append(input.name, input.checked ? '1' : '0');
        } else if (input.type === 'radio') {
            if (input.checked) {
                formData.append(input.name, input.value);
            }
        } else if (input.type === 'select-one') {
            formData.append(input.name, input.value);
        } else {
            formData.append(input.name, input.value || '');
        }
    });
    
    // Send AJAX request
    fetch('{{ route("admin.settings.mobile-app.update") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Update badge
        if (isEnabled) {
            statusBadge.textContent = 'Active';
            statusBadge.className = 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700';
        } else {
            statusBadge.textContent = 'Inactive';
            statusBadge.className = 'px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600';
        }
        
        // Show success notification
        const gatewayName = gateway.charAt(0).toUpperCase() + gateway.slice(1).replace('-', ' ');
        showNotification(data.message || `${gatewayName} settings saved successfully`, 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert checkbox state
        checkbox.checked = !isEnabled;
        statusBadge.textContent = originalText;
        statusBadge.className = originalClass;
        showNotification('Failed to update payment gateway', 'error');
    });
}

// Update onboarding status - auto-save when toggle changes
function updateOnboardingStatus(checkbox) {
    const isEnabled = checkbox.checked;
    
    // Prepare form data
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PUT');
    formData.append('onboarding_enabled', isEnabled ? '1' : '0');
    
    // Send AJAX request
    fetch('{{ route("admin.settings.mobile-app.update") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Show success notification
        showNotification(data.message || 'Onboarding setting saved successfully', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert checkbox state
        checkbox.checked = !isEnabled;
        showNotification('Failed to update onboarding setting', 'error');
    });
}

function showNotification(message, type = 'success') {
    const colors = {
        success: { bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-800', icon: 'text-green-600', path: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
        error: { bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-800', icon: 'text-red-600', path: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
        info: { bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-800', icon: 'text-blue-600', path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }
    };
    
    const color = colors[type] || colors.success;
    
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 flex items-center gap-3 p-4 rounded-lg shadow-lg ${color.bg} border ${color.border}`;
    notification.innerHTML = `
        <svg class="w-5 h-5 ${color.icon}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${color.path}"/>
        </svg>
        <p class="text-sm ${color.text}">${message}</p>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    togglePushOptions();
    
    // Check Firebase configuration status on page load
    checkFirebaseConfigStatus();
    
    // Restore active tab from session
    const activeTab = sessionStorage.getItem('activeTab');
    if (activeTab) {
        switchTab(activeTab);
    }
    
    // Add event listeners to all payment gateway toggles
    const gateways = ['razorpay', 'stripe', 'paypal', 'paystack', 'phonepe', 'paytm', 'flutterwave', 'midtrans', 'myfatoorah', 'instamojo', 'cod', 'bank-transfer'];
    
    gateways.forEach(gateway => {
        const checkbox = document.getElementById(gateway.replaceAll('-', '_') + '_enabled');
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                updatePaymentGatewayStatus(gateway, this);
            });
        }
    });
});

// Check Firebase configuration status
async function checkFirebaseConfigStatus() {
    const statusDiv = document.getElementById('firebase-setup-status');
    const autoSetupBtn = document.getElementById('auto-setup-btn');
    const projectIdInput = document.getElementById('firebase_project_id');
    const serviceAccountInput = document.getElementById('firebase_service_account');
    const databaseUrlInput = document.getElementById('firebase_database_url');
    
    // Check if all required fields are filled
    const projectId = projectIdInput?.value?.trim();
    const serviceAccount = serviceAccountInput?.value?.trim();
    const databaseUrl = databaseUrlInput?.value?.trim();
    
    if (!projectId || !serviceAccount) {
        statusDiv.innerHTML = `
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Enter your Firebase credentials above to begin setup</span>
            </div>
        `;
        return;
    }
    
    if (databaseUrl) {
        // Already configured — but still allow re-setup if credentials changed
        const parsedAccount = (() => { try { return JSON.parse(serviceAccount); } catch { return null; } })();
        const currentProject = parsedAccount?.project_id ?? '';
        const projectMismatch = currentProject && currentProject !== projectId;

        statusDiv.innerHTML = `
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-green-900 mb-1">✅ Firebase Already Configured!</p>
                        <p class="text-xs text-green-700 mb-2">Database URL: ${databaseUrl}</p>
                        ${projectMismatch
                            ? `<p class="text-xs text-orange-600 font-medium">⚠️ Service account project (${currentProject}) differs from saved project (${projectId}). Click Re-Setup to switch.</p>`
                            : `<p class="text-xs text-green-600">Your chat system is ready to use!</p>`
                        }
                    </div>
                </div>
            </div>
        `;

        // Keep button enabled so admin can re-run setup with new credentials
        autoSetupBtn.disabled = false;
        autoSetupBtn.innerHTML = `
            <span class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Re-Setup Firebase
            </span>
        `;
        autoSetupBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        // Ready to setup
        statusDiv.innerHTML = `
            <div class="flex items-center gap-2 text-sm text-blue-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Ready to setup! Click the button below to begin.</span>
            </div>
        `;
    }
}


// Firebase JSON file upload handler
function loadFirebaseJSON(input) {
    const file = input.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const json = JSON.parse(e.target.result);
            
            // Validate it's a service account JSON
            if (!json.type || json.type !== 'service_account') {
                showNotification('Invalid service account JSON file', 'error');
                return;
            }
            
            if (!json.project_id || !json.private_key) {
                showNotification('Service account JSON is missing required fields', 'error');
                return;
            }
            
            // Always overwrite project ID from the uploaded JSON
            const projectIdInput = document.querySelector('input[name="firebase_project_id"]');
            if (projectIdInput) {
                projectIdInput.value = json.project_id;
            }
            
            // Fill the textarea with formatted JSON
            const textarea = document.getElementById('firebase_service_account');
            textarea.value = JSON.stringify(json, null, 2);
            
            // Clear the old database URL so the status check shows "ready to setup"
            // instead of "already configured" with the previous project's URL
            const dbUrlInput = document.getElementById('firebase_database_url');
            if (dbUrlInput) {
                dbUrlInput.value = '';
            }
            
            // Auto-fill web SDK fields that can be derived from the service account
            const projectId = json.project_id;
            if (projectId) {
                const authDomainInput = document.getElementById('firebase_auth_domain');
                const storageBucketInput = document.getElementById('firebase_storage_bucket');
                // Always overwrite — these are derived from project_id, so they must match
                if (authDomainInput) authDomainInput.value = `${projectId}.firebaseapp.com`;
                if (storageBucketInput) storageBucketInput.value = `${projectId}.firebasestorage.app`;
            }
            
            // Show success status
            showFirebaseStatus('Service account JSON loaded. Fill Web SDK Config fields then click Auto-Setup.', 'success');
            
            // Re-evaluate status now that credentials changed
            checkFirebaseConfigStatus();
            
        } catch (error) {
            showNotification('Invalid JSON file: ' + error.message, 'error');
        }
    };
    reader.readAsText(file);
}

// Show Firebase configuration status
function showFirebaseStatus(message, type) {
    const statusDiv = document.getElementById('firebase_status');
    const colors = {
        success: { bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-800', icon: 'text-green-600' },
        error: { bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-800', icon: 'text-red-600' },
        info: { bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-800', icon: 'text-blue-600' }
    };
    
    const color = colors[type] || colors.info;
    
    statusDiv.className = `p-3 rounded-lg border ${color.bg} ${color.border}`;
    statusDiv.querySelector('svg').className = `w-5 h-5 ${color.icon}`;
    statusDiv.querySelector('span').className = `text-sm font-medium ${color.text}`;
    statusDiv.querySelector('span').textContent = message;
    statusDiv.classList.remove('hidden');
    
    setTimeout(() => {
        statusDiv.classList.add('hidden');
    }, 5000);
}

// Test Firebase notification
function testFirebaseNotification() {
    const resultSpan = document.getElementById('test_notification_result');
    resultSpan.innerHTML = '<svg class="w-4 h-4 animate-spin inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Testing...';
    
    fetch('{{ route("admin.settings.test-fcm") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultSpan.innerHTML = '<span class="text-green-600">✓ ' + data.message + '</span>';
            showNotification(data.message, 'success');
        } else {
            resultSpan.innerHTML = '<span class="text-red-600">✗ ' + data.message + '</span>';
            showNotification(data.message, 'error');
        }
        
        setTimeout(() => {
            resultSpan.innerHTML = '';
        }, 8000);
    })
    .catch(error => {
        resultSpan.innerHTML = '<span class="text-red-600">✗ Failed to send</span>';
        showNotification('Error: ' + error.message, 'error');
        
        setTimeout(() => {
            resultSpan.innerHTML = '';
        }, 5000);
    });
}

// Parse pasted firebaseConfig JS object into individual fields
function parseFirebaseWebConfig() {
    const raw = document.getElementById('firebase_web_config_paste')?.value?.trim();
    if (!raw) {
        showNotification('Paste your firebaseConfig object first', 'error');
        return;
    }

    try {
        // Accept both JS object literal and JSON — normalise to JSON
        const jsonStr = raw
            .replace(/\/\/.*$/gm, '')           // strip // comments
            .replace(/,\s*([}\]])/g, '$1')       // strip trailing commas
            .replace(/([{,]\s*)(\w+)\s*:/g, '$1"$2":')  // quote unquoted keys
            .replace(/:\s*'([^']*)'/g, ': "$1"'); // single → double quotes

        const cfg = JSON.parse(jsonStr);

        const map = {
            apiKey:            'firebase_api_key',
            authDomain:        'firebase_auth_domain',
            databaseURL:       'firebase_database_url',
            projectId:         'firebase_project_id',
            storageBucket:     'firebase_storage_bucket',
            messagingSenderId: 'firebase_messaging_sender_id',
            appId:             'firebase_app_id',
        };

        let filled = 0;
        for (const [jsKey, settingKey] of Object.entries(map)) {
            if (cfg[jsKey]) {
                const el = document.getElementById(settingKey);
                if (el) { el.value = cfg[jsKey]; filled++; }
            }
        }

        document.getElementById('firebase_web_config_paste').value = '';
        showFirebaseStatus(`Parsed ${filled} fields from firebaseConfig. Review and save.`, 'success');
        checkFirebaseConfigStatus();
    } catch (e) {
        showNotification('Could not parse firebaseConfig: ' + e.message, 'error');
    }
}

// Firebase Auto-Setup Functions
function toggleManualSetup() {
    const manualSection = document.getElementById('manual-setup-section');
    manualSection.classList.toggle('hidden');
}

async function autoSetupFirebase() {
    const btn = document.getElementById('auto-setup-btn');
    const statusDiv = document.getElementById('firebase-setup-status');
    const projectId = document.getElementById('firebase_project_id').value;
    const serviceAccount = document.getElementById('firebase_service_account').value;

    if (!projectId || !serviceAccount) {
        showNotification('Please enter Firebase Project ID and Service Account JSON first', 'error');
        // Show manual setup section
        document.getElementById('manual-setup-section').classList.remove('hidden');
        return;
    }

    // Validate JSON
    try {
        JSON.parse(serviceAccount);
    } catch (e) {
        showNotification('Invalid Service Account JSON format', 'error');
        return;
    }

    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = `
        <span class="flex items-center gap-2">
            <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Setting up Firebase...
        </span>
    `;

    statusDiv.innerHTML = `
        <div class="flex items-center gap-2 text-sm text-blue-600">
            <div class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></div>
            <span>Configuring Firebase Realtime Database...</span>
        </div>
    `;

    try {
        const response = await fetch('{{ route("admin.settings.firebase-auto-setup") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                project_id: projectId,
                service_account: serviceAccount
            })
        });

        const result = await response.json();

        if (result.success) {
            // Update database URL if provided
            if (result.database_url) {
                document.getElementById('firebase_database_url').value = result.database_url;
            }

            statusDiv.innerHTML = `
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-green-900 mb-1">✅ Firebase Setup Complete!</p>
                            <ul class="text-xs text-green-700 space-y-1">
                                <li>✓ Realtime Database: ${result.database_url || 'Configured'}</li>
                                <li>✓ Security Rules: ${result.rules_deployed ? 'Deployed' : 'Pending'}</li>
                                <li>✓ Connection Test: ${result.connection_test ? 'Passed' : 'Pending'}</li>
                            </ul>
                            <p class="text-xs text-green-600 mt-2">Your chat system is ready to use! Don't forget to save settings.</p>
                        </div>
                    </div>
                </div>
            `;

            showNotification('Firebase configured successfully!', 'success');
            
            btn.innerHTML = `
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Setup Complete
                </span>
            `;
        } else {
            // Show guided manual setup if database creation failed
            if (result.manual_steps) {
                showGuidedManualSetup(result.manual_steps, result.message);
            } else {
                statusDiv.innerHTML = `
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-yellow-900">${result.message}</p>
                                <p class="text-xs text-yellow-700 mt-1">Please try manual setup below.</p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            showNotification(result.message, 'warning');
            
            btn.disabled = false;
            btn.innerHTML = `
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Retry Auto-Setup
                </span>
            `;
        }
    } catch (error) {
        console.error('Auto-setup error:', error);
        statusDiv.innerHTML = `
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-red-900">Setup failed: ${error.message}</p>
                        <p class="text-xs text-red-700 mt-1">Please try manual setup or contact support.</p>
                    </div>
                </div>
            </div>
        `;
        showNotification('Setup failed. Please try again.', 'error');
        
        btn.disabled = false;
        btn.innerHTML = `
            <span class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Retry Auto-Setup
            </span>
        `;
    }
}

// Show guided manual setup with step-by-step instructions
function showGuidedManualSetup(steps, message) {
    const statusDiv = document.getElementById('firebase-setup-status');
    const projectId = document.getElementById('firebase_project_id').value;
    
    let stepsHtml = '';
    steps.forEach((step, index) => {
        const isLast = index === steps.length - 1;
        
        stepsHtml += `
            <div class="flex gap-3 ${!isLast ? 'pb-4' : ''}">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-semibold text-sm flex-shrink-0">
                        ${step.step}
                    </div>
                    ${!isLast ? '<div class="w-0.5 h-full bg-blue-200 mt-2"></div>' : ''}
                </div>
                <div class="flex-1 ${!isLast ? 'pb-2' : ''}">
                    <p class="font-medium text-gray-900 text-sm mb-1">${step.title}</p>
                    <p class="text-xs text-gray-600 mb-2">${step.description}</p>
                    ${step.action === 'open_console' ? `
                        <a href="${step.url}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Open Firebase Console
                        </a>
                    ` : ''}
                    ${step.action === 'return_here' ? `
                        <button type="button" onclick="completeFirebaseSetup()" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Complete Setup
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    statusDiv.innerHTML = `
        <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 mb-1">Quick Manual Setup Required</h4>
                    <p class="text-sm text-gray-700 mb-4">${message || 'Automatic database creation requires special permissions. Follow these 5 simple steps:'}</p>
                    <div class="bg-white rounded-lg p-4 border border-blue-200">
                        ${stepsHtml}
                    </div>
                    <p class="text-xs text-gray-600 mt-3">
                        <strong>Note:</strong> This is a one-time setup. After creating the database, we'll automatically configure everything else!
                    </p>
                </div>
            </div>
        </div>
    `;
}

// Complete setup after manual database creation
async function completeFirebaseSetup(manualDatabaseUrl = null) {
    const statusDiv = document.getElementById('firebase-setup-status');
    const projectId = document.getElementById('firebase_project_id').value;
    const serviceAccount = document.getElementById('firebase_service_account').value;

    // If manual URL input exists, get its value
    if (!manualDatabaseUrl) {
        const manualUrlInput = document.getElementById('manual_database_url');
        if (manualUrlInput) {
            manualDatabaseUrl = manualUrlInput.value.trim();
        }
    }

    statusDiv.innerHTML = `
        <div class="flex items-center gap-2 text-sm text-blue-600">
            <div class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></div>
            <span>${manualDatabaseUrl ? 'Verifying database URL...' : 'Detecting database and completing setup...'}</span>
        </div>
    `;

    try {
        const requestBody = {
            project_id: projectId,
            service_account: serviceAccount
        };
        
        if (manualDatabaseUrl) {
            requestBody.database_url = manualDatabaseUrl;
        }

        const response = await fetch('{{ route("admin.settings.firebase-complete-setup") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestBody)
        });

        const result = await response.json();

        if (result.success) {
            // Update database URL if provided
            if (result.database_url) {
                document.getElementById('firebase_database_url').value = result.database_url;
            }

            statusDiv.innerHTML = `
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-green-900 mb-1">✅ Firebase Setup Complete!</p>
                            <ul class="text-xs text-green-700 space-y-1">
                                <li>✓ Realtime Database: ${result.database_url || 'Configured'}</li>
                                <li>✓ Security Rules: ${result.rules_deployed ? 'Deployed' : 'Pending'}</li>
                                <li>✓ Connection Test: ${result.connection_test ? 'Passed' : 'Pending'}</li>
                            </ul>
                            <p class="text-xs text-green-600 mt-2">Your chat system is ready to use! Don't forget to save settings.</p>
                        </div>
                    </div>
                </div>
            `;

            showNotification('Firebase configured successfully!', 'success');
            
            document.getElementById('auto-setup-btn').innerHTML = `
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Setup Complete
                </span>
            `;
            document.getElementById('auto-setup-btn').disabled = true;
        } else {
            if (result.retry || result.show_manual_input) {
                statusDiv.innerHTML = `
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-yellow-900 mb-2">${result.message}</p>
                                <p class="text-xs text-yellow-700 mb-3">You can either retry auto-detection or manually enter your database URL from Firebase Console.</p>
                                
                                <div class="mb-3">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Database URL (optional)</label>
                                    <input type="url" id="manual_database_url" placeholder="https://your-project-default-rtdb.firebaseio.com" 
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <p class="text-xs text-gray-500 mt-1">Find this in Firebase Console → Realtime Database → Data tab</p>
                                </div>
                                
                                <div class="flex gap-2">
                                    <button type="button" onclick="completeFirebaseSetup()" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white text-xs font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Retry Auto-Detection
                                    </button>
                                    <button type="button" onclick="completeFirebaseSetup(document.getElementById('manual_database_url').value)" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-xs font-medium rounded-lg hover:bg-purple-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Verify Manual URL
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                statusDiv.innerHTML = `
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-900">${result.message}</p>
                                <p class="text-xs text-red-700 mt-1">Please contact support if the issue persists.</p>
                            </div>
                        </div>
                    </div>
                `;
            }
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Complete setup error:', error);
        statusDiv.innerHTML = `
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-red-900">Setup failed: ${error.message}</p>
                        <p class="text-xs text-red-700 mt-1">Please try again or contact support.</p>
                    </div>
                </div>
            </div>
        `;
        showNotification('Setup failed. Please try again.', 'error');
    }
}

/**
 * Auto-Setup Cloudflare
 */
function autoSetupCloudflare() {
    const email = document.getElementById('cf_email').value;
    const apiKey = document.getElementById('cf_api_key').value;
    const btn = document.getElementById('cf-setup-btn');

    if (!email || !apiKey) {
        showNotification('Please enter both Cloudflare Email and Global API Key', 'error');
        return;
    }

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-5 h-5 animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Optimizing...';

    fetch('{{ route("admin.settings.cloudflare-auto-setup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ email, api_key: apiKey })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showNotification(data.message || 'Setup failed', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An unexpected error occurred during setup', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

/**
 * Purge Cloudflare Cache
 */
function purgeCloudflareCache() {
    const btn = document.getElementById('cf-purge-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-5 h-5 animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Purging...';

    fetch('{{ route("admin.settings.cloudflare-purge-cache") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Purge failed', 'error');
        }
        btn.disabled = false;
        btn.innerHTML = originalText;
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An unexpected error occurred during purge', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>
@endpush
@endsection





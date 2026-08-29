@extends('admin.layouts.app')

@section('title', 'Business Settings')

@push('styles')
<style>
    .tab-btn { transition: all 0.2s ease; }
    .tab-btn.active { color: #f97316; border-color: #f97316; background: #fff7ed; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    /* Drop Zone */
    .drop-zone {
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        padding: 28px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fafbfc;
    }
    .drop-zone:hover, .drop-zone.drag-over {
        border-color: #f97316;
        background: #fff7ed;
    }
    .drop-zone.drag-over { transform: scale(1.01); }

    /* Maintenance overlay preview */
    .maintenance-preview {
        background: linear-gradient(135deg, #1a2332 0%, #243044 100%);
        border-radius: 12px;
        padding: 32px;
        color: white;
        text-align: center;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 16px;
    }
</style>
@endpush

@section('content')
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
            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">Business Settings</h1>
            <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">Configure business identity, branding, support, and maintenance settings</p>
        </div>
    </div>

    <form action="{{ route('admin.settings.app-settings.update') }}" method="POST" id="appSettingsForm" enctype="multipart/form-data" onsubmit="return false;">
        @csrf
        @method('PUT')

        <!-- Horizontal Tabs -->
        <div class="bg-white border border-gray-200 rounded-lg mb-4 overflow-hidden">
            <div class="flex overflow-x-auto border-b border-gray-200">
                <button type="button" onclick="switchTab('identity')" class="tab-btn active flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="identity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>App Identity</span>
                </button>
                <button type="button" onclick="switchTab('branding')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="branding">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Logo &amp; Favicon</span>
                </button>
                <button type="button" onclick="switchTab('support')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="support">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Support</span>
                </button>
                <button type="button" onclick="switchTab('maintenance')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="maintenance">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Maintenance</span>
                </button>
                <button type="button" onclick="switchTab('storage')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="storage">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                    <span>S3 Storage</span>
                </button>
                <button type="button" onclick="switchTab('app-update')" class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="app-update">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>In-App Version Update</span>
                </button>            </div>
        </div>

        <!-- Tab Contents -->
        <div class="space-y-4">

            <!-- ================== APP IDENTITY TAB ================== -->
            <div id="tab-identity" class="tab-content active">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 text-lg">App Identity</h3>
                        <p class="text-sm text-gray-500">Basic information about your application</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- App Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">App Name <span class="text-red-500">*</span></label>
                            <input type="text" name="app_name" id="app_name"
                                value="{{ $settings['app_name'] ?? config('app.name', 'InAllCart') }}"
                                class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                placeholder="e.g. My Store App" required>
                            <p class="text-xs text-gray-500 mt-1">Displayed throughout the admin panel and sent to the mobile app</p>
                        </div>

                        <!-- Copyright Details -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Copyright Text</label>
                            <input type="text" name="app_copyright"
                                value="{{ $settings['app_copyright'] ?? '© 2025 ' . ($settings['app_name'] ?? 'InAllCart') . '. All rights reserved.' }}"
                                class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                placeholder="© 2025 My Store. All rights reserved.">
                            <p class="text-xs text-gray-500 mt-1">Shown in the footer of your web and mobile app</p>
                        </div>

                        <!-- Company Address -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Address</label>
                            <textarea name="company_address" rows="3"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                placeholder="123 Business Street, City, Country, ZIP">{{ $settings['company_address'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-permission-btn
                            permission="settings.manage"
                            type="button"
                            onclick="saveTab('identity')"
                            class="btn-primary"
                            label="Save App Identity"
                            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                        />
                    </div>
                </div>
            </div>

            <!-- ================== BRANDING TAB ================== -->
            <div id="tab-branding" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Admin Sidebar Logo -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="mb-4">
                            <h3 class="font-semibold text-gray-900">Admin Sidebar Logo</h3>
                            <p class="text-sm text-gray-500">Displayed in the admin panel sidebar (recommended: 160x40px, PNG)</p>
                        </div>

                        <!-- Current Logo Preview -->
                        <div class="flex items-center gap-4 mb-4 p-3 bg-[#1a2332] rounded-lg">
                            @if(!empty($settings['admin_sidebar_logo']))
                                <img id="sidebar_logo_preview" src="{{ storage_url($settings['admin_sidebar_logo']) }}" alt="Sidebar Logo" class="h-8 object-contain">
                            @else
                                <div id="sidebar_logo_preview_placeholder" class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <span class="text-white font-bold text-sm">{{ $settings['app_name'] ?? 'InAllCart' }}</span>
                                </div>
                                <img id="sidebar_logo_preview" src="" alt="Sidebar Logo" class="h-8 object-contain hidden">
                            @endif
                        </div>

                        <!-- Drop Zone -->
                        <div class="drop-zone" id="sidebar_logo_drop"
                            ondragover="handleDragOver(event, 'sidebar_logo_drop')"
                            ondragleave="handleDragLeave(event, 'sidebar_logo_drop')"
                            ondrop="handleDrop(event, 'admin_sidebar_logo', 'sidebar_logo_preview')">
                            <input type="file" name="admin_sidebar_logo" id="admin_sidebar_logo" accept="image/*" class="hidden"
                                onchange="previewImage(this, 'sidebar_logo_preview')">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-sm text-gray-600 font-medium">Drag & drop logo here</p>
                            <p class="text-xs text-gray-400 mt-1">or <button type="button" onclick="document.getElementById('admin_sidebar_logo').click()" class="text-orange-500 hover:underline font-medium">browse files</button></p>
                            <p class="text-xs text-gray-400 mt-2">SVG, PNG, JPG (max. 2MB)</p>
                        </div>

                        @if(!empty($settings['admin_sidebar_logo']))
                        <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                            <span>Current: {{ basename($settings['admin_sidebar_logo']) }}</span>
                            <button type="button" onclick="removeImage('admin_sidebar_logo', 'sidebar_logo_preview')" class="text-red-500 hover:text-red-700">Remove</button>
                        </div>
                        <input type="hidden" name="remove_admin_sidebar_logo" id="remove_admin_sidebar_logo" value="0">
                        @endif
                    </div>

                    <!-- Admin Login Logo -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="mb-4">
                            <h3 class="font-semibold text-gray-900">Admin Login Screen Logo</h3>
                            <p class="text-sm text-gray-500">Shown on the <code class="bg-gray-100 px-1 rounded text-xs">/login</code> page (recommended: 200x60px, PNG/SVG)</p>
                        </div>

                        <!-- Current Preview -->
                        <div class="flex items-center justify-center mb-4 p-4 rounded-lg" style="background:#1a1a2e;min-height:72px;">
                            @if(!empty($settings['admin_login_logo']))
                                <img id="login_logo_preview" src="{{ storage_url($settings['admin_login_logo']) }}" alt="Login Logo" class="h-12 object-contain">
                            @else
                                <div id="login_logo_preview_placeholder" class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <span class="text-white font-bold text-sm">{{ $settings['app_name'] ?? 'InAllCart' }}</span>
                                </div>
                                <img id="login_logo_preview" src="" alt="Login Logo" class="h-12 object-contain hidden">
                            @endif
                        </div>

                        <!-- Drop Zone -->
                        <div class="drop-zone" id="login_logo_drop"
                            ondragover="handleDragOver(event, 'login_logo_drop')"
                            ondragleave="handleDragLeave(event, 'login_logo_drop')"
                            ondrop="handleDrop(event, 'admin_login_logo', 'login_logo_preview')">
                            <input type="file" name="admin_login_logo" id="admin_login_logo" accept="image/*" class="hidden"
                                onchange="previewImage(this, 'login_logo_preview')">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-sm text-gray-600 font-medium">Drag & drop logo here</p>
                            <p class="text-xs text-gray-400 mt-1">or <button type="button" onclick="document.getElementById('admin_login_logo').click()" class="text-orange-500 hover:underline font-medium">browse files</button></p>
                            <p class="text-xs text-gray-400 mt-2">SVG, PNG, JPG (max. 2MB)</p>
                        </div>

                        @if(!empty($settings['admin_login_logo']))
                        <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                            <span>Current: {{ basename($settings['admin_login_logo']) }}</span>
                            <button type="button" onclick="removeImage('admin_login_logo', 'login_logo_preview')" class="text-red-500 hover:text-red-700">Remove</button>
                        </div>
                        <input type="hidden" name="remove_admin_login_logo" id="remove_admin_login_logo" value="0">
                        @endif
                    </div>

                    <!-- Favicon -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="mb-4">
                            <h3 class="font-semibold text-gray-900">Favicon</h3>
                            <p class="text-sm text-gray-500">Browser tab icon (recommended: 32x32px or 64x64px, ICO/PNG)</p>
                        </div>

                        <!-- Current Favicon Preview -->
                        <div class="flex items-center gap-3 mb-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="flex items-center gap-2 border border-gray-300 rounded px-2 py-1 bg-white text-xs text-gray-600">
                                @if(!empty($settings['app_favicon']))
                                    <img id="favicon_preview" src="{{ storage_url($settings['app_favicon']) }}" alt="Favicon" class="w-4 h-4 object-contain">
                                @else
                                    <div id="favicon_preview_placeholder" class="w-4 h-4 bg-orange-500 rounded-sm flex items-center justify-center">
                                        <span class="text-white text-[6px] font-bold">Q</span>
                                    </div>
                                    <img id="favicon_preview" src="" alt="Favicon" class="w-4 h-4 object-contain hidden">
                                @endif
                                <span>{{ $settings['app_name'] ?? 'InAllCart' }} Admin</span>
                            </div>
                            <span class="text-xs text-gray-400">Tab preview</span>
                        </div>

                        <!-- Drop Zone -->
                        <div class="drop-zone" id="favicon_drop"
                            ondragover="handleDragOver(event, 'favicon_drop')"
                            ondragleave="handleDragLeave(event, 'favicon_drop')"
                            ondrop="handleDrop(event, 'app_favicon', 'favicon_preview')">
                            <input type="file" name="app_favicon" id="app_favicon" accept="image/*,.ico" class="hidden"
                                onchange="previewImage(this, 'favicon_preview')">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                            <p class="text-sm text-gray-600 font-medium">Drag & drop favicon here</p>
                            <p class="text-xs text-gray-400 mt-1">or <button type="button" onclick="document.getElementById('app_favicon').click()" class="text-orange-500 hover:underline font-medium">browse files</button></p>
                            <p class="text-xs text-gray-400 mt-2">ICO, PNG (max. 512KB, 32x32 or 64x64px)</p>
                        </div>

                        @if(!empty($settings['app_favicon']))
                        <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                            <span>Current: {{ basename($settings['app_favicon']) }}</span>
                            <button type="button" onclick="removeImage('app_favicon', 'favicon_preview')" class="text-red-500 hover:text-red-700">Remove</button>
                        </div>
                        <input type="hidden" name="remove_app_favicon" id="remove_app_favicon" value="0">
                        @endif
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <x-permission-btn
                        permission="settings.manage"
                        type="button"
                        onclick="saveTab('branding')"
                        class="btn-primary"
                        label="Save Branding"
                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                    />
                </div>
            </div>

            <!-- ================== SUPPORT TAB ================== -->
            <div id="tab-support" class="tab-content">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 text-lg">Support Information</h3>
                        <p class="text-sm text-gray-500">Contact details shown to customers in the app</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Support Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Support Phone Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <input type="text" name="support_phone"
                                    value="{{ $settings['support_phone'] ?? '' }}"
                                    class="w-full h-10 pl-10 pr-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                    placeholder="+1 (555) 000-0000">
                            </div>
                        </div>

                        <!-- Support Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <input type="email" name="support_email"
                                    value="{{ $settings['support_email'] ?? '' }}"
                                    class="w-full h-10 pl-10 pr-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                    placeholder="support@yourapp.com">
                            </div>
                        </div>

                        <!-- Support WhatsApp -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number <span class="text-gray-400 font-normal">(optional)</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                </div>
                                <input type="text" name="support_whatsapp"
                                    value="{{ $settings['support_whatsapp'] ?? '' }}"
                                    class="w-full h-10 pl-10 pr-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                    placeholder="+1 (555) 000-0000">
                            </div>
                        </div>

                        <!-- Support Website -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Support Website / Help Center <span class="text-gray-400 font-normal">(optional)</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                </div>
                                <input type="url" name="support_website"
                                    value="{{ $settings['support_website'] ?? '' }}"
                                    class="w-full h-10 pl-10 pr-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                    placeholder="https://help.yourapp.com">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-permission-btn
                            permission="settings.manage"
                            type="button"
                            onclick="saveTab('support')"
                            class="btn-primary"
                            label="Save Support Info"
                            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                        />
                    </div>
                </div>
            </div>

            <!-- ================== MAINTENANCE TAB ================== -->
            <div id="tab-maintenance" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Settings Panel -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 text-lg">Maintenance Mode</h3>
                            <p class="text-sm text-gray-500">When enabled, end-users will see a maintenance page instead of the app</p>
                        </div>

                        <!-- On/Off Toggle -->
                        <div class="flex items-start justify-between p-4 bg-gray-50 border border-gray-200 rounded-lg mb-6">
                            <div>
                                <p class="font-medium text-gray-900">Enable Maintenance Mode</p>
                                <p class="text-xs text-gray-500 mt-0.5">Blocks app access for all non-admin users in real-time</p>
                            </div>
                            <label class="toggle ml-4 flex-shrink-0">
                                <input type="checkbox" name="maintenance_mode" value="1" id="maintenance_toggle"
                                    {{ ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' }}
                                    onchange="updateMaintenancePreview()">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        @if(class_exists('\App\Helpers\DemoHelper') ? \App\Helpers\DemoHelper::isDemoMode() : (bool)env('DEMO_MODE', false))
                        <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <p class="text-xs sm:text-sm text-amber-800 font-medium">Demo Mode Active: Modifying or toggling Maintenance Mode settings is locked in Demo Mode.</p>
                        </div>
                        @endif

                        @if(($settings['maintenance_mode'] ?? '0') == '1')
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex gap-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <p class="text-sm text-red-700 font-medium">Maintenance mode is currently ACTIVE. User app is blocked.</p>
                        </div>
                        @endif

                        <!-- Maintenance Title -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Maintenance Title</label>
                            <input type="text" name="maintenance_title"
                                value="{{ $settings['maintenance_title'] ?? 'We\'ll be back soon!' }}"
                                oninput="updateMaintenancePreview()"
                                class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                placeholder="We'll be back soon!">
                        </div>

                        <!-- Maintenance Message -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Maintenance Message</label>
                            <textarea name="maintenance_message" rows="3"
                                oninput="updateMaintenancePreview()"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                placeholder="We are performing scheduled maintenance. Please check back later.">{{ $settings['maintenance_message'] ?? 'We are performing scheduled maintenance. Please check back later.' }}</textarea>
                        </div>

                        <!-- Maintenance Image -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Maintenance Image <span class="text-gray-400 font-normal">(optional)</span></label>
                            <div class="drop-zone" id="maintenance_img_drop"
                                ondragover="handleDragOver(event, 'maintenance_img_drop')"
                                ondragleave="handleDragLeave(event, 'maintenance_img_drop')"
                                ondrop="handleDrop(event, 'maintenance_image', 'maintenance_img_preview')">
                                <input type="file" name="maintenance_image" id="maintenance_image" accept="image/*" class="hidden"
                                    onchange="previewMaintenance(this)">
                                @if(!empty($settings['maintenance_image']))
                                    <img id="maintenance_img_preview" src="{{ storage_url($settings['maintenance_image']) }}" class="max-h-24 mx-auto mb-2 rounded object-contain">
                                @else
                                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <img id="maintenance_img_preview" src="" class="max-h-24 mx-auto mb-2 rounded object-contain hidden">
                                @endif
                                <p class="text-sm text-gray-600 font-medium">Drag & drop image here</p>
                                <p class="text-xs text-gray-400 mt-1">or <button type="button" onclick="document.getElementById('maintenance_image').click()" class="text-orange-500 hover:underline font-medium">browse files</button></p>
                                <p class="text-xs text-gray-400 mt-2">PNG, JPG, GIF (max. 2MB)</p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-permission-btn
                                permission="settings.manage"
                                type="button"
                                onclick="saveTab('maintenance')"
                                class="btn-primary"
                                label="Save Maintenance Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </div>

                    <!-- Preview Panel -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="mb-4">
                            <h3 class="font-semibold text-gray-900">Live Preview</h3>
                            <p class="text-sm text-gray-500">This is what users will see on the app</p>
                        </div>
                        <div class="maintenance-preview" id="maintenance_preview_box">
                            <div id="preview_img_container">
                                @if(!empty($settings['maintenance_image']))
                                    <img src="{{ storage_url($settings['maintenance_image']) }}" class="w-24 h-24 object-contain mx-auto opacity-80">
                                @else
                                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto">
                                        <svg class="w-8 h-8 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h2 id="preview_title" class="text-xl font-bold text-white">{{ $settings['maintenance_title'] ?? "We'll be back soon!" }}</h2>
                                <p id="preview_message" class="text-white/70 text-sm mt-2 max-w-xs">{{ $settings['maintenance_message'] ?? 'We are performing scheduled maintenance. Please check back later.' }}</p>
                            </div>
                            <div id="preview_status_badge" class="{{ ($settings['maintenance_mode'] ?? '0') == '1' ? '' : 'hidden' }}">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-500/20 border border-red-400/30 text-red-200 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span>
                                    Maintenance Active
                                </span>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex gap-2 text-xs text-blue-700">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <p class="font-medium mb-0.5">Real-time blocking</p>
                                    <p>When maintenance mode is ON, users opening the app will immediately see this screen via Firebase realtime sync. Admin accounts are never blocked.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- end maintenance tab -->

            <!-- ================== S3 STORAGE TAB ================== -->
            <div id="tab-storage" class="tab-content">
                <div class="p-6">
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="mb-6">
                            <h3 class="text-base font-semibold text-gray-900">Amazon S3 Storage</h3>
                            <p class="text-sm text-gray-500 mt-1">Configure S3 to store uploaded files (images, documents) in the cloud instead of local disk.</p>
                        </div>

                        <!-- Enable toggle -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg mb-6">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Enable S3 Storage</p>
                                <p class="text-xs text-gray-500 mt-0.5">When enabled, all file uploads will go to your S3 bucket</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="s3_enabled" value="1" class="sr-only peer"
                                    {{ env('FILESYSTEM_DISK', 'local') === 's3' ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Access Key ID <span class="text-red-500">*</span></label>
                                <input type="text" name="aws_access_key_id"
                                    value="{{ env('AWS_ACCESS_KEY_ID') }}"
                                    class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none font-mono"
                                    placeholder="AKIAIOSFODNN7EXAMPLE">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Secret Access Key <span class="text-red-500">*</span></label>
                                <input type="password" name="aws_secret_access_key"
                                    value="{{ env('AWS_SECRET_ACCESS_KEY') }}"
                                    class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none font-mono"
                                    placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Region <span class="text-red-500">*</span></label>
                                <input type="text" name="aws_default_region"
                                    value="{{ env('AWS_DEFAULT_REGION', 'us-east-1') }}"
                                    class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none font-mono"
                                    placeholder="us-east-1">
                                <p class="text-xs text-gray-400 mt-1">e.g. us-east-1, ap-south-1, auto</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bucket Name <span class="text-red-500">*</span></label>
                                <input type="text" name="aws_bucket"
                                    value="{{ env('AWS_BUCKET') }}"
                                    class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                    placeholder="my-app-bucket">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Custom URL <span class="text-gray-400 font-normal">(optional)</span></label>
                                <input type="text" name="aws_url"
                                    value="{{ env('AWS_URL') }}"
                                    class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                    placeholder="https://cdn.example.com">
                                <p class="text-xs text-gray-400 mt-1">CDN or custom domain for serving files</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Custom Endpoint <span class="text-gray-400 font-normal">(optional)</span></label>
                                <input type="text" name="aws_endpoint"
                                    value="{{ env('AWS_ENDPOINT') }}"
                                    class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                    placeholder="https://s3.example.com">
                                <p class="text-xs text-gray-400 mt-1">For S3-compatible services (MinIO, DigitalOcean Spaces, etc.)</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mt-5 pt-5 border-t border-gray-100">
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" name="aws_use_path_style_endpoint" value="1" class="rounded border-gray-300 text-orange-500"
                                    {{ env('AWS_USE_PATH_STYLE_ENDPOINT', false) ? 'checked' : '' }}>
                                Use path-style endpoint
                            </label>
                            <span class="text-xs text-gray-400">(required for MinIO and some S3-compatible services)</span>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium mb-1">Before enabling S3</p>
                                    <ul class="list-disc list-inside space-y-1 text-blue-700">
                                        <li>Make sure your bucket has public read access or a proper bucket policy</li>
                                        <li>The IAM user needs <code class="bg-blue-100 px-1 rounded">s3:PutObject</code>, <code class="bg-blue-100 px-1 rounded">s3:GetObject</code>, and <code class="bg-blue-100 px-1 rounded">s3:DeleteObject</code> permissions</li>
                                        <li>Install the AWS SDK: <code class="bg-blue-100 px-1 rounded">composer require league/flysystem-aws-s3-v3</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <button type="button" onclick="testS3Connection()"
                                data-url="{{ route('admin.settings.s3.test') }}"
                                class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Test Connection
                            </button>
                            <button type="button" onclick="saveTab('storage')"
                                data-original-label="Save S3 Settings"
                                class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition">
                                Save S3 Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end storage tab -->

            <!-- ================== IN-APP VERSION UPDATE TAB ================== -->
            <div id="tab-app-update" class="tab-content">
                <div class="p-6">
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="mb-6 border-b border-gray-100 pb-4 flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900 text-lg">In-App Version & Force Update Controls</h3>
                                <p class="text-sm text-gray-500">Configure minimum required app version, latest release version, force update prompt, and Play Store / App Store links</p>
                            </div>
                            <span class="px-2.5 py-1 bg-orange-100 text-orange-700 text-xs font-bold uppercase tracking-wider rounded-md">Mobile Apps</span>
                        </div>

                        @if(class_exists('\App\Helpers\DemoHelper') ? \App\Helpers\DemoHelper::isDemoMode() : (bool)env('DEMO_MODE', false))
                        <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <p class="text-xs sm:text-sm text-amber-800 font-medium">Demo Mode Active: Modifying or toggling Force Update & App Version settings is locked in Demo Mode.</p>
                        </div>
                        @endif

                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Minimum App Version -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum App Version <span class="text-red-500">*</span></label>
                                    <input type="text" name="app_min_version" id="app_min_version"
                                        value="{{ $settings['app_min_version'] ?? '1.0.0' }}"
                                        class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                        placeholder="e.g. 1.0.0">
                                    <p class="text-xs text-gray-500 mt-1">Users running version below this will be prompted to update.</p>
                                </div>

                                <!-- Latest App Version -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Latest App Version <span class="text-red-500">*</span></label>
                                    <input type="text" name="app_latest_version" id="app_latest_version"
                                        value="{{ $settings['app_latest_version'] ?? '1.0.0' }}"
                                        class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                        placeholder="e.g. 1.1.0">
                                    <p class="text-xs text-gray-500 mt-1">Latest version available on Play Store / App Store.</p>
                                </div>
                            </div>

                            <!-- Force Update Switch -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl bg-gray-50">
                                <div>
                                    <h4 class="font-semibold text-gray-900 text-sm">Force Update Mode</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">If enabled, user cannot dismiss the update dialog and must update to continue using the app.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="app_force_update" value="1" {{ ($settings['app_force_update'] ?? '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-600"></div>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Play Store URL -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Google Play Store URL</label>
                                    <input type="url" name="app_play_store_url" id="app_play_store_url"
                                        value="{{ $settings['app_play_store_url'] ?? 'https://play.google.com/store/apps/details?id=com.inallcart.app' }}"
                                        class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                        placeholder="https://play.google.com/store/apps/details?id=...">
                                </div>

                                <!-- App Store URL -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Apple App Store URL</label>
                                    <input type="url" name="app_app_store_url" id="app_app_store_url"
                                        value="{{ $settings['app_app_store_url'] ?? 'https://apps.apple.com/app/inallcart/id123456789' }}"
                                        class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none"
                                        placeholder="https://apps.apple.com/app/...">
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-gray-100">
                                <button type="button" onclick="saveTab('app-update')"
                                    data-original-label="Save In-App Update Settings"
                                    class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition">
                                    Save In-App Update Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end app update tab -->

        </div>
        <!-- end tab contents -->
    </form>
</div>

@push('scripts')
<script>
/* ---- Tab Switching ---- */
function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('active');
        el.style.borderColor = 'transparent';
        el.style.color = '';
        el.style.background = '';
    });
    const content = document.getElementById('tab-' + tab);
    if (content) content.classList.add('active');
    const btn = document.querySelector('[data-tab="' + tab + '"]');
    if (btn) btn.classList.add('active');
}

/* ---- Drag & Drop ---- */
function handleDragOver(e, zoneId) {
    e.preventDefault();
    document.getElementById(zoneId).classList.add('drag-over');
}
function handleDragLeave(e, zoneId) {
    document.getElementById(zoneId).classList.remove('drag-over');
}
function handleDrop(e, inputId, previewId) {
    e.preventDefault();
    const zoneId = e.currentTarget.id;
    document.getElementById(zoneId).classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const input = document.getElementById(inputId);
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    previewImage(input, previewId);
}
function previewImage(input, previewId) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById(previewId);
        if (preview) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            preview.style.display = 'block';
            // Hide placeholder if any
            const placeholder = document.getElementById(previewId + '_placeholder');
            if (placeholder) placeholder.style.display = 'none';
        }
    };
    reader.readAsDataURL(file);
}
function removeImage(inputId, previewId) {
    document.getElementById(inputId).value = '';
    const preview = document.getElementById(previewId);
    if (preview) { preview.src = ''; preview.classList.add('hidden'); }
    const removeFlag = document.getElementById('remove_' + inputId);
    if (removeFlag) removeFlag.value = '1';
    // Show placeholder again
    const placeholder = document.getElementById(previewId + '_placeholder');
    if (placeholder) placeholder.style.display = '';
}

/* ---- Maintenance Preview ---- */
function previewMaintenance(input) {
    previewImage(input, 'maintenance_img_preview');
    // Also update the live preview box image
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview_img_container').innerHTML =
                '<img src="' + e.target.result + '" class="w-24 h-24 object-contain mx-auto opacity-80">';
        };
        reader.readAsDataURL(file);
    }
}
function updateMaintenancePreview() {
    const title = document.querySelector('[name="maintenance_title"]').value || "We'll be back soon!";
    const msg = document.querySelector('[name="maintenance_message"]').value || 'Scheduled maintenance in progress.';
    const isOn = document.getElementById('maintenance_toggle').checked;
    document.getElementById('preview_title').textContent = title;
    document.getElementById('preview_message').textContent = msg;
    const badge = document.getElementById('preview_status_badge');
    if (isOn) badge.classList.remove('hidden');
    else badge.classList.add('hidden');
}

/* ---- Save per Tab ---- */
function saveTab(tab) {
    const form = document.getElementById('appSettingsForm');
    const formData = new FormData(form);
    formData.set('_save_tab', tab);

    const btn = document.querySelector('#tab-' + tab + ' button[onclick*="saveTab"]');
    if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }

    fetch('{{ route('admin.settings.app-settings.update') }}', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.success ? data.message : (data.message || 'Error saving'), data.success ? 'success' : 'error');
        if (data.success && data.redirect_logo) {
            // If admin sidebar logo changed, reload to see updated layout
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(() => showToast('Network error. Please try again.', 'error'))
    .finally(() => {
        if (btn) { btn.disabled = false; btn.innerHTML = btn.getAttribute('data-original-label') || 'Save'; }
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 z-50 px-5 py-3 rounded-lg shadow-xl text-sm font-medium text-white transition-all ' +
        (type === 'success' ? 'bg-green-500' : 'bg-red-500');
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
}

function testS3Connection() {
    const btn = event.currentTarget;
    const url = btn.dataset.url;
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Testing...';

    const form = document.getElementById('appSettingsForm');
    const formData = new FormData(form);
    formData.delete('_method');

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => showToast(data.message, data.success ? 'success' : 'error'))
    .catch(() => showToast('Network error. Please try again.', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Test Connection';
    });
}
</script>
@endpush
@endsection

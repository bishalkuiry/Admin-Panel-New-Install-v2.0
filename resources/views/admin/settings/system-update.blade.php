@extends('admin.layouts.app')

@section('title', 'System Update')

@section('content')
<div x-data="{ isSubmitting: false, fileName: '' }">

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
                <a href="{{ route('admin.settings.system-update') }}"
                   class="flex items-center gap-2 px-4 sm:px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap
                          {{ request()->routeIs('admin.settings.system-update') ? 'border-orange-500 text-orange-600 bg-orange-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span>System Update</span>
                </a>
            </nav>
        </div>
    </div>

    {{-- Page Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>System Update</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500">Upload system update ZIP package to override files, run database migrations, and clear system caches.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium flex items-center gap-2">
        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- System Status & Environment info --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="p-4 bg-white rounded-xl shadow-sm border border-gray-100">
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Software Version</span>
            <div class="text-lg font-bold text-orange-600 mt-1">v{{ $systemInfo['software_version'] }}</div>
        </div>
        <div class="p-4 bg-white rounded-xl shadow-sm border border-gray-100">
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Laravel Version</span>
            <div class="text-lg font-bold text-gray-900 mt-1">v{{ $systemInfo['laravel_version'] }}</div>
        </div>
        <div class="p-4 bg-white rounded-xl shadow-sm border border-gray-100">
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">PHP Version</span>
            <div class="text-lg font-bold text-gray-900 mt-1">v{{ $systemInfo['php_version'] }}</div>
        </div>
        <div class="p-4 bg-white rounded-xl shadow-sm border border-gray-100">
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Max Upload Size</span>
            <div class="text-lg font-bold text-gray-900 mt-1">{{ $systemInfo['max_upload_size'] }}</div>
        </div>
        <div class="p-4 bg-white rounded-xl shadow-sm border border-gray-100">
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">ZIP Extension</span>
            <div class="text-lg font-bold mt-1 {{ $systemInfo['zip_enabled'] ? 'text-emerald-600' : 'text-red-600' }}">
                {{ $systemInfo['zip_enabled'] ? '✓ Enabled' : '✗ Disabled' }}
            </div>
        </div>
    </div>

    {{-- Main Upload Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6">
        
        @if(!empty($systemInfo['is_demo_mode']))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Action Restricted: System Update is disabled in Demo Mode for security reasons.</span>
        </div>
        @else
        <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="leading-relaxed">
                <strong class="font-bold block mb-1">Important Safety Notice Before Updating:</strong>
                <p>1. Make sure you have a current backup of your database and system files.</p>
                <p>2. Upload a valid <code>.zip</code> package containing relative system paths (e.g. <code>app/</code>, <code>resources/</code>, <code>plugins/</code>, <code>routes/</code>).</p>
                <p>3. Do not close or refresh this page while update is in progress.</p>
            </div>
        </div>
        @endif

        <form action="{{ route('admin.settings.system-update.process') }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Upload System Update ZIP Package</label>
                
                <div class="relative border-2 border-dashed border-gray-300 hover:border-orange-500 rounded-2xl p-8 text-center bg-gray-50/50 hover:bg-orange-50/30 transition-all group {{ !empty($systemInfo['is_demo_mode']) ? 'opacity-60 cursor-not-allowed' : '' }}">
                    <input type="file" name="update_zip" accept=".zip" required {{ !empty($systemInfo['is_demo_mode']) ? 'disabled' : '' }}
                           class="absolute inset-0 w-full h-full opacity-0 {{ !empty($systemInfo['is_demo_mode']) ? 'cursor-not-allowed' : 'cursor-pointer z-10' }}"
                           @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                    
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-gray-900 mb-1" x-text="fileName ? fileName : 'Click or Drag & Drop ZIP file here'"></p>
                        <p class="text-xs text-gray-500">Supports .ZIP update files up to 200 MB</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="submit" :disabled="isSubmitting || {{ !empty($systemInfo['is_demo_mode']) ? 'true' : 'false' }}"
                        class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2"
                        :class="{ 'opacity-60 cursor-not-allowed': isSubmitting || {{ !empty($systemInfo['is_demo_mode']) ? 'true' : 'false' }} }">
                    <svg class="w-4 h-4" :class="{ 'animate-spin': isSubmitting }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span x-text="isSubmitting ? 'Updating System (Extracting & Migrating)...' : 'Update System Now'"></span>
                </button>
            </div>
        </form>

    </div>

    {{-- Execution Report Log --}}
    @if(session('update_report'))
    @php $report = session('update_report'); @endphp
    <div class="bg-gray-900 rounded-2xl shadow-xl border border-gray-800 p-6 text-gray-100 font-mono text-xs mb-8">
        <div class="flex items-center justify-between mb-4 border-b border-gray-800 pb-3">
            <h3 class="font-bold text-sm text-emerald-400 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>System Update Execution Log Report</span>
            </h3>
            <span class="text-gray-400 text-xs">{{ $report['updated_count'] }} files updated</span>
        </div>

        <div class="space-y-4 max-h-96 overflow-y-auto pr-2 scrollbar-thin">
            {{-- Updated Files --}}
            <div>
                <span class="text-orange-400 font-bold uppercase tracking-wider block mb-1">Files Overridden / Created:</span>
                @if(!empty($report['updated_files']))
                    <ul class="list-disc list-inside space-y-0.5 text-gray-300">
                        @foreach($report['updated_files'] as $uf)
                            <li>{{ $uf }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">No files were modified.</p>
                @endif
            </div>

            {{-- Database Migrations --}}
            <div class="border-t border-gray-800 pt-3">
                <span class="text-emerald-400 font-bold uppercase tracking-wider block mb-1">Database Migrations Execution Output:</span>
                <pre class="bg-gray-950 p-3 rounded-lg text-emerald-300 overflow-x-auto whitespace-pre-wrap">{{ $report['migration'] }}</pre>
            </div>

            {{-- Cache Logs --}}
            <div class="border-t border-gray-800 pt-3">
                <span class="text-cyan-400 font-bold uppercase tracking-wider block mb-1">Cache Optimization Logs:</span>
                <pre class="bg-gray-950 p-3 rounded-lg text-cyan-300 overflow-x-auto whitespace-pre-wrap">{{ $report['cache_logs'] }}</pre>
            </div>

            @if(!empty($report['skipped_files']))
            <div class="border-t border-gray-800 pt-3">
                <span class="text-amber-400 font-bold uppercase tracking-wider block mb-1">Skipped / Protected Files:</span>
                <ul class="list-disc list-inside space-y-0.5 text-amber-200">
                    @foreach($report['skipped_files'] as $sf)
                        <li>{{ $sf }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($report['errors']))
            <div class="border-t border-gray-800 pt-3">
                <span class="text-red-400 font-bold uppercase tracking-wider block mb-1">Errors:</span>
                <ul class="list-disc list-inside space-y-0.5 text-red-300">
                    @foreach($report['errors'] as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection

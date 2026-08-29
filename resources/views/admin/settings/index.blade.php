@extends('admin.layouts.app')

@section('title', 'System Settings')

@section('content')
<div x-data="settingsPage()">

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

    <div class="page-header flex flex-wrap gap-3 justify-between items-center">
        <div>
            <h1 class="page-title">System Settings</h1>
            <p class="page-subtitle">Configure broadcasting, cache, queue, and other system settings</p>
        </div>
        <div class="flex gap-2">
            <button @click="runStorageLink()" :disabled="storageLinking" class="btn-secondary" :class="{ 'opacity-60 cursor-not-allowed': storageLinking }">
                <svg class="w-4 h-4" :class="{ 'animate-spin': storageLinking }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                <span x-text="storageLinking ? 'Linking...' : 'Storage Link'"></span>
            </button>
            <span x-show="storageLinkResult" x-text="storageLinkResult"
                  :class="storageLinkSuccess ? 'text-green-600' : 'text-red-600'"
                  class="text-sm self-center font-medium"></span>
            <a href="{{ route('admin.settings.clear-cache') }}" class="btn-secondary" onclick="return confirm('Clear all caches?')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Clear Cache
            </a>
        </div>
    </div>

    {{-- Driver Status Card --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="card-title">Current Status</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="text-sm text-gray-500">Active Driver</div>
                    <div class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $driverInfo['current_driver'] === 'pusher' ? 'bg-green-500' : ($driverInfo['current_driver'] === 'redis' ? 'bg-blue-500' : 'bg-yellow-500') }}"></span>
                        {{ ucfirst($driverInfo['current_driver']) }}
                    </div>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="text-sm text-gray-500">Pusher</div>
                    <div class="text-lg font-semibold {{ $driverInfo['pusher_configured'] ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $driverInfo['pusher_configured'] ? '✓ Configured' : '✗ Not Configured' }}
                    </div>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="text-sm text-gray-500">Redis</div>
                    <div class="text-lg font-semibold {{ $driverInfo['redis_available'] ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $driverInfo['redis_available'] ? '✓ Available' : '✗ Not Available' }}
                    </div>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="text-sm text-gray-500">Fallback</div>
                    <div class="text-lg font-semibold text-green-600">✓ Database/SSE</div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Tabs --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-4">
                <button type="button" @click="activeTab = 'broadcast'" 
                        :class="activeTab === 'broadcast' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-3 px-1 border-b-2 font-medium text-sm transition">
                    Broadcasting
                </button>
                <button type="button" @click="activeTab = 'cache'" 
                        :class="activeTab === 'cache' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-3 px-1 border-b-2 font-medium text-sm transition">
                    Cache & Queue
                </button>
            </nav>
        </div>

        {{-- Broadcasting Tab --}}
        <div x-show="activeTab === 'broadcast'" class="space-y-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Broadcast Driver</h3>
                </div>
                <div class="card-body">
                    @foreach($groups['broadcast']->where('key', 'driver') as $setting)
                    <div class="form-group">
                        <label class="label">{{ $setting->label }}</label>
                        <select name="{{ $setting->key }}" class="input">
                            @foreach(json_decode($setting->options, true) ?? [] as $val => $label)
                                <option value="{{ $val }}" {{ $setting->value === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint">{{ $setting->description }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Pusher Settings --}}
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h3 class="card-title">Pusher Configuration (WebSocket)</h3>
                    <button type="button" @click="testConnection('pusher')" class="btn-sm btn-secondary">
                        Test Connection
                    </button>
                </div>
                <div class="card-body">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-blue-800">
                            <strong>Pusher</strong> provides real-time WebSocket connections. Get free credentials at 
                            <a href="https://pusher.com" target="_blank" class="underline">pusher.com</a>
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($groups['broadcast']->whereIn('key', ['pusher_app_id', 'pusher_app_key', 'pusher_app_secret', 'pusher_app_cluster']) as $setting)
                        <div class="form-group">
                            <label class="label">{{ $setting->label }}</label>
                            @if($setting->type === 'select')
                                <select name="{{ $setting->key }}" class="input">
                                    @foreach(json_decode($setting->options, true) ?? [] as $val => $label)
                                        <option value="{{ $val }}" {{ $setting->value === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @elseif($setting->type === 'password')
                                <input type="password" name="{{ $setting->key }}" value="{{ $setting->value }}" class="input" placeholder="••••••••">
                            @else
                                <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}" class="input">
                            @endif
                            <p class="form-hint">{{ $setting->description }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Redis Settings --}}
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h3 class="card-title">Redis Configuration</h3>
                    <button type="button" @click="testConnection('redis')" class="btn-sm btn-secondary">
                        Test Connection
                    </button>
                </div>
                <div class="card-body">
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-purple-800">
                            <strong>Redis</strong> provides fast in-memory caching and pub/sub messaging. 
                            Required for horizontal scaling. Not available on most shared hosting.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($groups['broadcast']->whereIn('key', ['redis_host', 'redis_port', 'redis_password']) as $setting)
                        <div class="form-group">
                            <label class="label">{{ $setting->label }}</label>
                            @if($setting->type === 'password')
                                <input type="password" name="{{ $setting->key }}" value="{{ $setting->value }}" class="input" placeholder="Leave empty if none">
                            @elseif($setting->type === 'number')
                                <input type="number" name="{{ $setting->key }}" value="{{ $setting->value }}" class="input">
                            @else
                                <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}" class="input">
                            @endif
                            <p class="form-hint">{{ $setting->description }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Cache & Queue Tab --}}
        <div x-show="activeTab === 'cache'" class="space-y-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cache Configuration</h3>
                </div>
                <div class="card-body">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-yellow-800">
                            <strong>Database</strong> cache works on all hosting. Use <strong>Redis</strong> for better performance if available.
                        </p>
                    </div>
                    @foreach($groups['cache'] as $setting)
                    <div class="form-group">
                        <label class="label">{{ $setting->label }}</label>
                        <select name="{{ $setting->key }}" class="input max-w-md">
                            @foreach(json_decode($setting->options, true) ?? [] as $val => $label)
                                <option value="{{ $val }}" {{ $setting->value === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint">{{ $setting->description }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Queue Configuration</h3>
                </div>
                <div class="card-body">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-green-800">
                            <strong>Sync</strong> processes jobs immediately. <strong>Database</strong> queues jobs for background processing (requires cron).
                        </p>
                    </div>
                    @foreach($groups['queue'] as $setting)
                    <div class="form-group">
                        <label class="label">{{ $setting->label }}</label>
                        <select name="{{ $setting->key }}" class="input max-w-md">
                            @foreach(json_decode($setting->options, true) ?? [] as $val => $label)
                                <option value="{{ $val }}" {{ $setting->value === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint">{{ $setting->description }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <x-permission-btn 
                permission="settings.manage" 
                type="submit"
                class="btn-primary" 
                label="Save Settings"
            />
        </div>
    </form>

    {{-- Test Result Modal --}}
    <div x-show="showTestResult" x-transition class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showTestResult = false">
        <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
            <div class="flex items-center gap-3 mb-4">
                <div :class="testResult.success ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'" class="w-10 h-10 rounded-full flex items-center justify-center">
                    <svg x-show="testResult.success" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg x-show="!testResult.success" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h3 class="text-lg font-semibold" x-text="testResult.success ? 'Connection Successful' : 'Connection Failed'"></h3>
            </div>
            <p class="text-gray-600 mb-4" x-text="testResult.message"></p>
            <button @click="showTestResult = false" class="btn-primary w-full">Close</button>
        </div>
    </div>
</div>

<script>
function settingsPage() {
    return {
        activeTab: 'broadcast',
        showTestResult: false,
        testResult: { success: false, message: '' },
        storageLinking: false,
        storageLinkResult: '',
        storageLinkSuccess: false,

        async testConnection(driver) {
            try {
                const response = await fetch(`{{ route('admin.settings.test-broadcast') }}?driver=${driver}`);
                this.testResult = await response.json();
                this.showTestResult = true;
            } catch (error) {
                this.testResult = { success: false, message: error.message };
                this.showTestResult = true;
            }
        },

        async testNotification() {
            try {
                const response = await fetch('{{ route('admin.settings.test-notification') }}');
                const result = await response.json();
                this.testResult = { 
                    success: result.success, 
                    message: `Notification sent via ${result.driver} driver` 
                };
                this.showTestResult = true;
            } catch (error) {
                this.testResult = { success: false, message: error.message };
                this.showTestResult = true;
            }
        },

        async runStorageLink() {
            this.storageLinking = true;
            this.storageLinkResult = '';
            try {
                const res = await fetch('{{ route('admin.settings.storage-link') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                this.storageLinkSuccess = data.success;
                this.storageLinkResult = data.message;
            } catch (e) {
                this.storageLinkSuccess = false;
                this.storageLinkResult = 'Request failed: ' + e.message;
            } finally {
                this.storageLinking = false;
            }
        }
    };
}
</script>
@endsection

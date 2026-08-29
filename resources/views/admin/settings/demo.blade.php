@extends('admin.layouts.app')

@section('title', 'Demo Auto-Reset Settings')

@section('content')
<div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="min-w-0">
            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">Demo Auto-Reset Settings</h1>
            <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">Automated database and media restoration engine for live demo environment</p>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="btn-secondary text-sm flex-shrink-0 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Back to Settings</span>
        </a>
    </div>

    <!-- Alert Notices -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2-Columns: Configuration Form & Information -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Main Configuration Card -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <div class="mb-4 pb-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900 text-lg">Auto-Reset Engine Configuration</h3>
                        <p class="text-sm text-gray-500">Configure how frequently data and images are reverted back to pristine condition</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $info['is_enabled'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $info['is_enabled'] ? 'ACTIVE (Enabled)' : 'DISABLED' }}
                    </span>
                </div>

                <form action="{{ route('admin.settings.demo.update') }}" method="POST">
                    @csrf

                    <div class="space-y-5">
                        <!-- Enable/Disable Toggle -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Automated Reset Status</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $info['is_enabled'] ? 'border-primary bg-blue-50/30' : 'border-gray-200' }}">
                                    <input type="radio" name="demo_reset_enabled" value="1" {{ $info['is_enabled'] ? 'checked' : '' }} class="text-primary focus:ring-primary h-4 w-4">
                                    <span class="ml-3 text-sm font-medium text-gray-900">Enable 30-Min Auto Reset</span>
                                </label>
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ !$info['is_enabled'] ? 'border-primary bg-blue-50/30' : 'border-gray-200' }}">
                                    <input type="radio" name="demo_reset_enabled" value="0" {{ !$info['is_enabled'] ? 'checked' : '' }} class="text-primary focus:ring-primary h-4 w-4">
                                    <span class="ml-3 text-sm font-medium text-gray-900">Disable Auto Reset</span>
                                </label>
                            </div>
                        </div>

                        <!-- Interval Selection -->
                        <div>
                            <label for="demo_reset_interval" class="block text-sm font-medium text-gray-700 mb-1">Reset Frequency Interval</label>
                            <select name="demo_reset_interval" id="demo_reset_interval" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring-primary text-sm">
                                <option value="15" {{ $info['reset_interval'] == 15 ? 'selected' : '' }}>Every 15 Minutes</option>
                                <option value="30" {{ $info['reset_interval'] == 30 ? 'selected' : '' }}>Every 30 Minutes (Recommended)</option>
                                <option value="60" {{ $info['reset_interval'] == 60 ? 'selected' : '' }}>Every 1 Hour</option>
                                <option value="120" {{ $info['reset_interval'] == 120 ? 'selected' : '' }}>Every 2 Hours</option>
                                <option value="360" {{ $info['reset_interval'] == 360 ? 'selected' : '' }}>Every 6 Hours</option>
                                <option value="720" {{ $info['reset_interval'] == 720 ? 'selected' : '' }}>Every 12 Hours</option>
                                <option value="1440" {{ $info['reset_interval'] == 1440 ? 'selected' : '' }}>Every 24 Hours (Daily)</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">The Laravel scheduler will trigger clean database & image restores based on this interval.</p>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="btn-primary text-sm px-5 py-2">
                            Save Reset Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Manual Actions Card -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900 text-lg mb-2">Manual Baseline & Reset Actions</h3>
                <p class="text-sm text-gray-500 mb-6">Manually capture the current state as pristine baseline, or force an immediate reset now.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Save Baseline Action -->
                    <div class="border border-blue-100 bg-blue-50/40 p-4 rounded-lg flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                <h4 class="font-medium text-gray-900">Save Baseline Snapshot</h4>
                            </div>
                            <p class="text-xs text-gray-600 mb-4">Saves the current database state and uploaded images as the pristine standard for all future auto-resets.</p>
                        </div>
                        <form action="{{ route('admin.settings.demo.save-baseline') }}" method="POST" onsubmit="return confirm('Are you sure you want to save the current database and files as the NEW pristine baseline snapshot?');">
                            @csrf
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <span>Capture Current State as Baseline</span>
                            </button>
                        </form>
                    </div>

                    <!-- Force Reset Action -->
                    <div class="border border-amber-100 bg-amber-50/40 p-4 rounded-lg flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <h4 class="font-medium text-gray-900">Restore Baseline Now</h4>
                            </div>
                            <p class="text-xs text-gray-600 mb-4">Instantly resets the database and storage files back to the saved baseline snapshot immediately.</p>
                        </div>
                        <form action="{{ route('admin.settings.demo.reset-now') }}" method="POST" onsubmit="return confirm('Are you sure you want to IMMEDIATELY restore the database and images back to baseline state?');">
                            @csrf
                            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <span>Reset Demo Data Now</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Status & Information -->
        <div class="space-y-6">
            
            <!-- Snapshot Status Box -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900 text-base mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Baseline Snapshot Status</span>
                </h3>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <dt class="text-gray-500">Baseline Status</dt>
                        <dd class="font-medium text-gray-900">
                            @if($info['is_configured'])
                                <span class="text-green-600 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Ready
                                </span>
                            @else
                                <span class="text-amber-600 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Not Created Yet
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <dt class="text-gray-500">Database Snapshot</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $info['has_sql'] ? round($info['sql_file_size'] / 1024 / 1024, 2) . ' MB' : 'Missing' }}
                        </dd>
                    </div>

                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <dt class="text-gray-500">Public Media Snapshot</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $info['has_public'] ? 'Available' : 'Missing' }}
                        </dd>
                    </div>

                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <dt class="text-gray-500">Baseline Created At</dt>
                        <dd class="font-medium text-gray-900 text-xs">
                            {{ $info['baseline_created_at'] ? \Carbon\Carbon::parse($info['baseline_created_at'])->format('d M Y, h:i A') : 'N/A' }}
                        </dd>
                    </div>

                    <div class="flex justify-between py-1">
                        <dt class="text-gray-500">Last Restored At</dt>
                        <dd class="font-medium text-gray-900 text-xs">
                            {{ $info['last_reset_at'] ? \Carbon\Carbon::parse($info['last_reset_at'])->format('d M Y, h:i A') : 'Never' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- How It Works Guide -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                <h4 class="font-semibold text-blue-900 text-sm mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>How 30-Min Demo Reset Works</span>
                </h4>
                <ul class="text-xs text-blue-800 space-y-2 list-disc ml-4">
                    <li>Prospective customers test your demo admin panel and add/edit products or uploaded images.</li>
                    <li>Every 30 minutes, the background Laravel scheduler executes `php artisan demo:reset`.</li>
                    <li>The system imports `database.sql` and clones pristine media files to restore everything back to pristine state.</li>
                    <li>System cache is flushed automatically so all pages load fresh data immediately.</li>
                </ul>
            </div>

        </div>

    </div>
</div>
@endsection

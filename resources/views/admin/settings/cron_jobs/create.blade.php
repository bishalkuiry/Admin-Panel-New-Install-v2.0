@extends('admin.layouts.app')

@section('title', 'Create New Job')

@section('content')
<form action="{{ route('admin.scheduler-jobs.store') }}" method="POST" class="space-y-6">
    @csrf

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.scheduler-jobs.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-wide">Create New Job</h1>
                <p class="text-sm text-gray-500 mt-1">Configure a new scheduled task</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.scheduler-jobs.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-bold uppercase tracking-wider transition-all shadow-sm">
                Cancel
            </a>
            <x-permission-btn 
                permission="settings.view" 
                type="submit"
                class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-wider hover:bg-black transition-all shadow-md active:scale-[0.98] flex items-center gap-2" 
                label="Save Job"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
            />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Configuration</h3>
                
                <div class="space-y-5">
                    <!-- Name -->
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Job Name</label>
                        <input type="text" name="name" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-gray-400" placeholder="e.g. Daily Currency Update" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Type -->
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <div class="relative">
                                <select name="type" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer" onchange="toggleTargetPlaceholder(this.value)">
                                    <option value="url">URL (External Ping)</option>
                                    <option value="command">Artisan Command</option>
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Frequency -->
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                            <div class="relative">
                                <select name="frequency" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer">
                                    <option value="everyMinute">Every Minute (* * * * *)</option>
                                    <option value="everyFiveMinutes">Every 5 Minutes (*/5 * * * *)</option>
                                    <option value="hourly">Hourly (0 * * * *)</option>
                                    <option value="daily">Daily (0 0 * * *)</option>
                                    <option value="weekly">Weekly (0 0 * * 0)</option>
                                    <option value="monthly">Monthly (0 0 1 * *)</option>
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Target -->
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target</label>
                        <div class="relative">
                            <input type="text" name="target" id="targetInput" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-mono pl-10" placeholder="https://example.com/api/cron" required>
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg id="targetIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5" id="targetHint">Enter the full URL to get (e.g. https://site.com/cron).</p>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-gray-400" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Options -->
        <div class="space-y-6">
            <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Job Status</h3>
                
                <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-xl">
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Active</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Enable or disable this job</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    </label>
                </div>
            </div>
        </div>
    
    </div>
</form>

<script>
    function toggleTargetPlaceholder(type) {
        const input = document.getElementById('targetInput');
        const hint = document.getElementById('targetHint');
        const icon = document.getElementById('targetIcon');
        
        if (type === 'url') {
            input.placeholder = 'https://example.com/api/cron';
            hint.textContent = 'Enter the full URL to get (e.g. https://site.com/cron).';
            // Globe icon
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>';
        } else {
            input.placeholder = 'schedule:run';
            hint.textContent = 'Enter the artisan command signature (e.g. "inspire").';
            // Terminal icon
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>';
        }
    }
</script>
@endsection

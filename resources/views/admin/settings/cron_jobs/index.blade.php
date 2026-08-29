@extends('admin.layouts.app')

@section('title', 'Cron Jobs Manager')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-wide">Cron Jobs Manager</h1>
                <p class="text-sm text-gray-500 mt-1">Manage scheduled background tasks and services</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
             <x-permission-btn 
                permission="settings.view" 
                href="{{ route('admin.scheduler-jobs.create') }}"
                class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-wider hover:bg-black transition-all shadow-md active:scale-[0.98] flex items-center gap-2" 
                label="Add New Job"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>'
            />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main List -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Active Scheduler Jobs</h3>
                    <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">{{ $jobs->count() }} Jobs</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Job Details</th>
                                <th class="px-6 py-4">Schedule</th>
                                <th class="px-6 py-4">Last Run</th>
                                <th class="px-6 py-4 text-right">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($jobs as $job)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900">{{ $job->name }}</span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border {{ $job->type === 'url' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                                {{ $job->type }}
                                            </span>
                                            <code class="text-xs text-gray-500 bg-gray-100 px-1 rounded">{{ Str::limit($job->target, 30) }}</code>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ $job->frequency_label }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($job->last_run_at)
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1.5 {{ $job->last_run_status === 'success' ? 'text-green-600' : 'text-red-600' }} font-medium text-xs uppercase tracking-wide">
                                                <div class="w-1.5 h-1.5 rounded-full {{ $job->last_run_status === 'success' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                                {{ $job->last_run_status }}
                                            </div>
                                            <span class="text-gray-400 text-xs mt-0.5" title="{{ $job->last_run_at }}">{{ $job->last_run_at->diffForHumans() }}</span>
                                            @if($job->last_error)
                                                <div class="mt-1 group relative">
                                                    <span class="text-gray-400 border-b border-dotted border-gray-400 cursor-help text-[10px]">View Output</span>
                                                    <div class="hidden group-hover:block absolute left-0 bottom-full mb-2 w-64 p-2 bg-gray-900 text-white text-xs rounded shadow-lg z-10 break-all">
                                                        {{ Str::limit($job->last_error, 200) }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">Never run</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('admin.scheduler-jobs.toggle', $job->id) }}" method="POST" class="inline-block">
                                        @csrf 
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $job->is_active ? 'checked' : '' }}>
                                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div>
                                        </label>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('admin.scheduler-jobs.run', $job->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded transition-colors" title="Run Now">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.scheduler-jobs.edit', $job->id) }}" class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form action="{{ route('admin.scheduler-jobs.destroy', $job->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this job?');" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-400">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <h3 class="text-gray-900 font-bold">No Jobs Scheduled</h3>
                                        <p class="text-gray-500 text-sm mt-1 max-w-xs mx-auto">Create a new cron job to automate tasks and run background processes.</p>
                                        <a href="{{ route('admin.scheduler-jobs.create') }}" class="mt-4 text-primary-600 font-medium text-sm hover:underline">Create First Job</a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Setup Guide -->
            <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Server Configuration</h3>
                        <p class="text-xs text-gray-500 mt-1">To ensure your jobs run effectively, add this single cron entry to your server:</p>
                        
                        <div class="mt-3 bg-gray-900 rounded-lg p-3 relative group">
                            <code class="text-xs text-green-400 font-mono break-all block">
                                * * * * * php {{ base_path('artisan') }} schedule:run >> /dev/null 2>&1
                            </code>
                            <button onclick="navigator.clipboard.writeText(this.previousElementSibling.textContent.trim()); showToast('Copied to clipboard!')" class="absolute top-2 right-2 text-gray-400 hover:text-white p-1 rounded transition-colors opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Features</h3>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3 text-sm text-gray-600">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        <span>Command & URL Support</span>
                    </li>
                    <li class="flex items-center gap-3 text-sm text-gray-600">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        <span>Real-time Output Logging</span>
                    </li>
                    <li class="flex items-center gap-3 text-sm text-gray-600">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        <span>Execution Duration Tracking</span>
                    </li>
                    <li class="flex items-center gap-3 text-sm text-gray-600">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        <span>Detailed Status Reporting</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

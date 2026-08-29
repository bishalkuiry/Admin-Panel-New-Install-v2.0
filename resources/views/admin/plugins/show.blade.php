@extends('admin.layouts.app')

@section('title', $plugin->display_name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.plugins.index') }}" class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-900 border border-gray-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $plugin->display_name }}</h1>
                    @if($plugin->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Active</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">Inactive</span>
                    @endif
                </div>
                <div class="flex items-center gap-4 mt-1">
                    <span class="text-sm text-gray-500 font-medium tracking-tight">Version {{ $plugin->version }}</span>
                    @if($plugin->author)
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <span class="text-sm text-gray-500 font-medium">by {{ $plugin->author }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
             @if($plugin->is_active)
                <a href="{{ route('admin.plugins.settings', $plugin) }}" class="btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>
                <form method="POST" action="{{ route('admin.plugins.deactivate', $plugin) }}">
                    @csrf
                    <button type="submit" class="btn-orange-outline flex items-center gap-2" onclick="return confirm('Stop plugin?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Deactivate
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.plugins.activate', $plugin) }}">
                    @csrf
                    <button type="submit" class="btn-primary flex items-center gap-2" {{ !empty($requirementErrors) || !empty($dependencyErrors) ? 'disabled' : '' }}>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Activate Plugin
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-2xl flex gap-3 items-center animate-fade-in-down">
        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 space-y-6">
            <!-- Documentation / Description -->
            <div class="card bg-white border-0 shadow-sm overflow-hidden auto-rows-min">
                <div class="p-8">
                    <h2 class="text-lg font-bold text-gray-900 border-b border-gray-50 pb-4">Logic Overview</h2>
                    <div class="mt-6 prose prose-sm prose-indigo max-w-none text-gray-600 leading-relaxed">
                        {!! nl2br(e($plugin->description)) !!}
                    </div>
                </div>
                
                @if($plugin->pluginHooks->isNotEmpty())
                <div class="border-t border-gray-50">
                    <div class="px-8 py-6">
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-6">Registered Interactions</h3>
                        <div class="rounded-2xl border border-gray-100 overflow-hidden">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50/50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Hook Point</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Callback Module</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Weight</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($plugin->pluginHooks as $hook)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <code class="inline-flex px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold border border-indigo-100/50">{{ $hook->hook_name }}</code>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $hook->callback }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 text-center font-bold">{{ $hook->priority }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            @if(!empty($requirementErrors) || !empty($dependencyErrors))
            <div class="p-8 bg-red-50 border border-red-100 rounded-3xl space-y-4">
                <div class="flex items-center gap-3 text-red-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <h2 class="text-lg font-bold">Unresolved Conflicts</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if(!empty($requirementErrors))
                    <div class="space-y-2">
                        <h3 class="text-xs font-bold text-red-900 uppercase tracking-wider">Environment Compliance</h3>
                        <ul class="space-y-2">
                            @foreach($requirementErrors as $error)
                            <li class="flex items-start gap-2 text-sm text-red-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-400 mt-1.5 flex-shrink-0"></span>
                                {{ $error }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @if(!empty($dependencyErrors))
                    <div class="space-y-2">
                        <h3 class="text-xs font-bold text-red-900 uppercase tracking-wider">Missing Modules</h3>
                        <ul class="space-y-2">
                            @foreach($dependencyErrors as $error)
                            <li class="flex items-start gap-2 text-sm text-red-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-400 mt-1.5 flex-shrink-0"></span>
                                {{ $error }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="lg:col-span-4 space-y-6">
            <!-- Identity Card -->
            <div class="card bg-white border-0 shadow-sm p-6 overflow-hidden relative">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-50 rounded-full opacity-50"></div>
                
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Plugin Identity</h3>
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-400 border border-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Installation Origin</p>
                            <p class="text-sm font-bold text-gray-900">{{ $plugin->installed_at?->format('F d, Y') }}</p>
                        </div>
                    </div>

                    @if($plugin->author)
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-400 border border-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Developer</p>
                            @if($plugin->author_url)
                                <a href="{{ $plugin->author_url }}" target="_blank" class="text-sm font-bold text-indigo-600 hover:underline">{{ $plugin->author }}</a>
                            @else
                                <p class="text-sm font-bold text-gray-900">{{ $plugin->author }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <div class="mt-10 pt-6 border-t border-gray-50 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Package Unique ID</p>
                        <p class="text-sm font-mono text-gray-900 truncate max-w-[150px]">{{ $plugin->name }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.plugins.destroy', $plugin) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 text-gray-300 hover:text-red-500 transition-colors" onclick="return confirm('Wipe all files?')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Validation/License Card -->
            <div class="card bg-gradient-to-br from-indigo-900 to-indigo-800 border-0 shadow-xl p-8 text-white">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                        <svg class="w-5 h-5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold">Verification</h3>
                </div>

                @if($plugin->license_status === 'valid')
                    <div class="space-y-4">
                        <div class="p-4 bg-white/5 rounded-2xl border border-white/10">
                            <div class="flex items-center justify-between">
                                <p class="text-[10px] font-bold text-indigo-300 uppercase">License Status</p>
                                <span class="px-2 py-0.5 rounded-lg bg-green-500/20 text-green-300 text-[10px] font-bold">Secure</span>
                            </div>
                            <p class="text-xl font-bold mt-2">Active & Valid</p>
                        </div>
                        <p class="text-xs text-indigo-300">Verified natively {{ $plugin->license_verified_at?->diffForHumans() }}</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.plugins.license.update', $plugin) }}" class="space-y-4">
                        @csrf @method('PUT')
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-indigo-300 uppercase tracking-widest pl-1">Insert License Key</label>
                            <input type="text" name="license_key" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-indigo-400 focus:ring-2 focus:ring-white/30 transition-all font-mono text-sm" placeholder="XXXX-XXXX-XXXX-XXXX">
                        </div>
                        <button type="submit" class="w-full h-12 bg-white text-indigo-900 rounded-xl font-bold hover:bg-indigo-50 transition-all shadow-lg active:scale-95">Validate Key</button>
                    </form>
                @endif
            </div>

            <!-- Compliance Status -->
            <div class="card bg-white border-0 shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Environment Profile</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <span class="text-xs font-bold text-gray-500">PHP Target</span>
                        <span class="px-2 py-1 bg-white rounded-lg text-xs font-bold text-indigo-600 border border-gray-100 shadow-sm">{{ $plugin->requires_php ?? 'Any' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <span class="text-xs font-bold text-gray-500">Laravel Spec</span>
                        <span class="px-2 py-1 bg-white rounded-lg text-xs font-bold text-indigo-600 border border-gray-100 shadow-sm">{{ $plugin->requires_laravel ?? 'Any' }}</span>
                    </div>
                    @if($plugin->hasDependencies())
                        <div class="pt-4 mt-4 border-t border-gray-50">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Linked Modules</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($plugin->getDependencies() as $dependency)
                                    <span class="px-2.5 py-1 bg-gray-100 rounded-lg text-xs font-bold text-gray-600">{{ $dependency }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes fade-in-down {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fade-in-down 0.4s ease-out forwards;
}
.btn-orange-outline {
    @apply inline-flex items-center px-5 py-2.5 rounded-2xl text-sm font-bold border-2 border-orange-100 text-orange-600 hover:bg-orange-50 hover:border-orange-200 transition-all;
}
</style>
@endsection

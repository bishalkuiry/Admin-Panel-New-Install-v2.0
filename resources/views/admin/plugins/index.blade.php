@extends('admin.layouts.app')

@section('title', 'Plugins')

@section('content')
<div x-data="{ 
    activeTab: new URLSearchParams(window.location.search).get('tab') || 'my-plugins',
    allPluginsLoading: true,
    setTab(tab) {
        this.activeTab = tab;
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    }
}" class="space-y-6">

    {{-- Top Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Plugins Management</h1>
            <p class="text-sm text-gray-500 mt-1">Extend and customize InAllCart's features with modular plugins and marketplace extensions</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('admin.plugins.sync') }}">
                @csrf
                <button type="submit" class="btn-secondary group tooltip" title="Scan /plugins directory for new folders">
                    <svg class="w-4 h-4 transition-transform group-hover:rotate-180 duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Sync Local</span>
                </button>
            </form>
            <a href="{{ route('admin.plugins.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload ZIP
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-2xl flex gap-3 items-center animate-fade-in-down">
        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Modern Tab Navigation Bar --}}
    <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100 flex flex-wrap gap-2">
        <button 
            @click="setTab('my-plugins')"
            :class="activeTab === 'my-plugins' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20 font-bold' : 'bg-transparent text-gray-600 hover:bg-gray-100 font-medium'"
            class="flex items-center gap-2.5 px-5 py-3 rounded-xl transition-all duration-200 text-sm"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span>My Plugins</span>
            <span 
                :class="activeTab === 'my-plugins' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700'"
                class="px-2 py-0.5 rounded-full text-xs font-bold transition-colors"
            >
                {{ $plugins->total() }}
            </span>
        </button>

        <button 
            @click="setTab('all-plugins')"
            :class="activeTab === 'all-plugins' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20 font-bold' : 'bg-transparent text-gray-600 hover:bg-gray-100 font-medium'"
            class="flex items-center gap-2.5 px-5 py-3 rounded-xl transition-all duration-200 text-sm"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.6 9h16.8M3.6 15h16.8"/>
            </svg>
            <span>All Plugins</span>
            <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-700" :class="activeTab === 'all-plugins' ? 'bg-white/20 text-white' : ''">Live Marketplace</span>
        </button>
    </div>

    {{-- TAB 1: MY PLUGINS --}}
    <div x-show="activeTab === 'my-plugins'" class="space-y-6">
        <div class="card overflow-hidden border-0 shadow-sm transition-all duration-300">
            <div class="card-header border-b border-gray-100 bg-white p-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 w-full">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-gray-800">Installed Plugins</h2>
                        <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">{{ $plugins->total() }} total</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <form method="GET" class="flex items-center gap-3">
                            <input type="hidden" name="tab" value="my-plugins">
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search plugins..." class="input pl-10 h-10 text-sm" style="width: 280px;">
                                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <select name="status" class="input h-10 text-sm" style="width: 140px;" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                @if($plugins->isEmpty())
                <div class="py-24 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">No plugins found</h3>
                    <p class="text-gray-500 max-w-sm mx-auto mt-2">Connect and expand your platform by uploading your first plugin module.</p>
                    <div class="mt-8 flex items-center justify-center gap-4">
                        <a href="{{ route('admin.plugins.create') }}" class="btn-primary">Upload Plugin</a>
                        <form method="POST" action="{{ route('admin.plugins.sync') }}">
                            @csrf
                            <button type="submit" class="btn-secondary">Check /plugins folder</button>
                        </form>
                    </div>
                </div>
                @else
                <div class="grid grid-cols-1 divide-y divide-gray-50">
                    @foreach($plugins as $plugin)
                    <div class="p-6 transition-all duration-200 hover:bg-gray-50/50 group">
                        <div class="flex flex-col md:flex-row md:items-center gap-6">
                            <div class="relative flex-shrink-0">
                                <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl flex items-center justify-center shadow-lg transform transition-transform group-hover:scale-105 duration-300">
                                    @if(isset($plugin->metadata['icon_svg']))
                                        {!! $plugin->metadata['icon_svg'] !!}
                                    @else
                                        <svg class="w-10 h-10 text-white opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    @endif
                                </div>
                                @if($plugin->is_active)
                                <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 border-4 border-white rounded-full"></div>
                                @endif
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center justify-between gap-4 pr-4">
                                    <div>
                                        <div class="flex items-center gap-3">
                                            <h3 class="text-lg font-bold text-gray-900 leading-tight">{{ $plugin->display_name }}</h3>
                                            @if($plugin->license_status === 'valid')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 tracking-wider uppercase">Verified</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1 line-clamp-1 max-w-xl">{{ $plugin->description }}</p>
                                        <div class="flex flex-wrap items-center gap-y-2 gap-x-6 mt-3">
                                            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                <span class="font-medium text-gray-600">v{{ $plugin->version }}</span>
                                            </div>
                                            @if($plugin->author)
                                            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                <span class="font-medium text-gray-600">{{ $plugin->author }}</span>
                                            </div>
                                            @endif
                                            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"/></svg>
                                                <span class="font-medium text-gray-600">Installed {{ $plugin->installed_at?->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <div class="dropdown relative">
                                            <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors border border-transparent hover:border-gray-200">
                                                <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex flex-wrap items-center gap-3 mt-6">
                                    <a href="{{ route('admin.plugins.show', $plugin) }}" class="btn-primary-soft btn-sm group">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View Details
                                    </a>
                                    
                                    @if($plugin->is_active)
                                    @php
                                        $settingsUrl = route('admin.plugins.settings', $plugin);
                                        if (isset($plugin->metadata['settings_page']['route']) && Route::has($plugin->metadata['settings_page']['route'])) {
                                            $settingsUrl = route($plugin->metadata['settings_page']['route']);
                                        }
                                    @endphp
                                    <a href="{{ $settingsUrl }}" class="btn-secondary-soft btn-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Config
                                    </a>
                                    
                                    <form method="POST" action="{{ route('admin.plugins.deactivate', $plugin) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-orange-outline btn-sm" onclick="return confirm('Stop using this plugin?')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Deactivate
                                        </button>
                                    </form>
                                    @else
                                    <form method="POST" action="{{ route('admin.plugins.activate', $plugin) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-green btn-sm shadow-sm ring-1 ring-green-600/20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Activate Plugin
                                        </button>
                                    </form>
                                    @endif
                                    
                                    <div class="ml-auto">
                                        <form method="POST" action="{{ route('admin.plugins.destroy', $plugin) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all duration-300 group/delete" title="Full Uninstall" onclick="return confirm('Wipe plugin files and database data? This CANNOT be undone.')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                    {{ $plugins->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TAB 2: ALL PLUGINS (Loads store.oblifo.com/inallcart/allplugin via live proxy) --}}
    <div x-show="activeTab === 'all-plugins'" class="space-y-4">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.6 9h16.8M3.6 15h16.8"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">InAllCart Plugins Marketplace</h3>
                    <p class="text-xs text-gray-500">Live URL: <a href="https://store.oblifo.com/inallcart/allplugin" target="_blank" class="text-orange-600 font-bold hover:underline">https://store.oblifo.com/inallcart/allplugin</a></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    @click="allPluginsLoading = true; $refs.allPluginsIframe.src = $refs.allPluginsIframe.src"
                    class="btn-secondary btn-sm"
                    title="Reload page"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh Live
                </button>
                <a 
                    href="https://store.oblifo.com/inallcart/allplugin" 
                    target="_blank" 
                    rel="noopener noreferrer" 
                    class="btn-primary btn-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Open in New Tab
                </a>
            </div>
        </div>

        <div class="relative bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden min-h-[750px]">
            {{-- Loading Overlay --}}
            <div x-show="allPluginsLoading" class="absolute inset-0 bg-white/90 backdrop-blur-sm z-10 flex flex-col items-center justify-center gap-3">
                <div class="w-10 h-10 border-4 border-orange-500 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm font-semibold text-gray-600">Loading All Plugins from store.oblifo.com...</p>
                <span class="text-xs text-gray-400">https://store.oblifo.com/inallcart/allplugin</span>
            </div>

            <iframe 
                x-ref="allPluginsIframe"
                src="{{ route('admin.plugins.marketplace-feed') }}" 
                class="w-full h-[850px] border-0"
                @load="allPluginsLoading = false"
                allowfullscreen
            ></iframe>
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

.btn-primary-soft {
    @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 hover:text-indigo-800 transition-all duration-300;
}
.btn-secondary-soft {
    @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 hover:text-gray-800 transition-all duration-300;
}
.btn-orange-outline {
    @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border-2 border-orange-100 text-orange-600 hover:bg-orange-50 hover:border-orange-200 transition-all duration-300;
}
.btn-green {
    @apply inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-green-600 text-white hover:bg-green-700 transition-all duration-300;
}
</style>
@endsection

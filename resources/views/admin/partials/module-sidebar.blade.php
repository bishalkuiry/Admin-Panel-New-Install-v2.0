{{-- 📦 Module Sidebar - Accessible from everywhere except app-content page --}}
@php
if(request()->routeIs('admin.app-content.*') || request()->routeIs('admin.website-content.*')) return;
$sidebarModules = \App\Models\HomeHeaderTab::orderBy('sort_order')->get();
$currentModuleId = request()->query('tab_id') ?? $sidebarModules->first()?->id;
@endphp

{{-- Module Sidebar Toggle Button (Mobile/Desktop) --}}
<div x-data="{ 
    moduleSidebarOpen: false,
    currentModuleId: {{ $currentModuleId ?? 'null' }}
}" class="relative">
    
    {{-- Floating Toggle Button --}}
    <button 
        @click="moduleSidebarOpen = !moduleSidebarOpen"
        class="fixed right-4 top-1/2 -translate-y-1/2 z-40 bg-gradient-to-br from-orange-500 to-orange-600 text-white p-3 rounded-full shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50 hover:scale-110 transition-all duration-300 hidden lg:flex items-center justify-center group"
        title="Quick Module Access"
    >
        <svg class="w-6 h-6 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        <span class="absolute right-full mr-3 bg-gray-900 text-white text-xs px-2 py-1 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
            Modules
        </span>
    </button>

    {{-- Mobile Toggle Button (Bottom Right) --}}
    <button 
        @click="moduleSidebarOpen = !moduleSidebarOpen"
        class="fixed right-4 bottom-20 z-50 bg-gradient-to-br from-orange-500 to-orange-600 text-white p-3.5 rounded-full shadow-lg shadow-orange-500/30 lg:hidden flex items-center justify-center"
        title="Quick Module Access"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
    </button>

    {{-- Overlay --}}
    <div 
        x-show="moduleSidebarOpen" 
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="moduleSidebarOpen = false"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40"
        style="display: none;"
    ></div>

    {{-- Sidebar Panel --}}
    <div 
        x-show="moduleSidebarOpen"
        x-transition:enter="transition-transform ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed right-0 top-0 bottom-0 w-80 bg-white shadow-2xl z-50 flex flex-col"
        style="display: none;"
    >
        {{-- Header --}}
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Modules</h3>
                    <p class="text-xs text-white/70">Quick Access</p>
                </div>
            </div>
            <button @click="moduleSidebarOpen = false" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Module List --}}
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            @forelse($sidebarModules as $module)
                <a 
                    href="{{ route('admin.app-content.index', ['tab_id' => $module->id]) }}"
                    class="group flex items-center gap-3 p-3 rounded-xl border-2 transition-all duration-200 {{ $currentModuleId == $module->id ? 'border-orange-500 bg-orange-50' : 'border-gray-100 bg-white hover:border-orange-300 hover:bg-orange-50/50' }}"
                >
                    {{-- Module Image/Icon --}}
                    <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100 border border-gray-200">
                        @if($module->background_url && $module->background_type !== 'video')
                            <img src="{{ $module->background_url }}" alt="" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-50">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Module Info --}}
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-gray-900 text-sm truncate {{ $currentModuleId == $module->id ? 'text-orange-700' : '' }}">
                            {{ $module->display_name }}
                        </h4>
                        <div class="flex items-center gap-2 mt-0.5">
                            @if($module->is_active)
                                <span class="text-[10px] px-1.5 py-0.5 bg-green-100 text-green-700 rounded-full">Active</span>
                            @else
                                <span class="text-[10px] px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded-full">Hidden</span>
                            @endif
                            <span class="text-[10px] text-gray-400">{{ $module->allCards()->count() }} cards</span>
                        </div>
                    </div>

                    {{-- Active Indicator --}}
                    @if($currentModuleId == $module->id)
                        <div class="w-2 h-2 rounded-full bg-orange-500 flex-shrink-0"></div>
                    @endif
                </a>
            @empty
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">No modules yet</p>
                    <a href="{{ route('admin.home-header.index') }}" class="text-xs text-orange-600 hover:text-orange-700 mt-1 inline-block">
                        Create Module →
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="p-3 border-t border-gray-100 bg-gray-50">
            <a 
                href="{{ route('admin.home-header.index') }}"
                class="flex items-center justify-center gap-2 w-full py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-xl font-medium transition-all duration-200 text-sm shadow-md hover:shadow-lg"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add More Module
            </a>
        </div>
    </div>
</div>

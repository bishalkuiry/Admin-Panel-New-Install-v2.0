@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
{{-- Hidden SVG sprite. All dashboard widget icons are declared here once and
     referenced via <use href="#icon-..."/>. This keeps raw SVG out of Blade
     variables so we never need to output {!! !!} for widget metadata. --}}
<svg aria-hidden="true" focusable="false" width="0" height="0" style="position:absolute;width:0;height:0;overflow:hidden;" xmlns="http://www.w3.org/2000/svg">
    <symbol id="icon-widget-grid" viewBox="0 0 24 24">
        <rect x="3" y="3" width="8" height="8" rx="1.5" fill="currentColor"/>
        <rect x="13" y="3" width="8" height="8" rx="1.5" fill="currentColor"/>
        <rect x="3" y="13" width="8" height="8" rx="1.5" fill="currentColor"/>
        <rect x="13" y="13" width="8" height="8" rx="1.5" fill="currentColor"/>
    </symbol>
    <symbol id="icon-widget-horizontal" viewBox="0 0 24 24">
        <rect x="2" y="5" width="12" height="14" rx="2" fill="currentColor"/>
        <rect x="16" y="5" width="8" height="14" rx="2" fill="currentColor" opacity=".5"/>
    </symbol>
    <symbol id="icon-widget-list" viewBox="0 0 24 24">
        <rect x="2" y="3" width="20" height="5" rx="1.5" fill="currentColor"/>
        <rect x="2" y="10" width="20" height="5" rx="1.5" fill="currentColor" opacity=".65"/>
        <rect x="2" y="17" width="20" height="4" rx="1.5" fill="currentColor" opacity=".35"/>
    </symbol>
    <symbol id="icon-widget-circle" viewBox="0 0 24 24">
        <circle cx="5" cy="12" r="4" fill="currentColor"/>
        <circle cx="12" cy="12" r="4" fill="currentColor" opacity=".6"/>
        <circle cx="19" cy="12" r="4" fill="currentColor" opacity=".35"/>
    </symbol>
    <symbol id="icon-widget-card" viewBox="0 0 24 24">
        <rect x="2" y="2" width="9" height="11" rx="2" fill="currentColor"/>
        <rect x="13" y="2" width="9" height="11" rx="2" fill="currentColor" opacity=".6"/>
        <rect x="2" y="15" width="9" height="7" rx="2" fill="currentColor" opacity=".6"/>
        <rect x="13" y="15" width="9" height="7" rx="2" fill="currentColor" opacity=".35"/>
    </symbol>
    <symbol id="icon-widget-banner-full" viewBox="0 0 24 24">
        <rect x="2" y="4" width="20" height="16" rx="2" fill="currentColor"/>
    </symbol>
    <symbol id="icon-widget-banner-short" viewBox="0 0 24 24">
        <rect x="2" y="6" width="20" height="12" rx="2.5" fill="currentColor"/>
    </symbol>
    <symbol id="icon-widget-padded" viewBox="0 0 24 24">
        <rect x="1" y="1" width="22" height="22" rx="3" fill="currentColor" opacity=".12"/>
        <rect x="4" y="5" width="16" height="14" rx="2" fill="currentColor"/>
    </symbol>
    <symbol id="icon-widget-rounded" viewBox="0 0 24 24">
        <rect x="2" y="5" width="20" height="14" rx="6" fill="currentColor"/>
    </symbol>
    <symbol id="icon-widget-u-shape" viewBox="0 0 24 24">
        <rect x="2" y="2" width="20" height="10" rx="2" fill="currentColor"/>
        <rect x="2" y="14" width="9" height="8" rx="2" fill="currentColor" opacity=".7"/>
        <rect x="13" y="14" width="9" height="8" rx="2" fill="currentColor" opacity=".5"/>
    </symbol>
    <symbol id="icon-widget-cover" viewBox="0 0 24 24">
        <rect x="2" y="2" width="9" height="20" rx="2" fill="currentColor"/>
        <rect x="13" y="2" width="9" height="9" rx="2" fill="currentColor" opacity=".7"/>
        <rect x="13" y="13" width="9" height="9" rx="2" fill="currentColor" opacity=".45"/>
    </symbol>
    <symbol id="icon-widget-scroll" viewBox="0 0 24 24">
        <rect x="2" y="5" width="10" height="14" rx="2" fill="currentColor"/>
        <rect x="14" y="5" width="7" height="14" rx="2" fill="currentColor" opacity=".6"/>
        <rect x="23" y="5" width="4" height="14" rx="2" fill="currentColor" opacity=".3"/>
    </symbol>
</svg>

<div class="space-y-8 animate-fade-in">
    <!-- ═══ App Content Builder Teaser — shown FIRST for strong first impression ═══ -->
    <div id="app-builder-teaser" class="relative rounded-2xl overflow-hidden border border-gray-100 shadow-sm group"
         style="background:#f8fafc; min-height:450px;">

        {{-- Glow aura behind the card --}}
        <div class="absolute -inset-1 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
             style="background:radial-gradient(ellipse at 50% 0%,rgba(249,115,22,.12),transparent 70%);"></div>

        {{-- ── Inner builder mock-up ── --}}
        <div class="flex h-full" style="min-height:420px; max-height:450px; overflow:hidden; pointer-events:none;">

            {{-- Left dark panel — element library --}}
            <div id="lib-panel" class="flex-shrink-0 flex flex-col py-4 px-3 gap-3 overflow-hidden"
                 style="width:160px; background:#1a1a2e; border-right:1px solid rgba(255,255,255,0.07);">
                <div class="mb-1">
                    <div class="text-[8px] font-bold uppercase tracking-widest mb-0.5" style="color:#718096;">Widget Elements</div>
                    <div class="text-[7px]" style="color:#4a5568;">Click or drag to canvas</div>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 flex-shrink-0"></span>
                    <span class="text-[7.5px] font-bold uppercase tracking-widest" style="color:#718096;">Product Widgets</span>
                </div>
                <div class="grid grid-cols-2 gap-1.5">
                    @foreach([
                        ['#3b82f6','Grid','icon-widget-grid'],
                        ['#3b82f6','Horizontal','icon-widget-horizontal'],
                        ['#3b82f6','List','icon-widget-list'],
                        ['#22c55e','Circle','icon-widget-circle'],
                        ['#22c55e','Card','icon-widget-card'],
                        ['#a855f7','Banner','icon-widget-banner-full'],
                        ['#a855f7','Padded','icon-widget-padded'],
                        ['#a855f7','Rounded','icon-widget-rounded'],
                    ] as [$color,$label,$icon])
                    <div class="lib-card rounded-lg p-1.5" style="border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04);">
                        <div class="rounded flex items-center justify-center mb-1" style="height:24px; background:rgba(255,255,255,0.06);">
                            <svg class="w-3.5 h-3.5" style="color:{{ $color }};" viewBox="0 0 24 24" aria-hidden="true"><use href="#{{ $icon }}"/></svg>
                        </div>
                        <div class="text-[8px] font-semibold truncate" style="color:#e2e8f0;">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 flex-shrink-0"></span>
                    <span class="text-[7.5px] font-bold uppercase tracking-widest" style="color:#718096;">Brand / Store</span>
                </div>
                <div class="grid grid-cols-2 gap-1.5">
                    @foreach([
                        ['#eab308','Circle','icon-widget-circle'],
                        ['#eab308','Card','icon-widget-card'],
                        ['#eab308','Banner','icon-widget-banner-short'],
                        ['#f97316','U-Shape','icon-widget-u-shape'],
                        ['#f97316','Cover','icon-widget-cover'],
                        ['#f97316','Scroll','icon-widget-scroll'],
                    ] as [$color,$label,$icon])
                    <div class="lib-card rounded-lg p-1.5" style="border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04);">
                        <div class="rounded flex items-center justify-center mb-1" style="height:24px; background:rgba(255,255,255,0.06);">
                            <svg class="w-3.5 h-3.5" style="color:{{ $color }};" viewBox="0 0 24 24" aria-hidden="true"><use href="#{{ $icon }}"/></svg>
                        </div>
                        <div class="text-[8px] font-semibold truncate" style="color:#e2e8f0;">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Center canvas --}}
            <div class="flex-1 flex flex-col overflow-hidden bg-slate-100">
                <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 border-b border-slate-200 flex-shrink-0">
                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                    <span class="text-[9px] font-semibold text-slate-500 uppercase tracking-wider">Page Canvas</span>
                    <span id="widget-count-badge" class="ml-1 inline-flex items-center px-1 py-0.5 rounded text-[8px] font-bold bg-slate-200 text-slate-500">8 widgets</span>
                    <span class="ml-auto text-[8px] text-slate-400 hidden sm:block">Drag to reorder • Click to edit</span>
                </div>
                <div id="canvas-rows-list" class="flex-1 p-3 space-y-2 overflow-hidden">
                    @foreach([
                        ['#dbeafe','#2563eb','Product','Featured Products','Grid','2×3'],
                        ['#dbeafe','#2563eb','Product','Recently Added','Horizontal',''],
                        ['#dcfce7','#16a34a','Category','Top Categories','Circle',''],
                        ['#dcfce7','#16a34a','Category','Category Tabs','Tabs+Items',''],
                        ['#f3e8ff','#9333ea','Media','Summer Sale Banner','Full Width',''],
                        ['#f3e8ff','#9333ea','Media','Promo Slider','Padded',''],
                        ['#ffedd5','#ea580c','Store','Our Stores','Horizontal',''],
                        ['#ffedd5','#ea580c','Store','Store Feed','Cover',''],
                    ] as [$iconBg,$iconColor,$type,$title,$style,$grid])
                    <div class="canvas-row bg-white rounded-lg border border-gray-200 flex items-center gap-2.5 px-3 py-2 shadow-sm">
                        <div class="flex flex-col gap-0.5 flex-shrink-0 opacity-30">
                            <div class="flex gap-0.5"><span class="w-1 h-1 rounded-full bg-gray-400"></span><span class="w-1 h-1 rounded-full bg-gray-400"></span></div>
                            <div class="flex gap-0.5"><span class="w-1 h-1 rounded-full bg-gray-400"></span><span class="w-1 h-1 rounded-full bg-gray-400"></span></div>
                        </div>
                        <div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0" style="background:{{ $iconBg }}; color:{{ $iconColor }};">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </div>
                        <div class="flex items-center gap-1.5 flex-1 min-w-0">
                            <span class="text-[10px] font-semibold text-gray-700 truncate">{{ $title }}</span>
                            <span class="inline-flex px-1 py-0.5 rounded text-[8px] font-bold uppercase" style="background:{{ $iconBg }}; color:{{ $iconColor }};">{{ $type }}</span>
                            <span class="inline-flex px-1 py-0.5 rounded text-[8px] bg-gray-100 text-gray-500">{{ $style }}</span>
                            @if($grid)<span class="inline-flex px-1 py-0.5 rounded text-[8px] bg-indigo-50 text-indigo-500">{{ $grid }}</span>@endif
                        </div>
                        <div class="w-5 h-3 rounded-full bg-emerald-400 flex items-center px-0.5 flex-shrink-0">
                            <span class="w-2 h-2 bg-white rounded-full ml-auto shadow-sm"></span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right phone preview stub --}}
            <div class="hidden xl:flex flex-col flex-shrink-0 bg-slate-50 border-l border-gray-200 overflow-hidden" style="width:220px;">
                {{-- Panel header --}}
                <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200/70" style="background:#f8fafc;">
                    <span class="text-[7.5px] font-bold uppercase tracking-widest text-slate-400">Live Preview</span>
                    <span id="phone-live-dot" class="flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400" style="box-shadow:0 0 0 2px rgba(52,211,153,0.3);"></span>
                        <span class="text-[6px] font-semibold text-emerald-500 uppercase tracking-wide">Live</span>
                    </span>
                </div>
                <div class="flex-1 flex items-start justify-center pt-4 px-4">
                    <div id="phone-frame" class="w-full rounded-3xl overflow-hidden shadow-lg border-4 border-gray-800" style="background:#fff; aspect-ratio:9/19; transition:box-shadow 0.3s ease;">
                        <div class="h-full flex flex-col">
                            {{-- Status bar --}}
                            <div class="h-8 bg-gray-800 flex items-center justify-between px-4 pt-1">
                                <div class="text-[7px] text-white font-medium">9:41</div>
                                <div class="flex gap-1">
                                    <div class="w-2 h-2 bg-white rounded-sm"></div>
                                    <div class="w-3 h-1.5 bg-white rounded-sm"></div>
                                </div>
                            </div>
                            {{-- Screen content --}}
                            <div id="phone-preview-screen" class="flex-1 p-2 space-y-2 overflow-hidden" style="scroll-behavior:smooth;">
                                {{-- Media Banner --}}
                                <div class="relative h-12 rounded-xl bg-gradient-to-r from-orange-400 via-pink-500 to-purple-500 shadow-lg overflow-hidden">
                                    <div class="absolute inset-0 bg-black/10"></div>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="text-center">
                                            <div class="text-[7px] font-bold text-white">Special Offer</div>
                                            <div class="text-[5px] text-white/90">Up to 70% OFF</div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Product Slider - 4 in one line --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="text-[7px] font-bold text-gray-700 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            Products
                                        </div>
                                        <div class="text-[5px] text-blue-500 font-semibold">See All</div>
                                    </div>
                                    <div class="flex gap-1.5 overflow-hidden">
                                        <div class="flex-shrink-0 w-16 bg-white rounded-lg shadow-sm border border-gray-100 p-1">
                                            <div class="w-full aspect-square bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg mb-1"></div>
                                            <div class="w-3/4 h-1 bg-gray-200 rounded mx-auto"></div>
                                        </div>
                                        <div class="flex-shrink-0 w-16 bg-white rounded-lg shadow-sm border border-gray-100 p-1">
                                            <div class="w-full aspect-square bg-gradient-to-br from-green-100 to-green-200 rounded-lg mb-1"></div>
                                            <div class="w-3/4 h-1 bg-gray-200 rounded mx-auto"></div>
                                        </div>
                                        <div class="flex-shrink-0 w-16 bg-white rounded-lg shadow-sm border border-gray-100 p-1">
                                            <div class="w-full aspect-square bg-gradient-to-br from-purple-100 to-purple-200 rounded-lg mb-1"></div>
                                            <div class="w-3/4 h-1 bg-gray-200 rounded mx-auto"></div>
                                        </div>
                                        <div class="flex-shrink-0 w-16 bg-white rounded-lg shadow-sm border border-gray-100 p-1">
                                            <div class="w-full aspect-square bg-gradient-to-br from-orange-100 to-orange-200 rounded-lg mb-1"></div>
                                            <div class="w-3/4 h-1 bg-gray-200 rounded mx-auto"></div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Category Grid - 4x2 --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="text-[7px] font-bold text-gray-700 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                            Categories
                                        </div>
                                        <div class="text-[5px] text-green-500 font-semibold">See All</div>
                                    </div>
                                    <div class="grid grid-cols-4 gap-1.5">
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-red-100 to-red-200 shadow-sm mb-0.5 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-red-400 rounded-full"></div>
                                            </div>
                                            <span class="text-[5px] text-gray-600 font-medium">Food</span>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-100 to-yellow-200 shadow-sm mb-0.5 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-yellow-400 rounded-full"></div>
                                            </div>
                                            <span class="text-[5px] text-gray-600 font-medium">Grocery</span>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 shadow-sm mb-0.5 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-blue-400 rounded-full"></div>
                                            </div>
                                            <span class="text-[5px] text-gray-600 font-medium">Fresh</span>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-100 to-green-200 shadow-sm mb-0.5 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-green-400 rounded-full"></div>
                                            </div>
                                            <span class="text-[5px] text-gray-600 font-medium">Meat</span>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-100 to-purple-200 shadow-sm mb-0.5 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-purple-400 rounded-full"></div>
                                            </div>
                                            <span class="text-[5px] text-gray-600 font-medium">Bakery</span>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-pink-100 to-pink-200 shadow-sm mb-0.5 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-pink-400 rounded-full"></div>
                                            </div>
                                            <span class="text-[5px] text-gray-600 font-medium">Dairy</span>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-100 to-indigo-200 shadow-sm mb-0.5 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-indigo-400 rounded-full"></div>
                                            </div>
                                            <span class="text-[5px] text-gray-600 font-medium">Frozen</span>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-100 to-teal-200 shadow-sm mb-0.5 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-teal-400 rounded-full"></div>
                                            </div>
                                            <span class="text-[5px] text-gray-600 font-medium">Snacks</span>
                                        </div>
                                    </div>
                                </div>
                                {{-- Brand Round Slider - 4 in one line --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="text-[7px] font-bold text-gray-700 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                            Top Brands
                                        </div>
                                        <div class="text-[5px] text-amber-500 font-semibold">See All</div>
                                    </div>
                                    <div class="flex gap-2 overflow-hidden">
                                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br from-amber-100 to-amber-200 shadow-sm border-2 border-amber-300 flex items-center justify-center">
                                            <div class="w-4 h-4 bg-amber-400 rounded-full"></div>
                                        </div>
                                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 shadow-sm border-2 border-blue-300 flex items-center justify-center">
                                            <div class="w-4 h-4 bg-blue-400 rounded-full"></div>
                                        </div>
                                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br from-green-100 to-green-200 shadow-sm border-2 border-green-300 flex items-center justify-center">
                                            <div class="w-4 h-4 bg-green-400 rounded-full"></div>
                                        </div>
                                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br from-purple-100 to-purple-200 shadow-sm border-2 border-purple-300 flex items-center justify-center">
                                            <div class="w-4 h-4 bg-purple-400 rounded-full"></div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Store Horizontal Slider - 2 stores --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="text-[7px] font-bold text-gray-700 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            Stores
                                        </div>
                                        <div class="text-[5px] text-purple-500 font-semibold">See All</div>
                                    </div>
                                    <div class="flex gap-2 overflow-hidden">
                                        <div class="flex-shrink-0 w-20 bg-white rounded-xl shadow-sm border border-gray-100 p-1.5">
                                            <div class="w-full h-10 bg-gradient-to-br from-purple-100 to-purple-200 rounded-lg mb-1"></div>
                                            <div class="w-3/4 h-1 bg-gray-200 rounded mb-0.5"></div>
                                            <div class="w-1/2 h-1 bg-gray-300 rounded"></div>
                                        </div>
                                        <div class="flex-shrink-0 w-20 bg-white rounded-xl shadow-sm border border-gray-100 p-1.5">
                                            <div class="w-full h-10 bg-gradient-to-br from-pink-100 to-pink-200 rounded-lg mb-1"></div>
                                            <div class="w-3/4 h-1 bg-gray-200 rounded mb-0.5"></div>
                                            <div class="w-1/2 h-1 bg-gray-300 rounded"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Home indicator --}}
                            <div class="h-5 bg-white flex items-center justify-center">
                                <div class="w-12 h-1 bg-gray-300 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Drag-and-drop simulation ghost ── --}}
        <div id="drag-ghost" style="position:absolute;pointer-events:none;z-index:60;opacity:0;display:flex;align-items:center;gap:5px;padding:5px 10px;border-radius:8px;border:1.5px solid rgba(249,115,22,0.55);background:rgba(255,255,255,0.97);box-shadow:0 8px 28px rgba(0,0,0,0.18),0 0 0 1px rgba(249,115,22,0.15);font-size:8px;font-weight:700;color:#374151;white-space:nowrap;will-change:transform,opacity,top,left;top:0;left:0;">
            <span id="ghost-dot" style="width:7px;height:7px;border-radius:50%;background:#3b82f6;flex-shrink:0;"></span>
            <span id="ghost-title">Featured Products</span>
            <svg style="width:9px;height:9px;flex-shrink:0;color:#f97316;" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        </div>
        {{-- Drop zone indicator --}}
        <div id="drop-zone-indicator" style="position:absolute;pointer-events:none;z-index:55;opacity:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,rgba(249,115,22,0.7),transparent);border-radius:4px;transition:opacity 0.2s ease;"></div>

        {{-- ── Bottom gradient fade + CTA overlay ── --}}
        <div class="absolute inset-x-0 bottom-0 flex flex-col items-center justify-end pb-6 pt-16"
             style="background:linear-gradient(to bottom, transparent 0%, rgba(248,250,252,0.75) 35%, rgba(248,250,252,0.97) 65%, #f8fafc 100%);">
            <div class="flex flex-wrap items-center justify-center gap-2 mb-4">
                @foreach([
                    ['#3b82f6','Drag & Drop Builder'],
                    ['#a855f7','Live Phone Preview'],
                    ['#22c55e','Background Media'],
                    ['#f97316','Grid & Animation'],
                    ['#eab308','Multi-Widget Types'],
                ] as [$dot,$badge])
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold text-gray-700 bg-white shadow-sm border border-gray-100">
                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:{{ $dot }};"></span>
                    {{ $badge }}
                </span>
                @endforeach
            </div>
            <a href="{{ route('admin.app-content.index') }}"
               class="inline-flex items-center gap-2.5 px-7 py-3 rounded-2xl text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition-all duration-200 hover:scale-105 hover:shadow-xl hover:shadow-orange-500/40 active:scale-100"
               style="background:linear-gradient(135deg,#f97316 0%,#ea580c 100%);">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span id="dya-typewriter" style="min-width:110px;display:inline-block;"></span>
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <script>
            (function(){
                var el = document.getElementById('dya-typewriter');
                if(!el) return;
                var phrases = ['Design Your App','Build Your Store','Customize Layout','Launch Your Brand'];
                var pi = 0, ci = 0, deleting = false, pause = 0;
                function tick(){
                    var word = phrases[pi];
                    if(pause > 0){ pause--; setTimeout(tick,120); return; }
                    if(!deleting){
                        el.textContent = word.slice(0,++ci);
                        if(ci === word.length){ deleting = true; pause = 14; }
                        setTimeout(tick, 85);
                    } else {
                        el.textContent = word.slice(0,--ci);
                        if(ci === 0){ deleting = false; pi = (pi+1)%phrases.length; pause = 4; }
                        setTimeout(tick, 45);
                    }
                }
                tick();
            })();
            </script>
        </div>

        {{-- Top-right label badge --}}
        <div class="absolute top-3 right-3 flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-sm border border-orange-100 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
            <span class="text-[10px] font-bold text-orange-600 uppercase tracking-wider">Mobile App Builder</span>
        </div>
    </div>
    <!-- ═══ End App Builder Teaser ═══ -->

    <!-- Premium Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">System Overview</h1>
            <p class="text-sm text-gray-500 mt-1">Hello, {{ auth()->user()->name }}. Here's what's happening today, {{ now()->format('l, jS F') }}.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard.report') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Report
            </a>
            <a href="{{ route('admin.settings.index') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Settings
            </a>
        </div>
    </div>

    <!-- Main Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Revenue Card -->
        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="group block bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[10px] font-semibold text-green-600 bg-green-50 px-1.5 py-0.5 rounded-full">+12%</span>
            </div>
            <div class="mt-3">
                <p class="text-xs font-medium text-gray-500">Gross Revenue</p>
                <h3 class="text-lg font-bold text-gray-900 mt-0.5">{{ \App\Helpers\CurrencyHelper::format($stats['total_revenue']) }}</h3>
            </div>
        </a>

        <!-- Commission Card -->
        <a href="{{ route('admin.commissions.index') }}" class="group block bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <span class="text-[10px] font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded-full">Earnings</span>
            </div>
            <div class="mt-3">
                <p class="text-xs font-medium text-gray-500">Net Commission</p>
                <h3 class="text-lg font-bold text-gray-900 mt-0.5">{{ \App\Helpers\CurrencyHelper::format($stats['total_commission']) }}</h3>
            </div>
        </a>

        <!-- Orders Card -->
        <a href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}" class="group block bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <span class="text-[10px] font-semibold text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded-full">{{ $stats['pending_orders'] }} New</span>
            </div>
            <div class="mt-3">
                <p class="text-xs font-medium text-gray-500">Active Orders</p>
                <h3 class="text-lg font-bold text-gray-900 mt-0.5">{{ number_format($stats['processing_orders']) }}</h3>
            </div>
        </a>

        <!-- Active Items Card -->
        <a href="{{ route('admin.products.index') }}" class="group block bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-full">Products</span>
            </div>
            <div class="mt-3">
                <p class="text-xs font-medium text-gray-500">Active Items</p>
                <h3 class="text-lg font-bold text-gray-900 mt-0.5">{{ number_format($stats['active_products']) }}</h3>
            </div>
        </a>

        <!-- Stores Card -->
        <a href="{{ route('admin.stores.index') }}" class="group block bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <span class="text-[10px] font-semibold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-full">{{ $stats['pending_stores'] }} New</span>
            </div>
            <div class="mt-3">
                <p class="text-xs font-medium text-gray-500">Active Stores</p>
                <h3 class="text-lg font-bold text-gray-900 mt-0.5">{{ number_format($stats['active_stores']) }}</h3>
            </div>
        </a>
    </div>

    <!-- Analytics & Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Revenue Chart -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Revenue Overview</h3>
                    <p class="text-xs text-gray-500">Track your daily sales performance</p>
                </div>
                <select class="text-xs bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-orange-500/20">
                    <option>Last 7 Days</option>
                    <option>Last 30 Days</option>
                </select>
            </div>
            <div class="h-[300px]">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Order Breakdown -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Order Summary</h3>
            <p class="text-xs text-gray-500 mb-6">Current order status distribution</p>
            
            <div class="flex-1 space-y-4">
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="flex items-center justify-between p-3 rounded-xl bg-orange-50/50 hover:bg-orange-100 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                        <span class="text-sm font-medium text-gray-700">Pending</span>
                    </div>
                    <span class="text-sm font-bold text-gray-900">{{ $stats['pending_orders'] }}</span>
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}" class="flex items-center justify-between p-3 rounded-xl bg-blue-50/50 hover:bg-blue-100 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        <span class="text-sm font-medium text-gray-700">Processing</span>
                    </div>
                    <span class="text-sm font-bold text-gray-900">{{ $stats['processing_orders'] }}</span>
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="flex items-center justify-between p-3 rounded-xl bg-green-50/50 hover:bg-green-100 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <span class="text-sm font-medium text-gray-700">Delivered</span>
                    </div>
                    <span class="text-sm font-bold text-gray-900">Live View</span>
                </a>
                <div class="flex flex-col gap-2 pt-4">
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                        <span>Platform Growth</span>
                        <span>78%</span>
                    </div>
                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-orange-400 to-orange-600 rounded-full" style="width: 78%"></div>
                    </div>
                </div>
            </div>
            
            <a href="{{ route('admin.orders.index') }}" class="mt-6 w-full py-3 bg-gray-50 text-gray-700 text-sm font-bold rounded-xl text-center hover:bg-gray-100 transition-colors">
                Manage All Orders
            </a>
        </div>
    </div>

    <!-- Data Tables Section -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <!-- Recent Orders -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Recent Transactions</h3>
                    <p class="text-xs text-gray-500">Latest orders across the platform</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700 transition-colors">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Store</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Amount</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentOrders as $order)
                        <tr class="hover:bg-gray-50/50 transition-colors group cursor-pointer" onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900">#{{ $order->order_number }}</span>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $order->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-[10px] font-bold">
                                        {{ substr($order->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ $order->user->name ?? 'Deleted User' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-medium text-gray-500 bg-gray-50 px-2 py-1 rounded">{{ $order->store->name ?? 'Direct' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold text-gray-900">{{ \App\Helpers\CurrencyHelper::format($order->total) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold uppercase {{ $order->status->color() }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending Store Approvals -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col">
            <div class="p-6 border-b border-gray-50">
                <h3 class="text-lg font-bold text-gray-900">Store verifications</h3>
                <p class="text-xs text-gray-500 mt-1">Sellers waiting for documentation check</p>
            </div>
            <div class="flex-1 p-6 space-y-4">
                @forelse($pendingStores as $store)
                <a href="{{ route('admin.stores.show', $store) }}" class="flex items-center justify-between p-4 border border-gray-50 rounded-2xl hover:border-orange-200 hover:bg-orange-50/10 transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-100 overflow-hidden ring-2 ring-white shadow-sm">
                            @if($store->logo)
                                <img src="{{ storage_url($store->logo) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-50">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                            @endif
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 group-hover:text-orange-600 transition-colors">{{ $store->name }}</h4>
                            <p class="text-xs text-gray-500">{{ $store->email }}</p>
                            <p class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Joined {{ $store->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    <div class="p-2 text-gray-400 group-hover:text-orange-600 group-hover:bg-white rounded-lg transition-all shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                @empty
                <div class="h-full flex flex-col items-center justify-center text-center opacity-40">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-medium">No pending store approvals</p>
                </div>
                @endforelse
            </div>
            <div class="p-4 bg-gray-50/50 mt-auto border-t border-gray-50">
                <a href="{{ route('admin.stores.pending') }}" class="flex items-center justify-center gap-2 text-xs font-bold text-gray-600 hover:text-orange-600 transition-colors uppercase tracking-wider">
                    Show All Requests
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@keyframes phone-live-pulse {
    0%,100% { box-shadow: 0 0 0 2px rgba(52,211,153,0.3); }
    50%      { box-shadow: 0 0 0 4px rgba(52,211,153,0.5); }
}
#phone-live-dot span:first-child {
    animation: phone-live-pulse 2s ease-in-out infinite;
}
@keyframes phone-widget-flash {
    0%   { background: rgba(249,115,22,0.08); }
    100% { background: transparent; }
}
.phone-widget-enter {
    animation: phone-widget-flash 0.6s ease forwards;
}
</style>
@endpush

@push('scripts')
<script>
// ── App Builder Drag-and-Drop Demo Animation ──
(function () {
    'use strict';

    var container   = document.getElementById('app-builder-teaser');
    var canvasList  = document.getElementById('canvas-rows-list');
    var badge       = document.getElementById('widget-count-badge');
    var ghost       = document.getElementById('drag-ghost');
    var ghostDot    = document.getElementById('ghost-dot');
    var ghostTitle  = document.getElementById('ghost-title');
    var dropLine    = document.getElementById('drop-zone-indicator');
    var phoneScreen = document.getElementById('phone-preview-screen');

    if (!container || !canvasList || !ghost) return;

    var widgetCount = 8;
    var tplIdx = 0;

    var widgetTemplates = [
        {bg:'#dbeafe', color:'#2563eb', type:'Product',  title:'Flash Sale Items', style:'Grid'},
        {bg:'#dcfce7', color:'#16a34a', type:'Category', title:'Browse All',       style:'Circle'},
        {bg:'#f3e8ff', color:'#9333ea', type:'Media',    title:'Hero Banner',      style:'Full Width'},
        {bg:'#ffedd5', color:'#ea580c', type:'Brand',    title:'Top Brands',       style:'Scroll'},
        {bg:'#dbeafe', color:'#1d4ed8', type:'Product',  title:'New Arrivals',     style:'Horizontal'},
    ];

    function relPos(el) {
        var er = el.getBoundingClientRect();
        var cr = container.getBoundingClientRect();
        return { top: er.top - cr.top, left: er.left - cr.left, w: er.width, h: er.height };
    }

    function highlightCard(card) {
        card.style.transition = 'all 0.25s ease';
        card.style.transform  = 'scale(1.1)';
        card.style.borderColor = 'rgba(249,115,22,0.75)';
        card.style.background  = 'rgba(249,115,22,0.18)';
        card.style.boxShadow   = '0 0 0 2px rgba(249,115,22,0.35)';
    }

    function resetCard(card) {
        card.style.transition  = 'all 0.3s ease';
        card.style.transform   = '';
        card.style.borderColor = '';
        card.style.background  = '';
        card.style.boxShadow   = '';
    }

    function showGhost(fromEl, toEl, tpl, onDone) {
        var fp = relPos(fromEl);
        var tp = relPos(toEl);
        var tplColor = tpl.color;

        ghostDot.style.background = tplColor;
        ghostTitle.textContent     = tpl.title;

        // Start at card centre
        ghost.style.transition = 'none';
        ghost.style.opacity    = '0';
        ghost.style.top        = (fp.top + fp.h / 2 - 13) + 'px';
        ghost.style.left       = (fp.left + fp.w / 2 - 44) + 'px';
        ghost.style.transform  = 'scale(0.85) rotate(-3deg)';

        // Force reflow
        void ghost.offsetWidth;

        // Fly to canvas
        ghost.style.transition = 'top 0.52s cubic-bezier(0.25,0.46,0.45,0.94), left 0.52s cubic-bezier(0.25,0.46,0.45,0.94), opacity 0.22s ease, transform 0.52s ease';
        ghost.style.opacity    = '1';
        ghost.style.top        = (tp.top - 16) + 'px';
        ghost.style.left       = (tp.left + 12) + 'px';
        ghost.style.transform  = 'scale(1) rotate(0deg)';

        setTimeout(function () {
            ghost.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            ghost.style.opacity    = '0';
            ghost.style.transform  = 'scale(0.9)';
            if (onDone) onDone();
        }, 560);
    }

    function flashDropLine(y) {
        if (!dropLine) return;
        dropLine.style.top     = (y - 2) + 'px';
        dropLine.style.opacity = '1';
        setTimeout(function () { dropLine.style.opacity = '0'; }, 500);
    }

    function addCanvasRow(tpl) {
        var row = document.createElement('div');
        row.className = 'canvas-row bg-white rounded-lg border border-gray-200 flex items-center gap-2.5 px-3 py-2 shadow-sm';
        row.style.cssText = 'opacity:0;transform:translateY(-12px) scale(0.96);transition:none;';
        row.innerHTML =
            '<div class="flex flex-col gap-0.5 flex-shrink-0 opacity-30">' +
                '<div class="flex gap-0.5"><span class="w-1 h-1 rounded-full bg-gray-400"></span><span class="w-1 h-1 rounded-full bg-gray-400"></span></div>' +
                '<div class="flex gap-0.5"><span class="w-1 h-1 rounded-full bg-gray-400"></span><span class="w-1 h-1 rounded-full bg-gray-400"></span></div>' +
            '</div>' +
            '<div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0" style="background:' + tpl.bg + ';color:' + tpl.color + ';">' +
                '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>' +
            '</div>' +
            '<div class="flex items-center gap-1.5 flex-1 min-w-0">' +
                '<span class="text-[10px] font-semibold text-gray-700 truncate">' + tpl.title + '</span>' +
                '<span class="inline-flex px-1 py-0.5 rounded text-[8px] font-bold uppercase" style="background:' + tpl.bg + ';color:' + tpl.color + ';">' + tpl.type + '</span>' +
                '<span class="inline-flex px-1 py-0.5 rounded text-[8px] bg-gray-100 text-gray-500">' + tpl.style + '</span>' +
            '</div>' +
            '<div class="w-5 h-3 rounded-full bg-emerald-400 flex items-center px-0.5 flex-shrink-0">' +
                '<span class="w-2 h-2 bg-white rounded-full ml-auto shadow-sm"></span>' +
            '</div>';

        canvasList.insertBefore(row, canvasList.firstChild);
        void row.offsetWidth;
        row.style.transition = 'opacity 0.38s ease, transform 0.38s cubic-bezier(0.34,1.56,0.64,1)';
        row.style.opacity    = '1';
        row.style.transform  = 'translateY(0) scale(1)';

        widgetCount++;
        if (badge) badge.textContent = widgetCount + ' widgets';

        // Prune if too many
        var rows = canvasList.querySelectorAll('.canvas-row');
        if (rows.length > 10) {
            var last = rows[rows.length - 1];
            last.style.transition = 'opacity 0.25s ease';
            last.style.opacity = '0';
            setTimeout(function () { if (last.parentNode) last.parentNode.removeChild(last); }, 280);
        }

        // Pulse phone
        pulsePhone(tpl);
    }

    function pulsePhone(tpl) {
        if (!phoneScreen) return;

        var c  = tpl.color;
        var bg = tpl.bg;
        var t  = tpl.type.toLowerCase();
        var lbl = tpl.title;

        // Shared header row builder
        function hdr(icon, title, accentColor) {
            return '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">' +
                     '<div style="display:flex;align-items:center;gap:3px;">' +
                       '<div style="width:10px;height:10px;border-radius:3px;background:' + accentColor + '22;display:flex;align-items:center;justify-content:center;">' +
                         '<div style="width:5px;height:5px;border-radius:50%;background:' + accentColor + ';"></div>' +
                       '</div>' +
                       '<span style="font-size:6.5px;font-weight:700;color:#1e293b;">' + title + '</span>' +
                     '</div>' +
                     '<span style="font-size:5px;font-weight:600;color:' + accentColor + ';">See All ›</span>' +
                   '</div>';
        }

        var inner = '';

        if (t === 'product') {
            // 2-column product grid with image + price
            var cols = ['linear-gradient(135deg,'+bg+','+c+'44)', 'linear-gradient(135deg,#f0fdf4,#86efac44)'];
            var prices = ['$12.99','$8.49','$19.00','$5.75'];
            var cards = '';
            for (var i = 0; i < 4; i++) {
                var grad = (i % 2 === 0) ? cols[0] : cols[1];
                cards += '<div style="background:#fff;border-radius:7px;border:1px solid #f1f5f9;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.07);">' +
                           '<div style="height:28px;background:' + grad + ';position:relative;">' +
                             '<div style="position:absolute;top:3px;right:3px;width:8px;height:8px;border-radius:50%;background:' + c + ';opacity:0.2;"></div>' +
                           '</div>' +
                           '<div style="padding:4px 4px 5px;">' +
                             '<div style="height:3px;border-radius:2px;background:#e2e8f0;margin-bottom:3px;width:85%;"></div>' +
                             '<div style="display:flex;align-items:center;justify-content:space-between;">' +
                               '<span style="font-size:5.5px;font-weight:700;color:' + c + ';">' + prices[i] + '</span>' +
                               '<div style="width:10px;height:10px;border-radius:3px;background:' + c + ';display:flex;align-items:center;justify-content:center;">' +
                                 '<div style="width:4px;height:4px;background:#fff;border-radius:1px;"></div>' +
                               '</div>' +
                             '</div>' +
                           '</div>' +
                         '</div>';
            }
            inner = hdr('', lbl, c) +
                    '<div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;">' + cards + '</div>';

        } else if (t === 'category') {
            // Circle row with label + gradient ring
            var catNames = ['Veggies','Fruits','Dairy','Bakery','Meat','Frozen'];
            var catColors = [c, '#22c55e', '#f59e0b', '#a855f7', '#ef4444', '#06b6d4'];
            var circles = '';
            for (var j = 0; j < 5; j++) {
                var cc = catColors[j % catColors.length];
                circles += '<div style="display:flex;flex-direction:column;align-items:center;gap:2px;">' +
                              '<div style="width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,' + cc + '22,' + cc + '44);border:2px solid ' + cc + '55;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px ' + cc + '33;">' +
                                '<div style="width:8px;height:8px;border-radius:50%;background:' + cc + ';opacity:0.85;"></div>' +
                              '</div>' +
                              '<span style="font-size:4.5px;color:#475569;font-weight:600;white-space:nowrap;">' + catNames[j] + '</span>' +
                           '</div>';
            }
            inner = hdr('', lbl, c) +
                    '<div style="display:flex;gap:5px;justify-content:space-between;">' + circles + '</div>';

        } else if (t === 'media') {
            // Full-width hero banner with badge + text
            inner = '<div style="position:relative;height:46px;border-radius:10px;background:linear-gradient(135deg,' + c + ',' + c + 'bb,#7c3aed);overflow:hidden;box-shadow:0 4px 12px ' + c + '55;">' +
                      '<div style="position:absolute;top:-8px;right:-8px;width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.12);"></div>' +
                      '<div style="position:absolute;bottom:-12px;left:30px;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.07);"></div>' +
                      '<div style="position:absolute;inset:0;padding:8px 10px;display:flex;flex-direction:column;justify-content:center;">' +
                        '<div style="display:inline-flex;background:rgba(255,255,255,0.25);border-radius:20px;padding:1.5px 5px;margin-bottom:3px;width:fit-content;">' +
                          '<span style="font-size:4.5px;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:0.5px;">Limited Time</span>' +
                        '</div>' +
                        '<div style="height:5px;border-radius:3px;background:rgba(255,255,255,0.9);width:65%;margin-bottom:3px;"></div>' +
                        '<div style="height:3px;border-radius:2px;background:rgba(255,255,255,0.55);width:40%;"></div>' +
                      '</div>' +
                    '</div>';

        } else if (t === 'brand') {
            // Brand logo circles with gradient border + shine
            var brandColors = [c, '#f59e0b', '#3b82f6', '#22c55e', '#a855f7'];
            var brandNames  = ['Nike','H&M','Apple','Sony','Zara'];
            var bCircles = '';
            for (var k = 0; k < 4; k++) {
                var bc = brandColors[k % brandColors.length];
                bCircles += '<div style="display:flex;flex-direction:column;align-items:center;gap:2px;">' +
                               '<div style="width:26px;height:26px;border-radius:50%;background:linear-gradient(145deg,' + bc + '18,#fff);border:2px solid ' + bc + '55;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px ' + bc + '40;">' +
                                 '<div style="width:10px;height:10px;border-radius:3px;background:' + bc + ';opacity:0.8;"></div>' +
                               '</div>' +
                               '<span style="font-size:4.5px;color:#475569;font-weight:600;">' + brandNames[k] + '</span>' +
                            '</div>';
            }
            inner = hdr('', lbl, c) +
                    '<div style="display:flex;gap:8px;justify-content:space-around;">' + bCircles + '</div>';

        } else if (t === 'store') {
            // Store cards with cover image + rating badge
            var storeGrads = ['linear-gradient(135deg,'+bg+','+c+'55)', 'linear-gradient(135deg,#f0fdf4,#86efac66)'];
            var storeCards = '';
            for (var s = 0; s < 2; s++) {
                storeCards += '<div style="flex:1;background:#fff;border-radius:9px;border:1px solid #f1f5f9;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">' +
                                '<div style="height:24px;background:' + storeGrads[s % 2] + ';position:relative;">' +
                                  '<div style="position:absolute;bottom:-7px;left:6px;width:14px;height:14px;border-radius:4px;background:#fff;border:1.5px solid #e2e8f0;display:flex;align-items:center;justify-content:center;">' +
                                    '<div style="width:6px;height:6px;border-radius:2px;background:' + c + ';opacity:0.7;"></div>' +
                                  '</div>' +
                                '</div>' +
                                '<div style="padding:10px 6px 5px;">' +
                                  '<div style="height:3px;border-radius:2px;background:#e2e8f0;margin-bottom:3px;width:70%;"></div>' +
                                  '<div style="display:flex;align-items:center;gap:2px;">' +
                                    '<div style="width:5px;height:5px;border-radius:50%;background:#fbbf24;"></div>' +
                                    '<div style="height:2.5px;border-radius:2px;background:#fde68a;width:20px;"></div>' +
                                    '<span style="font-size:4.5px;color:#94a3b8;">(24)</span>' +
                                  '</div>' +
                                '</div>' +
                              '</div>';
            }
            inner = hdr('', lbl, c) +
                    '<div style="display:flex;gap:5px;">' + storeCards + '</div>';

        } else {
            // Generic promo strip fallback
            inner = '<div style="height:14px;border-radius:8px;background:linear-gradient(90deg,' + bg + ',' + c + '55);position:relative;overflow:hidden;">' +
                      '<div style="position:absolute;inset:0;display:flex;align-items:center;padding:0 8px;gap:4px;">' +
                        '<div style="width:6px;height:6px;border-radius:50%;background:' + c + ';opacity:0.8;"></div>' +
                        '<div style="height:3px;border-radius:2px;background:' + c + ';opacity:0.4;width:55%;"></div>' +
                        '<div style="margin-left:auto;height:3px;border-radius:2px;background:' + c + ';opacity:0.25;width:20%;"></div>' +
                      '</div>' +
                    '</div>';
        }

        // Wrapper with entry animation
        var wrap = document.createElement('div');
        wrap.style.cssText = 'opacity:0;transform:translateY(-10px) scale(0.93);transition:opacity 0.4s ease,transform 0.4s cubic-bezier(0.34,1.4,0.64,1);flex-shrink:0;';
        wrap.innerHTML = inner;

        phoneScreen.insertBefore(wrap, phoneScreen.firstChild);
        void wrap.offsetWidth;
        wrap.style.opacity   = '1';
        wrap.style.transform = 'translateY(0) scale(1)';
        wrap.classList.add('phone-widget-enter');

        // Flash phone frame glow
        var frame = document.getElementById('phone-frame');
        if (frame) {
            frame.style.boxShadow = '0 0 0 3px ' + c + '66, 0 8px 24px ' + c + '44';
            setTimeout(function () { frame.style.boxShadow = ''; }, 700);
        }
        // Pulse live dot
        var liveDot = document.querySelector('#phone-live-dot span:first-child');
        if (liveDot) {
            liveDot.style.background = c;
            liveDot.style.boxShadow  = '0 0 0 3px ' + c + '44';
            setTimeout(function () {
                liveDot.style.background  = '';
                liveDot.style.boxShadow   = '0 0 0 2px rgba(52,211,153,0.3)';
            }, 800);
        }

        // Prune old blocks
        var blocks = phoneScreen.children;
        if (blocks.length > 6) {
            var lastBlock = blocks[blocks.length - 1];
            lastBlock.style.transition = 'opacity 0.25s ease, max-height 0.3s ease';
            lastBlock.style.opacity = '0';
            setTimeout(function () { if (lastBlock.parentNode) lastBlock.parentNode.removeChild(lastBlock); }, 280);
        }
    }

    function runAdd(next) {
        var cards = document.querySelectorAll('#lib-panel .lib-card');
        if (!cards.length) { setTimeout(next, 800); return; }

        var card = cards[Math.floor(Math.random() * cards.length)];
        highlightCard(card);

        var tpl = widgetTemplates[tplIdx % widgetTemplates.length];
        tplIdx++;

        setTimeout(function () {
            var firstRow = canvasList.querySelector('.canvas-row');
            if (!firstRow) { resetCard(card); setTimeout(next, 800); return; }

            var tp = relPos(firstRow);
            flashDropLine(tp.top);

            showGhost(card, firstRow, tpl, function () {
                resetCard(card);
                addCanvasRow(tpl);
                setTimeout(next, 1200);
            });
        }, 300);
    }

    function runReorder(next) {
        var rows = canvasList.querySelectorAll('.canvas-row');
        if (rows.length < 3) { setTimeout(next, 600); return; }

        var idx  = Math.floor(Math.random() * (rows.length - 1));
        var row1 = rows[idx];
        var row2 = rows[idx + 1];
        var h1   = row1.offsetHeight;
        var h2   = row2.offsetHeight;
        var gap  = 8;

        row1.style.transition = 'background 0.25s ease';
        row2.style.transition = 'background 0.25s ease';
        row1.style.background = '#fff7ed';
        row2.style.background = '#fff7ed';

        setTimeout(function () {
            var dur = 'transform 0.42s cubic-bezier(0.25,0.46,0.45,0.94)';
            row1.style.transition = dur;
            row2.style.transition = dur;
            row1.style.transform  = 'translateY(' + (h2 + gap) + 'px)';
            row2.style.transform  = 'translateY(-' + (h1 + gap) + 'px)';

            setTimeout(function () {
                row1.style.transition = 'none';
                row2.style.transition = 'none';
                row1.style.transform  = '';
                row2.style.transform  = '';
                row1.style.background = '';
                row2.style.background = '';
                if (row1.parentNode) row1.parentNode.insertBefore(row2, row1);
                setTimeout(next, 1000);
            }, 250);
        }, 300);
    }

    var sequence = ['add', 'add', 'reorder', 'add', 'reorder', 'add', 'add', 'reorder'];
    var seqIdx   = 0;

    function runCycle() {
        var action = sequence[seqIdx % sequence.length];
        seqIdx++;
        if (action === 'add') {
            runAdd(runCycle);
        } else {
            runReorder(runCycle);
        }
    }

    setTimeout(runCycle, 0);
}());
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Gradient Background
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(249, 115, 22, 0.2)');
    gradient.addColorStop(1, 'rgba(249, 115, 22, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode($chartData['data']) !!},
                borderColor: '#f97316',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#f97316',
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#f97316',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ' + context.formattedValue;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(241, 245, 249, 1)',
                        drawBorder: false
                    },
                    ticks: {
                        font: { size: 11, family: 'Inter' },
                        color: '#94a3b8'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11, family: 'Inter' },
                        color: '#94a3b8'
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection

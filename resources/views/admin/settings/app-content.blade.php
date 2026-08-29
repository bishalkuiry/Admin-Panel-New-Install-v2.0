@extends('admin.layouts.app')
@section('title', 'App Content Builder')

@push('styles')
<style>
/* Hide the top header on laptop/desktop only for the App Builder page */
@media (min-width: 1024px) {
    #admin-topbar { display: none !important; }
}
</style>
{{-- Sidebar auto-collapse/restore behaviour lives in an external asset
     so this view stays free of inline UI-state scripts. --}}
<script src="{{ asset('js/sidebar-autocollapse.js') }}"></script>
@endpush

@php
$elementLibrary = [
    'product' => [
        'label' => 'Product Widgets', 'dot' => '#3b82f6', 'iconClass' => 'text-blue-600', 'bgClass' => 'bg-blue-50', 'borderClass' => 'border-blue-100 hover:border-blue-400 hover:bg-blue-50/80',
        'items' => [
            ['style' => 'style_1', 'name' => 'Grid',       'desc' => '2–4 column grid',       'svg' => '<rect x="3" y="3" width="8" height="8" rx="1.5" fill="currentColor"/><rect x="13" y="3" width="8" height="8" rx="1.5" fill="currentColor"/><rect x="3" y="13" width="8" height="8" rx="1.5" fill="currentColor"/><rect x="13" y="13" width="8" height="8" rx="1.5" fill="currentColor"/>'],
            ['style' => 'style_2', 'name' => 'Horizontal', 'desc' => 'Horizontal scroll row', 'svg' => '<rect x="2" y="5" width="12" height="14" rx="2" fill="currentColor"/><rect x="16" y="5" width="8" height="14" rx="2" fill="currentColor" opacity=".5"/>'],
            ['style' => 'style_3', 'name' => 'Item List',  'desc' => 'Vertical product rows',  'svg' => '<rect x="2" y="3" width="20" height="5" rx="1.5" fill="currentColor"/><rect x="2" y="10" width="20" height="5" rx="1.5" fill="currentColor" opacity=".65"/><rect x="2" y="17" width="20" height="4" rx="1.5" fill="currentColor" opacity=".35"/>'],
        ]
    ],

    'category' => [
        'label' => 'Category Widgets', 'dot' => '#22c55e', 'iconClass' => 'text-green-600', 'bgClass' => 'bg-green-50', 'borderClass' => 'border-green-100 hover:border-green-400 hover:bg-green-50/80',
        'items' => [
            ['style' => 'style_1', 'name' => 'Circle',         'desc' => 'Icon circles in row',       'svg' => '<circle cx="5" cy="12" r="4" fill="currentColor"/><circle cx="12" cy="12" r="4" fill="currentColor" opacity=".6"/><circle cx="19" cy="12" r="4" fill="currentColor" opacity=".35"/>'],
            ['style' => 'style_2', 'name' => 'Card Grid',      'desc' => 'Grid of category cards',   'svg' => '<rect x="2" y="2" width="9" height="11" rx="2" fill="currentColor"/><rect x="13" y="2" width="9" height="11" rx="2" fill="currentColor" opacity=".6"/><rect x="2" y="15" width="9" height="7" rx="2" fill="currentColor" opacity=".6"/><rect x="13" y="15" width="9" height="7" rx="2" fill="currentColor" opacity=".35"/>'],
            ['style' => 'style_3', 'name' => 'Tabs+Products',  'desc' => 'Category tabs with items', 'svg' => '<rect x="2" y="2" width="7" height="4" rx="1.5" fill="currentColor"/><rect x="11" y="2" width="6" height="4" rx="1.5" fill="currentColor" opacity=".4"/><rect x="19" y="2" width="3" height="4" rx="1.5" fill="currentColor" opacity=".4"/><rect x="2" y="8" width="20" height="14" rx="2" fill="currentColor" opacity=".6"/>'],
            ['style' => 'style_4', 'name' => 'Checkmark',      'desc' => 'Checklist category layout','svg' => '<circle cx="5" cy="7" r="2.5" fill="currentColor"/><rect x="10" y="6" width="12" height="2.5" rx="1.25" fill="currentColor" opacity=".6"/><circle cx="5" cy="13" r="2.5" fill="currentColor" opacity=".7"/><rect x="10" y="12" width="12" height="2.5" rx="1.25" fill="currentColor" opacity=".4"/><circle cx="5" cy="19" r="2.5" fill="currentColor" opacity=".4"/><rect x="10" y="18" width="9" height="2.5" rx="1.25" fill="currentColor" opacity=".3"/>'],
        ]
    ],
    'brand' => [
        'label' => 'Brand Widgets', 'dot' => '#eab308', 'iconClass' => 'text-yellow-600', 'bgClass' => 'bg-yellow-50', 'borderClass' => 'border-yellow-100 hover:border-yellow-400 hover:bg-yellow-50/80',
        'items' => [
            ['style' => 'style_1', 'name' => 'Circle',    'desc' => 'Circular brand logos',   'svg' => '<circle cx="5" cy="12" r="4" fill="currentColor"/><circle cx="12" cy="12" r="4" fill="currentColor" opacity=".6"/><circle cx="19" cy="12" r="4" fill="currentColor" opacity=".35"/>'],
            ['style' => 'style_2', 'name' => 'Card Grid', 'desc' => 'Grid of brand cards',    'svg' => '<rect x="2" y="2" width="9" height="11" rx="2" fill="currentColor"/><rect x="13" y="2" width="9" height="11" rx="2" fill="currentColor" opacity=".6"/><rect x="2" y="15" width="9" height="7" rx="2" fill="currentColor" opacity=".6"/><rect x="13" y="15" width="9" height="7" rx="2" fill="currentColor" opacity=".35"/>'],
            ['style' => 'style_3', 'name' => 'Banner',    'desc' => 'Full-width brand strip', 'svg' => '<rect x="2" y="6" width="20" height="12" rx="2.5" fill="currentColor"/>'],
        ]
    ],
    'media' => [
        'label' => 'Media Banner', 'dot' => '#a855f7', 'iconClass' => 'text-purple-600', 'bgClass' => 'bg-purple-50', 'borderClass' => 'border-purple-100 hover:border-purple-400 hover:bg-purple-50/80',
        'items' => [
            ['style' => 'style_1', 'name' => 'Full Width', 'desc' => 'Edge-to-edge banner',    'svg' => '<rect x="2" y="4" width="20" height="16" rx="2" fill="currentColor"/>'],
            ['style' => 'style_2', 'name' => 'Padded',     'desc' => 'Banner with side margin', 'svg' => '<rect x="1" y="1" width="22" height="22" rx="3" fill="currentColor" opacity=".12"/><rect x="4" y="5" width="16" height="14" rx="2" fill="currentColor"/>'],
            ['style' => 'style_3', 'name' => 'Rounded',    'desc' => 'Banner with round corners','svg' => '<rect x="2" y="5" width="20" height="14" rx="6" fill="currentColor"/>'],
        ]
    ],
    'store' => [
        'label' => 'Store Widget', 'dot' => '#f97316', 'iconClass' => 'text-orange-600', 'bgClass' => 'bg-orange-50', 'borderClass' => 'border-orange-100 hover:border-orange-400 hover:bg-orange-50/80',
        'items' => [
            ['style' => 'style_1', 'name' => 'U-Shape Grid',    'desc' => '1 large + 2 small',      'svg' => '<rect x="2" y="2" width="20" height="10" rx="2" fill="currentColor"/><rect x="2" y="14" width="9" height="8" rx="2" fill="currentColor" opacity=".7"/><rect x="13" y="14" width="9" height="8" rx="2" fill="currentColor" opacity=".5"/>'],
            ['style' => 'style_2', 'name' => 'Cover Grid',      'desc' => 'Full-cover store cards', 'svg' => '<rect x="2" y="2" width="9" height="20" rx="2" fill="currentColor"/><rect x="13" y="2" width="9" height="9" rx="2" fill="currentColor" opacity=".7"/><rect x="13" y="13" width="9" height="9" rx="2" fill="currentColor" opacity=".45"/>'],
            ['style' => 'style_3', 'name' => 'Horizontal Scroll','desc' => 'Swipeable store row',   'svg' => '<rect x="2" y="5" width="10" height="14" rx="2" fill="currentColor"/><rect x="14" y="5" width="7" height="14" rx="2" fill="currentColor" opacity=".6"/><rect x="23" y="5" width="4" height="14" rx="2" fill="currentColor" opacity=".3"/>'],
            ['style' => 'style_4', 'name' => 'Feed Cards',      'desc' => 'Vertical feed layout',  'svg' => '<rect x="2" y="2" width="20" height="6" rx="2" fill="currentColor"/><rect x="2" y="10" width="20" height="6" rx="2" fill="currentColor" opacity=".65"/><rect x="2" y="18" width="20" height="4" rx="2" fill="currentColor" opacity=".35"/>'],
        ]
    ],
];

// ── Quick-Option elements (shortcuts that pre-fill the modal) ──
$quickElements = [
    'product_options' => [
        'label' => 'Product Options', 'dot' => '#3b82f6', 'type' => 'product',
        'items' => [
            // Source presets
            ['name' => 'Featured',  'desc' => 'Featured products',    'options' => ['source' => 'featured'], 'svg' => '<polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" fill="currentColor"/>'],
            ['name' => 'Recent',    'desc' => 'Recently added items', 'options' => ['source' => 'recent'],   'svg' => '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 7v5l3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none"/>'],
            ['name' => 'Animated',  'desc' => 'Horizontal auto-scroll','options' => ['enable_horizontal_animation' => true, 'style' => 'style_2'], 'svg' => '<path d="M5 12h14M15 8l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M4 8l-4 4 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'],
            // BG presets
            ['name' => 'BG Color',  'desc' => 'Solid color background','options' => ['enable_background' => true, 'background_type' => 'color'],  'svg' => '<circle cx="12" cy="12" r="9" fill="currentColor"/><circle cx="12" cy="12" r="5" fill="none" stroke="white" stroke-width="1.5"/>'],
            ['name' => 'BG Image',  'desc' => 'Image background',      'options' => ['enable_background' => true, 'background_type' => 'image'],  'svg' => '<rect x="3" y="3" width="18" height="18" rx="2" fill="currentColor" opacity=".35"/><circle cx="8.5" cy="8.5" r="2" fill="currentColor"/><path d="M21 15l-5-5-4 4-2-2-5 5" fill="none" stroke="currentColor" stroke-width="1.5"/>'],
            ['name' => 'BG GIF',    'desc' => 'Animated GIF background','options' => ['enable_background' => true, 'background_type' => 'gif'],   'svg' => '<rect x="3" y="3" width="18" height="18" rx="2" fill="currentColor" opacity=".25"/><rect x="3" y="3" width="2.5" height="3.5" rx=".8" fill="currentColor"/><rect x="3" y="10" width="2.5" height="3.5" rx=".8" fill="currentColor" opacity=".7"/><rect x="3" y="17" width="2.5" height="4" rx=".8" fill="currentColor" opacity=".4"/><rect x="18.5" y="3" width="2.5" height="3.5" rx=".8" fill="currentColor"/><rect x="18.5" y="10" width="2.5" height="3.5" rx=".8" fill="currentColor" opacity=".7"/><rect x="18.5" y="17" width="2.5" height="4" rx=".8" fill="currentColor" opacity=".4"/><rect x="7.5" y="8" width="9" height="8" rx="1.5" fill="currentColor" opacity=".8"/>'],
            ['name' => 'BG Video',  'desc' => 'Video background',      'options' => ['enable_background' => true, 'background_type' => 'video'],  'svg' => '<rect x="2" y="6" width="14" height="12" rx="2" fill="currentColor" opacity=".7"/><path d="M16 10l6-3v10l-6-3V10z" fill="currentColor"/>'],
            
            // Grid presets
            ['name' => '2 per Row', 'desc' => '2 items per line',      'options' => ['style' => 'style_1', 'grid_columns' => 2, 'grid_rows' => 2], 'svg' => '<rect x="2" y="2" width="9" height="20" rx="1.5" fill="currentColor"/><rect x="13" y="2" width="9" height="20" rx="1.5" fill="currentColor" opacity=".6"/>'],
            ['name' => '3 per Row', 'desc' => '3 items per line',      'options' => ['style' => 'style_1', 'grid_columns' => 3, 'grid_rows' => 2], 'svg' => '<rect x="1" y="3" width="6" height="18" rx="1.5" fill="currentColor"/><rect x="9" y="3" width="6" height="18" rx="1.5" fill="currentColor" opacity=".7"/><rect x="17" y="3" width="6" height="18" rx="1.5" fill="currentColor" opacity=".45"/>'],
            ['name' => '4 per Row', 'desc' => '4 items per line',      'options' => ['style' => 'style_1', 'grid_columns' => 4, 'grid_rows' => 2], 'svg' => '<rect x="1" y="4" width="4.5" height="16" rx="1" fill="currentColor"/><rect x="6.5" y="4" width="4.5" height="16" rx="1" fill="currentColor" opacity=".75"/><rect x="13" y="4" width="4.5" height="16" rx="1" fill="currentColor" opacity=".55"/><rect x="18.5" y="4" width="4.5" height="16" rx="1" fill="currentColor" opacity=".35"/>'],
        ]
    ],
    'category_options' => [
        'label' => 'Category Options', 'dot' => '#22c55e', 'type' => 'category',
        'items' => [
            // Source presets
            ['name' => 'Featured',  'desc' => 'Featured categories',      'options' => ['source' => 'featured'], 'svg' => '<polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" fill="currentColor"/>'],
            ['name' => 'Recent',    'desc' => 'Recently added categories','options' => ['source' => 'recent'],   'svg' => '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 7v5l3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none"/>'],
            ['name' => 'Slider',    'desc' => 'Slider animation row',     'options' => ['enable_horizontal_animation' => true, 'style' => 'style_1'], 'svg' => '<path d="M5 12h14M15 8l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M4 8l-4 4 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'],
            // BG presets
            ['name' => 'BG Color',  'desc' => 'Solid color background',   'options' => ['enable_background' => true, 'background_type' => 'color'],  'svg' => '<circle cx="12" cy="12" r="9" fill="currentColor"/><circle cx="12" cy="12" r="5" fill="none" stroke="white" stroke-width="1.5"/>'],
            ['name' => 'BG Image',  'desc' => 'Image background',         'options' => ['enable_background' => true, 'background_type' => 'image'],  'svg' => '<rect x="3" y="3" width="18" height="18" rx="2" fill="currentColor" opacity=".35"/><circle cx="8.5" cy="8.5" r="2" fill="currentColor"/><path d="M21 15l-5-5-4 4-2-2-5 5" fill="none" stroke="currentColor" stroke-width="1.5"/>'],
            ['name' => 'BG GIF',    'desc' => 'Animated GIF background',  'options' => ['enable_background' => true, 'background_type' => 'gif'],   'svg' => '<rect x="3" y="3" width="18" height="18" rx="2" fill="currentColor" opacity=".25"/><rect x="3" y="3" width="2.5" height="3.5" rx=".8" fill="currentColor"/><rect x="3" y="10" width="2.5" height="3.5" rx=".8" fill="currentColor" opacity=".7"/><rect x="3" y="17" width="2.5" height="4" rx=".8" fill="currentColor" opacity=".4"/><rect x="18.5" y="3" width="2.5" height="3.5" rx=".8" fill="currentColor"/><rect x="18.5" y="10" width="2.5" height="3.5" rx=".8" fill="currentColor" opacity=".7"/><rect x="18.5" y="17" width="2.5" height="4" rx=".8" fill="currentColor" opacity=".4"/><rect x="7.5" y="8" width="9" height="8" rx="1.5" fill="currentColor" opacity=".8"/>'],
            ['name' => 'BG Video',  'desc' => 'Video background',         'options' => ['enable_background' => true, 'background_type' => 'video'],  'svg' => '<rect x="2" y="6" width="14" height="12" rx="2" fill="currentColor" opacity=".7"/><path d="M16 10l6-3v10l-6-3V10z" fill="currentColor"/>'],
            // Grid presets
            ['name' => '2 per Row', 'desc' => '2 items per line',         'options' => ['style' => 'style_2', 'grid_columns' => 2, 'grid_rows' => 2], 'svg' => '<rect x="2" y="2" width="9" height="20" rx="1.5" fill="currentColor"/><rect x="13" y="2" width="9" height="20" rx="1.5" fill="currentColor" opacity=".6"/>'],
            ['name' => '3 per Row', 'desc' => '3 items per line',         'options' => ['style' => 'style_2', 'grid_columns' => 3, 'grid_rows' => 2], 'svg' => '<rect x="1" y="3" width="6" height="18" rx="1.5" fill="currentColor"/><rect x="9" y="3" width="6" height="18" rx="1.5" fill="currentColor" opacity=".7"/><rect x="17" y="3" width="6" height="18" rx="1.5" fill="currentColor" opacity=".45"/>'],
            ['name' => '4 per Row', 'desc' => '4 items per line',         'options' => ['style' => 'style_2', 'grid_columns' => 4, 'grid_rows' => 2], 'svg' => '<rect x="1" y="4" width="4.5" height="16" rx="1" fill="currentColor"/><rect x="6.5" y="4" width="4.5" height="16" rx="1" fill="currentColor" opacity=".75"/><rect x="13" y="4" width="4.5" height="16" rx="1" fill="currentColor" opacity=".55"/><rect x="18.5" y="4" width="4.5" height="16" rx="1" fill="currentColor" opacity=".35"/>'],
        ]
    ],
];
@endphp

@section('content')
<div x-data="appContentManager()"
     class="-mx-3 -my-3 sm:-mx-6 sm:-my-6 flex flex-col builder-root"
     style="height:calc(100vh - 57px); min-height:640px;">

    {{-- ═══════════════ TOP BAR ═══════════════ --}}
    <div class="builder-topbar flex items-center gap-2 sm:gap-3 px-3 sm:px-5 py-2 sm:py-2.5 flex-shrink-0 overflow-x-auto scrollbar-hide" style="background:#1a1a2e;border-bottom:1px solid rgba(255,255,255,.1);box-shadow:0 2px 12px rgba(0,0,0,.3);">

        {{-- Page title (hidden on small mobile to save space) --}}
        <div class="hidden sm:flex items-center gap-2.5 mr-4 flex-shrink-0">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            </div>
            <span class="text-sm font-bold" style="color:#fff;">App Builder</span>
        </div>

        {{-- Divider (hidden on mobile) --}}
        <div class="hidden sm:block h-6 w-px mr-1" style="background:rgba(255,255,255,.15);"></div>

        {{-- Tab selector --}}
        @if($tabs->isEmpty())
        <span class="text-sm" style="color:rgba(255,255,255,.5);">No modules — <a href="{{ route('admin.home-header.index') }}" class="text-orange-400 underline">create modules first</a></span>
        @else
        <div class="flex items-center gap-1 overflow-x-auto scrollbar-hide">
            @foreach($tabs as $tab)
            <button @click="selectTab({{ $tab->id }})"
                :class="selectedTabId === {{ $tab->id }} ? 'bg-orange-500 text-white shadow-sm' : 'hover:bg-white/10'"
                :style="selectedTabId === {{ $tab->id }} ? '' : 'color:rgba(255,255,255,.55);'"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all duration-150 flex-shrink-0">
                {{ $tab->display_name }}
            </button>
            @endforeach
        </div>
        @endif

        {{-- Right actions --}}
        <div class="ml-auto flex items-center gap-3 flex-shrink-0">
            <span class="text-xs font-medium hidden sm:block" style="color:rgba(255,255,255,.4);">
                <span x-text="contents.length"></span> widgets
            </span>
            <div class="h-5 w-px" style="background:rgba(255,255,255,.15);"></div>
            {{-- Add Widget dropdown (quick access) --}}
            <!-- <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.outside="open = false"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm shadow-orange-500/30">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Add Widget
                </button>
                <div x-show="open" x-cloak x-transition
                    class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-50">
                    @foreach([['product','Product Widget','bg-blue-500'],['category','Category Widget','bg-green-500'],['brand','Brand Widget','bg-yellow-500'],['media','Media Banner','bg-purple-500'],['store','Store Widget','bg-orange-500']] as [$type, $label, $color])
                    <button @click="openAddModal('{{ $type }}'); open = false"
                        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <span class="w-2 h-2 rounded-full {{ $color }} flex-shrink-0"></span>{{ $label }}
                    </button>
                    @endforeach
                </div>
            </div> -->
        </div>
    </div>

    {{-- ═══════════════ BUILDER BODY ═══════════════ --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ────── LEFT: ELEMENT LIBRARY ────── --}}
        <div class="builder-panel-left w-64 xl:w-72 flex flex-col flex-shrink-0 overflow-hidden" style="background:#1a1a2e; border-right:1px solid rgba(255,255,255,0.07);">
            <div class="px-4 pt-4 pb-3 flex-shrink-0" style="border-bottom:1px solid rgba(255,255,255,0.07);">
                <h3 class="text-xs font-bold uppercase tracking-wider" style="color:#a0aec0;">Widget Elements</h3>
                <p class="text-[11px] mt-0.5" style="color:#4a5568;">Click or drag to canvas</p>
            </div>
            <div class="flex-1 overflow-y-auto py-3 px-3 space-y-4 element-library-scroll">
                @foreach($elementLibrary as $type => $group)
                <div class="element-group">
                    {{-- Group header --}}
                    <div class="flex items-center gap-2 px-1 mb-2">
                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $group['dot'] }}"></span>
                        <span class="text-[10.5px] font-bold uppercase tracking-wider" style="color:#718096;">{{ $group['label'] }}</span>
                    </div>
                    {{-- Cards in 2-col grid --}}
                    <div class="element-library-group grid grid-cols-2 gap-2">
                        @foreach($group['items'] as $item)
                        <div class="element-card cursor-pointer rounded-xl p-2.5 transition-all duration-150 select-none"
                             data-type="{{ $type }}" data-style="{{ $item['style'] }}"
                             @click="openAddModal('{{ $type }}', '{{ $item['style'] }}')">
                            {{-- Visual icon --}}
                            <div class="rounded-lg flex items-center justify-center mb-2" style="height:40px; background:rgba(255,255,255,0.06);">
                                <svg class="w-5 h-5" style="color:{{ $group['dot'] }};" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    {!! $item['svg'] !!}
                                </svg>
                            </div>
                            <div class="text-[11.5px] font-semibold leading-tight truncate" style="color:#e2e8f0;">{{ $item['name'] }}</div>
                            <div class="text-[10px] leading-tight mt-0.5 truncate" style="color:#4a5568;">{{ $item['desc'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                {{-- ── Quick-Options Divider ── --}}
                <div class="quick-options-divider flex items-center gap-2 px-1 mt-1">
                    <div class="flex-1 h-px" style="background:rgba(255,255,255,0.08);"></div>
                    <span class="text-[9.5px] font-bold uppercase tracking-widest px-1" style="color:#4a5568;">Quick Options</span>
                    <div class="flex-1 h-px" style="background:rgba(255,255,255,0.08);"></div>
                </div>

                {{-- ── Quick-Option groups (product/category shortcuts) ── --}}
                @foreach($quickElements as $qKey => $qGroup)
                <div class="element-group">
                    <div class="flex items-center gap-2 px-1 mb-2">
                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $qGroup['dot'] }}"></span>
                        <span class="text-[10.5px] font-bold uppercase tracking-wider" style="color:#718096;">{{ $qGroup['label'] }}</span>
                    </div>
                    <div class="element-library-group grid grid-cols-2 gap-2">
                        @foreach($qGroup['items'] as $qItem)
                        <div class="element-card element-quick cursor-pointer rounded-xl p-2.5 transition-all duration-150 select-none"
                             data-type="{{ $qGroup['type'] }}" data-style="{{ $qItem['options']['style'] ?? 'style_1' }}"
                             data-quick-options="{{ json_encode($qItem['options']) }}"
                             @click="openQuickModal('{{ $qGroup['type'] }}', {{ json_encode($qItem['options']) }})">
                            <div class="rounded-lg flex items-center justify-center mb-2" style="height:40px; background:rgba(255,255,255,0.06);">
                                <svg class="w-5 h-5" style="color:{{ $qGroup['dot'] }};" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    {!! $qItem['svg'] !!}
                                </svg>
                            </div>
                            <div class="text-[11.5px] font-semibold leading-tight truncate" style="color:#e2e8f0;">{{ $qItem['name'] }}</div>
                            <div class="text-[10px] leading-tight mt-0.5 truncate" style="color:#4a5568;">{{ $qItem['desc'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

            </div>
        </div>

        {{-- ── MOBILE LIBRARY DRAWER (slide from left) ── --}}
        <div x-show="mobileLibraryOpen" x-cloak
             class="fixed inset-0 z-40 lg:hidden"
             @click.self="mobileLibraryOpen = false">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="mobileLibraryOpen = false"></div>
            <div class="absolute top-0 left-0 bottom-0 w-72 flex flex-col overflow-hidden"
                 style="background:#1a1a2e;"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full">
                {{-- Drawer header --}}
                <div class="flex items-center justify-between px-4 py-3 flex-shrink-0" style="border-bottom:1px solid rgba(255,255,255,0.07);">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider" style="color:#a0aec0;">Widget Elements</h3>
                        <p class="text-[11px]" style="color:#4a5568;">Tap to add to canvas</p>
                    </div>
                    <button @click="mobileLibraryOpen = false" class="p-1.5 rounded-lg transition-colors" style="color:#718096;" onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='transparent'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                {{-- Drawer elements --}}
                <div class="flex-1 overflow-y-auto py-3 px-3 space-y-4 element-library-scroll">
                    @foreach($elementLibrary as $type => $group)
                    <div class="element-group">
                        <div class="flex items-center gap-2 px-1 mb-2">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $group['dot'] }}"></span>
                            <span class="text-[10.5px] font-bold uppercase tracking-wider" style="color:#718096;">{{ $group['label'] }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($group['items'] as $item)
                            <div class="element-card cursor-pointer rounded-xl p-2.5 transition-all duration-150 select-none"
                                 @click="openAddModal('{{ $type }}', '{{ $item['style'] }}'); mobileLibraryOpen = false">
                                <div class="rounded-lg flex items-center justify-center mb-2" style="height:40px; background:rgba(255,255,255,0.06);">
                                    <svg class="w-5 h-5" style="color:{{ $group['dot'] }};" viewBox="0 0 24 24">
                                        {!! $item['svg'] !!}
                                    </svg>
                                </div>
                                <div class="text-[11.5px] font-semibold leading-tight truncate" style="color:#e2e8f0;">{{ $item['name'] }}</div>
                                <div class="text-[10px] leading-tight mt-0.5 truncate" style="color:#4a5568;">{{ $item['desc'] }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                    {{-- ── Quick-Options Divider (mobile) ── --}}
                    <div class="flex items-center gap-2 px-1 mt-1">
                        <div class="flex-1 h-px" style="background:rgba(255,255,255,0.08);"></div>
                        <span class="text-[9.5px] font-bold uppercase tracking-widest px-1" style="color:#4a5568;">Quick Options</span>
                        <div class="flex-1 h-px" style="background:rgba(255,255,255,0.08);"></div>
                    </div>

                    {{-- ── Quick-Option groups (mobile) ── --}}
                    @foreach($quickElements as $qKey => $qGroup)
                    <div class="element-group">
                        <div class="flex items-center gap-2 px-1 mb-2">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $qGroup['dot'] }}"></span>
                            <span class="text-[10.5px] font-bold uppercase tracking-wider" style="color:#718096;">{{ $qGroup['label'] }}</span>
                        </div>
                        <div class="element-library-group grid grid-cols-2 gap-2">
                            @foreach($qGroup['items'] as $qItem)
                            <div class="element-card element-quick cursor-pointer rounded-xl p-2.5 transition-all duration-150 select-none"
                                 data-type="{{ $qGroup['type'] }}" data-style="{{ $qItem['options']['style'] ?? 'style_1' }}"
                                 @click="openQuickModal('{{ $qGroup['type'] }}', {{ json_encode($qItem['options']) }}); mobileLibraryOpen = false">
                                <div class="rounded-lg flex items-center justify-center mb-2" style="height:40px; background:rgba(255,255,255,0.06);">
                                    <svg class="w-5 h-5" style="color:{{ $qGroup['dot'] }};" viewBox="0 0 24 24">
                                        {!! $qItem['svg'] !!}
                                    </svg>
                                </div>
                                <div class="text-[11.5px] font-semibold leading-tight truncate" style="color:#e2e8f0;">{{ $qItem['name'] }}</div>
                                <div class="text-[10px] leading-tight mt-0.5 truncate" style="color:#4a5568;">{{ $qItem['desc'] }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>

        {{-- ────── CENTER: CANVAS ────── --}}
        <div class="builder-panel-canvas flex-1 flex flex-col overflow-hidden bg-slate-100 relative">

            {{-- Canvas sub-header --}}
            <div class="flex items-center gap-3 px-5 py-2.5 bg-slate-100 border-b border-slate-200 flex-shrink-0">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                <span class="text-xs font-semibold text-slate-500">Page Canvas</span>
                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 text-slate-500" x-text="contents.length + ' widgets'"></span>
                <p class="text-[11px] text-slate-400 ml-auto hidden md:block">Drag to reorder • Click to edit</p>
            </div>

            {{-- Mobile: floating "Elements" button ── --}}
            <button @click="mobileLibraryOpen = true"
                class="fixed bottom-6 left-4 z-30 lg:hidden flex items-center gap-2 px-4 py-3 text-white text-sm font-bold rounded-full shadow-lg shadow-black/30 transition-all active:scale-95"
                style="background:#1a1a2e; border:1px solid rgba(255,255,255,0.15);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Elements
            </button>

            {{-- Widget canvas body --}}
            <div class="flex-1 overflow-y-auto p-4">

                {{-- Empty state --}}
                <template x-if="contents.length === 0">
                    <div class="canvas-empty flex flex-col items-center justify-center py-20 text-center">
                        <div class="w-20 h-20 bg-white rounded-2xl shadow-sm border-2 border-dashed border-slate-300 flex items-center justify-center mb-4">
                            <svg class="w-9 h-9 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-500 mb-1">No widgets yet</p>
                        <p class="text-xs text-slate-400">Click elements from the left panel or drag them here</p>
                    </div>
                </template>

                {{-- Drop zone + sortable list --}}
                <div x-ref="sortableList" class="desktop-sortable space-y-2.5 min-h-[120px] canvas-drop-target">
                    <template x-for="(content, index) in contents" :key="content.id">
                        <div class="widget-card bg-white rounded-xl border border-gray-200 flex items-stretch overflow-hidden select-none transition-all duration-150 group hover:shadow-md hover:border-gray-300"
                             :data-id="content.id"
                             :class="!content.is_active ? 'opacity-60' : ''">

                            {{-- Drag handle --}}
                            <div class="drag-handle w-8 flex items-center justify-center flex-shrink-0 bg-gray-50 border-r border-gray-100 text-gray-300 hover:text-gray-500 hover:bg-gray-100 transition-colors cursor-grab active:cursor-grabbing">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 10 16">
                                    <circle cx="2.5" cy="3"  r="1.3"/><circle cx="2.5" cy="7"  r="1.3"/>
                                    <circle cx="2.5" cy="11" r="1.3"/><circle cx="2.5" cy="15" r="1.3"/>
                                    <circle cx="7.5" cy="3"  r="1.3"/><circle cx="7.5" cy="7"  r="1.3"/>
                                    <circle cx="7.5" cy="11" r="1.3"/><circle cx="7.5" cy="15" r="1.3"/>
                                </svg>
                            </div>

                            {{-- Widget body --}}
                            <div class="flex items-center gap-3 flex-1 min-w-0 px-4 py-3">

                                {{-- Order number --}}
                                <div class="w-6 h-6 rounded-md bg-gray-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-[10px] font-bold text-gray-400" x-text="index + 1"></span>
                                </div>

                                {{-- Type icon/thumb --}}
                                <template x-if="content.type === 'media' && content.media_url">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 border border-gray-100 bg-gray-50"
                                        x-html="getMediaHtml(content, 'thumb')"></div>
                                </template>
                                <template x-if="!(content.type === 'media' && content.media_url)">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" :class="getTypeIcon(content.type).bg">
                                        <span :class="getTypeIcon(content.type).text" x-html="getTypeIcon(content.type).iconSmall"></span>
                                    </div>
                                </template>

                                {{-- Widget info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-sm font-semibold text-gray-800 truncate max-w-[140px] xl:max-w-xs"
                                            x-text="content.title || getTypeLabel(content.type)"></span>

                                        {{-- Type badge --}}
                                        <span class="inline-flex px-1.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide"
                                            :class="{
                                                'bg-blue-50 text-blue-600':    content.type==='product',
                                                'bg-green-50 text-green-600':  content.type==='category',
                                                'bg-yellow-50 text-yellow-600':content.type==='brand',
                                                'bg-purple-50 text-purple-600':content.type==='media',
                                                'bg-orange-50 text-orange-600':content.type==='store'
                                            }"
                                            x-text="getTypeLabel(content.type)"></span>

                                        {{-- Style badge --}}
                                        <span class="inline-flex px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-500"
                                            x-text="getStyleName(content.type, content.style)"></span>

                                        {{-- Grid badge --}}
                                        <span x-show="['product','category'].includes(content.type) && content.grid_columns && content.grid_rows"
                                            class="inline-flex px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-indigo-50 text-indigo-500"
                                            x-text="content.grid_columns + '×' + content.grid_rows"></span>

                                        {{-- Auto-scroll badge --}}
                                        <span x-show="content.enable_horizontal_animation"
                                            class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-emerald-50 text-emerald-600">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Auto
                                        </span>
                                    </div>
                                    <div x-show="content.show_subtitle && content.subtitle" class="text-xs text-gray-400 mt-0.5 truncate" x-text="content.subtitle"></div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-1 px-3 flex-shrink-0 border-l border-gray-100">
                                {{-- Up/Down (desktop fallback) --}}
                                <div class="hidden sm:flex flex-col gap-0.5 mr-1">
                                    <button @click.stop="moveUp(content.id)" :disabled="index === 0" :class="index === 0 ? 'opacity-20 cursor-not-allowed' : 'hover:bg-gray-100'"
                                        class="p-0.5 rounded text-gray-400 transition-colors" title="Move Up">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button @click.stop="moveDown(content.id)" :disabled="index === contents.length - 1" :class="index === contents.length - 1 ? 'opacity-20 cursor-not-allowed' : 'hover:bg-gray-100'"
                                        class="p-0.5 rounded text-gray-400 transition-colors" title="Move Down">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>

                                {{-- Toggle --}}
                                <label class="toggle cursor-pointer" title="Toggle visibility">
                                    <input type="checkbox" :checked="content.is_active" @change.stop="toggleActive(content)" class="sr-only">
                                    <span class="widget-toggle-track inline-block w-8 h-4 rounded-full relative transition-colors" :class="content.is_active ? 'bg-emerald-400' : 'bg-gray-200'">
                                        <span class="absolute top-0.5 left-0.5 w-3 h-3 bg-white rounded-full shadow transition-transform" :class="content.is_active ? 'translate-x-4' : 'translate-x-0'"></span>
                                    </span>
                                </label>

                                {{-- Edit --}}
                                <button @click.stop="editContent(content)" class="p-1.5 rounded-lg text-gray-300 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                {{-- Duplicate --}}
                                <button @click.stop="duplicateContent(content)" class="p-1.5 rounded-lg text-gray-300 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Duplicate">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>

                                {{-- Delete --}}
                                <button @click.stop="deleteContent(content)" class="p-1.5 rounded-lg text-gray-300 hover:text-red-600 hover:bg-red-50 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Drop hint when canvas has items --}}
                <template x-if="contents.length > 0">
                    <div class="mt-4 flex items-center justify-center py-5 border-2 border-dashed border-slate-300 rounded-xl text-slate-400 canvas-drop-hint">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                        <span class="text-xs font-medium">Drop a widget here or use the left panel</span>
                    </div>
                </template>
            </div>
        </div>

        {{-- ────── RIGHT: PHONE PREVIEW ────── --}}
        <div class="builder-panel-preview hidden xl:flex flex-col w-[290px] bg-slate-50 border-l border-gray-200 flex-shrink-0 overflow-hidden">
            <div class="px-4 py-2.5 border-b border-gray-200 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                    <span class="text-[10.5px] font-bold text-gray-500 uppercase tracking-wider">Live Preview</span>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4 flex justify-center">
                @include('admin.settings.partials._phone-preview-desktop')
            </div>
        </div>

    </div>{{-- end builder body --}}

    {{-- ═══════════════ MODAL (inside x-data scope) ═══════════════ --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="closeModal()">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal()"></div>
    <div class="fixed inset-x-0 bottom-0 lg:inset-auto lg:top-1/2 lg:left-1/2 lg:-translate-x-1/2 lg:-translate-y-1/2 bg-white rounded-t-2xl lg:rounded-2xl shadow-2xl w-full lg:max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-y-full lg:translate-y-0 lg:opacity-0 lg:scale-95"
         x-transition:enter-end="translate-y-0 lg:opacity-100 lg:scale-100">

        {{-- Modal header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b flex-shrink-0 bg-gray-50 rounded-t-2xl">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="getTypeIcon(modalType).bg">
                    <span :class="getTypeIcon(modalType).text" x-html="getTypeIcon(modalType).iconSmall"></span>
                </div>
                <h3 class="text-base font-bold text-gray-900" x-text="editingContent ? 'Edit Widget' : 'Add ' + getTypeLabel(modalType) + ' Widget'"></h3>
            </div>
            <button @click="closeModal()" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Modal body --}}
        <div class="flex-1 overflow-y-auto p-5">
            <form @submit.prevent="saveContent" id="contentForm">
                <div class="space-y-4">
                    <template x-if="modalType === 'product'">
                        <div>@include('admin.settings.partials._product-widget-form')</div>
                    </template>
                    <template x-if="modalType === 'category'">
                        <div>@include('admin.settings.partials._category-widget-form')</div>
                    </template>
                    <template x-if="modalType === 'brand'">
                        <div>@include('admin.settings.partials._brand-widget-form')</div>
                    </template>
                    <template x-if="modalType === 'media'">
                        <div>@include('admin.settings.partials._media-widget-form')</div>
                    </template>
                    <template x-if="modalType === 'store'">
                        <div>@include('admin.settings.partials._store-widget-form')</div>
                    </template>
                </div>
            </form>
        </div>

        {{-- Modal footer --}}
        <div class="flex justify-end gap-3 px-5 py-4 border-t bg-gray-50 flex-shrink-0 rounded-b-2xl">
            <button type="button" @click="closeModal()"
                class="px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <template x-if="editingContent">
                <x-permission-btn permission="mobile_app.manage" type="submit" form="contentForm"
                    class="btn-primary" x-bind:disabled="saving" label="Update Widget"/>
            </template>
            <template x-if="!editingContent">
                <x-permission-btn permission="mobile_app.manage" type="submit" form="contentForm"
                    class="btn-primary" x-bind:disabled="saving" label="Add to Canvas"/>
            </template>
        </div>
    </div>{{-- end modal inner --}}
</div>{{-- end modal outer --}}

</div>{{-- end builder root x-data --}}

<style>
/* ── Builder Layout ── */
.builder-root { font-family: 'Inter', sans-serif; }
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

/* ── Element Library ── */
.element-card {
    border: 1.5px solid rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.04);
    cursor: pointer;
    transition: all 0.15s ease;
}
.element-card:hover {
    transform: translateY(-1px);
    background: rgba(255,255,255,0.09);
    border-color: rgba(255,255,255,0.18);
    box-shadow: 0 4px 12px rgba(0,0,0,0.35);
}
.element-card.element-ghost {
    opacity: 0.35;
    border-style: dashed;
}
/* ── Quick-option cards (slightly different tint) ── */
.element-card.element-quick {
    border-style: dashed;
    border-color: rgba(255,255,255,0.06);
}
.element-card.element-quick:hover {
    border-style: solid;
}

/* ── Quick-options divider ── */
.quick-options-divider { margin-bottom: 4px; }

/* ── Element library scrollbar ── */
.element-library-scroll::-webkit-scrollbar { width: 4px; }
.element-library-scroll::-webkit-scrollbar-track { background: transparent; }
.element-library-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 4px; }
.element-library-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.22); }

/* ── Canvas ── */
.canvas-drop-target { transition: all 0.2s; }

/* ── Widget card drag states ── */
.widget-card.sortable-ghost {
    opacity: 0;
    background: #fff7ed !important;
    border: 2px dashed #f97316 !important;
}
.widget-card.sortable-chosen {
    box-shadow: 0 12px 32px -4px rgba(0,0,0,0.18) !important;
    border-color: #f97316 !important;
    z-index: 9999;
    border-radius: 12px;
}
.widget-card.sortable-drag {
    box-shadow: 0 16px 40px -8px rgba(0,0,0,0.2) !important;
    opacity: 1 !important;
    border-radius: 12px;
}

/* ── Drag handle cursor ── */
.drag-handle { cursor: grab; }
.drag-handle:active { cursor: grabbing; }

/* ── Drop hint animation ── */
@keyframes pulse-border {
    0%, 100% { border-color: #cbd5e1; }
    50%       { border-color: #f97316; }
}
.canvas-drop-hint { animation: pulse-border 2.5s ease-in-out infinite; }

/* ── Builder panel responsive ── */
@media (max-width: 1280px) {
    .builder-panel-left { width: 220px; }
}
@media (max-width: 1024px) {
    /* Hide the persistent left panel on tablet/mobile — use the drawer instead */
    .builder-panel-left { display: none; }
}
/* On large screens (1024px+) the drawer is hidden via lg:hidden on the button */
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
{{--
    App Content Builder — Alpine component + initial config.

    Business logic and UI state have been moved to an external asset.
    This block only serializes the server-side values (route URLs,
    CSRF token, initial state, inline type icons) into a single
    `window.QxAppContentConfig` object that the external script reads.
--}}
<script>
window.QxAppContentConfig = {
    csrfToken: @json(csrf_token()),
    selectedTabId: {{ $selectedTabId ?? $tabs->first()?->id ?? 'null' }},
    contents: @json($contents),
    routes: {
        reorder:      @json(route('admin.app-content.reorder')),
        byTab:        @json(route('admin.app-content.by-tab')),
        previewItems: @json(route('admin.app-content.preview-items')),
        store:        @json(route('admin.app-content.store')),
        products:     @json(route('admin.app-content.products')),
        categories:   @json(route('admin.app-content.categories')),
        brands:       @json(route('admin.app-content.brands')),
        stores:       @json(route('admin.app-content.stores')),
    },
    urls: {
        appContentBase: @json(url('admin/app-content')),
    },
    typeIcons: {
        product:  { bg: 'bg-blue-100',   text: 'text-blue-600',   icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', iconSmall: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>' },
        category: { bg: 'bg-green-100',  text: 'text-green-600',  icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>', iconSmall: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>' },
        brand:    { bg: 'bg-yellow-100', text: 'text-yellow-600', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>', iconSmall: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>' },
        store:    { bg: 'bg-orange-100', text: 'text-orange-600', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', iconSmall: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>' },
        media:    { bg: 'bg-purple-100', text: 'text-purple-600', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>', iconSmall: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' }
    }
};
</script>
<script src="{{ asset('js/app-content-manager.js') }}"></script>
@endpush



@extends('admin.layouts.app')
@section('title', 'Website Content Builder')

@push('styles')
<style>
/* Hide the top header on laptop/desktop for full workspace view */
@media (min-width: 1024px) {
    #admin-topbar { display: none !important; }
}
</style>
<script src="{{ asset('js/sidebar-autocollapse.js') }}"></script>
@endpush

@php
$elementLibrary = [
    'product' => [
        'label' => 'Product Widgets', 'dot' => '#3b82f6', 'iconClass' => 'text-blue-600', 'bgClass' => 'bg-blue-50', 'borderClass' => 'border-blue-100 hover:border-blue-400 hover:bg-blue-50/80',
        'items' => [
            ['style' => 'style_1', 'name' => 'Grid (2-6 Cols)', 'desc' => 'Responsive product grid', 'svg' => '<rect x="3" y="3" width="8" height="8" rx="1.5" fill="currentColor"/><rect x="13" y="3" width="8" height="8" rx="1.5" fill="currentColor"/><rect x="3" y="13" width="8" height="8" rx="1.5" fill="currentColor"/><rect x="13" y="13" width="8" height="8" rx="1.5" fill="currentColor"/>'],
            ['style' => 'style_2', 'name' => 'Horizontal Row', 'desc' => 'Swipeable product row', 'svg' => '<rect x="2" y="5" width="12" height="14" rx="2" fill="currentColor"/><rect x="16" y="5" width="8" height="14" rx="2" fill="currentColor" opacity=".5"/>'],
            ['style' => 'style_3', 'name' => 'Item List',  'desc' => '2-column desktop cards',  'svg' => '<rect x="2" y="3" width="20" height="5" rx="1.5" fill="currentColor"/><rect x="2" y="10" width="20" height="5" rx="1.5" fill="currentColor" opacity=".65"/><rect x="2" y="17" width="20" height="4" rx="1.5" fill="currentColor" opacity=".35"/>'],
        ]
    ],

    'category' => [
        'label' => 'Category Widgets', 'dot' => '#22c55e', 'iconClass' => 'text-green-600', 'bgClass' => 'bg-green-50', 'borderClass' => 'border-green-100 hover:border-green-400 hover:bg-green-50/80',
        'items' => [
            ['style' => 'style_1', 'name' => 'Circle Row',     'desc' => 'Icon circles row',         'svg' => '<circle cx="5" cy="12" r="4" fill="currentColor"/><circle cx="12" cy="12" r="4" fill="currentColor" opacity=".6"/><circle cx="19" cy="12" r="4" fill="currentColor" opacity=".35"/>'],
            ['style' => 'style_2', 'name' => 'Category Grid',  'desc' => '2-6 column category grid', 'svg' => '<rect x="2" y="2" width="9" height="11" rx="2" fill="currentColor"/><rect x="13" y="2" width="9" height="11" rx="2" fill="currentColor" opacity=".6"/><rect x="2" y="15" width="9" height="7" rx="2" fill="currentColor" opacity=".6"/><rect x="13" y="15" width="9" height="7" rx="2" fill="currentColor" opacity=".35"/>'],
            ['style' => 'style_3', 'name' => 'Tabs + Items',   'desc' => 'Category tabs with items', 'svg' => '<rect x="2" y="2" width="7" height="4" rx="1.5" fill="currentColor"/><rect x="11" y="2" width="6" height="4" rx="1.5" fill="currentColor" opacity=".4"/><rect x="19" y="2" width="3" height="4" rx="1.5" fill="currentColor" opacity=".4"/><rect x="2" y="8" width="20" height="14" rx="2" fill="currentColor" opacity=".6"/>'],
        ]
    ],
    'brand' => [
        'label' => 'Brand Widgets', 'dot' => '#eab308', 'iconClass' => 'text-yellow-600', 'bgClass' => 'bg-yellow-50', 'borderClass' => 'border-yellow-100 hover:border-yellow-400 hover:bg-yellow-50/80',
        'items' => [
            ['style' => 'style_1', 'name' => 'Circle Logos',  'desc' => 'Circular brand logos',   'svg' => '<circle cx="5" cy="12" r="4" fill="currentColor"/><circle cx="12" cy="12" r="4" fill="currentColor" opacity=".6"/><circle cx="19" cy="12" r="4" fill="currentColor" opacity=".35"/>'],
            ['style' => 'style_2', 'name' => 'Brand Grid',    'desc' => 'Grid of brand logos',    'svg' => '<rect x="2" y="2" width="9" height="11" rx="2" fill="currentColor"/><rect x="13" y="2" width="9" height="11" rx="2" fill="currentColor" opacity=".6"/><rect x="2" y="15" width="9" height="7" rx="2" fill="currentColor" opacity=".6"/><rect x="13" y="15" width="9" height="7" rx="2" fill="currentColor" opacity=".35"/>'],
            ['style' => 'style_3', 'name' => 'Brand Strip',   'desc' => 'Full-width brand banner','svg' => '<rect x="2" y="6" width="20" height="12" rx="2.5" fill="currentColor"/>'],
        ]
    ],
    'media' => [
        'label' => 'Website Banners', 'dot' => '#a855f7', 'iconClass' => 'text-purple-600', 'bgClass' => 'bg-purple-50', 'borderClass' => 'border-purple-100 hover:border-purple-400 hover:bg-purple-50/80',
        'items' => [
            ['style' => 'style_1', 'name' => 'Full Width', 'desc' => 'Edge-to-edge web banner', 'svg' => '<rect x="2" y="4" width="20" height="16" rx="2" fill="currentColor"/>'],
            ['style' => 'style_2', 'name' => 'Container',  'desc' => 'Padded layout banner',    'svg' => '<rect x="1" y="1" width="22" height="22" rx="3" fill="currentColor" opacity=".12"/><rect x="4" y="5" width="16" height="14" rx="2" fill="currentColor"/>'],
            ['style' => 'style_3', 'name' => 'Rounded',    'desc' => 'Rounded corners banner',   'svg' => '<rect x="2" y="5" width="20" height="14" rx="6" fill="currentColor"/>'],
        ]
    ],
    'store' => [
        'label' => 'Store Widgets', 'dot' => '#f97316', 'iconClass' => 'text-orange-600', 'bgClass' => 'bg-orange-50', 'borderClass' => 'border-orange-100 hover:border-orange-400 hover:bg-orange-50/80',
        'items' => [
            ['style' => 'style_1', 'name' => 'Store Grid',    'desc' => '2-5 column store grid',  'svg' => '<rect x="2" y="2" width="20" height="10" rx="2" fill="currentColor"/><rect x="2" y="14" width="9" height="8" rx="2" fill="currentColor" opacity=".7"/><rect x="13" y="14" width="9" height="8" rx="2" fill="currentColor" opacity=".5"/>'],
            ['style' => 'style_3', 'name' => 'Horizontal Row','desc' => 'Swipeable store row',    'svg' => '<rect x="2" y="5" width="10" height="14" rx="2" fill="currentColor"/><rect x="14" y="5" width="7" height="14" rx="2" fill="currentColor" opacity=".6"/><rect x="23" y="5" width="4" height="14" rx="2" fill="currentColor" opacity=".3"/>'],
        ]
    ],
];

$quickElements = [
    'product_options' => [
        'label' => 'Product Quick Presets', 'dot' => '#3b82f6', 'type' => 'product',
        'items' => [
            ['name' => '4 Cols (Laptop)', 'desc' => '4 products per row', 'options' => ['style' => 'style_1', 'grid_columns' => 4, 'grid_rows' => 2], 'svg' => '<rect x="1" y="4" width="4.5" height="16" rx="1" fill="currentColor"/><rect x="6.5" y="4" width="4.5" height="16" rx="1" fill="currentColor" opacity=".75"/><rect x="13" y="4" width="4.5" height="16" rx="1" fill="currentColor" opacity=".55"/><rect x="18.5" y="4" width="4.5" height="16" rx="1" fill="currentColor" opacity=".35"/>'],
            ['name' => '5 Cols (Desktop)', 'desc' => '5 products per row', 'options' => ['style' => 'style_1', 'grid_columns' => 5, 'grid_rows' => 2], 'svg' => '<rect x="1" y="4" width="3.5" height="16" rx="1" fill="currentColor"/><rect x="5.5" y="4" width="3.5" height="16" rx="1" fill="currentColor" opacity=".8"/><rect x="10" y="4" width="3.5" height="16" rx="1" fill="currentColor" opacity=".6"/><rect x="14.5" y="4" width="3.5" height="16" rx="1" fill="currentColor" opacity=".4"/><rect x="19" y="4" width="3.5" height="16" rx="1" fill="currentColor" opacity=".2"/>'],
            ['name' => '6 Cols (Wide)', 'desc' => '6 products per row', 'options' => ['style' => 'style_1', 'grid_columns' => 6, 'grid_rows' => 2], 'svg' => '<rect x="1" y="4" width="2.8" height="16" rx="1" fill="currentColor"/><rect x="4.8" y="4" width="2.8" height="16" rx="1" fill="currentColor" opacity=".8"/><rect x="8.6" y="4" width="2.8" height="16" rx="1" fill="currentColor" opacity=".65"/><rect x="12.4" y="4" width="2.8" height="16" rx="1" fill="currentColor" opacity=".5"/><rect x="16.2" y="4" width="2.8" height="16" rx="1" fill="currentColor" opacity=".35"/><rect x="20" y="4" width="2.8" height="16" rx="1" fill="currentColor" opacity=".2"/>'],
            ['name' => 'Featured Items',  'desc' => 'Featured products',   'options' => ['source' => 'featured'], 'svg' => '<polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" fill="currentColor"/>'],
            ['name' => 'BG Banner',       'desc' => 'Solid color background', 'options' => ['enable_background' => true, 'background_type' => 'color'], 'svg' => '<circle cx="12" cy="12" r="9" fill="currentColor"/>'],
        ]
    ]
];
@endphp

@section('content')
<div x-data="appContentManager()"
     class="-mx-3 -my-3 sm:-mx-6 sm:-my-6 flex flex-col builder-root"
     style="height:calc(100vh - 57px); min-height:640px;">

    {{-- ═══════════════ TOP BAR ═══════════════ --}}
    <div class="builder-topbar flex items-center gap-2 sm:gap-3 px-3 sm:px-5 py-2 sm:py-2.5 flex-shrink-0 overflow-x-auto scrollbar-hide" style="background:#0f172a;border-bottom:1px solid rgba(255,255,255,.1);box-shadow:0 2px 12px rgba(0,0,0,.3);">

        {{-- Page title --}}
        <div class="hidden sm:flex items-center gap-2.5 mr-4 flex-shrink-0">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0 shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
            </div>
            <span class="text-sm font-bold text-white tracking-wide">Website Builder</span>
        </div>

        <div class="hidden sm:block h-6 w-px mr-1" style="background:rgba(255,255,255,.15);"></div>

        {{-- Tab selector --}}
        @if($tabs->isEmpty())
        <span class="text-sm" style="color:rgba(255,255,255,.5);">No modules — <a href="{{ route('admin.home-header.index') }}" class="text-indigo-400 underline">create modules first</a></span>
        @else
        <div class="flex items-center gap-1 overflow-x-auto scrollbar-hide">
            @foreach($tabs as $tab)
            <button @click="selectTab({{ $tab->id }})"
                :class="selectedTabId === {{ $tab->id }} ? 'bg-indigo-600 text-white shadow-sm font-bold' : 'hover:bg-white/10 text-slate-300'"
                class="px-3.5 py-1.5 rounded-lg text-xs whitespace-nowrap transition-all duration-150 flex-shrink-0">
                {{ $tab->display_name }}
            </button>
            @endforeach
        </div>
        @endif

        {{-- Right actions --}}
        <div class="ml-auto flex items-center gap-3 flex-shrink-0">
            <a href="{{ url('/') }}" target="_blank" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-200 bg-slate-800 hover:bg-slate-700 transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Preview Site
            </a>
            <span class="text-xs font-medium hidden sm:block text-slate-400">
                <span x-text="contents.length"></span> widgets
            </span>
        </div>
    </div>

    {{-- ═══════════════ BUILDER BODY ═══════════════ --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- LEFT: ELEMENT LIBRARY --}}
        <div class="builder-panel-left w-64 xl:w-72 flex flex-col flex-shrink-0 overflow-hidden" style="background:#0f172a; border-right:1px solid rgba(255,255,255,0.07);">
            <div class="px-4 pt-4 pb-3 flex-shrink-0" style="border-bottom:1px solid rgba(255,255,255,0.07);">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Website Layout Widgets</h3>
                <p class="text-[11px] mt-0.5 text-slate-500">Click to add to canvas</p>
            </div>
            <div class="flex-1 overflow-y-auto py-3 px-3 space-y-4 element-library-scroll">
                @foreach($elementLibrary as $type => $group)
                <div class="element-group">
                    <div class="flex items-center gap-2 px-1 mb-2">
                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $group['dot'] }}"></span>
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">{{ $group['label'] }}</span>
                    </div>
                    <div class="element-library-group grid grid-cols-2 gap-2">
                        @foreach($group['items'] as $item)
                        <div class="element-card cursor-pointer rounded-xl p-2.5 transition-all duration-150 select-none"
                             data-type="{{ $type }}" data-style="{{ $item['style'] }}"
                             @click="openAddModal('{{ $type }}', '{{ $item['style'] }}')">
                            <div class="rounded-lg flex items-center justify-center mb-2" style="height:40px; background:rgba(255,255,255,0.06);">
                                <svg class="w-5 h-5" style="color:{{ $group['dot'] }};" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    {!! $item['svg'] !!}
                                </svg>
                            </div>
                            <div class="text-[11.5px] font-semibold leading-tight truncate text-slate-200">{{ $item['name'] }}</div>
                            <div class="text-[10px] leading-tight mt-0.5 truncate text-slate-400">{{ $item['desc'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- CENTER: CANVAS --}}
        <div class="builder-panel-canvas flex-1 flex flex-col overflow-hidden bg-slate-100 relative">
            <div class="flex items-center gap-3 px-5 py-2.5 bg-slate-100 border-b border-slate-200 flex-shrink-0">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                <span class="text-xs font-semibold text-slate-600">Website Home Canvas</span>
                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-700" x-text="contents.length + ' widgets'"></span>
                <p class="text-[11px] text-slate-400 ml-auto hidden md:block">Drag handles to reorder • Click widget to edit grid columns & styles</p>
            </div>

            {{-- Widget canvas body --}}
            <div class="flex-1 overflow-y-auto p-4">
                <template x-if="contents.length === 0">
                    <div class="canvas-empty flex flex-col items-center justify-center py-20 text-center">
                        <div class="w-20 h-20 bg-white rounded-2xl shadow-sm border-2 border-dashed border-slate-300 flex items-center justify-center mb-4">
                            <svg class="w-9 h-9 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-600 mb-1">No website widgets configured for this tab yet</p>
                        <p class="text-xs text-slate-400">Click elements from the left panel to build your website homepage</p>
                    </div>
                </template>

                <div x-ref="sortableList" class="desktop-sortable space-y-2.5 min-h-[120px] canvas-drop-target">
                    <template x-for="(content, index) in contents" :key="content.id">
                        <div class="widget-card bg-white rounded-xl border border-gray-200 flex items-stretch overflow-hidden select-none transition-all duration-150 group hover:shadow-md hover:border-gray-300"
                             :data-id="content.id"
                             :class="!content.is_active ? 'opacity-60' : ''">

                            <div class="drag-handle w-8 flex items-center justify-center flex-shrink-0 bg-gray-50 border-r border-gray-100 text-gray-300 hover:text-gray-500 hover:bg-gray-100 transition-colors cursor-grab active:cursor-grabbing">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 10 16">
                                    <circle cx="2.5" cy="3"  r="1.3"/><circle cx="2.5" cy="7"  r="1.3"/>
                                    <circle cx="2.5" cy="11" r="1.3"/><circle cx="2.5" cy="15" r="1.3"/>
                                    <circle cx="7.5" cy="3"  r="1.3"/><circle cx="7.5" cy="7"  r="1.3"/>
                                    <circle cx="7.5" cy="11" r="1.3"/><circle cx="7.5" cy="15" r="1.3"/>
                                </svg>
                            </div>

                            <div class="flex items-center gap-3 flex-1 min-w-0 px-4 py-3">
                                <div class="w-6 h-6 rounded-md bg-gray-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-[10px] font-bold text-gray-400" x-text="index + 1"></span>
                                </div>

                                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" :class="getTypeIcon(content.type).bg">
                                    <span :class="getTypeIcon(content.type).text" x-html="getTypeIcon(content.type).iconSmall"></span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-sm font-semibold text-gray-800 truncate max-w-[160px] xl:max-w-xs"
                                            x-text="content.title || getTypeLabel(content.type)"></span>

                                        <span class="inline-flex px-1.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide"
                                            :class="{
                                                'bg-blue-50 text-blue-600':    content.type==='product',
                                                'bg-green-50 text-green-600':  content.type==='category',
                                                'bg-yellow-50 text-yellow-600':content.type==='brand',
                                                'bg-purple-50 text-purple-600':content.type==='media',
                                                'bg-orange-50 text-orange-600':content.type==='store'
                                            }"
                                            x-text="getTypeLabel(content.type)"></span>

                                        <span class="inline-flex px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-500"
                                            x-text="getStyleName(content.type, content.style)"></span>

                                        <span x-show="['product','category','store','brand'].includes(content.type)"
                                            class="inline-flex px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-600"
                                            x-text="'Mobile: ' + (content.grid_columns_mobile || 2) + ' cols | Laptop: ' + (content.grid_columns_laptop || content.grid_columns || 5) + ' cols'"></span>

                                        <span x-show="content.show_on_mobile === false" class="inline-flex px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700">💻 Laptop Only</span>
                                        <span x-show="content.show_on_laptop === false" class="inline-flex px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700">📱 Mobile Only</span>
                                    </div>
                                    <div x-show="content.show_subtitle && content.subtitle" class="text-xs text-gray-400 mt-0.5 truncate" x-text="content.subtitle"></div>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 px-3 flex-shrink-0 border-l border-gray-100">
                                <label class="toggle cursor-pointer" title="Toggle visibility">
                                    <input type="checkbox" :checked="content.is_active" @change.stop="toggleActive(content)" class="sr-only">
                                    <span class="widget-toggle-track inline-block w-8 h-4 rounded-full relative transition-colors" :class="content.is_active ? 'bg-emerald-400' : 'bg-gray-200'">
                                        <span class="absolute top-0.5 left-0.5 w-3 h-3 bg-white rounded-full shadow transition-transform" :class="content.is_active ? 'translate-x-4' : 'translate-x-0'"></span>
                                    </span>
                                </label>

                                <button @click.stop="editContent(content)" class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <button @click.stop="duplicateContent(content)" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Duplicate">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>

                                <button @click.stop="deleteContent(content)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    {{-- MODAL --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="closeModal()">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal()"></div>
        <div class="fixed inset-x-0 bottom-0 lg:inset-auto lg:top-1/2 lg:left-1/2 lg:-translate-x-1/2 lg:-translate-y-1/2 bg-white rounded-t-2xl lg:rounded-2xl shadow-2xl w-full lg:max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-full lg:translate-y-0 lg:opacity-0 lg:scale-95"
             x-transition:enter-end="translate-y-0 lg:opacity-100 lg:scale-100">

            <div class="flex items-center justify-between px-5 py-4 border-b flex-shrink-0 bg-gray-50 rounded-t-2xl">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="getTypeIcon(modalType).bg">
                        <span :class="getTypeIcon(modalType).text" x-html="getTypeIcon(modalType).iconSmall"></span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900" x-text="editingContent ? 'Edit Website Widget' : 'Add Website ' + getTypeLabel(modalType) + ' Widget'"></h3>
                </div>
                <button @click="closeModal()" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

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

            <div class="flex justify-end gap-3 px-5 py-4 border-t bg-gray-50 flex-shrink-0 rounded-b-2xl">
                <button type="button" @click="closeModal()"
                    class="px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" form="contentForm" class="btn-primary px-4 py-2 text-sm font-semibold rounded-xl" x-bind:disabled="saving">
                    <span x-text="editingContent ? 'Update Website Widget' : 'Add to Website Canvas'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

<style>
.builder-root { font-family: 'Inter', sans-serif; }
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.element-card { border: 1.5px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.04); transition: all 0.15s ease; }
.element-card:hover { transform: translateY(-1px); background: rgba(255,255,255,0.09); border-color: rgba(255,255,255,0.18); box-shadow: 0 4px 12px rgba(0,0,0,0.35); }
.element-library-scroll::-webkit-scrollbar { width: 4px; }
.element-library-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 4px; }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
window.QxAppContentConfig = {
    csrfToken: @json(csrf_token()),
    selectedTabId: {{ $selectedTabId ?? $tabs->first()?->id ?? 'null' }},
    contents: @json($contents),
    routes: {
        reorder:      @json(route('admin.website-content.reorder')),
        byTab:        @json(route('admin.website-content.by-tab')),
        store:        @json(route('admin.website-content.store')),
        products:     @json(route('admin.website-content.products')),
        categories:   @json(route('admin.website-content.categories')),
        brands:       @json(route('admin.website-content.brands')),
        stores:       @json(route('admin.website-content.stores')),
    },
    urls: {
        appContentBase: @json(url('admin/website-content')),
    },
    typeIcons: {
        product:  { bg: 'bg-blue-100',   text: 'text-blue-600',   iconSmall: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>' },
        category: { bg: 'bg-green-100',  text: 'text-green-600',  iconSmall: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>' },
        brand:    { bg: 'bg-yellow-100', text: 'text-yellow-600', iconSmall: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>' },
        store:    { bg: 'bg-orange-100', text: 'text-orange-600', iconSmall: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>' },
        media:    { bg: 'bg-purple-100', text: 'text-purple-600', iconSmall: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' }
    }
};
</script>
<script src="{{ asset('js/app-content-manager.js') }}"></script>
@endpush

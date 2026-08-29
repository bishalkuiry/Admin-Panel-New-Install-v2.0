@extends('admin.layouts.app')

@section('title', 'Category Screen Content')

@section('content')
<div x-data="categoryScreenContentManager()" x-init="init()">
    
    <!-- Page Header -->
    <div class="page-header flex items-center justify-between mb-6">
        <div>
            <h1 class="page-title">Category Screen Content</h1>
            <p class="page-subtitle">Manage widgets that appear on the mobile app's category/browse page</p>
        </div>
    </div>

    <div class="flex gap-6">
        <!-- Left Panel: Widget List -->
        <div class="flex-1 space-y-4">
            <!-- Info Banner -->
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-xl p-4">
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-purple-900">Category Screen Widgets</h3>
                        <p class="text-sm text-purple-700 mt-0.5">These widgets appear on the dedicated category/browse page in the mobile app, separate from the home page tabs.</p>
                    </div>
                </div>
            </div>

            <!-- Add Widget Buttons -->
            <div class="card">
                <div class="card-body p-4">
                    <div class="grid grid-cols-2 gap-3">
                        <x-permission-btn 
                            permission="mobile_app.manage" 
                            type="button"
                            @click="openAddModal('category')" 
                            class="btn-secondary" 
                            label="Add Category Widget"
                            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>'
                        />
                        <x-permission-btn 
                            permission="mobile_app.manage" 
                            type="button"
                            @click="openAddModal('media')" 
                            class="btn-secondary" 
                            label="Add Media Banner"
                            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                        />
                    </div>
                </div>
            </div>

            <!-- Content Widgets List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Category Screen Widgets</h3>
                    <span class="badge badge-purple" x-text="contents.length + ' widgets'"></span>
                </div>
                <div class="card-body p-0">
                    <template x-if="contents.length === 0">
                        <div class="empty-state">
                            <div class="empty-icon"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></div>
                            <p class="empty-title">No content widgets</p>
                            <p class="empty-text">Add category or media widgets to customize the category screen</p>
                        </div>
                    </template>
                    <div x-ref="sortableList" class="divide-y divide-gray-100">
                        <template x-for="content in contents" :key="content.id">
                            <div class="p-4 hover:bg-gray-50 cursor-move flex items-center gap-4" :data-id="content.id">
                                <div class="text-gray-400 cursor-grab"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg></div>
                                <!-- Icon or Media Thumbnail -->
                                <template x-if="content.type === 'media' && content.media_url">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0" x-html="getMediaHtml(content, 'thumb')"></div>
                                </template>
                                <template x-if="content.type !== 'media' || !content.media_url">
                                    <div :class="getTypeIcon(content.type).bg" class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span :class="getTypeIcon(content.type).text" x-html="getTypeIcon(content.type).icon"></span>
                                    </div>
                                </template>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900" x-text="content.title || getTypeLabel(content.type)"></div>
                                    <div x-show="content.show_subtitle && content.subtitle" class="text-xs text-gray-600 mt-0.5" x-text="content.subtitle"></div>
                                    <div class="text-sm text-gray-500 mt-1 flex flex-wrap gap-x-2 gap-y-0.5">
                                        <span x-text="getStyleName(content.type, content.style)"></span>
                                        <span x-show="content.source"> • <span x-text="content.source"></span></span>
                                        <!-- Grid Configuration -->
                                        <span x-show="content.type === 'category' && (content.style === 'style_1' || content.style === 'style_2') && content.grid_columns && content.grid_rows">
                                            • <span class="text-blue-600" x-text="content.grid_columns + '×' + content.grid_rows + ' grid'"></span>
                                        </span>
                                        <span x-show="content.type === 'category' && content.style === 'style_4' && content.grid_rows">
                                            • <span class="text-purple-600" x-text="'1 banner + ' + content.grid_rows + ' rows'"></span>
                                        </span>
                                        <!-- Background -->
                                        <span x-show="content.type === 'category' && content.enable_background">
                                            • <span class="inline-flex items-center gap-1">
                                                <span x-show="content.background_type === 'color'" class="w-3 h-3 rounded border border-gray-300" :style="'background-color: ' + (content.background_color || '#ffffff')"></span>
                                                <span x-show="content.background_type !== 'color'" class="text-purple-600" x-text="content.background_type"></span>
                                            </span>
                                        </span>
                                        <!-- Horizontal Animation -->
                                        <span x-show="content.type === 'category' && ['style_1', 'style_3'].includes(content.style) && content.enable_horizontal_animation">
                                            • <span class="text-green-600">🔄 Auto-scroll</span>
                                        </span>
                                        <!-- Media Status -->
                                        <span x-show="content.type === 'media' && content.media_url"> • <span class="text-green-600">✓ Media</span></span>
                                        <span x-show="content.type === 'media' && !content.media_url"> • <span class="text-orange-500">⚠ No media</span></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="toggle {{ !auth()->user()->hasPermission('mobile_app.manage') ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}"><input type="checkbox" :checked="content.is_active" @change="toggleActive(content)" {{ !auth()->user()->hasPermission('mobile_app.manage') ? 'disabled' : '' }}><span class="toggle-slider"></span></label>
                                    @if(auth()->user()->hasPermission('mobile_app.manage'))
                                    <button @click="editContent(content)" class="action-btn action-btn-edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                    <button @click="duplicateContent(content)" class="action-btn action-btn-view"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></button>
                                    <button @click="deleteContent(content)" class="action-btn action-btn-delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    @else
                                    <button class="action-btn action-btn-edit opacity-50 cursor-not-allowed" onclick="alert('You do not have permission to mobile app manage.')"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                    <button class="action-btn action-btn-delete opacity-50 cursor-not-allowed" onclick="alert('You do not have permission to mobile app manage.')"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    @endif
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Category Screen Preview -->
        <div class="w-80 flex-shrink-0 hidden lg:block">
            <div class="sticky top-20">
                <div class="bg-gray-900 rounded-3xl p-2 shadow-2xl">
                    <div class="bg-white rounded-2xl overflow-hidden" style="height: 580px;">
                        <!-- Phone Header -->
                        <div class="bg-purple-600 text-white px-4 py-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                <span class="font-semibold">Categories</span>
                            </div>
                        </div>
                        
                        <!-- Content Preview -->
                        <div class="p-2 h-full overflow-y-auto bg-gray-50" style="max-height: 520px;">
                            <template x-if="contents.length === 0">
                                <div class="flex flex-col items-center justify-center h-full text-gray-400 text-center">
                                    <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <p class="text-xs">Category screen preview</p>
                                </div>
                            </template>
                            <template x-for="content in contents.filter(c => c.is_active)" :key="'preview-' + content.id">
                                <div class="mb-3">
                                    <!-- Title -->
                                    <div x-show="content.show_title && content.title" class="px-2 mb-2">
                                        <h3 class="font-semibold text-sm text-gray-800" x-text="content.title"></h3>
                                        <p x-show="content.show_subtitle && content.subtitle" class="text-xs text-gray-500" x-text="content.subtitle"></p>
                                    </div>
                                    
                                    <!-- Category Style Previews -->
                                    <template x-if="content.type === 'category'">
                                        <div :style="content.enable_background && content.background_type === 'color' ? 'background-color: ' + content.background_color + '; padding: 8px; border-radius: 8px;' : ''">
                                            <!-- Style 1: Circle Icons (Horizontal) -->
                                            <template x-if="content.style === 'style_1'">
                                                <div class="flex space-x-3 overflow-x-hidden pb-1 px-1">
                                                    <template x-for="i in 5">
                                                        <div class="flex-shrink-0 flex flex-col items-center">
                                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-100 to-indigo-100 border border-gray-100 mb-1 flex items-center justify-center">
                                                                <span class="text-[8px]">📦</span>
                                                            </div>
                                                            <div class="w-8 h-1.5 bg-gray-200 rounded"></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                            <!-- Style 2: Grid Layout -->
                                            <template x-if="content.style === 'style_2'">
                                                <div class="grid gap-1.5 px-1" :class="`grid-cols-${content.grid_columns || 3}`">
                                                    <template x-for="i in Math.min((content.grid_columns || 3) * (content.grid_rows || 2), 8)">
                                                        <div class="bg-white rounded p-1.5 flex flex-col items-center border border-gray-100 shadow-xs">
                                                            <div class="w-full aspect-square bg-gray-100 rounded mb-1 flex items-center justify-center">
                                                                <span class="text-[10px]">📦</span>
                                                            </div>
                                                            <div class="w-2/3 h-1.5 bg-gray-200 rounded"></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                            <!-- Style 3: Categories with Products (Tabs) -->
                                            <template x-if="content.style === 'style_3'">
                                                <div class="space-y-3 px-1">
                                                    <!-- Tabs -->
                                                    <div class="flex space-x-3 overflow-x-hidden border-b border-gray-100 pb-1.5">
                                                        <template x-for="i in 4">
                                                            <div class="flex-shrink-0 flex flex-col items-center">
                                                                <div :class="i===1 ? 'border-purple-500 bg-purple-50' : 'border-gray-50 bg-gray-100'" class="w-8 h-8 rounded-full border mb-1 flex items-center justify-center">
                                                                    <span class="text-[8px]">📦</span>
                                                                </div>
                                                                <div class="w-6 h-1 bg-gray-200 rounded"></div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <!-- Product Row -->
                                                    <div class="flex space-x-2 overflow-x-hidden">
                                                        <template x-for="i in 3">
                                                            <div class="flex-shrink-0 w-24 bg-white border border-gray-100 rounded p-1 shadow-xs">
                                                                <div class="w-full h-16 bg-gray-100 rounded mb-1.5"></div>
                                                                <div class="w-3/4 h-1.5 bg-gray-200 rounded mb-1"></div>
                                                                <div class="w-1/2 h-1.5 bg-gray-200 rounded"></div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Style 4: Highlight Style (Checkmark Grid) -->
                                            <template x-if="content.style === 'style_4'">
                                                <div class="space-y-1.5 px-1">
                                                    <!-- First Row: 1 Big + 2 Small -->
                                                    <div class="flex space-x-1">
                                                        <div class="flex-[2] h-20 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-lg relative overflow-hidden border border-purple-200">
                                                            <div class="absolute bottom-1.5 left-1.5 w-1/2 h-2 bg-white/80 rounded z-10"></div>
                                                            <div class="absolute right-0 top-0 w-12 h-12 opacity-20"><span class="text-2xl">📦</span></div>
                                                        </div>
                                                        <div class="flex-1 space-y-1">
                                                            <div class="h-[38px] bg-gray-100 rounded-lg border border-gray-200 relative overflow-hidden flex flex-col justify-end p-1">
                                                                <div class="w-2/3 h-1.5 bg-gray-300 rounded"></div>
                                                            </div>
                                                            <div class="h-[38px] bg-gray-100 rounded-lg border border-gray-200 relative overflow-hidden flex flex-col justify-end p-1">
                                                                <div class="w-2/3 h-1.5 bg-gray-300 rounded"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Remaining grid items simplified -->
                                                    <div class="grid grid-cols-4 gap-1">
                                                        <template x-for="i in 4">
                                                            <div class="aspect-square bg-gray-50 border border-gray-200 rounded-lg relative overflow-hidden flex flex-col justify-end p-1">
                                                                <div class="w-full h-1 bg-gray-300 rounded-full"></div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    
                                    <!-- Media Banner Preview -->
                                    <template x-if="content.type === 'media'">
                                        <div class="rounded-lg overflow-hidden mx-1">
                                            <template x-if="content.media_url">
                                                <img :src="content.media_url" class="w-full object-cover" :style="'height: ' + (content.media_height || 120) + 'px'">
                                            </template>
                                            <template x-if="!content.media_url">
                                                <div class="w-full bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center" :style="'height: ' + (content.media_height || 120) + 'px'">
                                                    <svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <p class="text-center text-xs text-gray-400 mt-2">Category Screen Preview</p>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="showModal = false">
        <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
        <div class="fixed inset-x-0 bottom-0 lg:inset-auto lg:top-1/2 lg:left-1/2 lg:-translate-x-1/2 lg:-translate-y-1/2 bg-white rounded-t-2xl lg:rounded-xl shadow-xl w-full lg:max-w-lg max-h-[85vh] overflow-hidden flex flex-col"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-full lg:translate-y-0 lg:opacity-0 lg:scale-95"
             x-transition:enter-end="translate-y-0 lg:opacity-100 lg:scale-100">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b flex-shrink-0">
                <h3 class="text-lg font-semibold" x-text="editingContent ? 'Edit Widget' : 'Add ' + getTypeLabel(modalType) + ' Widget'"></h3>
                <button @click="showModal = false" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <!-- Modal Body (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-6">
                <form @submit.prevent="saveContent" id="contentForm">
                    <div class="space-y-4">
                        <!-- Category Widget Form -->
                        <template x-if="modalType === 'category'">
                            <div>
                                <!-- Title -->
                                <div class="form-group">
                                    <label class="label">Title (optional)</label>
                                    <input type="text" x-model="formData.title" class="input" placeholder="e.g., Browse Categories">
                                </div>
                                <div class="form-group">
                                    <label class="toggle"><input type="checkbox" x-model="formData.show_title"><span class="toggle-slider"></span></label>
                                    <span class="ml-2 text-sm">Show title in app</span>
                                </div>

                                <!-- Subtitle -->
                                <div class="form-group">
                                    <label class="label">Subtitle (optional)</label>
                                    <input type="text" x-model="formData.subtitle" class="input" placeholder="e.g., Explore all categories" maxlength="200">
                                </div>
                                <div class="form-group">
                                    <label class="toggle"><input type="checkbox" x-model="formData.show_subtitle"><span class="toggle-slider"></span></label>
                                    <span class="ml-2 text-sm">Show subtitle in app</span>
                                </div>

                                <!-- Style Selection -->
                                <div class="form-group">
                                    <label class="label">Style</label>
                                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                        <label :class="formData.style === 'style_1' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'" class="border-2 rounded-lg p-3 cursor-pointer text-center">
                                            <input type="radio" x-model="formData.style" value="style_1" class="hidden">
                                            <div class="text-2xl mb-1">▭▭</div>
                                            <div class="text-xs text-gray-600">Horizontal Scroll</div>
                                        </label>
                                        <label :class="formData.style === 'style_2' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'" class="border-2 rounded-lg p-3 cursor-pointer text-center">
                                            <input type="radio" x-model="formData.style" value="style_2" class="hidden">
                                            <div class="text-2xl mb-1">⊞</div>
                                            <div class="text-xs text-gray-600">Grid Layout</div>
                                        </label>
                                        <label :class="formData.style === 'style_3' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'" class="border-2 rounded-lg p-3 cursor-pointer text-center">
                                            <input type="radio" x-model="formData.style" value="style_3" class="hidden">
                                            <div class="text-2xl mb-1">⊟⊟</div>
                                            <div class="text-xs text-gray-600">With Products</div>
                                        </label>
                                        <label :class="formData.style === 'style_4' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'" class="border-2 rounded-lg p-3 cursor-pointer text-center">
                                            <input type="radio" x-model="formData.style" value="style_4" class="hidden">
                                            <div class="text-2xl mb-1">▦</div>
                                            <div class="text-xs text-gray-600">Highlight Style</div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Data Source -->
                                <div class="form-group">
                                    <label class="label">Data Source</label>
                                    <select x-model="formData.source" class="input">
                                        <option value="featured">Featured Categories</option>
                                        <option value="all">All Root Categories</option>
                                        <option value="custom">Custom Selection</option>
                                    </select>
                                </div>

                                <!-- Custom Items Selection -->
                                <template x-if="formData.source === 'custom'">
                                    <div class="form-group">
                                        <label class="label">Select Categories</label>
                                        <div class="border rounded-lg p-3 max-h-48 overflow-y-auto">
                                            <input type="text" x-model="searchQuery" @input.debounce.300ms="searchItems" class="input mb-2" placeholder="Search categories...">
                                            <div class="space-y-2">
                                                <template x-for="item in searchResults" :key="item.id">
                                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                        <input type="checkbox" :value="item.id" x-model="formData.custom_items" class="checkbox">
                                                        <span x-text="item.name" class="text-sm"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Grid Configuration (for style_2 and style_4) -->
                                <template x-if="formData.style === 'style_2' || formData.style === 'style_4'">
                                    <div class="form-group border-t pt-4 mt-4">
                                        <label class="label">Grid Configuration</label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="text-xs text-gray-500">Columns</label>
                                                <select x-model="formData.grid_columns" class="input">
                                                    <option value="2">2 Columns</option>
                                                    <option value="3">3 Columns</option>
                                                    <option value="4">4 Columns</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-500">Rows</label>
                                                <select x-model="formData.grid_rows" class="input">
                                                    <option value="1">1 Row</option>
                                                    <option value="2">2 Rows</option>
                                                    <option value="3">3 Rows</option>
                                                    <option value="4">4 Rows</option>
                                                    <option value="5">5 Rows</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Horizontal Animation (for style_1 and style_3) -->
                                <template x-if="formData.style === 'style_1' || formData.style === 'style_3'">
                                    <div class="form-group border-t pt-4 mt-4">
                                        <label class="toggle"><input type="checkbox" x-model="formData.enable_horizontal_animation"><span class="toggle-slider"></span></label>
                                        <span class="ml-2 text-sm">Enable auto-scroll animation</span>
                                    </div>
                                </template>

                                <!-- Background Configuration -->
                                <div class="form-group border-t pt-4 mt-4">
                                    <label class="toggle"><input type="checkbox" x-model="formData.enable_background"><span class="toggle-slider"></span></label>
                                    <span class="ml-2 text-sm font-medium">Enable Background</span>
                                </div>
                                <template x-if="formData.enable_background">
                                    <div class="form-group">
                                        <label class="label">Background Type</label>
                                        <select x-model="formData.background_type" class="input">
                                            <option value="color">Solid Color</option>
                                            <option value="image">Image</option>
                                            <option value="gif">GIF</option>
                                            <option value="video">Video</option>
                                        </select>
                                        <template x-if="formData.background_type === 'color'">
                                            <div class="mt-2 flex items-center gap-2">
                                                <input type="color" x-model="formData.background_color" class="w-10 h-10 rounded border cursor-pointer">
                                                <input type="text" x-model="formData.background_color" class="input flex-1" placeholder="#ffffff">
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Media Widget Form -->
                        <template x-if="modalType === 'media'">
                            <div>
                                <!-- Title -->
                                <div class="form-group">
                                    <label class="label">Title (optional)</label>
                                    <input type="text" x-model="formData.title" class="input" placeholder="e.g., Featured Banner">
                                </div>
                                <div class="form-group">
                                    <label class="toggle"><input type="checkbox" x-model="formData.show_title"><span class="toggle-slider"></span></label>
                                    <span class="ml-2 text-sm">Show title in app</span>
                                </div>

                                <!-- Media Upload -->
                                <div class="form-group">
                                    <label class="label">Media</label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-purple-400"
                                         @click="$refs.mediaInput.click()"
                                         @dragover.prevent="$event.target.classList.add('border-purple-500', 'bg-purple-50')"
                                         @dragleave.prevent="$event.target.classList.remove('border-purple-500', 'bg-purple-50')"
                                         @drop.prevent="handleMediaDrop($event)">
                                        <template x-if="mediaPreview || formData.media_url">
                                            <div class="relative">
                                                <template x-if="formData.media_type === 'video'">
                                                    <video :src="mediaPreview || formData.media_url" class="max-h-40 mx-auto rounded" muted></video>
                                                </template>
                                                <template x-if="formData.media_type !== 'video'">
                                                    <img :src="mediaPreview || formData.media_url" class="max-h-40 mx-auto rounded">
                                                </template>
                                                <button type="button" @click.stop="removeMedia()" class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center">×</button>
                                            </div>
                                        </template>
                                        <template x-if="!mediaPreview && !formData.media_url">
                                            <div>
                                                <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <p class="text-sm text-gray-500">Click or drag to upload</p>
                                                <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF, MP4 (max 20MB)</p>
                                            </div>
                                        </template>
                                        <input type="file" x-ref="mediaInput" @change="handleMediaSelect($event)" class="hidden" accept="image/*,video/*">
                                    </div>
                                </div>

                                <!-- Media Height -->
                                <div class="form-group">
                                    <label class="label">Height (px)</label>
                                    <input type="number" x-model="formData.media_height" class="input" min="50" max="500">
                                </div>

                                <!-- Link Configuration -->
                                <div class="form-group">
                                    <label class="label">Link To</label>
                                    <select x-model="formData.link_type" @change="handleLinkTypeChange()" class="input">
                                        <option value="none">No Link</option>
                                        <option value="product">Product</option>
                                        <option value="category">Category</option>
                                        <option value="brand">Brand</option>
                                        <option value="store">Store</option>
                                        <option value="url">Custom URL</option>
                                    </select>
                                </div>
                                <template x-if="['product', 'category', 'brand', 'store'].includes(formData.link_type)">
                                    <div class="form-group">
                                        <label class="label">Select <span x-text="formData.link_type"></span></label>
                                        <select x-model="formData.link_id" class="input">
                                            <option value="">-- Select --</option>
                                            <template x-for="opt in linkOptions" :key="opt.id">
                                                <option :value="opt.id" x-text="opt.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>
                                <template x-if="formData.link_type === 'url'">
                                    <div class="form-group">
                                        <label class="label">URL</label>
                                        <input type="url" x-model="formData.link_url" class="input" placeholder="https://...">
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </form>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 p-4 border-t bg-gray-50 flex-shrink-0">
                <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                <template x-if="editingContent">
                    <x-permission-btn 
                        permission="mobile_app.manage" 
                        type="submit" 
                        form="contentForm" 
                        class="btn-primary" 
                        x-bind:disabled="saving" 
                        label="Update"
                    />
                </template>
                <template x-if="!editingContent">
                    <x-permission-btn 
                        permission="mobile_app.manage" 
                        type="submit" 
                        form="contentForm" 
                        class="btn-primary" 
                        x-bind:disabled="saving" 
                        label="Create"
                    />
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function categoryScreenContentManager() {
    return {
        contents: @json($contents),
        showModal: false,
        modalType: 'category',
        editingContent: null,
        saving: false,
        searchQuery: '',
        searchResults: [],
        sortableInstance: null,
        mediaPreview: null,
        mediaFile: null,
        uploadProgress: 0,
        linkOptions: [],
        backgroundMediaFile: null,
        backgroundMediaPreview: null,
        formData: {
            title: '',
            show_title: true,
            subtitle: '',
            show_subtitle: false,
            style: 'style_1',
            source: 'featured',
            enable_background: false,
            background_type: 'color',
            background_color: '#ffffff',
            grid_columns: 2,
            grid_rows: 2,
            enable_horizontal_animation: false,
            media_type: 'image',
            media_height: 200,
            media_url: '',
            link_type: 'none',
            link_id: '',
            link_url: '',
            custom_items: []
        },

        init() {
            this.$nextTick(() => this.initSortable());
            this.searchItems();
        },

        initSortable() {
            if (this.sortableInstance) this.sortableInstance.destroy();
            const list = this.$refs.sortableList;
            if (list) {
                this.sortableInstance = new Sortable(list, {
                    animation: 150,
                    handle: '.cursor-grab',
                    draggable: '[data-id]',
                    onEnd: (evt) => this.handleReorder(evt)
                });
            }
        },

        async handleReorder(evt) {
            const items = [...this.$refs.sortableList.querySelectorAll('[data-id]')];
            const reordered = items.map((el, index) => ({
                id: parseInt(el.dataset.id),
                sort_order: index
            }));
            
            await fetch('{{ route("admin.category-screen-content.reorder") }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ contents: reordered })
            });
            
            // Update local order
            this.contents = items.map((el) => {
                return this.contents.find(c => c.id === parseInt(el.dataset.id));
            });
        },

        openAddModal(type) {
            this.modalType = type;
            this.editingContent = null;
            this.mediaPreview = null;
            this.mediaFile = null;
            this.linkOptions = [];
            this.searchQuery = '';
            this.searchResults = [];
            
            this.formData = {
                title: '',
                show_title: true,
                subtitle: '',
                show_subtitle: false,
                style: 'style_1',
                source: 'featured',
                enable_background: false,
                background_type: 'color',
                background_color: '#ffffff',
                grid_columns: 2,
                grid_rows: 2,
                enable_horizontal_animation: false,
                media_type: 'image',
                media_height: 200,
                media_url: '',
                link_type: 'none',
                link_id: '',
                link_url: '',
                custom_items: []
            };
            this.showModal = true;
            
            if (type === 'category') {
                this.searchItems();
            }
        },

        editContent(content) {
            this.modalType = content.type;
            this.editingContent = content;
            this.mediaPreview = null;
            this.mediaFile = null;
            this.linkOptions = [];
            this.searchQuery = '';
            this.searchResults = [];
            
            this.formData = JSON.parse(JSON.stringify({
                ...content,
                custom_items: content.custom_items || [],
                media_url: content.media_url || '',
                link_id: content.link_id || '',
                link_url: content.link_url || '',
                enable_background: content.enable_background || false,
                background_type: content.background_type || 'color',
                background_color: content.background_color || '#ffffff',
                grid_columns: content.grid_columns || 2,
                grid_rows: content.grid_rows || 2,
                enable_horizontal_animation: content.enable_horizontal_animation || false
            }));
            
            if (['product', 'category', 'brand', 'store'].includes(content.link_type)) {
                this.loadLinkOptions(content.link_type);
            }
            
            if (content.type === 'category') {
                this.searchItems();
            }
            
            this.showModal = true;
        },

        async searchItems() {
            const endpoint = '{{ route("admin.category-screen-content.categories") }}';
            const url = new URL(endpoint, window.location.origin);
            if (this.searchQuery) url.searchParams.set('search', this.searchQuery);
            
            const res = await fetch(url);
            const data = await res.json();
            this.searchResults = data.data || [];
        },

        handleMediaSelect(event) {
            const file = event.target.files[0];
            if (file) this.processMediaFile(file);
        },

        handleMediaDrop(event) {
            event.target.classList.remove('border-purple-500', 'bg-purple-50');
            const file = event.dataTransfer.files[0];
            if (file) this.processMediaFile(file);
        },

        processMediaFile(file) {
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
            if (!validTypes.includes(file.type)) {
                alert('Invalid file type. Please upload JPG, PNG, GIF, MP4, or WebM.');
                return;
            }
            if (file.size > 20 * 1024 * 1024) {
                alert('File too large. Maximum size is 20MB.');
                return;
            }
            this.mediaFile = file;
            if (file.type.startsWith('video/')) {
                this.formData.media_type = 'video';
            } else if (file.type === 'image/gif') {
                this.formData.media_type = 'gif';
            } else {
                this.formData.media_type = 'image';
            }
            const reader = new FileReader();
            reader.onload = (e) => { this.mediaPreview = e.target.result; };
            reader.readAsDataURL(file);
        },

        removeMedia() {
            this.mediaPreview = null;
            this.mediaFile = null;
            this.formData.media_url = '';
        },

        async uploadMedia(contentId) {
            if (!this.mediaFile) return null;
            const formData = new FormData();
            formData.append('file', this.mediaFile);
            formData.append('type', this.formData.media_type);

            const res = await fetch(`{{ url('admin/category-screen-content') }}/${contentId}/media`, {
                method: 'POST',
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });
            return await res.json();
        },

        async handleLinkTypeChange() {
            this.formData.link_id = '';
            this.linkOptions = [];
            if (['product', 'category', 'brand', 'store'].includes(this.formData.link_type)) {
                await this.loadLinkOptions(this.formData.link_type);
            }
        },

        async loadLinkOptions(type) {
            let endpoint;
            switch (type) {
                case 'product': endpoint = '{{ route("admin.category-screen-content.products") }}'; break;
                case 'category': endpoint = '{{ route("admin.category-screen-content.categories") }}'; break;
                case 'brand': endpoint = '{{ route("admin.category-screen-content.brands") }}'; break;
                case 'store': endpoint = '{{ route("admin.category-screen-content.stores") }}'; break;
                default: return;
            }
            const res = await fetch(endpoint);
            const data = await res.json();
            this.linkOptions = data.data || [];
        },

        async saveContent() {
            this.saving = true;
            const payload = { ...this.formData, type: this.modalType };

            try {
                const url = this.editingContent
                    ? `{{ url('admin/category-screen-content') }}/${this.editingContent.id}/update`
                    : '{{ route("admin.category-screen-content.store") }}';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                
                const data = await res.json();

                if (data.success) {
                    let savedContent = data.data;
                    
                    // Upload media for media widgets
                    if (this.modalType === 'media' && this.mediaFile) {
                        try {
                            const uploadResult = await this.uploadMedia(savedContent.id);
                            if (uploadResult.success) {
                                savedContent.media_url = uploadResult.data.media_url;
                                savedContent.media_type = uploadResult.data.media_type;
                            }
                        } catch (e) { console.error('Media upload failed:', e); }
                    }
                    
                    if (this.editingContent) {
                        const idx = this.contents.findIndex(c => c.id === savedContent.id);
                        if (idx !== -1) this.contents[idx] = savedContent;
                    } else {
                        this.contents.push(savedContent);
                    }
                    
                    this.showModal = false;
                }
            } catch (error) {
                console.error('Save failed:', error);
                alert('Failed to save. Please try again.');
            }
            
            this.saving = false;
        },

        async toggleActive(content) {
            content.is_active = !content.is_active;
            await fetch(`{{ url('admin/category-screen-content') }}/${content.id}/update`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ is_active: content.is_active })
            });
        },

        async duplicateContent(content) {
            const res = await fetch(`{{ url('admin/category-screen-content') }}/${content.id}/duplicate`, {
                method: 'POST',
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                this.contents.push(data.data);
            }
        },

        async deleteContent(content) {
            if (!confirm('Delete this widget?')) return;
            
            try {
                const res = await fetch(`{{ url('admin/category-screen-content') }}/${content.id}/delete`, {
                    method: 'POST',
                    headers: { 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                // Guard against non-JSON responses (e.g. HTML error pages from 403/500)
                const contentType = res.headers.get('Content-Type') || '';
                if (!contentType.includes('application/json')) {
                    if (res.status === 403) {
                        alert('Access denied. You do not have permission to delete widgets.');
                    } else {
                        alert('Failed to delete widget. Server returned an unexpected response (HTTP ' + res.status + ').');
                    }
                    return;
                }

                const data = await res.json();
                if (data.success) {
                    this.contents = this.contents.filter(c => c.id !== content.id);
                } else {
                    alert('Failed to delete widget: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Delete failed:', error);
                alert('Failed to delete widget. Please try again.');
            }
        },

        getTypeIcon(type) {
            const icons = {
                category: {
                    bg: 'bg-green-100',
                    text: 'text-green-600',
                    icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>'
                },
                media: {
                    bg: 'bg-purple-100',
                    text: 'text-purple-600',
                    icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                }
            };
            return icons[type] || icons.category;
        },

        getTypeLabel(type) {
            const labels = { category: 'Category', media: 'Media Banner' };
            return labels[type] || type;
        },

        getStyleName(type, style) {
            if (type === 'category') {
                const styles = {
                    style_1: 'Horizontal Scroll',
                    style_2: 'Grid Layout',
                    style_3: 'With Products',
                    style_4: 'Highlight Style'
                };
                return styles[style] || style;
            }
            if (type === 'media') {
                return 'Banner';
            }
            return style;
        },

        getMediaHtml(content, size) {
            if (!content.media_url) return '';
            if (content.media_type === 'video') {
                return `<video src="${content.media_url}" class="w-full h-full object-cover" muted></video>`;
            }
            return `<img src="${content.media_url}" class="w-full h-full object-cover">`;
        }
    }
}
</script>
@endpush

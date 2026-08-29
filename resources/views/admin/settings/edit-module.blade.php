@extends('admin.layouts.app')

@section('title', 'Edit Module - ' . ($tab->name ?? 'Module #' . $tab->id))

@section('content')
<div x-data="editModuleApp()" x-init="init()" class="space-y-6 max-w-7xl mx-auto pb-16">
    
    <!-- Top Navigation & Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-100 shadow-xs">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.home-header.index') }}" 
               class="w-11 h-11 rounded-xl bg-gray-100 hover:bg-orange-50 text-gray-600 hover:text-orange-600 flex items-center justify-center transition-colors shadow-xs" title="Back to Header Settings">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <template x-if="tabData.icon_url">
                        <img :src="tabData.icon_url" class="w-8 h-8 rounded-lg object-cover border border-gray-200 shadow-xs">
                    </template>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight" x-text="tabData.name || 'Module #{{ $tab->id }}'"></h1>
                    <span :class="tabData.is_active ? 'bg-green-100 text-green-700 border-green-200' : 'bg-gray-100 text-gray-500 border-gray-200'"
                          class="px-3 py-0.5 rounded-full text-xs font-extrabold uppercase tracking-wider border"
                          x-text="tabData.is_active ? 'Active' : 'Disabled'"></span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Configure module identity, top header styling, media layer, and quick shortcut cards with real-time live preview.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.home-header.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl transition-colors">
                Cancel
            </a>
            <button type="button" @click="saveTabSettings()" 
                    class="px-6 py-2.5 bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-orange-600/25 flex items-center gap-2 transition-all cursor-pointer active:scale-95"
                    :disabled="saving">
                <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <svg x-show="saving" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="saving ? 'Saving...' : 'SAVE MODULE CHANGES'"></span>
            </button>
        </div>
    </div>

    <!-- Main Content Grid: 2 Cols Form + 1 Col Live Device Preview -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left 7 Columns: Form Controls -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Section 1: General Module Information -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-5 relative" x-data="{ showInfo: false }">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center font-black text-sm">1</div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h2 class="font-black text-gray-900 text-base">General Information</h2>
                                <button type="button" @click="showInfo = !showInfo" class="w-4 h-4 rounded-full bg-orange-100 text-orange-600 font-bold text-[10px] flex items-center justify-center hover:bg-orange-600 hover:text-white transition-colors cursor-pointer" title="Feature Info">i</button>
                            </div>
                            <p class="text-xs text-gray-500">Module title, icon image, and vertical feature type</p>
                        </div>
                    </div>
                </div>

                <!-- Info Tooltip -->
                <div x-show="showInfo" x-cloak x-transition.opacity class="p-3 bg-orange-50 border border-orange-200 rounded-xl text-xs text-orange-950 space-y-1">
                    <p class="font-bold">General Information</p>
                    <p class="text-[11px] leading-relaxed text-orange-900">
                        Configures the primary module name, icon badge, active visibility, and vertical classification (e.g. Grocery, Food, Pharmacy, Ride Sharing). Changing vertical type automatically adjusts units and layout attributes in the mobile app.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Module Name -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Module Name *</label>
                        <input type="text" x-model="tabData.name" placeholder="e.g. Grocery, Pharmacy, Food"
                               class="w-full h-11 px-3.5 text-sm border border-gray-200 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                    </div>

                    <!-- Vertical Module Type -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Vertical Feature Type *</label>
                        <select x-model="tabData.module_type" 
                                class="w-full h-11 px-3.5 text-sm border border-gray-200 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all bg-white">
                            <option value="grocery">Grocery (Units: kg/g/pcs, Shelf Life)</option>
                            <option value="food">Food Delivery (Veg/Non-Veg, Add-ons, Prep Time)</option>
                            <option value="pharmacy">Pharmacy (Prescription Doctor Rx Upload)</option>
                            <option value="ecommerce">Ecommerce / General Store (Color/Size Specs)</option>
                            <option value="cosmetic">Cosmetic & Beauty Products (Skincare Specs)</option>
                            <option value="flower">Flowers & Gift Items (Bouquets)</option>
                            <option value="ride">Ride Sharing (Taxi, Auto, Bike Fares)</option>
                            <option value="parcel">Parcel / Courier Delivery (Weight, Addresses)</option>
                            <option value="service">Home Services & Repairs</option>
                        </select>
                    </div>
                </div>

                <!-- Module Icon Upload -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Module Icon Image</label>
                    <div class="flex items-center gap-3">
                        <template x-if="tabData.icon_url">
                            <img :src="tabData.icon_url" class="w-11 h-11 rounded-xl object-cover border border-gray-200 shadow-xs shrink-0">
                        </template>
                        <input type="text" x-model="tabData.icon_url" placeholder="https://example.com/icon.png"
                               class="flex-1 h-11 px-3.5 text-xs border border-gray-200 rounded-xl focus:border-orange-500 outline-none">
                        <label class="px-4 h-11 bg-orange-50 hover:bg-orange-100 text-orange-700 font-bold text-xs rounded-xl flex items-center justify-center cursor-pointer transition-colors shrink-0 gap-2">
                            <svg x-show="uploadingIcon" x-cloak class="w-4 h-4 animate-spin text-orange-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="uploadingIcon ? 'Uploading...' : 'Upload Icon'"></span>
                            <input type="file" @change="uploadModuleIconFile($event)" class="hidden" accept="image/*" :disabled="uploadingIcon">
                        </label>
                    </div>
                </div>

                <!-- Status Toggle -->
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div>
                        <p class="font-bold text-gray-900 text-xs">Module Active Status</p>
                        <p class="text-[11px] text-gray-500">Enable or disable module in user app</p>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" x-model="tabData.is_active">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Section 2: Top Header Bar Background (Location, Searchbar, Module Tabs) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-5 relative" x-data="{ showInfo: false }">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 text-white flex items-center justify-center font-black text-sm">2</div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h2 class="font-black text-gray-900 text-base">Top Header Bar Background</h2>
                                <button type="button" @click="showInfo = !showInfo" class="w-4 h-4 rounded-full bg-amber-100 text-amber-700 font-bold text-[10px] flex items-center justify-center hover:bg-amber-600 hover:text-white transition-colors cursor-pointer" title="Feature Info">i</button>
                            </div>
                            <p class="text-xs text-gray-500">Background for Location bar, Search bar & Module tabs</p>
                        </div>
                    </div>
                </div>

                <!-- Info Tooltip -->
                <div x-show="showInfo" x-cloak x-transition.opacity class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-950 space-y-1">
                    <p class="font-bold">Top Header Bar Background</p>
                    <p class="text-[11px] leading-relaxed text-amber-900">
                        Defines the background style wrapping the Location selector, Category tabs, Search bar, and AI section. Supports 2-color linear gradients (with 8 directional styles), solid single color, or custom static header image.
                    </p>
                </div>

                <!-- Header Background Type -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Background Fill Type</label>
                    <select x-model="tabData.header_bg_type" 
                            class="w-full h-11 px-3.5 text-sm border border-gray-200 rounded-xl focus:border-orange-500 outline-none bg-white">
                        <option value="gradient">2-Color Linear Gradient (8 Directional Options)</option>
                        <option value="solid">Solid Single Color</option>
                        <option value="image">Header Background Image</option>
                    </select>
                </div>

                <!-- Gradient Controls -->
                <div x-show="tabData.header_bg_type === 'gradient' || tabData.header_bg_type === 'solid'" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Primary Color (Color 1)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="tabData.header_gradient_color1" class="w-11 h-11 rounded-xl border border-gray-200 cursor-pointer p-1">
                                <input type="text" x-model="tabData.header_gradient_color1" placeholder="#FF5722" class="flex-1 h-11 px-3 text-xs font-mono border border-gray-200 rounded-xl outline-none uppercase font-bold">
                            </div>
                        </div>
                        <div x-show="tabData.header_bg_type === 'gradient'">
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Secondary Color (Color 2)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="tabData.header_gradient_color2" class="w-11 h-11 rounded-xl border border-gray-200 cursor-pointer p-1">
                                <input type="text" x-model="tabData.header_gradient_color2" placeholder="#FF9800" class="flex-1 h-11 px-3 text-xs font-mono border border-gray-200 rounded-xl outline-none uppercase font-bold">
                            </div>
                        </div>
                    </div>

                    <!-- 8 Directional Styles -->
                    <div x-show="tabData.header_bg_type === 'gradient'">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Gradient Direction / Style (8 Direction Options)</label>
                        <select x-model="tabData.header_gradient_style" 
                                class="w-full h-11 px-3.5 text-sm border border-gray-200 rounded-xl focus:border-orange-500 outline-none bg-white">
                            <option value="top_to_bottom">Option 1: Top to Bottom (Vertical ⬇️)</option>
                            <option value="bottom_to_top">Option 2: Bottom to Top (Reverse Vertical ⬆️)</option>
                            <option value="left_to_right">Option 3: Left to Right (Horizontal ➡️)</option>
                            <option value="right_to_left">Option 4: Right to Left (Reverse Horizontal ⬅️)</option>
                            <option value="top_left_to_bottom_right">Option 5: Top-Left to Bottom-Right (Diagonal ↘️)</option>
                            <option value="bottom_right_to_top_left">Option 6: Bottom-Right to Top-Left (Diagonal ↖️)</option>
                            <option value="top_right_to_bottom_left">Option 7: Top-Right to Bottom-Left (Diagonal ↙️)</option>
                            <option value="bottom_left_to_top_right">Option 8: Bottom-Left to Top-Right (Diagonal ↗️)</option>
                        </select>
                    </div>
                </div>

                <!-- Header Image Input -->
                <div x-show="tabData.header_bg_type === 'image'" class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Header Background Image URL</label>
                    <input type="text" x-model="tabData.header_bg_image_url" placeholder="https://example.com/top-header-bg.png"
                           class="w-full h-11 px-3.5 text-xs border border-gray-200 rounded-xl focus:border-orange-500 outline-none">
                </div>
            </div>

            <!-- Section 3: Quick Access Cards Background Media -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-5 relative" x-data="{ showInfo: false }">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-black text-sm">3</div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h2 class="font-black text-gray-900 text-base">Quick Access Cards Background Media</h2>
                                <button type="button" @click="showInfo = !showInfo" class="w-4 h-4 rounded-full bg-purple-100 text-purple-600 font-bold text-[10px] flex items-center justify-center hover:bg-purple-600 hover:text-white transition-colors cursor-pointer" title="Feature Info">i</button>
                            </div>
                            <p class="text-xs text-gray-500">Video, GIF, or Image layer wrapping quick shortcut cards</p>
                        </div>
                    </div>
                </div>

                <!-- Media Type -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Cards Background Media Type</label>
                    <select x-model="tabData.background_type" 
                            class="w-full h-11 px-3.5 text-sm border border-gray-200 rounded-xl focus:border-orange-500 outline-none bg-white">
                        <option value="image">Image Background (JPG/PNG/WebP)</option>
                        <option value="video">MP4 Video Background</option>
                        <option value="gif">Animated GIF Background</option>
                    </select>
                </div>

                <!-- Media Upload / URL -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Media File Upload / URL</label>
                    <div class="flex items-center gap-3">
                        <template x-if="tabData.background_url && tabData.background_type !== 'video'">
                            <img :src="tabData.background_url" class="w-11 h-11 rounded-xl object-cover border border-purple-200 shadow-xs shrink-0">
                        </template>
                        <input type="text" x-model="tabData.background_url" placeholder="https://example.com/background.mp4"
                               class="flex-1 h-11 px-3.5 text-xs border border-gray-200 rounded-xl focus:border-orange-500 outline-none">
                        <label class="px-4 h-11 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs rounded-xl flex items-center justify-center cursor-pointer transition-colors shrink-0 gap-2">
                            <svg x-show="uploadingMedia" x-cloak class="w-4 h-4 animate-spin text-purple-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="uploadingMedia ? 'Uploading...' : 'Upload Media'"></span>
                            <input type="file" @change="uploadBackgroundMedia($event)" class="hidden" accept="image/*,video/mp4,image/gif" :disabled="uploadingMedia">
                        </label>
                    </div>
                </div>

                <!-- Sticky Shield Color Picker -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Sticky Header Pinned Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" x-model="tabData.sticky_header_color" class="w-11 h-11 rounded-xl border border-gray-200 cursor-pointer p-1">
                        <input type="text" x-model="tabData.sticky_header_color" placeholder="#E5E7EB" class="flex-1 h-11 px-3 text-xs font-mono border border-gray-200 rounded-xl outline-none uppercase font-bold">
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Color shown when user scrolls down and the header pins to the top of screen.</p>
                </div>
            </div>

            <!-- Section 4: Quick Access Cards Manager -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-black text-sm">4</div>
                        <div>
                            <h2 class="font-black text-gray-900 text-base">Quick Access Cards (Shortcuts)</h2>
                            <p class="text-xs text-gray-500">Shortcut buttons shown inside this module (max 6 cards)</p>
                        </div>
                    </div>
                    <button type="button" @click="openAddCardModal()" 
                            class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer"
                            :disabled="cards.length >= 6">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Quick Card</span>
                    </button>
                </div>

                <!-- Layout Switcher Toggle (Horizontal Scroll vs 2-Row Grid) -->
                <div class="flex items-center justify-between p-4 bg-blue-50/50 rounded-xl border border-blue-100">
                    <div>
                        <p class="font-bold text-gray-900 text-xs">Horizontal Scroll Cards Layout</p>
                        <p class="text-[11px] text-gray-500">Toggle ON for horizontal scroll pill row; OFF for 2-row Grid</p>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" x-model="tabData.cards_horizontal">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <!-- Quick Cards Table -->
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-[11px] font-black text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                <th class="py-3 px-3 w-12 text-center">Order</th>
                                <th class="py-3 px-3 w-16">Icon</th>
                                <th class="py-3 px-4">Destination Link</th>
                                <th class="py-3 px-3 w-20 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            <template x-for="(card, index) in cards" :key="card.id || index">
                                <tr class="hover:bg-orange-50/40 transition-colors">
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" @click="moveCardUp(index)" :disabled="index === 0" class="p-1 text-gray-400 hover:text-orange-600 disabled:opacity-20 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg></button>
                                            <button type="button" @click="moveCardDown(index)" :disabled="index === cards.length - 1" class="p-1 text-gray-400 hover:text-orange-600 disabled:opacity-20 cursor-pointer"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg></button>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center">
                                            <template x-if="card.image_url">
                                                <img :src="card.image_url" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!card.image_url">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <p class="font-bold text-gray-900" x-text="getCardTargetLabel(card)"></p>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider" x-text="'Type: ' + (card.link_type || 'category')"></p>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span :class="card.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400'"
                                              class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase"
                                              x-text="card.is_active ? 'ON' : 'OFF'"></span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" @click="openEditCardModal(card)" class="p-1.5 text-orange-600 hover:bg-orange-100 rounded-lg transition-colors cursor-pointer" title="Edit Card"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 012.828 0L20 7m-2.828-4L11 11.828V15h3.172l9.828-9.828z"/></svg></button>
                                            <button type="button" @click="deleteCard(card.id, index)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors cursor-pointer" title="Delete Card"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="cards.length === 0">
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-gray-400">
                                        <p class="font-bold text-gray-600 text-xs">No Quick Shortcut Cards Created</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">Click "Add Quick Card" above to add up to 6 shortcuts.</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right 5 Columns: Interactive Live Mobile Preview Phone Frame -->
        <div class="lg:col-span-5">
            <div class="sticky top-6 space-y-4">
                
                <div class="flex items-center justify-between px-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-ping"></span>
                        <h3 class="font-black text-gray-900 text-sm tracking-tight uppercase">Live Mobile Preview</h3>
                    </div>
                    <span class="text-[11px] font-bold text-orange-600 bg-orange-50 px-2.5 py-1 rounded-full border border-orange-200">Real-Time Sync</span>
                </div>

                <!-- Realistic Smartphone Mockup Outer Container -->
                <div class="w-full max-w-[375px] mx-auto bg-gray-950 rounded-[48px] p-4 shadow-2xl border-4 border-gray-800 relative ring-1 ring-black/40">
                    
                    <!-- Side Buttons -->
                    <div class="absolute -left-5 top-24 w-1 h-12 bg-gray-800 rounded-l-md"></div>
                    <div class="absolute -left-5 top-40 w-1 h-12 bg-gray-800 rounded-l-md"></div>
                    <div class="absolute -right-5 top-28 w-1 h-16 bg-gray-800 rounded-r-md"></div>

                    <!-- Inner Phone Screen Area -->
                    <div class="w-full bg-slate-900 rounded-[38px] overflow-hidden relative border border-gray-800 min-h-[640px] flex flex-col text-white shadow-inner">
                        
                        <!-- Top Camera Notch Island & Speaker -->
                        <div class="w-full h-8 bg-black flex items-center justify-between px-6 shrink-0 relative z-30">
                            <span class="text-[11px] font-bold text-white tracking-tighter">11:47</span>
                            <!-- Notch Island -->
                            <div class="w-20 h-4 bg-black rounded-b-xl mx-auto flex items-center justify-center gap-1.5 border-x border-b border-gray-800">
                                <div class="w-2 h-2 rounded-full bg-gray-900 border border-gray-700"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-blue-950"></div>
                            </div>
                            <!-- Status Icons -->
                            <div class="flex items-center gap-1 text-[10px] text-white">
                                <span>5G</span>
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 90 2.12.74 4.07 1.97 5.61l1.42-1.42C3.12 9.47 1.7 7.82 1.7 6h20.6c0 1.82-1.42 3.47-3.11 4.19l1.42 1.42C22.07 10.07 24 8.12 24 6c0-4.97-4.03-9-9-9z"/></svg>
                                <span>44%</span>
                            </div>
                        </div>

                        <!-- 1A. LIVE TOP HEADER BAR BACKGROUND LAYER -->
                        <div class="relative w-full overflow-hidden transition-all duration-300 pb-3"
                             :style="
                                tabData.header_bg_type === 'image' && tabData.header_bg_image_url ? `background-image: url('${tabData.header_bg_image_url}'); background-size: cover; background-position: center;` :
                                tabData.header_bg_type === 'solid' ? `background-color: ${tabData.header_gradient_color1 || '#FF5722'};` :
                                tabData.header_gradient_style === 'bottom_to_top' ? `background: linear-gradient(to top, ${tabData.header_gradient_color1 || '#FF5722'}, ${tabData.header_gradient_color2 || '#FF9800'});` :
                                tabData.header_gradient_style === 'left_to_right' ? `background: linear-gradient(to right, ${tabData.header_gradient_color1 || '#FF5722'}, ${tabData.header_gradient_color2 || '#FF9800'});` :
                                tabData.header_gradient_style === 'right_to_left' ? `background: linear-gradient(to left, ${tabData.header_gradient_color1 || '#FF5722'}, ${tabData.header_gradient_color2 || '#FF9800'});` :
                                (tabData.header_gradient_style === 'top_left_to_bottom_right' || tabData.header_gradient_style === 'diagonal') ? `background: linear-gradient(to bottom right, ${tabData.header_gradient_color1 || '#FF5722'}, ${tabData.header_gradient_color2 || '#FF9800'});` :
                                tabData.header_gradient_style === 'bottom_right_to_top_left' ? `background: linear-gradient(to top left, ${tabData.header_gradient_color1 || '#FF5722'}, ${tabData.header_gradient_color2 || '#FF9800'});` :
                                tabData.header_gradient_style === 'top_right_to_bottom_left' ? `background: linear-gradient(to bottom left, ${tabData.header_gradient_color1 || '#FF5722'}, ${tabData.header_gradient_color2 || '#FF9800'});` :
                                tabData.header_gradient_style === 'bottom_left_to_top_right' ? `background: linear-gradient(to top right, ${tabData.header_gradient_color1 || '#FF5722'}, ${tabData.header_gradient_color2 || '#FF9800'});` :
                                `background: linear-gradient(to bottom, ${tabData.header_gradient_color1 || '#FF5722'}, ${tabData.header_gradient_color2 || '#FF9800'});`
                             ">
                            
                            <!-- Location Bar -->
                            <div class="px-4 py-2 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-white/20 backdrop-blur-xs flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-white/80 font-bold uppercase tracking-tight">Deliver to</p>
                                        <p class="text-xs font-black text-white flex items-center gap-1">
                                            <span>Patna, 800001</span>
                                            <svg class="w-3 h-3 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                        </p>
                                    </div>
                                </div>
                                <div class="w-7 h-7 rounded-full bg-white/20 backdrop-blur-xs flex items-center justify-center relative">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    <span class="w-2 h-2 rounded-full bg-red-500 absolute top-1 right-1 border border-white"></span>
                                </div>
                            </div>

                            <!-- Category / Module Tabs Row -->
                            <div class="px-3 py-1 flex items-center gap-2 overflow-x-auto scrollbar-none">
                                <div class="px-3 py-1.5 rounded-full bg-white text-gray-900 font-extrabold text-[11px] shadow-sm shrink-0 flex items-center gap-1.5 border border-white">
                                    <template x-if="tabData.icon_url">
                                        <img :src="tabData.icon_url" class="w-4 h-4 rounded-md object-cover">
                                    </template>
                                    <span x-text="tabData.name || 'Module'"></span>
                                </div>
                                <div class="px-3 py-1.5 rounded-full bg-white/20 text-white font-bold text-[11px] shrink-0 border border-white/20">Category 1</div>
                                <div class="px-3 py-1.5 rounded-full bg-white/20 text-white font-bold text-[11px] shrink-0 border border-white/20">Category 2</div>
                                <div class="px-3 py-1.5 rounded-full bg-white/20 text-white font-bold text-[11px] shrink-0 border border-white/20">Category 3</div>
                            </div>

                            <!-- Search Bar & AI Section -->
                            <div class="px-3 pt-2">
                                <div class="w-full bg-white rounded-xl h-10 px-3 flex items-center justify-between text-gray-500 shadow-md">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <span class="text-xs text-gray-400 font-medium">Search "Fresh Spinach"</span>
                                    </div>
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-center text-white text-[10px] font-black shadow-xs">
                                        AI
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 1B. LIVE QUICK ACCESS CARDS BACKGROUND MEDIA & CARDS LAYER (Anchored to BOTTOM like user app) -->
                        <div class="flex-1 relative bg-slate-950 flex flex-col justify-end overflow-hidden p-2.5 pb-4 min-h-[300px]">
                            
                            <!-- Media Background Layer -->
                            <div class="absolute inset-0 z-0">
                                <template x-if="tabData.background_type === 'image' && tabData.background_url">
                                    <img :src="tabData.background_url" class="w-full h-full object-cover opacity-95">
                                </template>
                                <template x-if="tabData.background_type === 'video' && tabData.background_url">
                                    <video :src="tabData.background_url" autoplay loop muted playsinline class="w-full h-full object-cover opacity-95"></video>
                                </template>
                                <template x-if="tabData.background_type === 'gif' && tabData.background_url">
                                    <img :src="tabData.background_url" class="w-full h-full object-cover opacity-95">
                                </template>
                                <template x-if="!tabData.background_url">
                                    <div class="w-full h-full bg-gradient-to-b from-slate-900 to-slate-950"></div>
                                </template>
                            </div>

                            <!-- Cards Container Layer (Anchored to BOTTOM of media background just like app) -->
                            <div class="relative z-10 w-full mt-auto">
                                <!-- Horizontal Scroll Mode -->
                                <template x-if="tabData.cards_horizontal">
                                    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                                        <template x-for="(card, i) in cards" :key="card.id || i">
                                            <div class="w-22 h-26 bg-white/95 backdrop-blur-md p-1.5 rounded-2xl border border-white/60 shadow-md text-center shrink-0 flex flex-col items-center justify-between transition-all">
                                                <div class="w-full flex-1 rounded-xl overflow-hidden bg-purple-50 flex items-center justify-center border border-gray-100 shrink-0">
                                                    <template x-if="card.image_url">
                                                        <img :src="card.image_url" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!card.image_url">
                                                        <span class="text-[9px] font-bold text-gray-400" x-text="'Card ' + (i+1)"></span>
                                                    </template>
                                                </div>
                                                <span class="text-[10px] font-extrabold text-gray-900 line-clamp-1 py-0.5 leading-none" x-text="getCardTargetLabel(card)"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <!-- Grid Mode (3 Columns x 2 Rows anchored at bottom) -->
                                <template x-if="!tabData.cards_horizontal">
                                    <div class="grid grid-cols-3 gap-2">
                                        <template x-for="(card, i) in cards" :key="card.id || i">
                                            <div class="aspect-square bg-white/95 backdrop-blur-md p-1.5 rounded-2xl border border-white/60 shadow-md text-center flex flex-col items-center justify-between transition-all">
                                                <div class="w-full flex-1 rounded-xl overflow-hidden bg-purple-50 flex items-center justify-center border border-gray-100">
                                                    <template x-if="card.image_url">
                                                        <img :src="card.image_url" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="!card.image_url">
                                                        <span class="text-[10px] font-bold text-gray-400" x-text="'Card ' + (i+1)"></span>
                                                    </template>
                                                </div>
                                                <span class="text-[10px] font-extrabold text-gray-900 line-clamp-1 py-0.5 leading-none" x-text="getCardTargetLabel(card)"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access Card Add/Edit Modal -->
    <div x-show="showCardModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCardModal" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showCardModal" x-transition.scale.origin.center class="inline-block align-bottom bg-white rounded-2xl text-left shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100 overflow-visible relative">
                <div class="bg-gray-900 text-white px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <h3 class="font-bold text-base" x-text="cardForm.id ? 'Edit Quick Card' : 'Add New Quick Card'"></h3>
                    <button type="button" @click="showCardModal = false" class="text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>

                <div class="p-6 space-y-4">
                    <!-- Card Icon Image -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Card Icon Image *</label>
                        <div class="flex gap-2 mb-2">
                            <input type="text" x-model="cardForm.image_url" placeholder="https://example.com/icon.png" class="flex-1 h-10 px-3.5 text-xs border border-gray-200 rounded-xl outline-none focus:border-orange-500">
                            <label class="px-3.5 h-10 bg-orange-50 hover:bg-orange-100 text-orange-700 font-semibold text-xs rounded-xl flex items-center justify-center cursor-pointer transition-colors shrink-0">
                                <span>Upload File</span>
                                <input type="file" @change="uploadCardIconFile($event)" class="hidden" accept="image/*">
                            </label>
                        </div>
                        <template x-if="cardForm.image_url">
                            <div class="w-14 h-14 rounded-xl border border-gray-200 overflow-hidden bg-gray-50">
                                <img :src="cardForm.image_url" class="w-full h-full object-cover">
                            </div>
                        </template>
                    </div>

                    <!-- Target Action Type -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Target Action Type *</label>
                        <select x-model="cardForm.link_type" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-xl outline-none focus:border-orange-500 bg-white">
                            <option value="category">Category Link</option>
                            <option value="product">Product Link</option>
                            <option value="store">Store Link</option>
                            <option value="url">External URL / Screen</option>
                        </select>
                    </div>

                    <!-- Searchable Destination Dropdown -->
                    <div>
                        <template x-if="cardForm.link_type !== 'url'">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1" x-text="'Select Destination ' + cardForm.link_type.toUpperCase() + ' *'"></label>
                                <div class="relative" x-data="{ openDropdown: false }">
                                    <div class="flex items-center relative">
                                        <input type="text" 
                                               x-model="destinationSearch"
                                               @focus="openDropdown = true"
                                               @click="openDropdown = true"
                                               placeholder="Click or type to search destination..." 
                                               class="w-full h-10 px-3.5 pr-8 text-xs border border-gray-200 rounded-xl outline-none focus:border-orange-500 bg-white">
                                        <button type="button" @click="openDropdown = !openDropdown" class="absolute right-2 text-gray-400 hover:text-gray-600 p-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </div>
                                    
                                    <!-- Search Results Dropdown -->
                                    <div x-show="openDropdown" @click.outside="openDropdown = false" x-cloak class="absolute left-0 right-0 top-11 bg-white border border-gray-200 rounded-xl shadow-2xl z-50 max-h-56 overflow-y-auto divide-y divide-gray-100">
                                        <div @click="destinationSearch = ''; openDropdown = true;" class="p-2 bg-orange-50/60 text-[11px] text-orange-600 font-bold hover:bg-orange-100 cursor-pointer text-center">
                                            Show All Options
                                        </div>

                                        <template x-if="cardForm.link_type === 'category'">
                                            <div>
                                                <template x-for="cat in filteredCategories" :key="cat.id">
                                                    <div @click="selectDestination(cat.id, cat.name); openDropdown = false;"
                                                         class="p-2.5 hover:bg-orange-50 cursor-pointer flex items-center justify-between transition-colors">
                                                        <span class="text-xs font-semibold text-gray-800" x-text="cat.name"></span>
                                                        <span class="text-[10px] text-gray-400">ID: #<span x-text="cat.id"></span></span>
                                                    </div>
                                                </template>
                                                <div x-show="filteredCategories.length === 0" class="p-3 text-center text-xs text-gray-400">No categories found</div>
                                            </div>
                                        </template>

                                        <template x-if="cardForm.link_type === 'product'">
                                            <div>
                                                <template x-for="prod in filteredProducts" :key="prod.id">
                                                    <div @click="selectDestination(prod.id, prod.name); openDropdown = false;"
                                                         class="p-2.5 hover:bg-orange-50 cursor-pointer flex items-center justify-between transition-colors">
                                                        <span class="text-xs font-semibold text-gray-800" x-text="prod.name"></span>
                                                        <span class="text-[10px] text-gray-400">ID: #<span x-text="prod.id"></span></span>
                                                    </div>
                                                </template>
                                                <div x-show="filteredProducts.length === 0" class="p-3 text-center text-xs text-gray-400">No products found</div>
                                            </div>
                                        </template>

                                        <template x-if="cardForm.link_type === 'store'">
                                            <div>
                                                <template x-for="st in filteredStores" :key="st.id">
                                                    <div @click="selectDestination(st.id, st.name); openDropdown = false;"
                                                         class="p-2.5 hover:bg-orange-50 cursor-pointer flex items-center justify-between transition-colors">
                                                        <span class="text-xs font-semibold text-gray-800" x-text="st.name"></span>
                                                        <span class="text-[10px] text-gray-400">ID: #<span x-text="st.id"></span></span>
                                                    </div>
                                                </template>
                                                <div x-show="filteredStores.length === 0" class="p-3 text-center text-xs text-gray-400">No stores found</div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="cardForm.link_type === 'url'">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Custom Link URL / Screen *</label>
                                <input type="text" x-model="cardForm.link_url" placeholder="https://example.com or /screen" class="w-full h-10 px-3.5 text-xs border border-gray-200 rounded-xl outline-none focus:border-orange-500">
                            </div>
                        </template>
                    </div>

                    <!-- Active Status Toggle -->
                    <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-xs font-semibold text-gray-700">Active Status</span>
                        <label class="toggle">
                            <input type="checkbox" x-model="cardForm.is_active">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                    <button type="button" @click="showCardModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900">Cancel</button>
                    <button type="button" @click="saveCard()" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer">Save Card</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function editModuleApp() {
    return {
        tabData: @json($tab),
        cards: @json($tab->allCards),
        availableCategories: @json($categories),
        availableProducts: @json($products),
        availableStores: @json($stores),
        uploadingIcon: false,
        uploadingMedia: false,
        saving: false,
        showCardModal: false,
        destinationSearch: '',
        cardForm: { id: null, image_url: '', link_type: 'category', link_id: '', link_url: '', is_active: true },

        init() {
            this.tabData.is_active = Boolean(this.tabData.is_active);
            this.tabData.cards_horizontal = Boolean(this.tabData.cards_horizontal);
            this.tabData.header_bg_type = this.tabData.header_bg_type || 'gradient';
            this.tabData.header_gradient_color1 = this.tabData.header_gradient_color1 || '#FF5722';
            this.tabData.header_gradient_color2 = this.tabData.header_gradient_color2 || '#FF9800';
            this.tabData.header_gradient_style = this.tabData.header_gradient_style || 'top_to_bottom';
        },

        get filteredCategories() {
            if (!this.destinationSearch || this.destinationSearch === this.getCardTargetLabel(this.cardForm)) {
                return this.availableCategories;
            }
            const q = this.destinationSearch.toLowerCase();
            return this.availableCategories.filter(c => c.name && c.name.toLowerCase().includes(q));
        },

        get filteredProducts() {
            if (!this.destinationSearch || this.destinationSearch === this.getCardTargetLabel(this.cardForm)) {
                return this.availableProducts;
            }
            const q = this.destinationSearch.toLowerCase();
            return this.availableProducts.filter(p => p.name && p.name.toLowerCase().includes(q));
        },

        get filteredStores() {
            if (!this.destinationSearch || this.destinationSearch === this.getCardTargetLabel(this.cardForm)) {
                return this.availableStores;
            }
            const q = this.destinationSearch.toLowerCase();
            return this.availableStores.filter(s => s.name && s.name.toLowerCase().includes(q));
        },

        getCardTargetLabel(card) {
            if (card.link_type === 'category') {
                const item = this.availableCategories.find(c => String(c.id) === String(card.link_id));
                return item ? item.name : ('Category #' + card.link_id);
            }
            if (card.link_type === 'product') {
                const item = this.availableProducts.find(p => String(p.id) === String(card.link_id));
                return item ? item.name : ('Product #' + card.link_id);
            }
            if (card.link_type === 'store') {
                const item = this.availableStores.find(s => String(s.id) === String(card.link_id));
                return item ? item.name : ('Store #' + card.link_id);
            }
            return card.link_url || 'Custom Link';
        },

        async saveTabSettings() {
            this.saving = true;
            try {
                const response = await fetch("{{ route('admin.home-header.tabs.update', $tab->id) }}", {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.tabData)
                });
                const res = await response.json();
                if (res.success) {
                    alert('Module updated successfully!');
                } else {
                    alert(res.message || 'Failed to update module.');
                }
            } catch (e) {
                alert('Error saving settings: ' + e.message);
            } finally {
                this.saving = false;
            }
        },

        async uploadModuleIconFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.uploadingIcon = true;
            const formData = new FormData();
            formData.append('file', file);
            try {
                const response = await fetch("{{ route('admin.home-header.tabs.icon', $tab->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const res = await response.json();
                if (res.success && (res.url || res.data?.icon_url)) {
                    const newUrl = res.url || res.data.icon_url;
                    this.tabData.icon_url = newUrl;
                } else {
                    alert(res.message || 'Module icon upload failed');
                }
            } catch (e) {
                alert('Module icon upload failed: ' + e.message);
            } finally {
                this.uploadingIcon = false;
                event.target.value = '';
            }
        },

        async uploadBackgroundMedia(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.uploadingMedia = true;
            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', this.tabData.background_type || 'image');
            try {
                const response = await fetch("{{ route('admin.home-header.tabs.background', $tab->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const res = await response.json();
                if (res.success && (res.url || res.data?.background_url)) {
                    const newUrl = res.url || res.data.background_url;
                    this.tabData.background_url = newUrl;
                } else {
                    alert(res.message || 'Media upload failed');
                }
            } catch (e) {
                alert('Upload failed: ' + e.message);
            } finally {
                this.uploadingMedia = false;
                event.target.value = '';
            }
        },

        async uploadCardIconFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('file', file);
            try {
                const response = await fetch("{{ route('admin.home-header.cards.upload-image') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const res = await response.json();
                if (res.url) {
                    this.cardForm.image_url = res.url;
                } else if (res.data?.image_url) {
                    this.cardForm.image_url = res.data.image_url;
                }
            } catch (e) {
                alert('Card icon upload failed: ' + e.message);
            }
        },

        selectDestination(id, name) {
            this.cardForm.link_id = id;
            this.destinationSearch = name;
        },

        openAddCardModal() {
            this.destinationSearch = '';
            this.cardForm = { id: null, image_url: '', link_type: 'category', link_id: '', link_url: '', is_active: true };
            this.showCardModal = true;
        },

        openEditCardModal(card) {
            this.cardForm = { ...card };
            this.destinationSearch = this.getCardTargetLabel(card);
            this.showCardModal = true;
        },

        async saveCard() {
            try {
                const isEdit = Boolean(this.cardForm.id);
                const url = isEdit 
                    ? `/admin/home-header/cards/${this.cardForm.id}`
                    : "{{ route('admin.home-header.cards.store', $tab->id) }}";
                const method = isEdit ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.cardForm)
                });
                const res = await response.json();
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'Error saving card');
                }
            } catch (e) {
                alert('Error: ' + e.message);
            }
        },

        async moveCardUp(index) {
            if (index <= 0) return;
            const temp = this.cards[index];
            this.cards[index] = this.cards[index - 1];
            this.cards[index - 1] = temp;
            await this.saveCardsReorder();
        },

        async moveCardDown(index) {
            if (index >= this.cards.length - 1) return;
            const temp = this.cards[index];
            this.cards[index] = this.cards[index + 1];
            this.cards[index + 1] = temp;
            await this.saveCardsReorder();
        },

        async saveCardsReorder() {
            const cardIds = this.cards.map(c => c.id);
            try {
                await fetch("{{ route('admin.home-header.cards.reorder', $tab->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ card_ids: cardIds })
                });
            } catch (e) {
                console.error('Reorder save error', e);
            }
        },

        async deleteCard(cardId, index) {
            if (!confirm('Are you sure you want to delete this card?')) return;
            try {
                const response = await fetch(`/admin/home-header/cards/${cardId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const res = await response.json();
                if (res.success) {
                    this.cards.splice(index, 1);
                }
            } catch (e) {
                alert('Error deleting card');
            }
        }
    };
}
</script>
@endsection

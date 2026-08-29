<!-- Common Widget Fields: Title, Subtitle, Style -->

<!-- 📱 Widget Header Section -->
<div class="bg-gradient-to-r from-orange-50 to-white border border-orange-100 rounded-xl p-4 mb-4">
    <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
        <h4 class="font-semibold text-gray-800">Widget Header</h4>
    </div>
    <p class="text-xs text-gray-500 mb-4">This text appears at the top of your widget in the app</p>

    <!-- Title -->
    <div class="form-group">
        <label class="label flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Widget Title
            <span class="text-xs text-gray-400 font-normal">(Optional)</span>
        </label>
        <input type="text" x-model="formData.title" class="input" placeholder="e.g., 🔥 Trending Products, 🎉 New Arrivals">
        <p class="text-xs text-gray-400 mt-1">Leave empty to hide the title</p>
    </div>

    <div class="form-group flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-100">
        <label class="toggle"><input type="checkbox" x-model="formData.show_title"><span class="toggle-slider"></span></label>
        <div>
            <span class="text-sm font-medium text-gray-700">Show title in app</span>
            <p class="text-xs text-gray-400">Toggle visibility on mobile screen</p>
        </div>
    </div>
</div>

<!-- Subtitle -->
<div class="form-group">
    <label class="label flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
        </svg>
        Widget Subtitle
        <span class="text-xs text-gray-400 font-normal">(Optional)</span>
    </label>
    <input type="text" x-model="formData.subtitle" class="input" placeholder="e.g., Handpicked just for you, Limited time offers" maxlength="200">
    <p class="text-xs text-gray-400 mt-1">Short description shown below the title (max 200 characters)</p>
</div>

<!-- 📱/💻 Device Target Visibility -->
<div class="form-group border-t pt-4 mt-4">
    <div class="flex items-center gap-2 mb-2">
        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        <label class="label mb-0 font-semibold text-gray-800">Device Target Visibility</label>
    </div>
    <p class="text-xs text-gray-500 mb-3 ml-7">Select which devices will display this widget on the website</p>

    <div class="grid grid-cols-3 gap-3">
        <!-- All Devices -->
        <label :class="(formData.show_on_mobile && formData.show_on_laptop) ? 'border-indigo-500 bg-indigo-50/70 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50'"
               class="border-2 rounded-xl p-3 cursor-pointer text-center transition-all flex flex-col items-center justify-center select-none">
            <input type="radio" name="device_target" value="all" 
                   :checked="formData.show_on_mobile && formData.show_on_laptop"
                   @change="formData.show_on_mobile = true; formData.show_on_laptop = true;" class="hidden">
            <div class="text-xl mb-1">💻 📱</div>
            <div class="text-xs font-bold text-gray-800">All Devices</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Mobile & Laptop</div>
        </label>

        <!-- Mobile Only -->
        <label :class="(formData.show_on_mobile && !formData.show_on_laptop) ? 'border-purple-500 bg-purple-50/70 ring-2 ring-purple-200' : 'border-gray-200 hover:border-purple-300 hover:bg-gray-50'"
               class="border-2 rounded-xl p-3 cursor-pointer text-center transition-all flex flex-col items-center justify-center select-none">
            <input type="radio" name="device_target" value="mobile" 
                   :checked="formData.show_on_mobile && !formData.show_on_laptop"
                   @change="formData.show_on_mobile = true; formData.show_on_laptop = false;" class="hidden">
            <div class="text-xl mb-1">📱</div>
            <div class="text-xs font-bold text-gray-800">Mobile Only</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Hide on Laptop</div>
        </label>

        <!-- Laptop / Desktop Only -->
        <label :class="(!formData.show_on_mobile && formData.show_on_laptop) ? 'border-blue-500 bg-blue-50/70 ring-2 ring-blue-200' : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'"
               class="border-2 rounded-xl p-3 cursor-pointer text-center transition-all flex flex-col items-center justify-center select-none">
            <input type="radio" name="device_target" value="laptop" 
                   :checked="!formData.show_on_mobile && formData.show_on_laptop"
                   @change="formData.show_on_mobile = false; formData.show_on_laptop = true;" class="hidden">
            <div class="text-xl mb-1">💻</div>
            <div class="text-xs font-bold text-gray-800">Laptop Only</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Hide on Mobile</div>
        </label>
    </div>
</div>

<!-- Style Selection -->
<div class="form-group border-t pt-4 mt-4">
    <div class="flex items-center gap-2 mb-2">
        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
        </svg>
        <label class="label mb-0">Choose Display Style</label>
    </div>
    <p class="text-xs text-gray-500 mb-3 ml-7">Select how items will appear in the app</p>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <template x-for="style in getAvailableStyles(modalType)" :key="style">
            <label :class="formData.style === style ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-200' : 'border-gray-200 hover:border-orange-300 hover:bg-orange-25'" class="border-2 rounded-xl p-3 cursor-pointer text-center transition-all">
                <input type="radio" x-model="formData.style" :value="style" @change="handleStyleChange()" class="hidden">
                <div class="text-2xl mb-2" x-text="getStylePreview(modalType, style)"></div>
                <div class="text-xs font-medium text-gray-700" x-text="getStyleName(modalType, style)"></div>
                <div class="text-[10px] text-gray-400 mt-1" x-text="getStyleDescription(modalType, style)"></div>
            </label>
        </template>
    </div>
</div>

<!-- Data Source -->
<div class="form-group border-t pt-4 mt-4">
    <div class="flex items-center gap-2 mb-2">
        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <label class="label mb-0">Content Source</label>
    </div>
    <p class="text-xs text-gray-500 mb-3 ml-7">Where should the items come from?</p>

    <div class="space-y-2">
        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300" :class="formData.source === 'featured' ? 'border-orange-500 bg-orange-50' : 'border-gray-200'">
            <input type="radio" x-model="formData.source" value="featured" class="hidden">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
            <div>
                <div class="font-medium text-sm text-gray-800">Featured Items</div>
                <div class="text-xs text-gray-500">Automatically show items marked as featured</div>
            </div>
        </label>

        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300" :class="formData.source === 'recent' ? 'border-orange-500 bg-orange-50' : 'border-gray-200'">
            <input type="radio" x-model="formData.source" value="recent" class="hidden">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="font-medium text-sm text-gray-800">Recently Added</div>
                <div class="text-xs text-gray-500">Show newest items first (auto-updating)</div>
            </div>
        </label>

        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300" :class="formData.source === 'custom' ? 'border-orange-500 bg-orange-50' : 'border-gray-200'">
            <input type="radio" x-model="formData.source" value="custom" class="hidden">
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <div class="font-medium text-sm text-gray-800">Custom Selection</div>
                <div class="text-xs text-gray-500">Hand-pick specific items to display</div>
            </div>
        </label>
    </div>
</div>

<!-- Custom Items Selection -->
<template x-if="formData.source === 'custom'">
    <div class="form-group border-t pt-4 mt-4">
        <div class="flex items-center justify-between mb-3">
            <label class="label flex items-center gap-2 mb-0">
                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Select Items
            </label>
            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-medium" x-text="formData.custom_items.length + ' selected'"></span>
        </div>

        <div class="border-2 border-dashed border-gray-300 rounded-xl p-1 bg-gray-50">
            <!-- Search -->
            <div class="p-2">
                <div class="relative">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           x-model="searchQuery"
                           @input.debounce.300ms="searchItems"
                           class="input pl-10 text-sm"
                           placeholder="🔍 Search and select items...">
                </div>
            </div>

            <!-- Results -->
            <div class="max-h-52 overflow-y-auto px-2 pb-2">
                <div x-show="searchResults.length === 0" class="p-4 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-400">No items found</p>
                    <p class="text-xs text-gray-400 mt-1">Try a different search term</p>
                </div>

                <div class="space-y-1">
                    <template x-for="item in searchResults" :key="item.id">
                        <label class="flex items-center gap-3 p-3 hover:bg-orange-50 rounded-lg cursor-pointer transition-colors border border-transparent" :class="formData.custom_items.includes(String(item.id)) || formData.custom_items.includes(Number(item.id)) ? 'bg-orange-50 border-orange-200' : 'bg-white border-gray-100'">
                            <div class="relative">
                                <input type="checkbox" :value="item.id" x-model="formData.custom_items" class="checkbox w-5 h-5">
                                <svg x-show="formData.custom_items.includes(String(item.id)) || formData.custom_items.includes(Number(item.id))" class="w-3 h-3 text-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span x-text="item.name" class="text-sm font-medium text-gray-700 block truncate"></span>
                                <span x-show="item.subtitle || item.category" x-text="item.subtitle || item.category" class="text-xs text-gray-400 block truncate"></span>
                            </div>
                            <svg x-show="formData.custom_items.includes(String(item.id)) || formData.custom_items.includes(Number(item.id))" class="w-5 h-5 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex items-center justify-between mt-3" x-show="formData.custom_items.length > 0">
            <span class="text-xs text-gray-500" x-text="formData.custom_items.length + ' item(s) will be displayed'"></span>
            <button type="button" @click="formData.custom_items = []" class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Clear all
            </button>
        </div>
    </div>
</template>

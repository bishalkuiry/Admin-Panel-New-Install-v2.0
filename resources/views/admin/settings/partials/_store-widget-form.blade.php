<!-- Store Widget Configuration -->

<!-- 🏪 Widget Purpose Header -->
<div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 mb-4">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div>
            <h4 class="font-semibold text-gray-800">Store Directory Widget</h4>
            <p class="text-xs text-emerald-600 mt-1">Showcase your partner stores or vendors. Helps users discover and shop from specific stores in your marketplace.</p>
        </div>
    </div>
</div>

@include('admin.settings.partials._common-widget-fields')

<!-- Grid Configuration -->
@include('admin.settings.partials._grid-config')

<!-- Horizontal Animation -->
@include('admin.settings.partials._horizontal-animation')

<!-- Background Configuration -->
@include('admin.settings.partials._background-config')

<!-- View All Button Toggle -->
<div class="form-group border-t pt-4 mt-4">
    <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-green-50 to-white border border-green-100 rounded-xl">
        <label class="toggle"><input type="checkbox" x-model="formData.show_view_all"><span class="toggle-slider"></span></label>
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-800">Show "View All" Button</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Adds a "View All" link that takes users to the store directory</p>
        </div>
    </div>
</div>

<!-- Style Selection -->
<div class="form-group border-t pt-4 mt-4">
    <div class="flex items-center gap-2 mb-2">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
        </svg>
        <label class="label mb-0">Choose Display Style</label>
    </div>
    <p class="text-xs text-gray-500 mb-3 ml-7">Select how stores will appear in the app</p>

    <div class="grid grid-cols-2 gap-3">
        <!-- Style 1: U-Shape Grid -->
        <label :class="formData.style === 'style_1' ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-200' : 'border-gray-200 hover:border-emerald-300'"
               class="border-2 rounded-xl p-3 cursor-pointer transition-all">
            <input type="radio" x-model="formData.style" value="style_1" class="hidden">
            <!-- Skeleton: 3-col U-shape cards -->
            <div class="flex gap-1 justify-center mb-2">
                <template x-for="i in [1,2,3]" :key="i">
                    <div class="flex flex-col items-center bg-white rounded-lg shadow-sm border border-gray-100 w-10 overflow-hidden">
                        <div class="w-6 h-6 rounded-full bg-gray-200 mt-2 mb-1"></div>
                        <div class="w-7 h-1.5 bg-gray-200 rounded mb-1"></div>
                        <div class="w-full h-4 bg-green-200 rounded-b mt-1"></div>
                    </div>
                </template>
            </div>
            <div class="text-center">
                <div class="text-xs font-semibold text-gray-800">🏪 U-Shape Grid</div>
                <div class="text-[10px] text-gray-500 mt-1">Logo + Explore button</div>
                <div class="text-[9px] text-gray-400">Best for: Many stores</div>
            </div>
        </label>

        <!-- Style 2: Cover Grid -->
        <label :class="formData.style === 'style_2' ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-200' : 'border-gray-200 hover:border-emerald-300'"
               class="border-2 rounded-xl p-3 cursor-pointer transition-all">
            <input type="radio" x-model="formData.style" value="style_2" class="hidden">
            <!-- Skeleton: 2-col image cards with name overlay -->
            <div class="flex gap-1.5 justify-center mb-2">
                <template x-for="i in [1,2]" :key="i">
                    <div class="w-14 h-16 rounded-xl bg-gray-300 relative overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-6 bg-gradient-to-b from-gray-600 to-transparent opacity-60"></div>
                        <div class="absolute top-1.5 left-1.5 w-8 h-1.5 bg-white rounded opacity-80"></div>
                        <div class="absolute bottom-1.5 right-1.5 w-5 h-3 bg-white rounded-md opacity-90 flex items-center justify-center">
                            <div class="w-3 h-1 bg-yellow-400 rounded"></div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="text-center">
                <div class="text-xs font-semibold text-gray-800">🖼️ Cover Grid</div>
                <div class="text-[10px] text-gray-500 mt-1">2-column image cards</div>
                <div class="text-[9px] text-gray-400">Best for: Visual stores</div>
            </div>
        </label>

        <!-- Style 3: Horizontal Scroll -->
        <label :class="formData.style === 'style_3' ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-200' : 'border-gray-200 hover:border-emerald-300'"
               class="border-2 rounded-xl p-3 cursor-pointer transition-all">
            <input type="radio" x-model="formData.style" value="style_3" class="hidden">
            <!-- Skeleton: horizontal scrolling cards -->
            <div class="flex gap-1.5 justify-center mb-2 overflow-hidden">
                <template x-for="i in [1,2]" :key="i">
                    <div class="w-16 rounded-xl overflow-hidden border border-gray-100 shadow-sm flex-shrink-0">
                        <div class="h-9 bg-gray-300 relative">
                            <div class="absolute bottom-0 left-1 w-8 h-3 bg-green-400 rounded-full translate-y-1/2"></div>
                        </div>
                        <div class="bg-white px-1.5 pt-2.5 pb-1.5">
                            <div class="w-10 h-1.5 bg-gray-700 rounded mb-1"></div>
                            <div class="w-7 h-1 bg-green-300 rounded"></div>
                        </div>
                    </div>
                </template>
                <div class="w-4 flex items-center text-gray-300 text-xs">›</div>
            </div>
            <div class="text-center">
                <div class="text-xs font-semibold text-gray-800">⏩ Horizontal Scroll</div>
                <div class="text-[10px] text-gray-500 mt-1">Swipeable cards</div>
                <div class="text-[9px] text-gray-400">Best for: Many stores, compact</div>
            </div>
        </label>

        <!-- Style 4: Feed Cards -->
        <label :class="formData.style === 'style_4' ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-200' : 'border-gray-200 hover:border-emerald-300'"
               class="border-2 rounded-xl p-3 cursor-pointer transition-all">
            <input type="radio" x-model="formData.style" value="style_4" class="hidden">
            <!-- Skeleton: full-width vertical feed card -->
            <div class="space-y-1 mb-2">
                <div class="w-full h-10 bg-gray-300 rounded-xl relative overflow-hidden">
                    <div class="absolute top-1.5 left-2 w-10 h-2 bg-gray-500 rounded opacity-60"></div>
                    <div class="absolute top-1.5 right-2 w-4 h-4 border-2 border-white rounded opacity-70"></div>
                </div>
                <div class="bg-white rounded-xl px-2 py-1.5 border border-gray-100">
                    <div class="flex justify-between items-center mb-1">
                        <div class="w-14 h-2 bg-gray-700 rounded"></div>
                        <div class="w-6 h-3 bg-green-500 rounded"></div>
                    </div>
                    <div class="w-10 h-1.5 bg-green-300 rounded"></div>
                </div>
            </div>
            <div class="text-center">
                <div class="text-xs font-semibold text-gray-800">📜 Feed Cards</div>
                <div class="text-[10px] text-gray-500 mt-1">Full-width vertical list</div>
                <div class="text-[9px] text-gray-400">Best for: Featured stores</div>
            </div>
        </label>
    </div>
</div>

<!-- Grid Columns (style_1 only) -->
<template x-if="formData.style === 'style_1'">
    <div class="form-group border-t pt-4 mt-4 bg-emerald-50/30 rounded-xl p-4">
        <label class="label flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 4a2 2 0 012-2h2a2 2 0 012 2v6a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Grid Columns
        </label>
        <div class="flex gap-3">
            <label class="flex items-center gap-2 px-4 py-2 border rounded-lg cursor-pointer" :class="formData.grid_columns === 2 ? 'border-emerald-500 bg-emerald-100' : 'border-gray-200'">
                <input type="radio" x-model.number="formData.grid_columns" :value="2" class="hidden">
                <span class="text-sm font-medium">2 Columns</span>
            </label>
            <label class="flex items-center gap-2 px-4 py-2 border rounded-lg cursor-pointer" :class="formData.grid_columns === 3 ? 'border-emerald-500 bg-emerald-100' : 'border-gray-200'">
                <input type="radio" x-model.number="formData.grid_columns" :value="3" class="hidden">
                <span class="text-sm font-medium">3 Columns</span>
            </label>
        </div>
        <p class="text-xs text-gray-500 mt-2">More columns = smaller store cards</p>
    </div>
</template>

<!-- Custom Store Selection -->
<div class="form-group border-t pt-4 mt-4">
    <div class="flex items-center justify-between mb-3">
        <label class="label flex items-center gap-2 mb-0">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Select Stores to Display
        </label>
        <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full font-medium" x-text="formData.custom_items.length + ' selected'"></span>
    </div>
    <p class="text-xs text-gray-500 mb-3">Choose which stores will appear in this widget</p>

    <div class="border-2 border-dashed border-gray-300 rounded-xl p-1 bg-gray-50">
        <!-- Search -->
        <div class="p-2">
            <div class="relative">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       x-model="searchQuery"
                       @input.debounce.300ms="searchItems()"
                       class="input pl-10 text-sm"
                       placeholder="🔍 Search stores by name...">
            </div>
        </div>

        <!-- Results -->
        <div class="max-h-52 overflow-y-auto px-2 pb-2">
            <div x-show="searchResults.length === 0" class="p-4 text-center">
                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                </svg>
                <p class="text-sm text-gray-400">No stores found</p>
                <p class="text-xs text-gray-400 mt-1">Try a different search term</p>
            </div>

            <div class="space-y-1">
                <template x-for="store in searchResults" :key="store.id">
                    <label class="flex items-center gap-3 p-3 hover:bg-emerald-50 rounded-lg cursor-pointer transition-colors border border-transparent"
                           :class="formData.custom_items.map(Number).includes(Number(store.id)) ? 'bg-emerald-50 border-emerald-200' : 'bg-white border-gray-100'">
                        <input type="checkbox"
                               :value="store.id"
                               :checked="formData.custom_items.map(Number).includes(Number(store.id))"
                               @change="toggleStoreSelection(store.id)"
                               class="checkbox w-5 h-5 flex-shrink-0">
                        <template x-if="store.logo">
                            <img :src="store.logo" class="w-10 h-10 rounded-full object-cover flex-shrink-0 border border-gray-200" alt="">
                        </template>
                        <template x-if="!store.logo">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                                </svg>
                            </div>
                        </template>
                        <div class="flex-1 min-w-0">
                            <span class="text-sm font-medium text-gray-700 block truncate" x-text="store.name"></span>
                            <span x-show="store.location || store.city" x-text="store.location || store.city" class="text-xs text-gray-400 block truncate"></span>
                        </div>
                        <span x-show="formData.custom_items.map(Number).includes(Number(store.id))"
                              class="text-xs bg-emerald-500 text-white px-2 py-1 rounded-full font-medium flex-shrink-0">✓ Selected</span>
                    </label>
                </template>
            </div>
        </div>

        <!-- Selected count -->
        <div class="px-3 py-2 bg-emerald-50 border-t border-emerald-100 text-xs text-gray-600 flex items-center justify-between rounded-b-lg"
             x-show="formData.custom_items.length > 0">
            <span class="font-medium"><span x-text="formData.custom_items.length"></span> store(s) will be displayed</span>
            <button type="button" @click="formData.custom_items = []" class="text-red-500 hover:text-red-700 text-xs flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Clear all
            </button>
        </div>
    </div>
</div>

<!-- Background Config -->
@include('admin.settings.partials._background-config')

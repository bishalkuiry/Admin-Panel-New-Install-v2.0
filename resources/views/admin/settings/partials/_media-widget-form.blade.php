<!-- Media Widget Form Partial -->

<!-- 🖼️ Widget Purpose Header -->
<div class="bg-rose-50 border border-rose-100 rounded-xl p-4 mb-4">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <h4 class="font-semibold text-gray-800">Media & Banners Widget</h4>
            <p class="text-xs text-rose-600 mt-1">Showcase promotional banners, advertisements, or visual content. Supports images, GIFs, and videos with clickable links.</p>
        </div>
    </div>
</div>

<!-- Title and Subtitle Section -->
<div class="bg-gradient-to-r from-orange-50 to-white border border-orange-100 rounded-xl p-4 mb-4">
    <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
        <h4 class="font-semibold text-gray-800">Widget Header</h4>
    </div>

    <div class="space-y-4">
        <div class="form-group">
            <label class="label flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Widget Title
            </label>
            <input type="text" x-model="formData.title" class="input" placeholder="e.g., 🎯 Featured Banners, 🎉 Special Offers">
        </div>

        <div class="form-group flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-100">
            <input type="checkbox" x-model="formData.show_title" class="checkbox w-5 h-5">
            <div>
                <span class="text-sm font-medium text-gray-700">Show Title</span>
                <p class="text-xs text-gray-400">Display the title above the media content</p>
            </div>
        </div>

        <div class="form-group">
            <label class="label flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                Subtitle
                <span class="text-xs text-gray-400 font-normal">(Optional)</span>
            </label>
            <input type="text" x-model="formData.subtitle" class="input" placeholder="e.g., Check out our latest promotional offers">
        </div>

        <div class="form-group flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
            <input type="checkbox" x-model="formData.show_subtitle" class="checkbox w-5 h-5">
            <div>
                <span class="text-sm font-medium text-gray-700">Show Subtitle</span>
                <p class="text-xs text-gray-400">Appears as smaller text under the title</p>
            </div>
        </div>

        <!-- Special: Show on Order Tracking -->
        <div class="form-group flex items-center gap-3 p-3 bg-rose-50 rounded-lg border border-rose-100">
            <input type="checkbox" x-model="formData.show_on_tracking" class="checkbox w-5 h-5 accent-rose-500">
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-gray-800">Show on Order Tracking</span>
                    <span class="text-xs bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full">Elite Feature</span>
                </div>
            </div>
        </div>
    </div>

<!-- 📱/💻 Device Target Visibility -->
<div class="form-group border-t pt-4 mt-4">
    <div class="flex items-center gap-2 mb-2">
        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        <label class="label mb-0 font-semibold text-gray-800">Device Target Visibility</label>
    </div>
    <p class="text-xs text-gray-500 mb-3 ml-7">Select which devices will display this banner on the website</p>

    <div class="grid grid-cols-3 gap-3">
        <!-- All Devices -->
        <label :class="(formData.show_on_mobile && formData.show_on_laptop) ? 'border-indigo-500 bg-indigo-50/70 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50'"
               class="border-2 rounded-xl p-3 cursor-pointer text-center transition-all flex flex-col items-center justify-center select-none">
            <input type="radio" name="device_target_media" value="all" 
                   :checked="formData.show_on_mobile && formData.show_on_laptop"
                   @change="formData.show_on_mobile = true; formData.show_on_laptop = true;" class="hidden">
            <div class="text-xl mb-1">💻 📱</div>
            <div class="text-xs font-bold text-gray-800">All Devices</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Mobile & Laptop</div>
        </label>

        <!-- Mobile Only -->
        <label :class="(formData.show_on_mobile && !formData.show_on_laptop) ? 'border-purple-500 bg-purple-50/70 ring-2 ring-purple-200' : 'border-gray-200 hover:border-purple-300 hover:bg-gray-50'"
               class="border-2 rounded-xl p-3 cursor-pointer text-center transition-all flex flex-col items-center justify-center select-none">
            <input type="radio" name="device_target_media" value="mobile" 
                   :checked="formData.show_on_mobile && !formData.show_on_laptop"
                   @change="formData.show_on_mobile = true; formData.show_on_laptop = false;" class="hidden">
            <div class="text-xl mb-1">📱</div>
            <div class="text-xs font-bold text-gray-800">Mobile Only</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Hide on Laptop</div>
        </label>

        <!-- Laptop / Desktop Only -->
        <label :class="(!formData.show_on_mobile && formData.show_on_laptop) ? 'border-blue-500 bg-blue-50/70 ring-2 ring-blue-200' : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'"
               class="border-2 rounded-xl p-3 cursor-pointer text-center transition-all flex flex-col items-center justify-center select-none">
            <input type="radio" name="device_target_media" value="laptop" 
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
        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
        </svg>
        <label class="label mb-0">Choose Display Style</label>
    </div>
    <p class="text-xs text-gray-500 mb-3 ml-7">How your media items will be displayed</p>

    <div class="grid grid-cols-3 gap-3">
        <label class="cursor-pointer">
            <input type="radio" x-model="formData.style" value="style_1" class="peer hidden">
            <div class="border-2 peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:ring-2 peer-checked:ring-rose-200 rounded-xl p-3 text-center hover:border-rose-300 transition-all">
                <div class="w-full h-12 bg-gradient-to-r from-gray-200 to-gray-300 rounded-lg mb-2 relative overflow-hidden">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-8 h-8 bg-white/50 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="text-sm font-semibold text-gray-800">🎬 Full Width</div>
                <div class="text-xs text-gray-500 mt-1">Auto-sliding banner</div>
                <div class="text-[9px] text-rose-500 mt-1">Best for: Hero banners</div>
            </div>
        </label>
        <label class="cursor-pointer">
            <input type="radio" x-model="formData.style" value="style_2" class="peer hidden">
            <div class="border-2 peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:ring-2 peer-checked:ring-rose-200 rounded-xl p-3 text-center hover:border-rose-300 transition-all">
                <div class="grid grid-cols-2 gap-1 w-full h-12 p-1 bg-gray-100 rounded-lg mb-2">
                    <div class="bg-gray-300 rounded"></div>
                    <div class="bg-gray-300 rounded"></div>
                </div>
                <div class="text-sm font-semibold text-gray-800">📐 Padded Grid</div>
                <div class="text-xs text-gray-500 mt-1">Square with padding</div>
                <div class="text-[9px] text-rose-500 mt-1">Best for: Product promos</div>
            </div>
        </label>
        <label class="cursor-pointer">
            <input type="radio" x-model="formData.style" value="style_3" class="peer hidden">
            <div class="border-2 peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:ring-2 peer-checked:ring-rose-200 rounded-xl p-3 text-center hover:border-rose-300 transition-all">
                <div class="grid grid-cols-2 gap-1 w-full h-12 p-1 bg-gray-100 rounded-lg mb-2">
                    <div class="bg-gray-300 rounded-full"></div>
                    <div class="bg-gray-300 rounded-full"></div>
                </div>
                <div class="text-sm font-semibold text-gray-800">🔘 Rounded Grid</div>
                <div class="text-xs text-gray-500 mt-1">Circular style items</div>
                <div class="text-[9px] text-rose-500 mt-1">Best for: Categories</div>
            </div>
        </label>
    </div>

    <!-- Style Info -->
    <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
        <div class="flex items-start gap-2">
            <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-blue-700">
                <span x-show="formData.style === 'style_1'">📺 <strong>Full Width Slider:</strong> Banners stretch full width. Add multiple images to create an auto-sliding carousel.</span>
                <span x-show="formData.style === 'style_2'">📐 <strong>Padded Grid:</strong> Items have equal padding around them. Great for consistent product/banner grids.</span>
                <span x-show="formData.style === 'style_3'">🔘 <strong>Rounded Grid:</strong> Items have rounded corners. Modern look for category icons or circular promos.</span>
            </p>
        </div>
    </div>
</div>

<div class="space-y-4 border-t pt-4 mt-4">
    <!-- Background Configuration -->
    <div class="form-group">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" x-model="formData.enable_background" class="rounded">
            <span class="label mb-0">Enable Background</span>
        </label>
    </div>

    <template x-if="formData.enable_background">
        <div class="space-y-3 p-4 bg-gray-50 rounded-lg">
            <div class="form-group">
                <label class="label">Background Type</label>
                <select x-model="formData.background_type" class="input">
                    <option value="color">Solid Color</option>
                    <option value="image">Image</option>
                    <option value="gif">GIF</option>
                    <option value="video">Video</option>
                </select>
            </div>

            <!-- Color Picker -->
            <template x-if="formData.background_type === 'color'">
                <div class="form-group">
                    <label class="label">Background Color</label>
                    <input type="color" x-model="formData.background_color" class="input h-10">
                </div>
            </template>

            <!-- Background Media Upload -->
            <template x-if="formData.background_type !== 'color'">
                <div class="form-group">
                    <label class="label">Background Media</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-3 text-center">
                        <template x-if="!backgroundMediaPreview">
                            <div>
                                <input type="file" @change="handleBackgroundMediaSelect($event)" accept="image/*,video/*" class="hidden" x-ref="bgMediaInput">
                                <button type="button" @click="$refs.bgMediaInput.click()" class="btn-secondary text-sm">Upload Background</button>
                            </div>
                        </template>
                        <template x-if="backgroundMediaPreview">
                            <div class="relative">
                                <template x-if="formData.background_type === 'video'">
                                    <video :src="backgroundMediaPreview" class="max-h-24 mx-auto rounded" autoplay muted loop playsinline></video>
                                </template>
                                <template x-if="formData.background_type !== 'video'">
                                    <img :src="backgroundMediaPreview" class="max-h-24 mx-auto rounded" alt="Background">
                                </template>
                                <button type="button" @click="backgroundMediaPreview = null; backgroundMediaFile = null" class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <!-- Grid Configuration (ONLY for style_2 and style_3) -->
    <template x-if="['style_2', 'style_3'].includes(formData.style)">
        <div class="space-y-4 p-4 bg-rose-50/50 rounded-xl border border-rose-100">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <h4 class="font-semibold text-sm text-gray-800">Grid Configuration</h4>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group">
                    <label class="label text-xs flex items-center gap-1">
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 4a2 2 0 012-2h2a2 2 0 012 2v6a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Items per Row
                    </label>
                    <select x-model.number="formData.grid_columns" @change="updateMediaItemsGrid()" class="input text-sm">
                        <option value="1">1 per Row (Large)</option>
                        <option value="2">2 per Row</option>
                        <option value="3">3 per Row</option>
                        <option value="4">4 per Row (Small)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="label text-xs flex items-center gap-1">
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        How Many Rows
                    </label>
                    <select x-model.number="formData.grid_rows" @change="updateMediaItemsGrid()" class="input text-sm">
                        <option value="1">1 Row</option>
                        <option value="2">2 Rows</option>
                        <option value="3">3 Rows</option>
                        <option value="4">4 Rows</option>
                        <option value="5">5 Rows</option>
                    </select>
                </div>
            </div>

            <!-- Visual Preview -->
            <div class="bg-white rounded-lg p-3 border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Total media slots:</span>
                    <span class="text-lg font-bold text-rose-600" x-text="(formData.grid_columns || 1) * (formData.grid_rows || 1)"></span>
                </div>
                <div class="grid gap-1" :style="'grid-template-columns: repeat(' + (formData.grid_columns || 1) + ', 1fr)'">
                    <template x-for="i in (formData.grid_columns || 1) * (formData.grid_rows || 1)" :key="i">
                        <div class="aspect-square bg-gray-100 rounded border-2 border-dashed border-gray-300 flex items-center justify-center">
                            <span class="text-xs text-gray-400" x-text="i"></span>
                        </div>
                    </template>
                </div>
                <p class="text-xs text-orange-600 mt-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <strong>All slots must be filled with media</strong>
                </p>
            </div>
        </div>
    </template>

    <!-- Card Height Slider -->
    <div class="form-group bg-gray-50 rounded-xl p-4 border border-gray-100">
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-8l-7 7-7-7"/>
            </svg>
            <label class="label mb-0">Card Height</label>
        </div>
        <div class="flex items-center gap-4">
            <input type="range" x-model.number="formData.media_height" min="50" max="500" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-rose-500">
            <div class="bg-white border border-gray-200 rounded-lg px-3 py-1.5 min-w-[60px] text-center">
                <span class="text-sm font-semibold text-gray-800" x-text="formData.media_height + 'px'"></span>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Controls how tall each media card appears. Width automatically fills the container.
        </p>
    </div>

    <!-- Media Items Section -->
    <div class="space-y-3 border-t pt-4 mt-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h4 class="font-semibold text-sm text-gray-800">Media Items</h4>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-rose-100 text-rose-700 px-2 py-1 rounded-full font-medium" x-text="(formData.media_items?.filter(i => i).length || 0) + ' uploaded'"></span>
                <!-- Add More Button (ONLY for style_1) -->
                <template x-if="formData.style === 'style_1'">
                    <button type="button" @click="addMediaSlot()" class="text-xs bg-rose-500 hover:bg-rose-600 text-white px-3 py-1.5 rounded-lg flex items-center gap-1 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Media
                    </button>
                </template>
            </div>
        </div>

        <!-- Info Message -->
        <template x-if="formData.style === 'style_1'">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-blue-700">
                        <strong>Full Width Slider:</strong> You can add unlimited banners. When you add 2+ items, the app will automatically slide between them every 3 seconds.
                    </p>
                </div>
            </div>
        </template>

        <!-- Media Items List for Style 1 (Unlimited) -->
        <template x-if="formData.style === 'style_1'">
            <div class="space-y-3">
                <!-- Initialize with at least 1 slot if empty -->
                <template x-if="!formData.media_items || formData.media_items.length === 0">
                    <div x-init="formData.media_items = [null]"></div>
                </template>
                
                <template x-for="(item, index) in formData.media_items" :key="'style1-' + index">
                    <div class="border border-gray-200 rounded-lg p-3 bg-white">
                        <div class="flex items-start gap-3">
                            <!-- Media Preview/Upload -->
                            <div class="w-20 h-20 flex-shrink-0">
                                <template x-if="formData.media_items[index]">
                                    <div class="relative w-full h-full">
                                        <template x-if="formData.media_items[index].type === 'video'">
                                            <video :src="formData.media_items[index].url" class="w-full h-full object-cover rounded" muted loop playsinline></video>
                                        </template>
                                        <template x-if="formData.media_items[index].type !== 'video'">
                                            <img :src="formData.media_items[index].url" class="w-full h-full object-cover rounded" alt="">
                                        </template>
                                        <button type="button" @click="removeMediaSlot(index)" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </template>
                                <template x-if="!formData.media_items[index]">
                                    <label class="w-full h-full border-2 border-dashed border-gray-300 rounded flex items-center justify-center cursor-pointer hover:border-orange-400">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <input type="file" @change="handleMediaItemUpload($event, index)" accept="image/*,video/*" class="hidden">
                                    </label>
                                </template>
                            </div>

                            <!-- Link Configuration -->
                            <div class="flex-1 space-y-2">
                                <div class="text-xs font-medium text-gray-500" x-text="'Slot ' + (index + 1)"></div>
                                
                                <template x-if="formData.media_items[index]">
                                    <div class="space-y-2">
                                        <select x-model="formData.media_items[index].link_type" @change="handleMediaItemLinkChange(index)" class="input text-sm">
                                            <option value="none">No Link</option>
                                            <option value="product">Product</option>
                                            <option value="category">Category</option>
                                            <option value="brand">Brand</option>
                                            <option value="store">Store</option>
                                            <option value="url">Custom URL</option>
                                        </select>

                                        <div x-show="['product', 'category', 'brand', 'store'].includes(formData.media_items[index].link_type)">
                                            <input
                                                type="text"
                                                :placeholder="'Search ' + formData.media_items[index].link_type + '...'"
                                                x-model="mediaItemSearch[index]"
                                                @input="searchMediaItemLink(index, $event.target.value)"
                                                @focus="if (!mediaItemLinkOptions[index] || mediaItemLinkOptions[index].length === 0) searchMediaItemLink(index, mediaItemSearch[index] || '')"
                                                class="input text-sm"
                                            >
                                            <div x-show="(mediaItemLinkOptions[index] || []).length > 0" class="border border-gray-200 rounded mt-1 max-h-40 overflow-y-auto bg-white shadow-sm">
                                                <template x-for="opt in (mediaItemLinkOptions[index] || [])" :key="opt.id">
                                                    <div
                                                        @click="formData.media_items[index].link_id = String(opt.id); mediaItemSearch[index] = opt.name"
                                                        class="px-3 py-2 text-sm cursor-pointer hover:bg-orange-50 flex items-center justify-between"
                                                        :class="formData.media_items[index].link_id === String(opt.id) ? 'bg-orange-50 text-orange-700 font-medium' : 'text-gray-700'"
                                                    >
                                                        <span x-text="opt.name"></span>
                                                        <svg x-show="formData.media_items[index].link_id === String(opt.id)" class="w-4 h-4 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    </div>
                                                </template>
                                            </div>
                                            <div x-show="formData.media_items[index].link_id" class="text-xs text-green-600 mt-1" x-text="'Selected: ' + (mediaItemSearch[index] || formData.media_items[index].link_id)"></div>
                                        </div>

                                        <div x-show="formData.media_items[index].link_type === 'url'">
                                            <input type="url" x-model="formData.media_items[index].link_url" class="input text-sm" placeholder="https://...">
                                        </div>
                                    </div>
                                </template>
                                
                                <template x-if="!formData.media_items[index]">
                                    <div class="text-xs text-gray-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Upload image/video first, then add a link
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Media Items List for Style 2 & 3 (Fixed Grid) -->
        <template x-if="['style_2', 'style_3'].includes(formData.style)">
            <div class="space-y-3">
                <template x-for="(slot, index) in Array.from({length: (formData.grid_columns || 1) * (formData.grid_rows || 1)})" :key="'style23-' + index">
                    <div class="border border-gray-200 rounded-lg p-3 bg-white">
                        <div class="flex items-start gap-3">
                            <!-- Media Preview/Upload -->
                            <div class="w-20 h-20 flex-shrink-0">
                                <template x-if="formData.media_items && formData.media_items[index]">
                                    <div class="relative w-full h-full">
                                        <template x-if="formData.media_items[index].type === 'video'">
                                            <video :src="formData.media_items[index].url" class="w-full h-full object-cover rounded" muted loop playsinline></video>
                                        </template>
                                        <template x-if="formData.media_items[index].type !== 'video'">
                                            <img :src="formData.media_items[index].url" class="w-full h-full object-cover rounded" alt="">
                                        </template>
                                        <button type="button" @click="removeMediaItem(index)" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </template>
                                <template x-if="!formData.media_items || !formData.media_items[index]">
                                    <label class="w-full h-full border-2 border-dashed border-gray-300 rounded flex items-center justify-center cursor-pointer hover:border-orange-400">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <input type="file" @change="handleMediaItemUpload($event, index)" accept="image/*,video/*" class="hidden">
                                    </label>
                                </template>
                            </div>

                            <!-- Link Configuration -->
                            <div class="flex-1 space-y-2">
                                <div class="text-xs font-medium text-gray-500" x-text="'Slot ' + (index + 1)"></div>
                                
                                <template x-if="formData.media_items && formData.media_items[index]">
                                    <div class="space-y-2">
                                        <select x-model="formData.media_items[index].link_type" @change="handleMediaItemLinkChange(index)" class="input text-sm">
                                            <option value="none">No Link</option>
                                            <option value="product">Product</option>
                                            <option value="category">Category</option>
                                            <option value="brand">Brand</option>
                                            <option value="store">Store</option>
                                            <option value="url">Custom URL</option>
                                        </select>

                                        <div x-show="['product', 'category', 'brand', 'store'].includes(formData.media_items[index].link_type)">
                                            <input
                                                type="text"
                                                :placeholder="'Search ' + formData.media_items[index].link_type + '...'"
                                                x-model="mediaItemSearch[index]"
                                                @input="searchMediaItemLink(index, $event.target.value)"
                                                @focus="if (!mediaItemLinkOptions[index] || mediaItemLinkOptions[index].length === 0) searchMediaItemLink(index, mediaItemSearch[index] || '')"
                                                class="input text-sm"
                                            >
                                            <div x-show="(mediaItemLinkOptions[index] || []).length > 0" class="border border-gray-200 rounded mt-1 max-h-40 overflow-y-auto bg-white shadow-sm">
                                                <template x-for="opt in (mediaItemLinkOptions[index] || [])" :key="opt.id">
                                                    <div
                                                        @click="formData.media_items[index].link_id = String(opt.id); mediaItemSearch[index] = opt.name"
                                                        class="px-3 py-2 text-sm cursor-pointer hover:bg-orange-50 flex items-center justify-between"
                                                        :class="formData.media_items[index].link_id === String(opt.id) ? 'bg-orange-50 text-orange-700 font-medium' : 'text-gray-700'"
                                                    >
                                                        <span x-text="opt.name"></span>
                                                        <svg x-show="formData.media_items[index].link_id === String(opt.id)" class="w-4 h-4 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    </div>
                                                </template>
                                            </div>
                                            <div x-show="formData.media_items[index].link_id" class="text-xs text-green-600 mt-1" x-text="'Selected: ' + (mediaItemSearch[index] || formData.media_items[index].link_id)"></div>
                                        </div>

                                        <div x-show="formData.media_items[index].link_type === 'url'">
                                            <input type="url" x-model="formData.media_items[index].link_url" class="input text-sm" placeholder="https://...">
                                        </div>
                                    </div>
                                </template>
                                
                                <template x-if="!formData.media_items || !formData.media_items[index]">
                                    <div class="text-xs text-gray-400">Upload media to configure link</div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>

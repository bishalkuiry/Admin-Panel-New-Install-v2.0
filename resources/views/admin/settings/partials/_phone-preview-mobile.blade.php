<!-- Mobile Phone Preview -->
<div class="w-[200px] flex-shrink-0 bg-gray-100 p-2 flex flex-col items-center border-l">
    <span class="text-xs font-medium text-gray-400 uppercase mb-2">Preview</span>
    <div class="bg-gray-900 rounded-[20px] p-1 shadow-xl">
        <div class="bg-white rounded-[16px] overflow-hidden" style="width: 180px; height: 360px;">
            <!-- Phone Notch -->
            <div class="bg-gray-900 h-5 flex items-center justify-center">
                <div class="w-16 h-3 bg-black rounded-full"></div>
            </div>
            <!-- Header Preview -->
            <div class="bg-gradient-to-r from-orange-400 to-orange-500 p-2">
                <div class="bg-white/20 rounded px-2 py-1 text-white text-[8px]">📍 Location</div>
                <div class="bg-white rounded px-2 py-1 mt-1 text-gray-400 text-[8px]">🔍 Search...</div>
            </div>
            <!-- Content Preview -->
            <div class="p-1.5 space-y-1.5 overflow-y-auto" style="height: 260px;">
                <template x-for="content in contents.filter(c => c.is_active)" :key="'preview-mobile-' + content.id">
                    <div class="rounded p-1.5 relative overflow-hidden"
                         :style="['product', 'category', 'brand'].includes(content.type) && content.enable_background && content.background_type === 'color' ? 
                                 'background-color: ' + (content.background_color || '#f9fafb') : 
                                 (!['product', 'category', 'brand'].includes(content.type) || !content.enable_background ? 'background-color: #f9fafb' : '')">
                        <!-- Background Media Layer -->
                        <template x-if="['product', 'category', 'brand'].includes(content.type) && content.enable_background && content.background_type !== 'color' && content.background_media_url">
                            <div class="absolute inset-0 z-0">
                                <template x-if="content.background_type === 'video'">
                                    <video :src="content.background_media_url" class="w-full h-full object-cover" autoplay muted loop playsinline></video>
                                </template>
                                <template x-if="content.background_type !== 'video'">
                                    <img :src="content.background_media_url" class="w-full h-full object-cover" alt="">
                                </template>
                            </div>
                        </template>
                        <div x-show="content.show_title && content.title" class="text-[8px] font-semibold mb-0.5 truncate relative z-10" x-text="content.title"></div>
                        <div x-show="content.show_subtitle && content.subtitle" class="text-[7px] text-gray-600 mb-0.5 truncate relative z-10" x-text="content.subtitle"></div>
                        
                        <!-- Product Preview -->
                        <template x-if="content.type === 'product'">
                            <div class="relative z-10" :class="content.style === 'style_1' ? 'grid gap-0.5' : (content.style === 'style_2' ? 'flex gap-0.5 overflow-x-auto' : 'space-y-0.5')"
                                 :style="content.style === 'style_1' && content.grid_columns ? 'grid-template-columns: repeat(' + content.grid_columns + ', minmax(0, 1fr))' : ''">
                                <template x-for="i in (content.style === 'style_3' ? 1 : (content.style === 'style_1' && content.grid_columns && content.grid_rows ? Math.min(parseInt(content.grid_columns) * parseInt(content.grid_rows), 6) : 4))" :key="i">
                                    <div :class="content.style === 'style_2' ? 'flex-shrink-0 w-10' : ''" class="bg-white rounded p-0.5 shadow-sm">
                                        <div class="bg-gray-200 rounded aspect-square"></div>
                                        <div class="h-1 bg-gray-200 rounded w-3/4 mt-0.5"></div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                        <!-- Category Preview -->
                        <template x-if="content.type === 'category'">
                            <!-- Style 4: Checkmark Pattern -->
                            <template x-if="content.style === 'style_4'">
                                <div class="relative z-10 space-y-0.5">
                                    <!-- First row: 3 items -->
                                    <div class="grid grid-cols-3 gap-0.5">
                                        <template x-for="i in 3" :key="'r1-' + i">
                                            <div class="text-center">
                                                <div class="w-full aspect-square rounded bg-gray-200"></div>
                                                <div class="h-0.5 bg-gray-200 rounded w-3/4 mx-auto mt-0.5"></div>
                                            </div>
                                        </template>
                                    </div>
                                    <!-- Second row: 4 items (if rows > 1) -->
                                    <template x-if="content.grid_rows && content.grid_rows > 1">
                                        <div class="grid grid-cols-4 gap-0.5">
                                            <template x-for="i in 4" :key="'r2-' + i">
                                                <div class="text-center">
                                                    <div class="w-full aspect-square rounded bg-gray-200"></div>
                                                    <div class="h-0.5 bg-gray-200 rounded w-3/4 mx-auto mt-0.5"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            
                            <!-- Other styles -->
                            <template x-if="content.style !== 'style_4'">
                                <div class="relative z-10" :class="content.style === 'style_1' ? 'flex gap-1 overflow-x-auto pb-0.5' : (content.style === 'style_2' ? 'grid gap-0.5' : 'space-y-0.5')"
                                     :style="content.style === 'style_2' && content.grid_columns ? 'grid-template-columns: repeat(' + content.grid_columns + ', minmax(0, 1fr))' : ''">
                                    <template x-for="i in (content.style === 'style_3' ? 2 : (content.style === 'style_2' && content.grid_columns && content.grid_rows ? Math.min(parseInt(content.grid_columns) * parseInt(content.grid_rows), 6) : 4))" :key="i">
                                        <div :class="content.style === 'style_1' ? 'flex flex-col items-center flex-shrink-0' : (content.style === 'style_2' ? 'text-center' : '')">
                                            <div :class="content.style === 'style_1' ? 'w-5 h-5 rounded-full bg-gray-200' : (content.style === 'style_3' ? 'h-5 w-full rounded bg-gray-200' : 'w-full aspect-square rounded bg-gray-200')"></div>
                                            <div x-show="content.style === 'style_1'" class="h-0.5 bg-gray-200 rounded w-4 mt-0.5"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </template>
                        
                        <!-- Brand Preview -->
                        <template x-if="content.type === 'brand'">
                            <div class="relative z-10" :class="content.style === 'style_1' ? 'flex gap-1 overflow-x-auto pb-0.5' : (content.style === 'style_2' ? 'grid grid-cols-3 gap-0.5' : 'space-y-0.5')">
                                <template x-for="i in (content.style === 'style_3' ? 2 : 4)" :key="i">
                                    <div :class="content.style === 'style_1' ? 'flex flex-col items-center flex-shrink-0' : (content.style === 'style_2' ? 'text-center' : '')">
                                        <div :class="content.style === 'style_1' ? 'w-5 h-5 rounded-full bg-gray-200' : (content.style === 'style_3' ? 'h-5 w-full rounded bg-gray-200' : 'w-full aspect-square rounded bg-gray-200')"></div>
                                        <div x-show="content.style === 'style_1'" class="h-0.5 bg-gray-200 rounded w-4 mt-0.5"></div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                        <!-- Media Preview -->
                        <template x-if="content.type === 'media'">
                            <div class="rounded overflow-hidden relative z-10">
                                <!-- Show media items if available (new multi-grid format) -->
                                <template x-if="content.media_items && content.media_items.length > 0">
                                    <div>
                                        <!-- Style 1: Slider preview (show first item) -->
                                        <template x-if="content.style === 'style_1'">
                                            <div :style="'height: ' + Math.min((content.media_height || 200) / 5, 50) + 'px'">
                                                <template x-if="content.media_items[0].type === 'video'">
                                                    <video :src="content.media_items[0].url" class="w-full h-full object-cover" muted loop playsinline></video>
                                                </template>
                                                <template x-if="content.media_items[0].type !== 'video'">
                                                    <img :src="content.media_items[0].url" class="w-full h-full object-cover" alt="">
                                                </template>
                                            </div>
                                        </template>
                                        <!-- Style 2 & 3: Custom Grid (matches Flutter app exactly) -->
                                        <template x-if="['style_2', 'style_3'].includes(content.style)">
                                            <div class="space-y-0.5">
                                                <template x-for="rowIdx in parseInt(content.grid_rows || 1)" :key="'row-m-' + rowIdx">
                                                    <div class="flex gap-0.5">
                                                        <template x-for="colIdx in parseInt(content.grid_columns || 1)" :key="'col-m-' + colIdx">
                                                            <div x-data="{ 
                                                                itemIndex: (rowIdx - 1) * parseInt(content.grid_columns || 1) + (colIdx - 1),
                                                                item: content.media_items[(rowIdx - 1) * parseInt(content.grid_columns || 1) + (colIdx - 1)]
                                                            }" x-show="itemIndex < content.media_items.length" class="flex-1">
                                                                <div :style="'height: ' + Math.min((content.media_height || 200) / 5, 50) + 'px'" class="w-full">
                                                                    <template x-if="item && item.type === 'video'">
                                                                        <video :src="item.url" class="w-full h-full object-cover" muted loop playsinline></video>
                                                                    </template>
                                                                    <template x-if="item && item.type !== 'video'">
                                                                        <img :src="item.url" class="w-full h-full object-cover" alt="">
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <!-- Fallback to legacy single media -->
                                <template x-if="!content.media_items || content.media_items.length === 0">
                                    <div :style="'height: ' + Math.min((content.media_height || 200) / 5, 50) + 'px'">
                                        <template x-if="content.media_url && isVideo(content)">
                                            <video :src="content.media_url" class="w-full h-full object-cover" autoplay muted loop playsinline></video>
                                        </template>
                                        <template x-if="content.media_url && !isVideo(content)">
                                            <img :src="content.media_url" class="w-full h-full object-cover" alt="">
                                        </template>
                                        <template x-if="!content.media_url">
                                            <div class="bg-gray-300 w-full h-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="contents.filter(c => c.is_active).length === 0">
                    <div class="text-center text-gray-400 text-[8px] py-6">No active widgets</div>
                </template>
            </div>
        </div>
    </div>
</div>

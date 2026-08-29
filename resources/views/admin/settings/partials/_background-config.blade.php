<!-- Background Configuration (for product/category/brand widgets) -->
<div class="space-y-4 border-t pt-4 mt-4">
    <div class="flex items-center gap-2 mb-3">
        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
        </svg>
        <label class="label mb-0">Background Styling</label>
    </div>
    <p class="text-xs text-gray-500 mb-3 ml-7">Customize the background behind your widget items</p>

    <div class="form-group bg-gray-50 rounded-xl p-3 border border-gray-100">
        <label class="flex items-center gap-3 cursor-pointer">
            <label class="toggle"><input type="checkbox" x-model="formData.enable_background"><span class="toggle-slider"></span></label>
            <div>
                <span class="text-sm font-medium text-gray-800">Enable Custom Background</span>
                <p class="text-xs text-gray-500">Add a colorful or media background to make your widget stand out</p>
            </div>
        </label>
    </div>

    <template x-if="formData.enable_background">
        <div class="space-y-4 pl-4 border-l-2 border-purple-200 bg-purple-50/30 rounded-r-xl p-4">
            <!-- Background Type Selection -->
            <div class="form-group">
                <label class="label flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Background Type
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer transition-all" :class="formData.background_type === 'color' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                        <input type="radio" x-model="formData.background_type" value="color" class="hidden">
                        <div class="w-4 h-4 rounded-full bg-gradient-to-br from-red-400 via-green-400 to-blue-400"></div>
                        <span class="text-sm">Solid Color</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer transition-all" :class="formData.background_type === 'image' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                        <input type="radio" x-model="formData.background_type" value="image" class="hidden">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm">Image</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer transition-all" :class="formData.background_type === 'gif' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                        <input type="radio" x-model="formData.background_type" value="gif" class="hidden">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm">Animated GIF</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer transition-all" :class="formData.background_type === 'video' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                        <input type="radio" x-model="formData.background_type" value="video" class="hidden">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm">Video</span>
                    </label>
                </div>
            </div>

            <!-- Color Picker -->
            <template x-if="formData.background_type === 'color'">
                <div class="form-group">
                    <label class="label flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                        Pick Background Color
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color" x-model="formData.background_color" class="input h-12 w-20">
                        <div class="flex-1">
                            <input type="text" x-model="formData.background_color" class="input text-sm font-mono" placeholder="#FF5733">
                            <p class="text-xs text-gray-500 mt-1">Click color box or enter hex code</p>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Media Upload -->
            <template x-if="['image', 'gif', 'video'].includes(formData.background_type)">
                <div class="form-group">
                    <label class="label flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Upload Background Media
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-purple-400 transition-colors bg-white">
                        <input type="file" @change="handleBackgroundMediaSelect($event)" accept="image/*,video/*" class="hidden" x-ref="backgroundMediaInput">

                        <template x-if="!backgroundMediaPreview">
                            <button type="button" @click="$refs.backgroundMediaInput.click()" class="flex flex-col items-center gap-2 w-full">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Click to upload</span>
                                <span class="text-xs text-gray-500" x-text="formData.background_type === 'video' ? 'MP4, MOV up to 10MB' : 'JPG, PNG, GIF up to 5MB'"></span>
                            </button>
                        </template>

                        <template x-if="backgroundMediaPreview">
                            <div class="relative">
                                <template x-if="formData.background_type === 'video'">
                                    <video :src="backgroundMediaPreview" class="max-h-40 rounded-lg mx-auto" controls muted loop></video>
                                </template>
                                <template x-if="formData.background_type !== 'video'">
                                    <img :src="backgroundMediaPreview" class="max-h-40 rounded-lg mx-auto" alt="Background Preview">
                                </template>
                                <button type="button" @click="backgroundMediaPreview = null; backgroundMediaFile = null" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600 shadow-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <button type="button" @click="$refs.backgroundMediaInput.click()" class="mt-2 text-xs text-purple-600 hover:text-purple-800 font-medium">
                                    Change File
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>

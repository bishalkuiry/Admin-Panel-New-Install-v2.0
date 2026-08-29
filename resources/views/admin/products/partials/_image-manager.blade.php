<div x-data="imageManager({{ isset($product) ? json_encode($product->images->map(fn($img) => ['id' => $img->id, 'url' => storage_url($img->image), 'is_primary' => $img->is_primary])) : '[]' }}, {{ isset($product) ? $product->id : 'null' }})" x-init="init()" x-on:add-ai-images.window="addImagesFromGallery($event.detail)">
    <!-- Current Images -->
    <div x-show="images.length > 0" class="card mb-6">
        <div class="card-header flex items-center justify-between">
            <div>
                <h3 class="card-title">Product Images</h3>
                <p class="text-sm text-gray-500 mt-1"><span x-text="images.length"></span> image(s) • Drag to reorder</p>
            </div>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" id="sortable-images">
                <template x-for="(img, index) in images" :key="img.id ? 'img-' + img.id : 'img-idx-' + index + '-' + img.url">
                    <div class="relative group aspect-square rounded-lg overflow-hidden bg-gray-100 border-2 cursor-move"
                         :class="img.is_primary ? 'border-primary-500' : 'border-gray-200'"
                         :data-id="img.id || index">
                        <img :src="img.url" class="w-full h-full object-cover">
                        
                        <!-- Primary Badge -->
                        <div x-show="img.is_primary" class="absolute top-2 left-2">
                            <span class="badge badge-primary text-xs">Primary</span>
                        </div>
                        
                        <!-- Drag Handle -->
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="p-1.5 bg-white rounded shadow-lg cursor-move">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Actions Overlay -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                            <button type="button" @click="setPrimary(index)" 
                                    x-show="!img.is_primary"
                                    class="p-2 bg-white rounded-lg text-primary-600 hover:bg-primary-50 transition-colors"
                                    title="Set as primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </button>
                            <button type="button" @click="deleteImage(index)" 
                                    class="p-2 bg-white rounded-lg text-red-600 hover:bg-red-50 transition-colors"
                                    title="Remove image">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Upload Options -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Images</h3>
            <p class="text-sm text-gray-500 mt-1">Upload new images or select from media gallery</p>
        </div>
        <div class="card-body space-y-4">
            <!-- Upload Zone with Drag & Drop -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-primary-400 transition-colors"
                 @drop.prevent="handleDrop($event)"
                 @dragover.prevent="$el.classList.add('border-primary-500', 'bg-primary-50')"
                 @dragleave.prevent="$el.classList.remove('border-primary-500', 'bg-primary-50')">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="text-sm font-medium text-gray-700 mb-2">Drag & drop images here or click to browse</p>
                <p class="text-xs text-gray-500 mb-4">PNG, JPG up to 2MB each</p>
                <label class="btn-secondary inline-flex cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Browse Files
                    <input type="file" multiple accept="image/*" class="hidden" @change="handleFileSelect($event)">
                </label>
            </div>

            <!-- Media Gallery Button -->
            <div class="flex items-center justify-center">
                <button type="button" @click="openMediaGallery()" class="btn-outline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Select from Media Gallery
                </button>
            </div>

            <!-- Preview New Uploads -->
            <div x-show="newPreviews.length > 0" class="mt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">New Images to Upload</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <template x-for="(preview, index) in newPreviews" :key="'preview-' + index">
                        <div class="relative group aspect-square rounded-lg overflow-hidden bg-gray-100 border-2 border-green-200">
                            <img :src="preview.url" class="w-full h-full object-cover">
                            <div class="absolute top-2 right-2">
                                <button type="button" @click="removeNewPreview(index)" 
                                        class="p-1.5 bg-white rounded-full text-red-600 hover:bg-red-50 shadow-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="absolute bottom-2 left-2 right-2">
                                <p class="text-xs text-white bg-black bg-opacity-50 px-2 py-1 rounded truncate" x-text="preview.name"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden inputs for form submission -->
    <template x-for="(file, index) in newFiles" :key="'file-' + index">
        <input type="file" :name="'images[]'" class="hidden" :id="'hidden-file-' + index">
    </template>
    
    <!-- Hidden inputs for gallery/AI images (already on server) -->
    <template x-for="(img, index) in images" :key="'img-path-input-' + index + (img.id || '')">
        <template x-if="!img.id && img.url">
            <input type="hidden" name="gallery_images[]" :value="img.url">
        </template>
    </template>
    
    <!-- Primary image selection for new images -->
    <input type="hidden" name="primary_image" :value="images.find(img => img.is_primary && !img.id)?.url || ''">
</div>

<!-- Media Gallery Modal (Single instance for all image managers) -->
<div x-data="mediaGalleryModal()" 
     @open-media-gallery.window="showGallery = true"
     @close-media-gallery.window="showGallery = false"
     x-init="init()">
    <div x-show="showGallery" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showGallery = false"></div>
            
            <div class="inline-block w-full max-w-6xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Media Gallery</h3>
                    <button @click="showGallery = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="px-6 py-4">
                    <iframe src="{{ route('admin.media.index') }}?select=true" 
                            class="w-full border-0"
                            style="height: 70vh; min-height: 500px;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function mediaGalleryModal() {
    return {
        showGallery: false,
        
        init() {
            // This modal is shared, no need for message handling here
        }
    };
}
</script>

<script>
function imageManager(initialImages = [], productId = null) {
    return {
        images: initialImages,
        newPreviews: [],
        newFiles: [],
        productId: productId,
        
        init() {
            // Auto primary logic: 1 image -> auto primary, >1 images -> first image primary by default
            if (this.images.length === 1) {
                this.images[0].is_primary = true;
            } else if (this.images.length > 1 && !this.images.some(img => img.is_primary)) {
                this.images[0].is_primary = true;
            }

            // Initialize sortable if available
            if (typeof Sortable !== 'undefined') {
                const el = document.getElementById('sortable-images');
                if (el) {
                    Sortable.create(el, {
                        animation: 150,
                        handle: '.cursor-move',
                        onEnd: (evt) => {
                            const item = this.images.splice(evt.oldIndex, 1)[0];
                            this.images.splice(evt.newIndex, 0, item);
                            
                            // Save order to server if editing existing product
                            if (this.productId) {
                                this.saveImageOrder();
                            }
                        }
                    });
                }
            }
            
            // Listen for messages from media gallery iframe (only once)
            const messageHandler = (event) => {
                // Verify origin for security
                if (event.origin !== window.location.origin) return;
                
                if (event.data.action === 'imagesSelected') {
                    this.addImagesFromGallery(event.data.images);
                    window.dispatchEvent(new CustomEvent('close-media-gallery'));
                } else if (event.data.action === 'cancel') {
                    window.dispatchEvent(new CustomEvent('close-media-gallery'));
                }
            };
            
            // Remove any existing listener first
            window.removeEventListener('message', messageHandler);
            // Add the listener
            window.addEventListener('message', messageHandler);
        },
        
        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            this.addFiles(files);
            event.target.value = ''; // Reset input
        },
        
        handleDrop(event) {
            event.target.classList.remove('border-primary-500', 'bg-primary-50');
            const files = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            this.addFiles(files);
        },
        
        addFiles(files) {
            files.forEach(file => {
                if (file.size > 2 * 1024 * 1024) {
                    alert(`${file.name} is too large. Maximum size is 2MB.`);
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.newPreviews.push({
                        url: e.target.result,
                        name: file.name
                    });
                    this.newFiles.push(file);
                    
                    // Create hidden file input for form submission
                    this.$nextTick(() => {
                        const input = document.getElementById(`hidden-file-${this.newFiles.length - 1}`);
                        if (input) {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            input.files = dataTransfer.files;
                        }
                    });
                };
                reader.readAsDataURL(file);
            });
        },
        
        removeNewPreview(index) {
            this.newPreviews.splice(index, 1);
            this.newFiles.splice(index, 1);
        },
        
        async deleteImage(index) {
            // Confirmation removed as per user request
            
            const image = this.images[index];
            
            // If editing existing product and image has ID, delete from server
            if (this.productId && image.id) {
                try {
                    const response = await fetch(`/admin/products/${this.productId}/images/delete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ image_id: image.id })
                    });
                    
                    const data = await response.json();
                    if (!data.success) {
                        alert('Failed to delete image');
                        return;
                    }
                } catch (error) {
                    console.error('Error deleting image:', error);
                    alert('Failed to delete image');
                    return;
                }
            }
            
            this.images.splice(index, 1);
            
            // Auto primary logic after delete:
            if (this.images.length === 1) {
                this.images[0].is_primary = true;
                if (this.productId && this.images[0].id) {
                    this.savePrimaryImage(this.images[0].id);
                }
            } else if (this.images.length > 1 && !this.images.some(img => img.is_primary)) {
                this.images[0].is_primary = true;
                if (this.productId && this.images[0].id) {
                    this.savePrimaryImage(this.images[0].id);
                }
            }
        },
        
        async setPrimary(index) {
            this.images.forEach((img, i) => {
                img.is_primary = (i === index);
            });
            
            // Save to server if editing existing product
            if (this.productId && this.images[index].id) {
                this.savePrimaryImage(this.images[index].id);
            }
        },
        
        async saveImageOrder() {
            if (!this.productId) return;
            
            const order = this.images.map(img => img.id).filter(id => id);
            
            try {
                await fetch(`/admin/products/${this.productId}/images/reorder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ order })
                });
            } catch (error) {
                console.error('Error saving image order:', error);
            }
        },
        
        async savePrimaryImage(imageId) {
            if (!this.productId) return;
            
            try {
                await fetch(`/admin/products/${this.productId}/images/set-primary`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ image_id: imageId })
                });
            } catch (error) {
                console.error('Error setting primary image:', error);
            }
        },
        
        openMediaGallery() {
            window.dispatchEvent(new CustomEvent('open-media-gallery'));
        },
        
        addImagesFromGallery(imageUrls) {
            console.log('Images received by manager:', imageUrls);
            if (!Array.isArray(imageUrls)) return;
            
            // Add selected images from gallery to existing images
            imageUrls.forEach(url => {
                // Check if image already exists to prevent duplicates
                const exists = this.images.some(img => img.url === url);
                if (!exists) {
                    this.images.push({
                        id: null, // New image, no ID yet
                        url: url,
                        is_primary: false
                    });
                }
            });

            // Enforce primary rules: 1 image -> auto primary, >1 images -> first image primary by default
            if (this.images.length === 1) {
                this.images[0].is_primary = true;
            } else if (this.images.length > 1 && !this.images.some(img => img.is_primary)) {
                this.images[0].is_primary = true;
            }
            console.log('Updated images array:', JSON.parse(JSON.stringify(this.images)));
        }
    };
}
</script>

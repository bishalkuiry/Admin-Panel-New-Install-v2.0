@extends('admin.layouts.app')

@section('title', 'Send Push Notification')

@push('styles')
<style>
    .target-option { transition: all 0.2s ease; }
    .target-option input:checked + div { border-color: var(--primary-600); background: var(--primary-50); }
    .preview-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
</style>
@endpush

@section('content')
<div class="space-y-5">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Send Push Notification</h1>
            <p class="text-sm text-gray-500 mt-1">Send custom notifications to your app users</p>
        </div>
        <a href="{{ route('admin.settings.mobile-app') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Settings
        </a>
    </div>

    <form action="{{ route('admin.push-notifications.send') }}" method="POST" id="notificationForm" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Form Section -->
            <div class="lg:col-span-2 space-y-5">
                
                <!-- Notification Content -->
                <div class="card p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Notification Content</h3>
                    
                    <div class="space-y-4">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" id="notif_title" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" placeholder="e.g., Flash Sale Today!" maxlength="100" required oninput="updatePreview()">
                            <p class="text-xs text-gray-500 mt-1">Maximum 100 characters</p>
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Message <span class="text-red-500">*</span>
                            </label>
                            <textarea name="message" id="notif_message" rows="3" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" placeholder="e.g., Get up to 50% off on all electronics. Limited time offer!" maxlength="255" required oninput="updatePreview()"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Maximum 255 characters</p>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Notification Image <span class="text-gray-400">(Optional)</span>
                            </label>
                            
                            <!-- Drag & Drop Area -->
                            <div id="dropZone" class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors cursor-pointer">
                                <input type="file" name="image" id="imageInput" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="hidden" onchange="handleImageSelect(event)">
                                
                                <div id="uploadPrompt">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-sm text-gray-600 mb-1">
                                        <span class="text-primary-600 font-medium">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 2MB</p>
                                </div>
                                
                                <div id="imagePreviewContainer" class="hidden">
                                    <img id="uploadedImagePreview" src="" alt="Preview" class="max-h-48 mx-auto rounded-lg mb-3">
                                    <p id="imageName" class="text-sm text-gray-600 mb-2"></p>
                                    <button type="button" onclick="removeImage()" class="text-sm text-red-600 hover:text-red-700 font-medium">
                                        Remove Image
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Recommended size: 1200x600px for best results</p>
                        </div>
                    </div>
                </div>

                <!-- Target Destination -->
                <div class="card p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">When Tapped, Open</h3>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                        <!-- Home -->
                        <label class="target-option cursor-pointer">
                            <input type="radio" name="target_type" value="home" class="sr-only" checked onchange="updateTargetFields()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg text-center hover:border-gray-300">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span class="text-sm font-medium text-gray-900">Home</span>
                            </div>
                        </label>

                        <!-- Category -->
                        <label class="target-option cursor-pointer">
                            <input type="radio" name="target_type" value="category" class="sr-only" onchange="updateTargetFields()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg text-center hover:border-gray-300">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <span class="text-sm font-medium text-gray-900">Category</span>
                            </div>
                        </label>

                        <!-- Store -->
                        <label class="target-option cursor-pointer">
                            <input type="radio" name="target_type" value="store" class="sr-only" onchange="updateTargetFields()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg text-center hover:border-gray-300">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span class="text-sm font-medium text-gray-900">Store</span>
                            </div>
                        </label>

                        <!-- Product -->
                        <label class="target-option cursor-pointer">
                            <input type="radio" name="target_type" value="product" class="sr-only" onchange="updateTargetFields()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg text-center hover:border-gray-300">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span class="text-sm font-medium text-gray-900">Product</span>
                            </div>
                        </label>

                        <!-- Brand -->
                        <label class="target-option cursor-pointer">
                            <input type="radio" name="target_type" value="brand" class="sr-only" onchange="updateTargetFields()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg text-center hover:border-gray-300">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <span class="text-sm font-medium text-gray-900">Brand</span>
                            </div>
                        </label>

                        <!-- Custom Link -->
                        <label class="target-option cursor-pointer">
                            <input type="radio" name="target_type" value="custom" class="sr-only" onchange="updateTargetFields()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg text-center hover:border-gray-300">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                <span class="text-sm font-medium text-gray-900">Custom</span>
                            </div>
                        </label>
                    </div>

                    <!-- Dynamic Target Fields -->
                    <div id="target_fields">
                        <!-- Category Select -->
                        <div id="field_category" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Select Category <span class="text-red-500">*</span>
                            </label>
                            <select name="target_id" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                                <option value="">Choose a category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Store Select -->
                        <div id="field_store" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Select Store <span class="text-red-500">*</span>
                            </label>
                            <select name="target_id" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                                <option value="">Choose a store...</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Product Search -->
                        <div id="field_product" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Search Product <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="product_search" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" placeholder="Type to search products..." oninput="searchProducts(this.value)">
                            <input type="hidden" name="target_id" id="selected_product_id">
                            <div id="product_results" class="mt-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg hidden"></div>
                            <p class="text-xs text-gray-500 mt-1" id="product_selected_hint"></p>
                        </div>

                        <!-- Brand Select -->
                        <div id="field_brand" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Select Brand <span class="text-red-500">*</span>
                            </label>
                            <select name="target_id" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                                <option value="">Choose a brand...</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Custom Link -->
                        <div id="field_custom" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Custom Link <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="custom_link" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" placeholder="https://example.com or quixko://custom">
                            <p class="text-xs text-gray-500 mt-1">External URL or custom deep link</p>
                        </div>
                    </div>
                </div>

                <!-- Send To -->
                <div class="card p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Send To</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-gray-300">
                            <input type="radio" name="send_to" value="all" class="w-4 h-4 text-primary-600" checked onchange="updateSendToFields()">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">All Users</span>
                                <span class="block text-xs text-gray-500">Send to all users with the app installed</span>
                            </div>
                        </label>

                        <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-gray-300">
                            <input type="radio" name="send_to" value="topic" class="w-4 h-4 text-primary-600" onchange="updateSendToFields()">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">Specific Topic</span>
                                <span class="block text-xs text-gray-500">Send to users subscribed to a topic</span>
                            </div>
                        </label>

                        <div id="topic_field" class="hidden ml-7">
                            <select name="topic" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                                <option value="">Select topic...</option>
                                <option value="promotions">Promotions</option>
                                <option value="admin">Admin</option>
                                <option value="vendors">Vendors</option>
                                <option value="customers">Customers</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center gap-3">
                    <button type="submit" onclick="return validateForm()" class="btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Send Notification
                    </button>
                    <button type="button" onclick="resetForm()" class="btn-secondary">
                        Reset
                    </button>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="lg:col-span-1">
                <div class="sticky top-6">
                    <div class="card p-5">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Preview</h3>
                        
                        <!-- Phone Mockup -->
                        <div class="mx-auto" style="max-width: 280px;">
                            <div class="preview-card rounded-2xl p-4 shadow-xl">
                                <div class="bg-white rounded-xl p-3 shadow-lg">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p id="preview_title" class="text-sm font-semibold text-gray-900 truncate">Your Title Here</p>
                                            <p id="preview_message" class="text-xs text-gray-600 mt-0.5 line-clamp-2">Your message will appear here...</p>
                                            <p class="text-xs text-gray-400 mt-1">Just now</p>
                                        </div>
                                    </div>
                                    <div id="preview_image_container" class="mt-3 hidden">
                                        <img id="preview_image" src="" alt="Preview" class="w-full h-32 object-cover rounded-lg">
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 text-center mt-3">This is how your notification will appear on user devices</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let productSearchTimeout;
let uploadedImageUrl = null;

// Drag and Drop functionality
const dropZone = document.getElementById('dropZone');
const imageInput = document.getElementById('imageInput');

dropZone.addEventListener('click', () => imageInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-primary-500', 'bg-primary-50');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-primary-500', 'bg-primary-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-primary-500', 'bg-primary-50');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleImageFile(files[0]);
    }
});

function handleImageSelect(event) {
    const file = event.target.files[0];
    if (file) {
        handleImageFile(file);
    }
}

function handleImageFile(file) {
    // Validate file type
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        alert('Please upload a valid image file (JPEG, PNG, GIF, or WEBP)');
        return;
    }
    
    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert('Image size must be less than 2MB');
        return;
    }
    
    // Create FileList and assign to input
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    imageInput.files = dataTransfer.files;
    
    // Preview image
    const reader = new FileReader();
    reader.onload = (e) => {
        uploadedImageUrl = e.target.result;
        document.getElementById('uploadedImagePreview').src = uploadedImageUrl;
        document.getElementById('imageName').textContent = file.name;
        document.getElementById('uploadPrompt').classList.add('hidden');
        document.getElementById('imagePreviewContainer').classList.remove('hidden');
        updatePreview();
    };
    reader.readAsDataURL(file);
}

function removeImage() {
    imageInput.value = '';
    uploadedImageUrl = null;
    document.getElementById('uploadPrompt').classList.remove('hidden');
    document.getElementById('imagePreviewContainer').classList.add('hidden');
    updatePreview();
}

function updatePreview() {
    const title = document.getElementById('notif_title').value || 'Your Title Here';
    const message = document.getElementById('notif_message').value || 'Your message will appear here...';
    
    document.getElementById('preview_title').textContent = title;
    document.getElementById('preview_message').textContent = message;
    
    if (uploadedImageUrl) {
        document.getElementById('preview_image').src = uploadedImageUrl;
        document.getElementById('preview_image_container').classList.remove('hidden');
    } else {
        document.getElementById('preview_image_container').classList.add('hidden');
    }
}

function updateTargetFields() {
    const targetType = document.querySelector('input[name="target_type"]:checked').value;
    
    // Hide all fields and disable their inputs
    document.querySelectorAll('#target_fields > div').forEach(el => {
        el.classList.add('hidden');
        // Disable all inputs in hidden fields so they don't submit
        el.querySelectorAll('input, select').forEach(input => {
            input.disabled = true;
        });
    });
    
    // Clear all target_id fields
    document.querySelectorAll('select[name="target_id"]').forEach(el => el.value = '');
    document.getElementById('selected_product_id').value = '';
    document.getElementById('product_search').value = '';
    document.getElementById('product_selected_hint').textContent = '';
    
    // Show selected field and enable its inputs
    if (targetType !== 'home') {
        const selectedField = document.getElementById('field_' + targetType);
        if (selectedField) {
            selectedField.classList.remove('hidden');
            // Enable inputs in the visible field
            selectedField.querySelectorAll('input, select').forEach(input => {
                input.disabled = false;
            });
        }
    }
}

function updateSendToFields() {
    const sendTo = document.querySelector('input[name="send_to"]:checked').value;
    const topicField = document.getElementById('topic_field');
    
    if (sendTo === 'topic') {
        topicField.classList.remove('hidden');
    } else {
        topicField.classList.add('hidden');
    }
}

function searchProducts(query) {
    clearTimeout(productSearchTimeout);
    
    if (query.length < 2) {
        document.getElementById('product_results').classList.add('hidden');
        return;
    }
    
    productSearchTimeout = setTimeout(() => {
        fetch(`{{ route('admin.push-notifications.search-products') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                const resultsDiv = document.getElementById('product_results');
                
                if (data.data.length === 0) {
                    resultsDiv.innerHTML = '<p class="p-3 text-sm text-gray-500">No products found</p>';
                } else {
                    resultsDiv.innerHTML = data.data.map(product => `
                        <div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0" onclick="selectProduct(${product.id}, '${product.name.replace(/'/g, "\\'")}')">
                            <p class="text-sm font-medium text-gray-900">${product.name}</p>
                            <p class="text-xs text-gray-500">SKU: ${product.sku} • $${product.price}</p>
                        </div>
                    `).join('');
                }
                
                resultsDiv.classList.remove('hidden');
            });
    }, 300);
}

function selectProduct(id, name) {
    document.getElementById('selected_product_id').value = id;
    document.getElementById('product_search').value = name;
    document.getElementById('product_results').classList.add('hidden');
    document.getElementById('product_selected_hint').textContent = '✓ Product selected: ' + name;
    document.getElementById('product_selected_hint').classList.add('text-green-600');
}

function resetForm() {
    document.getElementById('notificationForm').reset();
    removeImage();
    updatePreview();
    updateTargetFields();
    updateSendToFields();
}

function validateForm() {
    const targetType = document.querySelector('input[name="target_type"]:checked').value;
    
    // Check if target_id is required and provided
    if (targetType !== 'home' && targetType !== 'custom') {
        // Only check enabled (visible) fields
        const targetIdInput = document.querySelector('input[name="target_id"]:not([disabled])');
        const targetIdSelect = document.querySelector('select[name="target_id"]:not([disabled])');
        const targetId = targetIdInput?.value || targetIdSelect?.value;
        
        if (!targetId) {
            let fieldName = targetType.charAt(0).toUpperCase() + targetType.slice(1);
            alert(`Please select a ${fieldName} before sending the notification.`);
            return false;
        }
    }
    
    // Check custom link
    if (targetType === 'custom') {
        const customLink = document.querySelector('input[name="custom_link"]')?.value;
        if (!customLink) {
            alert('Please enter a custom link before sending the notification.');
            return false;
        }
    }
    
    // Check send_to topic
    const sendTo = document.querySelector('input[name="send_to"]:checked').value;
    if (sendTo === 'topic') {
        const topic = document.querySelector('select[name="topic"]')?.value;
        if (!topic) {
            alert('Please select a topic before sending the notification.');
            return false;
        }
    }
    
    return true;
}

// Initialize
updatePreview();
updateTargetFields(); // Initialize field states on page load

</script>
@endpush


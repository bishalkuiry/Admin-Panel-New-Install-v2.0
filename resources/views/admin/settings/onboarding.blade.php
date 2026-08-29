@extends('admin.layouts.app')

@section('title', 'Onboarding Screens')

@push('styles')
<style>
    .screen-item { transition: all 0.2s ease; }
    .screen-item.dragging { opacity: 0.5; }
    @media (max-width: 640px) {
        .mobile-stack { flex-direction: column; }
        .mobile-full { width: 100%; }
        .mobile-center { justify-content: center; }
    }
</style>
@endpush

@section('content')
<div class="pb-20 lg:pb-0">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <a href="{{ route('admin.settings.mobile-app') }}" class="p-2 -ml-2 text-gray-400 hover:text-gray-600 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="min-w-0">
                <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">Onboarding Screens</h1>
                <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">Manage onboarding for new users</p>
            </div>
        </div>
        <x-permission-btn 
            permission="mobile_app.manage" 
            type="button"
            onclick="addScreen()" 
            class="btn-primary flex-shrink-0 text-sm sm:text-base" 
            label="Add Screen"
            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
        />
    </div>

    <!-- Onboarding Enable/Disable Toggle -->
    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900">Onboarding Status</h3>
                <p class="text-sm text-gray-500">Enable or disable the onboarding experience for new users</p>
            </div>
            @can('mobile_app.manage')
            <label class="toggle">
                <input type="checkbox" name="onboarding_enabled" value="1" id="onboarding_enabled" {{ ($onboardingEnabled ?? '1') == '1' ? 'checked' : '' }} onchange="toggleOnboarding(this)">
                <span class="toggle-slider"></span>
            </label>
            @else
            <label class="toggle opacity-50 cursor-not-allowed" onclick="alert('You do not have permission to change this setting.')">
                <input type="checkbox" disabled {{ ($onboardingEnabled ?? '1') == '1' ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
            @endcan
        </div>
    </div>

    <form action="{{ route('admin.settings.onboarding.save') }}" method="POST" id="onboardingForm" enctype="multipart/form-data">
        @csrf

        <!-- Mobile Preview Toggle -->
        <div class="lg:hidden mb-4">
            <button type="button" onclick="toggleMobilePreview()" class="w-full card p-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Preview</span>
                    <span id="mobile-preview-label" class="text-xs text-gray-500">Screen 1</span>
                </div>
                <svg id="preview-chevron" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div id="mobile-preview-panel" class="hidden mt-2 card p-4 flex justify-center">
                <div class="w-40 h-72 bg-gray-900 rounded-3xl p-1.5 shadow-xl">
                    <div class="w-full h-full bg-white rounded-2xl overflow-hidden flex flex-col">
                        <div class="h-5 bg-gray-100 flex items-center justify-center">
                            <div class="w-12 h-1 bg-gray-300 rounded-full"></div>
                        </div>
                        <div class="flex-1 p-2 flex flex-col items-center justify-center text-center">
                            <div id="mobile-preview-image" class="w-16 h-16 bg-gray-100 rounded-lg mb-2 flex items-center justify-center overflow-hidden">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h4 id="mobile-preview-title" class="font-bold text-gray-900 text-xs mb-0.5">Screen Title</h4>
                            <p id="mobile-preview-subtitle" class="text-[10px] text-gray-500 leading-tight">Subtitle here</p>
                        </div>
                        <div id="mobile-preview-dots" class="h-8 flex items-center justify-center gap-1"></div>
                        <div class="px-2 pb-2">
                            <div class="h-6 bg-primary-500 rounded-md"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
            <!-- Screens List -->
            <div class="lg:col-span-2">
                <div id="screens-container" class="space-y-2">
                    @forelse($screens as $index => $screen)
                    <div class="screen-item group cursor-pointer bg-white border border-gray-200 rounded-lg hover:border-primary-300 hover:shadow-sm transition-all" data-index="{{ $index }}">
                        <div class="flex items-center gap-2 p-2 sm:p-3">
                            <!-- Drag + Number -->
                            <div class="drag-handle cursor-grab active:cursor-grabbing p-1 text-gray-300 hover:text-gray-500 touch-manipulation">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                </svg>
                            </div>
                            <div class="w-6 h-6 bg-primary-50 text-primary-600 rounded flex items-center justify-center font-semibold text-xs screen-number flex-shrink-0">{{ $index + 1 }}</div>
                            
                            <!-- Clickable Image Upload -->
                            <div class="image-upload-area w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 hover:bg-gray-200 border-2 border-dashed border-gray-300 hover:border-primary-400 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center cursor-pointer transition-all relative" onclick="triggerFileInput(this)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event, this)">
                                @if(!empty($screen['image_url']))
                                @php
                                    $imgSrc = $screen['image_url'];
                                    if (!str_starts_with($imgSrc, 'http://') && !str_starts_with($imgSrc, 'https://') && !str_starts_with($imgSrc, 'data:')) {
                                        $imgSrc = asset('storage/' . ltrim($imgSrc, '/'));
                                    }
                                @endphp
                                <img src="{{ $imgSrc }}" class="w-full h-full object-cover absolute inset-0" alt="">
                                @endif
                                <div class="upload-placeholder flex items-center justify-center {{ !empty($screen['image_url']) ? 'hidden' : '' }}">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </div>
                                <input type="file" class="hidden image-file-input" accept="image/*" onchange="handleFileSelect(this)">
                                <input type="hidden" name="screens[{{ $index }}][image_url]" class="image-url-input" value="{{ $screen['image_url'] ?? '' }}">
                            </div>

                            <!-- Title & Subtitle Fields -->
                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2 min-w-0">
                                <input type="text" name="screens[{{ $index }}][title]" class="h-8 px-2 text-sm border border-gray-200 rounded focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" value="{{ $screen['title'] ?? '' }}" placeholder="Title" required>
                                <input type="text" name="screens[{{ $index }}][subtitle]" class="h-8 px-2 text-sm border border-gray-200 rounded focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" value="{{ $screen['subtitle'] ?? '' }}" placeholder="Subtitle" required>
                            </div>
                            <input type="hidden" name="screens[{{ $index }}][order]" value="{{ $screen['order'] ?? $index + 1 }}" class="order-input">

                            <!-- Delete -->
                            <button type="button" onclick="removeScreen(this)" class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded transition-colors flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div id="empty-state" class="bg-white border border-dashed border-gray-300 rounded-lg p-8 text-center">
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <p class="text-sm text-gray-500 mb-3">No screens yet</p>
                        <button type="button" onclick="addScreen()" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Add your first screen</button>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Desktop Sidebar -->
            <div class="hidden lg:block space-y-4">
                <!-- Desktop Preview -->
                <div class="card sticky top-6">
                    <div class="card-header border-b border-gray-100 flex items-center justify-between py-3">
                        <h3 class="font-semibold text-gray-900 text-sm">Preview</h3>
                        <span id="preview-screen-label" class="text-xs text-gray-500">Screen 1</span>
                    </div>
                    <div class="card-body flex justify-center py-4">
                        <div class="w-44 h-72 bg-gray-900 rounded-3xl p-1.5 shadow-xl">
                            <div class="w-full h-full bg-white rounded-2xl overflow-hidden flex flex-col">
                                <div class="h-5 bg-gray-100 flex items-center justify-center">
                                    <div class="w-12 h-1 bg-gray-300 rounded-full"></div>
                                </div>
                                <div class="flex-1 p-2 flex flex-col items-center justify-center text-center">
                                    <div id="preview-image" class="w-16 h-16 bg-gray-100 rounded-lg mb-2 flex items-center justify-center overflow-hidden">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <h4 id="preview-title" class="font-bold text-gray-900 text-xs mb-0.5">Screen Title</h4>
                                    <p id="preview-subtitle" class="text-[10px] text-gray-500 leading-tight">Subtitle here</p>
                                </div>
                                <div id="preview-dots" class="h-8 flex items-center justify-center gap-1"></div>
                                <div class="px-2 pb-2">
                                    <div class="h-6 bg-primary-500 rounded-md"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Save Button -->
                    <div class="card-footer border-t border-gray-100 p-3">
                        <x-permission-btn 
                            permission="mobile_app.manage" 
                            type="submit"
                            class="btn-primary w-full justify-center" 
                            label="Save All Screens"
                            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed Mobile Save Button -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 shadow-lg z-50">
            <x-permission-btn 
                permission="mobile_app.manage" 
                type="submit"
                class="btn-primary w-full justify-center" 
                label="Save All Screens"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
            />
        </div>
    </form>
</div>

<!-- Screen Template -->
<template id="screen-template">
    <div class="screen-item group cursor-pointer bg-white border border-gray-200 rounded-lg hover:border-primary-300 hover:shadow-sm transition-all" data-index="__INDEX__">
        <div class="flex items-center gap-2 p-2 sm:p-3">
            <div class="drag-handle cursor-grab active:cursor-grabbing p-1 text-gray-300 hover:text-gray-500 touch-manipulation">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                </svg>
            </div>
            <div class="w-6 h-6 bg-primary-50 text-primary-600 rounded flex items-center justify-center font-semibold text-xs screen-number flex-shrink-0">__NUMBER__</div>
            <!-- Clickable Image Upload -->
            <div class="image-upload-area w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 hover:bg-gray-200 border-2 border-dashed border-gray-300 hover:border-primary-400 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center cursor-pointer transition-all relative" onclick="triggerFileInput(this)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event, this)">
                <div class="upload-placeholder flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <input type="file" class="hidden image-file-input" accept="image/*" onchange="handleFileSelect(this)">
                <input type="hidden" name="screens[__INDEX__][image_url]" class="image-url-input" value="">
            </div>
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2 min-w-0">
                <input type="text" name="screens[__INDEX__][title]" class="h-8 px-2 text-sm border border-gray-200 rounded focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" placeholder="Title" required>
                <input type="text" name="screens[__INDEX__][subtitle]" class="h-8 px-2 text-sm border border-gray-200 rounded focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" placeholder="Subtitle" required>
            </div>
            <input type="hidden" name="screens[__INDEX__][order]" value="__NUMBER__" class="order-input">
            <button type="button" onclick="removeScreen(this)" class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
</template>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
let screenIndex = {{ count($screens) }};
let selectedScreenIndex = 0;
let mobilePreviewOpen = false;

// Toggle onboarding enabled/disabled
function toggleOnboarding(checkbox) {
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PUT');
    formData.append('onboarding_enabled', checkbox.checked ? '1' : '0');
    
    fetch('{{ route("admin.settings.mobile-app.update") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const alert = document.createElement('div');
            alert.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            alert.textContent = 'Onboarding ' + (checkbox.checked ? 'enabled' : 'disabled') + ' successfully';
            document.body.appendChild(alert);
            setTimeout(() => alert.remove(), 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        checkbox.checked = !checkbox.checked; // Revert on error
    });
}

// Image Upload Functions
function triggerFileInput(uploadArea) {
    const fileInput = uploadArea.querySelector('.image-file-input');
    fileInput.click();
}

function handleFileSelect(input) {
    const file = input.files[0];
    if (file) {
        processImageFile(file, input.closest('.image-upload-area'));
    }
}

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.add('border-primary-500', 'bg-primary-50');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('border-primary-500', 'bg-primary-50');
}

function handleDrop(e, uploadArea) {
    e.preventDefault();
    e.stopPropagation();
    uploadArea.classList.remove('border-primary-500', 'bg-primary-50');
    
    const files = e.dataTransfer.files;
    if (files.length > 0 && files[0].type.startsWith('image/')) {
        processImageFile(files[0], uploadArea);
    }
}

function processImageFile(file, uploadArea) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const dataUrl = e.target.result;
        
        // Update the upload area with image preview
        const existingImg = uploadArea.querySelector('img');
        const placeholder = uploadArea.querySelector('.upload-placeholder');
        
        if (existingImg) {
            existingImg.src = dataUrl;
        } else {
            const img = document.createElement('img');
            img.src = dataUrl;
            img.className = 'w-full h-full object-cover absolute inset-0';
            uploadArea.appendChild(img);
        }
        
        if (placeholder) placeholder.classList.add('hidden');
        
        // Store the data URL in hidden input
        const hiddenInput = uploadArea.querySelector('.image-url-input');
        if (hiddenInput) hiddenInput.value = dataUrl;
        
        // Update preview
        const screenItem = uploadArea.closest('.screen-item');
        if (screenItem && screenItem.classList.contains('border-primary-500')) {
            updatePreviewForScreen(screenItem);
        }
    };
    reader.readAsDataURL(file);
}

function toggleMobilePreview() {
    const panel = document.getElementById('mobile-preview-panel');
    const chevron = document.getElementById('preview-chevron');
    mobilePreviewOpen = !mobilePreviewOpen;
    
    if (mobilePreviewOpen) {
        panel.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        panel.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

function addScreen() {
    const container = document.getElementById('screens-container');
    const emptyState = document.getElementById('empty-state');
    const template = document.getElementById('screen-template').innerHTML;
    
    if (emptyState) emptyState.remove();
    
    const newScreen = template
        .replace(/__INDEX__/g, screenIndex)
        .replace(/__NUMBER__/g, screenIndex + 1);
    
    container.insertAdjacentHTML('beforeend', newScreen);
    
    const newScreenEl = container.lastElementChild;
    attachInputListeners(newScreenEl);
    attachClickListener(newScreenEl);
    
    screenIndex++;
    updateScreenNumbers();
    selectScreen(newScreenEl);
    
    newScreenEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function attachInputListeners(screenEl) {
    const inputs = screenEl.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('input', handleInputChange);
        input.addEventListener('focus', () => selectScreen(screenEl));
    });
}

function attachClickListener(screenEl) {
    screenEl.addEventListener('click', function(e) {
        if (e.target.closest('button[onclick*="removeScreen"]')) return;
        selectScreen(screenEl);
    });
}

function selectScreen(screenEl) {
    const container = document.getElementById('screens-container');
    const screens = container.querySelectorAll('.screen-item');
    
    screens.forEach(s => {
        s.classList.remove('border-primary-500', 'bg-primary-50/30');
        s.classList.add('border-gray-200');
    });
    screenEl.classList.remove('border-gray-200');
    screenEl.classList.add('border-primary-500', 'bg-primary-50/30');
    
    selectedScreenIndex = Array.from(screens).indexOf(screenEl);
    
    updatePreviewForScreen(screenEl);
    updatePreviewDots();
}

function handleInputChange(e) {
    const screenItem = e.target.closest('.screen-item');
    if (!screenItem) return;
    
    if (screenItem.classList.contains('border-primary-500')) {
        updatePreviewForScreen(screenItem);
    }
}

function updatePreviewForScreen(screenEl) {
    const title = screenEl.querySelector('input[name*="[title]"]')?.value || '';
    const subtitle = screenEl.querySelector('input[name*="[subtitle]"]')?.value || '';
    const imageUrl = screenEl.querySelector('input[name*="[image_url]"]')?.value || '';
    const screenNumber = screenEl.querySelector('.screen-number')?.textContent || '1';
    
    // Update desktop preview
    const desktopTitle = document.getElementById('preview-title');
    const desktopSubtitle = document.getElementById('preview-subtitle');
    const desktopImage = document.getElementById('preview-image');
    const desktopLabel = document.getElementById('preview-screen-label');
    
    if (desktopTitle) desktopTitle.textContent = title || 'Screen Title';
    if (desktopSubtitle) desktopSubtitle.textContent = subtitle || 'Subtitle here';
    if (desktopLabel) desktopLabel.textContent = `Screen ${screenNumber}`;
    if (desktopImage) {
        if (imageUrl) {
            desktopImage.innerHTML = `<img src="${imageUrl}" class="w-full h-full object-cover" onerror="this.style.display='none'">`;
        } else {
            desktopImage.innerHTML = `<svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
        }
    }
    
    // Update mobile preview
    const mobileTitle = document.getElementById('mobile-preview-title');
    const mobileSubtitle = document.getElementById('mobile-preview-subtitle');
    const mobileImage = document.getElementById('mobile-preview-image');
    const mobileLabel = document.getElementById('mobile-preview-label');
    
    if (mobileTitle) mobileTitle.textContent = title || 'Screen Title';
    if (mobileSubtitle) mobileSubtitle.textContent = subtitle || 'Subtitle here';
    if (mobileLabel) mobileLabel.textContent = `Screen ${screenNumber}`;
    if (mobileImage) {
        if (imageUrl) {
            mobileImage.innerHTML = `<img src="${imageUrl}" class="w-full h-full object-cover" onerror="this.style.display='none'">`;
        } else {
            mobileImage.innerHTML = `<svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
        }
    }
}

function updatePreviewDots() {
    const container = document.getElementById('screens-container');
    const screens = container.querySelectorAll('.screen-item');
    
    let dotsHtml = '';
    screens.forEach((_, index) => {
        if (index === selectedScreenIndex) {
            dotsHtml += '<div class="w-1.5 h-1.5 bg-primary-500 rounded-full"></div>';
        } else {
            dotsHtml += '<div class="w-1 h-1 bg-gray-300 rounded-full"></div>';
        }
    });
    
    const desktopDots = document.getElementById('preview-dots');
    const mobileDots = document.getElementById('mobile-preview-dots');
    if (desktopDots) desktopDots.innerHTML = dotsHtml;
    if (mobileDots) mobileDots.innerHTML = dotsHtml;
}

function removeScreen(button) {
    const screenItem = button.closest('.screen-item');
    const wasSelected = screenItem.classList.contains('ring-2');
    
    screenItem.style.transform = 'translateX(100%)';
    screenItem.style.opacity = '0';
    screenItem.style.transition = 'all 0.3s ease';
    
    setTimeout(() => {
        screenItem.remove();
        updateScreenNumbers();
        
        const container = document.getElementById('screens-container');
        const remainingScreens = container.querySelectorAll('.screen-item');
        
        if (remainingScreens.length === 0) {
            container.innerHTML = `
                <div id="empty-state" class="bg-white border border-dashed border-gray-300 rounded-lg p-8 text-center">
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <p class="text-sm text-gray-500 mb-3">No screens yet</p>
                    <button type="button" onclick="addScreen()" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Add your first screen</button>
                </div>
            `;
            updatePreviewForScreen({ querySelector: () => null });
            updatePreviewDots();
        } else if (wasSelected) {
            const newIndex = Math.min(selectedScreenIndex, remainingScreens.length - 1);
            selectScreen(remainingScreens[newIndex]);
        } else {
            updatePreviewDots();
        }
    }, 300);
}

function updateScreenNumbers() {
    const screens = document.querySelectorAll('.screen-item');
    screens.forEach((screen, index) => {
        screen.querySelector('.screen-number').textContent = index + 1;
        screen.querySelector('.order-input').value = index + 1;
        
        screen.querySelectorAll('input[name^="screens"]').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace(/screens\[\d+\]/, `screens[${index}]`));
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('screens-container');
    new Sortable(container, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'opacity-50',
        onEnd: function() {
            updateScreenNumbers();
            updatePreviewDots();
        }
    });
    
    const screens = container.querySelectorAll('.screen-item');
    screens.forEach(screenEl => {
        attachInputListeners(screenEl);
        attachClickListener(screenEl);
    });
    
    if (screens.length > 0) {
        selectScreen(screens[0]);
    }
});
</script>
@endpush
@endsection

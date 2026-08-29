@extends('admin.layouts.app')
@section('title', 'Create Store')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div x-data="storeForm()" class="space-y-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.stores.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Create New Store</h1>
                <p class="text-sm text-gray-500 mt-1">Add a new store to the platform</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.stores.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" form="store-form" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Create Store
            </button>
        </div>
    </div>

    <form id="store-form" action="{{ route('admin.stores.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-5">
                <!-- Basic Information -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Basic Information</h3></div>
                    <div class="card-body space-y-4">
                        <div x-data="{ 
                            name: '{{ old('name') }}', 
                            slug: '{{ old('slug') }}', 
                            slugManuallyEdited: {{ old('slug') ? 'true' : 'false' }},
                            generateSlug(name) {
                                return name.toLowerCase()
                                    .replace(/[^\w\s-]/g, '')
                                    .replace(/\s+/g, '-')
                                    .replace(/-+/g, '-')
                                    .trim();
                            }
                        }">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Store Name *</label>
                            <input type="text" name="name" x-model="name" 
                                   @input="if (!slugManuallyEdited) slug = generateSlug(name)"
                                   required class="input" placeholder="My Awesome Store">
                            @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Slug (URL)
                                    <span x-show="!slugManuallyEdited && slug" class="ml-2 text-xs font-normal text-green-600">✓ Auto-generated</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm pointer-events-none">/store/</span>
                                    <input type="text" name="slug" x-model="slug" 
                                           @input="slugManuallyEdited = true"
                                           @focus="if (!slugManuallyEdited && name) slug = generateSlug(name)"
                                           class="input pl-14" placeholder="my-awesome-store">
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="text-xs text-gray-500">Leave blank to auto-generate from name</p>
                                    <button type="button" x-show="slugManuallyEdited" 
                                            @click="slugManuallyEdited = false; slug = generateSlug(name)"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                        ↻ Reset to auto
                                    </button>
                                </div>
                                @error('slug')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Owner (Seller) *</label>
                            <select name="owner_id" class="input" required>
                                <option value="">-- Select seller --</option>
                                @foreach($sellers as $seller)
                                @php $storeCount = $seller->ownedStores()->count(); @endphp
                                <option value="{{ $seller->id }}" {{ old('owner_id') == $seller->id ? 'selected' : '' }}>
                                    {{ $seller->name }} ({{ $seller->email }}){{ $storeCount > 0 ? ' — ' . $storeCount . ' store' . ($storeCount > 1 ? 's' : '') : '' }}
                                </option>
                                @endforeach
                            </select>
                            @if($sellers->isEmpty())
                            <p class="text-xs text-gray-500 mt-1">No available sellers. Create a seller first.</p>
                            @else
                            <p class="text-xs text-gray-500 mt-1">Sellers can manage multiple stores.</p>
                            @endif
                            @error('owner_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="3" class="input">{{ old('description') }}</textarea>
                            @error('description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Store Images -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Store Images</h3></div>
                    <div class="card-body space-y-6">
                        <!-- Logo Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Store Logo</label>
                            <div class="flex items-start gap-4">
                                <!-- Preview -->
                                <div x-show="logoPreview" class="flex-shrink-0">
                                    <div class="relative w-24 h-24 rounded-lg overflow-hidden bg-gray-100 border-2 border-gray-200">
                                        <img :src="logoPreview" class="w-full h-full object-cover">
                                        <button type="button" @click="removeLogo()" class="absolute top-1 right-1 p-1 bg-white rounded-full text-red-600 hover:bg-red-50 shadow-lg">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <!-- Upload Zone -->
                                <div class="flex-1">
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-400 transition-colors"
                                         @drop.prevent="handleLogoDrop($event)"
                                         @dragover.prevent="$el.classList.add('border-indigo-500', 'bg-indigo-50')"
                                         @dragleave.prevent="$el.classList.remove('border-indigo-500', 'bg-indigo-50')">
                                        <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <p class="text-sm font-medium text-gray-700 mb-1">Drag & drop logo or click to browse</p>
                                        <p class="text-xs text-gray-500 mb-3">PNG, JPG up to 2MB • Recommended: 200x200px</p>
                                        <div class="flex items-center justify-center gap-2">
                                            <label class="btn-secondary text-xs cursor-pointer">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                </svg>
                                                Browse
                                                <input type="file" accept="image/*" class="hidden" @change="handleLogoSelect($event)">
                                            </label>
                                            <button type="button" @click="openMediaGallery('logo')" class="btn-outline text-xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                Media Library
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="file" name="logo" id="logo-input" class="hidden" accept="image/*">
                            @error('logo')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Banner Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Store Banner</label>
                            <div x-show="bannerPreview" class="mb-3">
                                <div class="relative w-full h-48 rounded-lg overflow-hidden bg-gray-100 border-2 border-gray-200">
                                    <img :src="bannerPreview" class="w-full h-full object-cover">
                                    <button type="button" @click="removeBanner()" class="absolute top-2 right-2 p-1.5 bg-white rounded-full text-red-600 hover:bg-red-50 shadow-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-400 transition-colors"
                                 @drop.prevent="handleBannerDrop($event)"
                                 @dragover.prevent="$el.classList.add('border-indigo-500', 'bg-indigo-50')"
                                 @dragleave.prevent="$el.classList.remove('border-indigo-500', 'bg-indigo-50')">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-700 mb-2">Drag & drop banner or click to browse</p>
                                <p class="text-xs text-gray-500 mb-4">PNG, JPG, GIF up to 2MB • Recommended: 1200x400px</p>
                                <div class="flex items-center justify-center gap-2">
                                    <label class="btn-secondary cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        Browse Files
                                        <input type="file" accept="image/*,image/gif" class="hidden" @change="handleBannerSelect($event)">
                                    </label>
                                    <button type="button" @click="openMediaGallery('banner')" class="btn-outline">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Media Library
                                    </button>
                                </div>
                            </div>
                            <input type="file" name="banner" id="banner-input" class="hidden" accept="image/*,image/gif">
                            @error('banner')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror>
                        </div>
                    </div>
                </div>

                <!-- Contact Information - Removed as it will come from seller -->

                <!-- Address Information -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Address Information</h3></div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Street Address *</label>
                            <input type="text" name="address" value="{{ old('address') }}" required class="input" placeholder="123 Main Street">
                            @error('address')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                                <input type="text" name="city" value="{{ old('city') }}" required class="input" placeholder="New York">
                                @error('city')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">State *</label>
                                <input type="text" name="state" value="{{ old('state') }}" required class="input" placeholder="NY">
                                @error('state')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code *</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code') }}" required class="input" placeholder="10001">
                                @error('postal_code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                                <input type="text" name="country" value="{{ old('country', 'India') }}" required class="input" placeholder="India">
                                @error('country')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Picker (Map) -->
                <div class="card" x-data="locationPicker()">
                    <div class="card-header">
                        <h3 class="card-title">Store Location</h3>
                        <p class="text-sm text-gray-500 mt-1">Search and pinpoint exact store location on map</p>
                    </div>
                    <div class="card-body space-y-4">
                        <!-- Search Box -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                x-model="searchQuery"
                                class="input pl-10 pr-10" 
                                placeholder="Start typing to search location (e.g., MG Road, Bangalore)"
                                autocomplete="off">
                            <div x-show="isSearching" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Search Results -->
                        <div x-show="searchResults.length > 0" class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-2">
                            <template x-for="result in searchResults" :key="result.place_id">
                                <button 
                                    type="button"
                                    @click="selectSearchResult(result)"
                                    class="w-full text-left p-3 hover:bg-gray-50 rounded-lg transition-colors border border-transparent hover:border-indigo-200">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900" x-text="result.display_name"></p>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>

                        <!-- Map Container -->
                        <div class="relative">
                            <div id="location-map" class="w-full h-96 rounded-lg border-2 border-gray-200 overflow-hidden"></div>
                            
                            <!-- Coordinates Display -->
                            <div class="absolute top-3 left-3 bg-white px-3 py-2 rounded-lg shadow-lg border border-gray-200">
                                <div class="flex items-center gap-2 text-xs">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="font-mono text-gray-700">
                                        <span x-text="latitude.toFixed(6)"></span>, <span x-text="longitude.toFixed(6)"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div class="absolute bottom-3 left-3 right-3 bg-white px-4 py-2 rounded-lg shadow-lg border border-gray-200">
                                <p class="text-xs text-gray-600 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Click on the map to pinpoint store location or search above
                                </p>
                            </div>
                        </div>

                        <!-- Hidden Inputs -->
                        <input type="hidden" name="latitude" x-model="latitude" required>
                        <input type="hidden" name="longitude" x-model="longitude" required>
                        
                        @error('latitude')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        @error('longitude')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>


                <!-- Zones -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Delivery Zones</h3></div>
                    <div class="card-body">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Zones</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($zones as $zone)
                            <label class="flex items-center gap-2 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="zone_ids[]" value="{{ $zone->id }}" {{ in_array($zone->id, old('zone_ids', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-900">{{ $zone->name }}</span>
                            </label>
                            @endforeach
                        </div>
                        @if($zones->isEmpty())
                        <p class="text-sm text-gray-500">No zones available. Create zones first.</p>
                        @endif
                        @error('zone_ids')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-5">
                <!-- Settings -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Settings</h3></div>
                    <div class="card-body space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="font-medium text-gray-900">Pickup Enabled</label>
                                <p class="text-sm text-gray-500 mt-1">Allow customers to pick up orders</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="pickup_enabled" value="1" {{ old('pickup_enabled', true) ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="font-medium text-gray-900">Delivery Enabled</label>
                                <p class="text-sm text-gray-500 mt-1">Allow home delivery</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="delivery_enabled" value="1" {{ old('delivery_enabled', true) ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="font-medium text-gray-900">Featured Store</label>
                                <p class="text-sm text-gray-500 mt-1">Show in featured section</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="border-t pt-4 mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount/Promo Text</label>
                            <input type="text" name="discount_text" value="{{ old('discount_text') }}" class="input" placeholder="e.g. Flat 50% OFF">
                            <p class="text-xs text-gray-500 mt-1">Displayed as a badge on store cards</p>
                            @error('discount_text')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Delivery Configuration -->
                <div class="card" x-data="deliveryConfig()">
                    <div class="card-header">
                        <h3 class="card-title">Delivery</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <!-- Free Delivery Toggle -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="font-medium text-gray-900">Free Delivery</label>
                                <p class="text-sm text-gray-500 mt-1">No delivery charge</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="store_free_delivery" value="1" {{ old('store_free_delivery') ? 'checked' : '' }} @change="freeDelivery = $event.target.checked">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <!-- Delivery Type Selection -->
                        <div x-show="!freeDelivery" class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">Delivery Type</label>
                            <select name="delivery_type" class="input text-sm" @change="deliveryType = $event.target.value">
                                <option value="global" {{ old('delivery_type', 'global') == 'global' ? 'selected' : '' }}>Use Global Settings</option>
                                <option value="self" {{ old('delivery_type') == 'self' ? 'selected' : '' }}>Store Self-Delivery (Free)</option>
                                <option value="custom" {{ old('delivery_type') == 'custom' ? 'selected' : '' }}>Custom Charge</option>
                            </select>

                            <!-- Custom Method Options -->
                            <div x-show="deliveryType === 'custom'" class="space-y-3 pt-2">
                                <label class="block text-sm font-medium text-gray-700">Calculation Method</label>
                                <select name="delivery_method" class="input text-sm" @change="deliveryMethod = $event.target.value">
                                    <option value="flat" {{ old('delivery_method', 'flat') == 'flat' ? 'selected' : '' }}>Flat Rate</option>
                                    <option value="percentage" {{ old('delivery_method') == 'percentage' ? 'selected' : '' }}>Percentage of Order</option>
                                    <option value="per_km" {{ old('delivery_method') == 'per_km' ? 'selected' : '' }}>Per Kilometer</option>
                                </select>

                                <!-- Flat Rate Input -->
                                <div x-show="deliveryMethod === 'flat'">
                                    <label class="block text-xs text-gray-500 mb-1">Flat Rate Amount</label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-500">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                        <input type="number" name="delivery_flat_rate" step="0.01" min="0" class="input text-sm flex-1" value="{{ old('delivery_flat_rate', '5.00') }}" placeholder="5.00">
                                    </div>
                                </div>

                                <!-- Percentage Input -->
                                <div x-show="deliveryMethod === 'percentage'">
                                    <label class="block text-xs text-gray-500 mb-1">Percentage</label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" name="delivery_percentage" step="0.01" min="0" max="100" class="input text-sm flex-1" value="{{ old('delivery_percentage', '10') }}" placeholder="10">
                                        <span class="text-sm text-gray-500">%</span>
                                    </div>
                                </div>

                                <!-- Per KM Input -->
                                <div x-show="deliveryMethod === 'per_km'">
                                    <label class="block text-xs text-gray-500 mb-1">Rate per Kilometer</label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-500">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                        <input type="number" name="delivery_per_km_rate" step="0.01" min="0" class="input text-sm flex-1" value="{{ old('delivery_per_km_rate', '2.00') }}" placeholder="2.00">
                                        <span class="text-sm text-gray-500">/km</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operational Settings -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Operations</h3></div>
                    <div class="card-body space-y-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Preparation Time</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="preparation_time" value="{{ old('preparation_time', 30) }}" min="5" max="180" class="input text-sm flex-1" placeholder="30">
                                <span class="text-sm text-gray-500">min</span>
                            </div>
                            @error('preparation_time')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Delivery Radius</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="delivery_radius_km" value="{{ old('delivery_radius_km', 10) }}" min="1" max="50" class="input text-sm flex-1" placeholder="10">
                                <span class="text-sm text-gray-500">km</span>
                            </div>
                            @error('delivery_radius_km')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Min Order Amount</label>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                <input type="number" name="min_order_amount" value="{{ old('min_order_amount', 0) }}" step="0.01" min="0" class="input text-sm flex-1" placeholder="0.00">
                            </div>
                            @error('min_order_amount')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Packing Charge</label>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                <input type="number" name="packing_charge" value="{{ old('packing_charge', 0) }}" step="0.01" min="0" class="input text-sm flex-1" placeholder="0.00">
                            </div>
                            @error('packing_charge')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Initial Base Rate</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="commission_percent" value="{{ old('commission_percent', 10) }}" step="0.01" min="0" max="100" class="input text-sm flex-1" placeholder="10">
                                <span class="text-sm text-gray-500">%</span>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">
                                <span class="font-medium text-indigo-600">Flow:</span> Product > Category > <strong class="text-indigo-700">Store Rate</strong> > Global
                            </p>
                            @error('commission_percent')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- Media Gallery Modal -->
<div x-data="mediaGalleryModal()" 
     @open-media-gallery.window="showGallery = true; galleryType = $event.detail.type"
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
                    <iframe src="{{ route('admin.media.index') }}?select=true&single=true" 
                            class="w-full border-0"
                            style="height: 70vh; min-height: 500px;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Leaflet JS
const leafletScript = document.createElement('script');
leafletScript.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
document.head.appendChild(leafletScript);

function storeForm() {
    return {
        logoPreview: null,
        bannerPreview: null,
        currentGalleryType: null,
        
        init() {
            // Listen for messages from media gallery iframe
            window.addEventListener('message', (event) => {
                if (event.origin !== window.location.origin) return;
                
                if (event.data.action === 'imagesSelected' && event.data.images && event.data.images.length > 0) {
                    const imageUrl = event.data.images[0];
                    
                    if (this.currentGalleryType === 'logo') {
                        this.logoPreview = imageUrl;
                        // Set hidden input value
                        document.getElementById('logo-input').setAttribute('data-url', imageUrl);
                    } else if (this.currentGalleryType === 'banner') {
                        this.bannerPreview = imageUrl;
                        // Set hidden input value
                        document.getElementById('banner-input').setAttribute('data-url', imageUrl);
                    }
                    
                    window.dispatchEvent(new CustomEvent('close-media-gallery'));
                } else if (event.data.action === 'cancel') {
                    window.dispatchEvent(new CustomEvent('close-media-gallery'));
                }
            });
        },
        
        handleLogoSelect(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Logo file is too large. Maximum size is 2MB.');
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoPreview = e.target.result;
                    // Transfer file to hidden input
                    const hiddenInput = document.getElementById('logo-input');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    hiddenInput.files = dataTransfer.files;
                };
                reader.readAsDataURL(file);
            }
            event.target.value = '';
        },
        
        handleLogoDrop(event) {
            event.target.classList.remove('border-indigo-500', 'bg-indigo-50');
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Logo file is too large. Maximum size is 2MB.');
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoPreview = e.target.result;
                    // Transfer file to hidden input
                    const hiddenInput = document.getElementById('logo-input');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    hiddenInput.files = dataTransfer.files;
                };
                reader.readAsDataURL(file);
            }
        },
        
        removeLogo() {
            this.logoPreview = null;
            document.getElementById('logo-input').value = '';
            document.getElementById('logo-input').removeAttribute('data-url');
        },
        
        handleBannerSelect(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Banner file is too large. Maximum size is 2MB.');
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.bannerPreview = e.target.result;
                    // Transfer file to hidden input
                    const hiddenInput = document.getElementById('banner-input');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    hiddenInput.files = dataTransfer.files;
                };
                reader.readAsDataURL(file);
            }
            event.target.value = '';
        },
        
        handleBannerDrop(event) {
            event.target.classList.remove('border-indigo-500', 'bg-indigo-50');
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Banner file is too large. Maximum size is 2MB.');
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.bannerPreview = e.target.result;
                    // Transfer file to hidden input
                    const hiddenInput = document.getElementById('banner-input');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    hiddenInput.files = dataTransfer.files;
                };
                reader.readAsDataURL(file);
            }
        },
        
        removeBanner() {
            this.bannerPreview = null;
            document.getElementById('banner-input').value = '';
            document.getElementById('banner-input').removeAttribute('data-url');
        },
        
        openMediaGallery(type) {
            this.currentGalleryType = type;
            window.dispatchEvent(new CustomEvent('open-media-gallery', { detail: { type } }));
        }
    };
}

function mediaGalleryModal() {
    return {
        showGallery: false,
        galleryType: null,
        
        init() {
            // Modal initialization
        }
    };
}

function deliveryConfig() {
    return {
        freeDelivery: false,
        deliveryType: 'global',
        deliveryMethod: 'flat',
        
        init() {
            // Initialize from old values if present
            this.freeDelivery = document.querySelector('input[name="store_free_delivery"]')?.checked || false;
            const selectedType = document.querySelector('input[name="delivery_type"]:checked');
            if (selectedType) {
                this.deliveryType = selectedType.value;
            }
            const selectedMethod = document.querySelector('input[name="delivery_method"]:checked');
            if (selectedMethod) {
                this.deliveryMethod = selectedMethod.value;
            }
        }
    };
}

function locationPicker() {
    return {
        map: null,
        marker: null,
        latitude: {{ old('latitude', '12.9716') }},
        longitude: {{ old('longitude', '77.5946') }},
        searchQuery: '',
        searchResults: [],
        isSearching: false,
        searchTimeout: null,
        
        init() {
            // Wait for Leaflet to load
            const checkLeaflet = setInterval(() => {
                if (typeof L !== 'undefined') {
                    clearInterval(checkLeaflet);
                    this.initMap();
                }
            }, 100);

            // Watch for search query changes (autocomplete)
            this.$watch('searchQuery', (value) => {
                this.handleSearchInput(value);
            });
        },

        handleSearchInput(value) {
            // Clear previous timeout
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            // Clear results if query is too short
            if (!value || value.trim().length < 3) {
                this.searchResults = [];
                return;
            }

            // Debounce: wait 500ms after user stops typing
            this.searchTimeout = setTimeout(() => {
                this.searchLocation();
            }, 500);
        },
        
        initMap() {
            // Initialize map
            this.map = L.map('location-map').setView([this.latitude, this.longitude], 15);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(this.map);
            
            // Add marker
            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: `
                    <div style="position: relative;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="#4F46E5" stroke="#fff" stroke-width="2"/>
                            <circle cx="12" cy="9" r="2.5" fill="#fff"/>
                        </svg>
                    </div>
                `,
                iconSize: [40, 40],
                iconAnchor: [20, 40],
            });
            
            this.marker = L.marker([this.latitude, this.longitude], {
                icon: customIcon,
                draggable: true
            }).addTo(this.map);
            
            // Update coordinates when marker is dragged
            this.marker.on('dragend', (e) => {
                const position = e.target.getLatLng();
                this.latitude = position.lat;
                this.longitude = position.lng;
                
                // Auto-fill address from new coordinates
                this.reverseGeocode(this.latitude, this.longitude);
            });
            
            // Update marker position when map is clicked
            this.map.on('click', (e) => {
                this.latitude = e.latlng.lat;
                this.longitude = e.latlng.lng;
                this.marker.setLatLng([this.latitude, this.longitude]);
                
                // Auto-fill address from coordinates
                this.reverseGeocode(this.latitude, this.longitude);
            });
        },
        
        async searchLocation() {
            if (!this.searchQuery.trim() || this.searchQuery.trim().length < 3) return;
            
            this.isSearching = true;
            
            try {
                // Use our backend proxy to avoid CORS issues
                const response = await fetch(
                    `{{ route('admin.geocoding.search') }}?q=${encodeURIComponent(this.searchQuery)}`
                );
                
                if (!response.ok) {
                    throw new Error('Search failed');
                }
                
                const data = await response.json();
                
                if (data.error) {
                    console.error('Search error:', data.error);
                    this.searchResults = [];
                    return;
                }
                
                this.searchResults = data;
            } catch (error) {
                console.error('Search error:', error);
                this.searchResults = [];
            } finally {
                this.isSearching = false;
            }
        },
        
        selectSearchResult(result) {
            this.latitude = parseFloat(result.lat);
            this.longitude = parseFloat(result.lon);
            
            // Update map view and marker
            this.map.setView([this.latitude, this.longitude], 16);
            this.marker.setLatLng([this.latitude, this.longitude]);
            
            // Auto-fill address fields from search result
            this.fillAddressFields(result.address);
            
            // Clear search results
            this.searchResults = [];
            this.searchQuery = result.display_name;
        },

        async reverseGeocode(lat, lon) {
            try {
                const response = await fetch(
                    `{{ route('admin.geocoding.reverse') }}?lat=${lat}&lon=${lon}`
                );
                
                if (!response.ok) return;
                
                const data = await response.json();
                
                if (data.address) {
                    this.fillAddressFields(data.address);
                }
            } catch (error) {
                console.error('Reverse geocoding error:', error);
            }
        },

        fillAddressFields(address) {
            if (!address) return;

            console.log('Address data received:', address);

            // Fill street address - try multiple field combinations
            const road = address.road || address.street || address.highway || address.pedestrian || '';
            const houseNumber = address.house_number || '';
            const suburb = address.suburb || address.neighbourhood || address.quarter || '';
            
            // Build street address from available components
            let streetAddress = '';
            if (houseNumber && road) {
                streetAddress = `${houseNumber} ${road}`;
            } else if (road) {
                streetAddress = road;
            } else if (suburb) {
                streetAddress = suburb;
            }
            
            if (streetAddress.trim()) {
                const addressInput = document.querySelector('input[name="address"]');
                if (addressInput) {
                    addressInput.value = streetAddress.trim();
                    console.log('Street address filled:', streetAddress);
                }
            }

            // Fill city - try multiple field names
            const city = address.city || address.town || address.village || address.municipality || 
                        address.county || address.district || '';
            if (city) {
                const cityInput = document.querySelector('input[name="city"]');
                if (cityInput) {
                    cityInput.value = city;
                    console.log('City filled:', city);
                }
            }

            // Fill state - try multiple field names
            const state = address.state || address.province || address.region || 
                         address.state_district || '';
            if (state) {
                const stateInput = document.querySelector('input[name="state"]');
                if (stateInput) {
                    stateInput.value = state;
                    console.log('State filled:', state);
                }
            }

            // Fill postal code - try multiple field names
            const postalCode = address.postcode || address.postal_code || address.zipcode || '';
            if (postalCode) {
                const postalInput = document.querySelector('input[name="postal_code"]');
                if (postalInput) {
                    postalInput.value = postalCode;
                    console.log('Postal code filled:', postalCode);
                }
            }

            // Fill country
            const country = address.country || '';
            if (country) {
                const countryInput = document.querySelector('input[name="country"]');
                if (countryInput) {
                    countryInput.value = country;
                    console.log('Country filled:', country);
                }
            }
        }
    };
}
</script>

<style>
.toggle {
    @apply relative inline-block w-12 h-6 cursor-pointer;
}
.toggle input {
    @apply opacity-0 w-0 h-0;
}
.toggle-slider {
    @apply absolute inset-0 bg-gray-300 rounded-full transition-colors duration-200;
}
.toggle-slider:before {
    @apply absolute content-[''] h-5 w-5 left-0.5 bottom-0.5 bg-white rounded-full transition-transform duration-200;
}
.toggle input:checked + .toggle-slider {
    @apply bg-indigo-600;
}
.toggle input:checked + .toggle-slider:before {
    @apply translate-x-6;
}
</style>
@endsection

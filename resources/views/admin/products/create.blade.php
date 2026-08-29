@extends('admin.layouts.app')
@section('title', 'Add Product')
@section('content')

<style>
/* Touch-Optimized Scrollbar Hide for Tab Bar */
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
</style>

<div x-data="productForm()" x-on:switch-tab.window="activeTab = $event.detail" class="space-y-6 pb-20 sm:pb-12">

    <!-- Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-gray-200/80 shadow-xs">
        <div class="flex items-center gap-3.5">
            <a href="{{ request('type') === 'seller' ? route('admin.products.seller') : route('admin.products.index') }}" class="p-2.5 hover:bg-gray-100 rounded-xl transition-colors text-gray-500 hover:text-gray-900 border border-gray-200 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-2xl font-extrabold text-gray-900 tracking-tight">Add New Product</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider {{ request('type') === 'seller' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-indigo-100 text-indigo-800 border border-indigo-200' }}">
                        {{ request('type') === 'seller' ? 'Seller Managed' : 'In-house' }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">Fill in product details, pricing, variants, and SEO configuration.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-end sm:self-auto w-full sm:w-auto justify-end">
            <a href="{{ request('type') === 'seller' ? route('admin.products.seller') : route('admin.products.index') }}" class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl">Cancel</a>
            <button type="submit" form="product-form" class="btn-primary px-4 py-2 sm:px-5 sm:py-2.5 text-xs sm:text-sm font-bold rounded-xl shadow-md flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Create Product</span>
            </button>
        </div>
    </div>

    <form id="product-form" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Hidden input for category selection -->
        <input type="hidden" name="category_id" x-model="finalCategoryId">

        <!-- Horizontally Scrollable Tab Bar with Touch Support -->
        <div class="bg-white rounded-2xl p-1.5 border border-gray-200 shadow-xs mb-6 sticky top-2 z-20">
            <div class="scrollbar-hide flex items-center gap-1.5 sm:gap-2 overflow-x-auto py-0.5 px-1" style="-webkit-overflow-scrolling: touch;">
                
                <!-- Tab: Basic Info -->
                <button type="button" @click="switchTab('basic')" 
                        :class="activeTab === 'basic' ? 'bg-orange-500 text-white font-bold shadow-md' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-transparent'"
                        class="flex-shrink-0 px-4 py-2.5 rounded-xl border text-xs font-semibold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Basic Info</span>
                </button>

                <!-- Tab: Pricing & Inventory -->
                <button type="button" @click="switchTab('pricing')" 
                        :class="activeTab === 'pricing' ? 'bg-orange-500 text-white font-bold shadow-md' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-transparent'"
                        class="flex-shrink-0 px-4 py-2.5 rounded-xl border text-xs font-semibold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Pricing & Stock</span>
                </button>

                <!-- Tab: Variants -->
                <button type="button" @click="switchTab('variants')" 
                        :class="activeTab === 'variants' ? 'bg-orange-500 text-white font-bold shadow-md' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-transparent'"
                        class="flex-shrink-0 px-4 py-2.5 rounded-xl border text-xs font-semibold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Variants</span>
                </button>

                <!-- Tab: Media -->
                <button type="button" @click="switchTab('media')" 
                        :class="activeTab === 'media' ? 'bg-orange-500 text-white font-bold shadow-md' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-transparent'"
                        class="flex-shrink-0 px-4 py-2.5 rounded-xl border text-xs font-semibold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Media & Policies</span>
                </button>

                <!-- Tab: Organization -->
                <button type="button" @click="switchTab('organization')" 
                        :class="activeTab === 'organization' ? 'bg-orange-500 text-white font-bold shadow-md' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-transparent'"
                        class="flex-shrink-0 px-4 py-2.5 rounded-xl border text-xs font-semibold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Organization</span>
                </button>

                <!-- Tab: SEO -->
                <button type="button" @click="switchTab('seo')" 
                        :class="activeTab === 'seo' ? 'bg-orange-500 text-white font-bold shadow-md' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-transparent'"
                        class="flex-shrink-0 px-4 py-2.5 rounded-xl border text-xs font-semibold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>SEO & Metadata</span>
                </button>

            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- Left Main Tab Content Column -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Basic Info Tab -->
                <div x-show="activeTab === 'basic'" x-cloak class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200/80 space-y-5">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-base font-bold text-gray-900">Basic Information</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Essential product details, identifiers, and descriptions.</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Product Name & AI Assistant -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Product Name <span class="text-red-500">*</span></label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="text" name="name" id="product_name" x-model="productName" value="{{ old('name') }}" class="input flex-1 text-sm py-2.5" required placeholder="e.g. Organic Whole Milk 1L" autofocus>
                                <button type="button" @click="window.dispatchEvent(new CustomEvent('open-ai-assistant', { detail: { title: document.getElementById('product_name').value } }))" class="px-3.5 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all shadow-xs flex-shrink-0">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L14.85 9.15L22 12L14.85 14.85L12 22L9.15 14.85L2 12L9.15 9.15L12 2Z"/></svg>
                                    AI Assist
                                </button>
                            </div>
                            @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- SKU & Barcode -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">SKU <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="text" name="sku" x-model="sku" class="input pr-10 font-mono text-sm tracking-wider py-2.5" required placeholder="PRD-001">
                                    <button type="button" @click="generateSku()" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-all" title="Generate Random SKU">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1 uppercase font-semibold">Unique system inventory code</p>
                                @error('sku')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">EAN-13 Barcode</label>
                                <div class="relative">
                                    <input type="text" name="barcode" x-model="barcode" class="input pr-10 font-mono text-sm tracking-wider py-2.5" placeholder="8901234567890">
                                    <button type="button" @click="generateBarcode()" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all" title="Generate EAN Barcode">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    </button>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1 uppercase font-semibold">Standard 13-digit barcode</p>
                            </div>
                        </div>

                        <!-- Short Description -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Short Summary</label>
                            <textarea name="short_description" rows="2" class="input text-sm" placeholder="Brief 1-2 sentence product overview for listing cards...">{{ old('short_description') }}</textarea>
                        </div>

                        <!-- Full Description -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Full Detailed Description</label>
                            <textarea name="description" rows="4" class="input text-sm" placeholder="Comprehensive product specifications, ingredients, directions...">{{ old('description') }}</textarea>
                        </div>

                        <!-- Food / Pharmacy Module Dynamic Extra Fields -->
                        @if(($moduleType ?? 'grocery') === 'food')
                        <div class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-200/80 space-y-3">
                            <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider">Restaurant & Food Attributes</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Veg / Non-Veg</label>
                                    <select name="is_veg" class="input text-xs py-2">
                                        <option value="none" {{ old('is_veg', 'none') == 'none' ? 'selected' : '' }}>Not Applicable</option>
                                        <option value="1" {{ old('is_veg') == '1' ? 'selected' : '' }}>Pure Veg</option>
                                        <option value="0" {{ old('is_veg') === '0' ? 'selected' : '' }}>Non-Veg / Contains Egg</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Min Prep Time (Mins)</label>
                                    <input type="number" name="prep_time_min" value="{{ old('prep_time_min', 15) }}" class="input text-xs py-2" placeholder="15">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Max Prep Time (Mins)</label>
                                    <input type="number" name="prep_time_max" value="{{ old('prep_time_max', 25) }}" class="input text-xs py-2" placeholder="25">
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Wizard Stepper Footer -->
                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="button" @click="switchTab('pricing')" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-xl transition-all flex items-center gap-1.5 shadow-sm w-full sm:w-auto justify-center">
                            <span>Next: Pricing & Stock</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Pricing & Inventory Tab -->
                <div x-show="activeTab === 'pricing'" x-cloak class="space-y-6">
                    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200/80 space-y-5">
                        <div class="border-b border-gray-100 pb-3">
                            <h3 class="text-base font-bold text-gray-900">Pricing & Taxes</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Configure regular prices, discounts, cost price, and taxes.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Regular MRP Price <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">{{ $currencySymbol ?? '₹' }}</span>
                                    <input type="number" name="mrp" x-model="mrp" step="0.01" min="0" class="input pl-8 font-bold text-gray-900 py-2.5" placeholder="0.00" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Selling Price <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">{{ $currencySymbol ?? '₹' }}</span>
                                    <input type="number" name="price" x-model="price" step="0.01" min="0" class="input pl-8 font-bold text-emerald-700 py-2.5" placeholder="0.00" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Cost Price (Purchase)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">{{ $currencySymbol ?? '₹' }}</span>
                                    <input type="number" name="cost_price" step="0.01" min="0" class="input pl-8 text-gray-700 py-2.5" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tax Rate (%)</label>
                                <select name="tax_id" class="input text-sm py-2.5">
                                    <option value="">No Tax / Tax Exempt</option>
                                    @foreach(($taxes ?? []) as $tax)
                                    <option value="{{ $tax->id }}" {{ old('tax_id') == $tax->id ? 'selected' : '' }}>{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tax Type</label>
                                <select name="tax_type" class="input text-sm py-2.5">
                                    <option value="exclusive" {{ old('tax_type', 'exclusive') == 'exclusive' ? 'selected' : '' }}>Tax Exclusive (Added at checkout)</option>
                                    <option value="inclusive" {{ old('tax_type') == 'inclusive' ? 'selected' : '' }}>Tax Inclusive (Included in price)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200/80 space-y-5">
                        <div class="border-b border-gray-100 pb-3">
                            <h3 class="text-base font-bold text-gray-900">Inventory & Units</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Manage stock count, low stock alerts, unit types, and weight.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Stock Quantity</label>
                                <input type="number" name="quantity" value="{{ old('quantity', 100) }}" class="input text-sm font-semibold py-2.5" min="0" placeholder="100">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Low Stock Threshold Alert</label>
                                <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 5) }}" class="input text-sm py-2.5" min="0" placeholder="5">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Measurement Unit</label>
                                <select name="unit" class="input text-sm py-2.5">
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->short_name }}">{{ $unit->name }} ({{ $unit->short_name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Weight (Grams)</label>
                                <input type="number" name="weight" value="{{ old('weight') }}" class="input text-sm py-2.5" placeholder="e.g. 500" step="0.01">
                            </div>
                        </div>

                        <!-- Stepper Footer -->
                        <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-2 justify-between">
                            <button type="button" @click="switchTab('basic')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                                ← Back: Basic Info
                            </button>
                            <button type="button" @click="switchTab('variants')" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-xl transition-all flex items-center gap-1.5 justify-center">
                                <span>Next: Variants</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Variants Tab -->
                <div x-show="activeTab === 'variants'" x-cloak class="space-y-6">
                    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200/80">
                        @include('admin.products.partials._variant-builder-simple')

                        <!-- Stepper Footer -->
                        <div class="mt-6 pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-2 justify-between">
                            <button type="button" @click="switchTab('pricing')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                                ← Back: Pricing
                            </button>
                            <button type="button" @click="switchTab('media')" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-xl transition-all flex items-center gap-1.5 justify-center">
                                <span>Next: Media & Policies</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Media & Policies Tab -->
                <div x-show="activeTab === 'media'" x-cloak class="space-y-6">
                    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200/80 space-y-6">
                        @include('admin.products.partials._image-manager')

                        <!-- Product Video Engine -->
                        <div class="pt-4 border-t border-gray-100 space-y-4">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span>Product Video</span>
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Video Type</label>
                                    <select name="video_type" class="input text-xs py-2.5">
                                        <option value="youtube" {{ old('video_type') == 'youtube' ? 'selected' : '' }}>YouTube Link</option>
                                        <option value="vimeo" {{ old('video_type') == 'vimeo' ? 'selected' : '' }}>Vimeo Link</option>
                                        <option value="url" {{ old('video_type') == 'url' ? 'selected' : '' }}>Direct MP4 Link</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Video URL</label>
                                    <input type="text" name="video_url" value="{{ old('video_url') }}" class="input text-xs py-2.5" placeholder="e.g. https://www.youtube.com/watch?v=xyz">
                                </div>
                            </div>
                        </div>

                        <!-- Policy Rules -->
                        <div class="pt-4 border-t border-gray-100 space-y-4">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Return & Warranty Policies</span>
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Return Days</label>
                                    <input type="number" name="return_period_days" value="{{ old('return_period_days', 7) }}" class="input text-xs py-2.5" placeholder="7">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Replacement Days</label>
                                    <input type="number" name="replacement_period_days" value="{{ old('replacement_period_days', 7) }}" class="input text-xs py-2.5" placeholder="7">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Lead Hours</label>
                                    <input type="number" name="delivered_by_lead_hours" value="{{ old('delivered_by_lead_hours', 24) }}" class="input text-xs py-2.5" placeholder="24">
                                </div>
                            </div>
                        </div>

                        <!-- Stepper Footer -->
                        <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-2 justify-between">
                            <button type="button" @click="switchTab('variants')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                                ← Back: Variants
                            </button>
                            <button type="button" @click="switchTab('organization')" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-xl transition-all flex items-center gap-1.5 justify-center">
                                <span>Next: Organization</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Organization Tab -->
                <div x-show="activeTab === 'organization'" x-cloak class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200/80 space-y-5">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-base font-bold text-gray-900">Category & Store Assignment</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Assign product to category levels, brand, and seller/store.</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Dynamic Categories -->
                        <div class="space-y-3">
                            <template x-for="(catLevel, index) in categoryLevels" :key="index">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5" x-text="index === 0 ? 'Main Category *' : 'Sub Category Level ' + (index + 1)"></label>
                                    <select 
                                        x-model="catLevel.selected" 
                                        @change="handleCategoryChange(index)" 
                                        class="input text-sm py-2.5 cursor-pointer"
                                        :required="index === 0"
                                    >
                                        <option value="">Select Category</option>
                                        <template x-for="category in catLevel.list" :key="category.id">
                                            <option :value="category.id" x-text="category.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            @error('category_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Brand -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Brand</label>
                            <select name="brand_id" class="input text-sm py-2.5">
                                <option value="">Select Brand</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Store/Seller Assignment -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Assigned Store / Seller @if(request('type') === 'seller')<span class="text-red-500">*</span>@endif</label>
                            @if(request('type') === 'inhouse')
                                <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-600 font-semibold italic">
                                    In-house Product (Managed directly by Admin)
                                </div>
                                <input type="hidden" name="store_id" value="">
                            @else
                                <select name="store_id" class="input text-sm py-2.5" {{ request('type') === 'seller' ? 'required' : '' }}>
                                    <option value="">Select Store</option>
                                    @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>

                    <!-- Stepper Footer -->
                    <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-2 justify-between">
                        <button type="button" @click="switchTab('media')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                            ← Back: Media
                        </button>
                        <button type="button" @click="switchTab('seo')" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-xl transition-all flex items-center gap-1.5 justify-center">
                            <span>Next: SEO & Metadata</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- SEO Tab -->
                <div x-show="activeTab === 'seo'" x-cloak class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200/80 space-y-5">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-base font-bold text-gray-900">SEO & Social Sharing</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Optimize product visibility on Google and social media cards.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title') }}" class="input text-sm py-2.5" placeholder="Product Name - Brand | Store Name">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Meta Description</label>
                            <textarea name="meta_description" rows="3" class="input text-sm" placeholder="Brief SEO meta snippet for Google search results (150-160 chars)...">{{ old('meta_description') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">URL Slug</label>
                            <input type="text" name="slug" value="{{ old('slug') }}" class="input text-sm font-mono py-2.5" placeholder="organic-whole-milk-1l">
                        </div>
                    </div>

                    <!-- Stepper Footer -->
                    <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-2 justify-between">
                        <button type="button" @click="switchTab('organization')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                            ← Back: Organization
                        </button>
                        <button type="submit" form="product-form" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Save & Publish Product</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Sticky Right Sidebar Column -->
            <div class="space-y-6 lg:sticky lg:top-6">
                <!-- Status Card -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-200/80 space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3">Visibility & Status</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded text-orange-600 focus:ring-orange-500 mt-0.5 w-4 h-4">
                            <div>
                                <p class="font-bold text-gray-900 text-xs">Active & Published</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">Visible to customers in storefront</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" name="is_featured" value="1" class="rounded text-orange-600 focus:ring-orange-500 mt-0.5 w-4 h-4">
                            <div>
                                <p class="font-bold text-gray-900 text-xs">Featured Product</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">Highlight on homepage & featured collections</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Realtime Preview Summary Card -->
                <div class="bg-gradient-to-br from-slate-900 to-indigo-950 rounded-2xl p-5 text-white shadow-md border border-indigo-900/40 space-y-4">
                    <h3 class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Live Preview Card</h3>
                    
                    <div class="space-y-2">
                        <p class="text-base font-extrabold text-white truncate" x-text="productName || 'New Product Name'"></p>
                        <div class="flex items-center gap-2 text-xs text-slate-300 font-mono">
                            <span>SKU: <strong class="text-amber-400" x-text="sku || 'PRD-001'"></strong></span>
                        </div>
                        <div class="pt-2 flex items-baseline gap-2 border-t border-slate-800">
                            <span class="text-xl font-black text-emerald-400" x-text="'{{ $currencySymbol ?? '₹' }}' + (price || '0.00')"></span>
                            <span class="text-xs text-slate-400 line-through" x-show="mrp" x-text="'{{ $currencySymbol ?? '₹' }}' + mrp"></span>
                        </div>
                    </div>

                    <button type="submit" form="product-form" class="w-full py-3.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold rounded-xl shadow-lg transition-all text-xs uppercase tracking-wider flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save & Publish</span>
                    </button>
                </div>
            </div>

        </div>
    </form>

    <!-- Mobile Floating Bottom Bar (Always Accessible on Phone Screens) -->
    <div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-gray-200 p-3 shadow-xl sm:hidden flex items-center justify-between gap-2">
        <div class="flex items-center gap-1.5">
            <span class="px-2.5 py-1 bg-orange-100 text-orange-800 font-extrabold rounded-lg text-[10px] uppercase tracking-wider truncate max-w-[110px]" x-text="activeTab"></span>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" form="product-form" class="px-4 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save Product</span>
            </button>
        </div>
    </div>

</div>

@include('admin.products.partials._ai-assistant')

<script>
function productForm() {
    return {
        activeTab: 'basic',
        productName: '{{ old('name') }}',
        sku: '{{ old('sku') }}',
        barcode: '{{ old('barcode') }}',
        mrp: '{{ old('mrp') }}',
        price: '{{ old('price') }}',

        // Category logic
        categoryLevels: [],
        finalCategoryId: '',
        
        switchTab(tab) {
            this.activeTab = tab;
            // Smooth scroll to top of form on mobile tab change
            const formEl = document.getElementById('product-form');
            if (formEl && window.innerWidth < 768) {
                const y = formEl.getBoundingClientRect().top + window.pageYOffset - 70;
                window.scrollTo({ top: y, behavior: 'smooth' });
            }
        },

        async init() {
            if (!this.sku) {
                this.generateSku();
            }
            if (!this.barcode) {
                this.generateBarcode();
            }

            await this.loadInitialRoots();

            // Auto switch tab if validation fails on form submit
            window.addEventListener('invalid', (e) => {
                const invalidEl = e.target;
                const tabPane = invalidEl.closest('[x-show*="activeTab"]');
                if (tabPane) {
                    const tabMatch = tabPane.getAttribute('x-show').match(/'([^']+)'/);
                    if (tabMatch && tabMatch[1]) {
                        this.switchTab(tabMatch[1]);
                    }
                }
            }, true);
        },

        async loadInitialRoots() {
            try {
                const response = await fetch('{{ route('api.categories.roots') }}');
                const roots = await response.json();
                this.categoryLevels.push({ selected: '', list: roots });
            } catch (error) {
                console.error('Failed to load root categories', error);
            }
        },

        async handleCategoryChange(level) {
            const selectedId = this.categoryLevels[level].selected;
            this.categoryLevels = this.categoryLevels.slice(0, level + 1);
            this.updateFinalCategoryId();

            if (selectedId) {
                try {
                    const response = await fetch(`/internal-api/categories/${selectedId}/subcategories`);
                    const subcategories = await response.json();
                    if (subcategories.length > 0) {
                        this.categoryLevels.push({ selected: '', list: subcategories });
                    }
                } catch (error) {
                    console.error('Failed to load subcategories', error);
                }
            }
        },

        updateFinalCategoryId() {
            let lastId = '';
            for (let i = this.categoryLevels.length - 1; i >= 0; i--) {
                if (this.categoryLevels[i].selected) {
                    lastId = this.categoryLevels[i].selected;
                    break;
                }
            }
            this.finalCategoryId = lastId;
        },

        generateBarcode() {
            let code = '890' + Math.floor(Math.random() * 1000000000).toString().padStart(9, '0');
            let sum = 0;
            for (let i = 0; i < 12; i++) {
                sum += parseInt(code[i]) * (i % 2 === 0 ? 1 : 3);
            }
            let checksum = (10 - (sum % 10)) % 10;
            this.barcode = code + checksum;
        },

        generateSku() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            let result = 'QK';
            for (let i = 0; i < 6; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            this.sku = result;
        }
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@endsection

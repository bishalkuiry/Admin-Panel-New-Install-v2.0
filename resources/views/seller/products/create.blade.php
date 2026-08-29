@extends('seller.layouts.app')

@section('title', 'Create New Product / Item')

@section('content')
<style>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
</style>

<div class="max-w-7xl mx-auto space-y-6 pb-20 sm:pb-12" x-data="{ 
    activeTab: 'info',
    productName: '{{ old('name') }}',
    price: '{{ old('price') }}',
    salePrice: '{{ old('sale_price') }}',
    sku: '{{ old('sku') }}',
    switchTab(tab) {
        this.activeTab = tab;
        const formEl = document.getElementById('product-create-form');
        if (formEl && window.innerWidth < 768) {
            const y = formEl.getBoundingClientRect().top + window.pageYOffset - 70;
            window.scrollTo({ top: y, behavior: 'smooth' });
        }
    },
    ...productForm() 
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-gray-200 shadow-xs">
        <div class="flex items-center gap-3.5">
            <a href="{{ route('seller.products.index') }}" class="p-2.5 hover:bg-gray-100 rounded-xl transition-colors text-gray-500 hover:text-gray-900 border border-gray-200 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">Add New Item / Product</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Add a new item to your store catalog with images, pricing, and variants.</p>
            </div>
        </div>
        <div class="flex items-center gap-2.5 self-end sm:self-auto w-full sm:w-auto justify-end">
            <a href="{{ route('seller.products.index') }}" class="btn-secondary px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl">Cancel</a>
            <button type="submit" form="product-create-form" class="btn-primary px-5 py-2.5 text-xs sm:text-sm font-bold rounded-xl shadow-md flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save Item</span>
            </button>
        </div>
    </div>

    <!-- Scrollable Tab Bar -->
    <div class="bg-white rounded-2xl p-1.5 border border-gray-200 shadow-xs sticky top-2 z-20">
        <div class="scrollbar-hide flex items-center gap-1.5 sm:gap-2 overflow-x-auto py-0.5 px-1" style="-webkit-overflow-scrolling: touch;">
            <button type="button" @click="switchTab('info')" 
                    :class="activeTab === 'info' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-transparent'"
                    class="flex-shrink-0 px-4 py-2.5 rounded-xl border text-xs font-semibold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Product Info</span>
            </button>

            <button type="button" @click="switchTab('pricing')" 
                    :class="activeTab === 'pricing' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-transparent'"
                    class="flex-shrink-0 px-4 py-2.5 rounded-xl border text-xs font-semibold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Pricing & Stock</span>
            </button>

            <button type="button" @click="switchTab('media')" 
                    :class="activeTab === 'media' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-transparent'"
                    class="flex-shrink-0 px-4 py-2.5 rounded-xl border text-xs font-semibold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Product Media</span>
            </button>

            <button type="button" @click="switchTab('variants')" 
                    :class="activeTab === 'variants' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-transparent'"
                    class="flex-shrink-0 px-4 py-2.5 rounded-xl border text-xs font-semibold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span>Variants</span>
            </button>
        </div>
    </div>

    <form id="product-create-form" action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        @csrf
        <input type="hidden" name="category_id" x-model="finalCategoryId">

        <!-- Left Column (Tab Content) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Tab: Product Info -->
            <div x-show="activeTab === 'info'" x-cloak class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200/80 space-y-5">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900">Product Information</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Basic details and descriptions for customer display.</p>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Product Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="productName" value="{{ old('name') }}" required placeholder="e.g. Fresh Farm Butter 500g" class="input text-sm font-medium py-2.5" autofocus>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">SKU <span class="text-rose-500">*</span></label>
                            <input type="text" name="sku" x-model="sku" value="{{ old('sku') }}" required placeholder="e.g. FFB-500" class="input text-sm font-mono tracking-wider uppercase py-2.5">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Short Description</label>
                        <textarea name="short_description" rows="2" placeholder="Brief summary for list views..." class="input text-sm">{{ old('short_description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Detailed Description</label>
                        <textarea name="description" rows="4" placeholder="Comprehensive product details, ingredients, usage..." class="input text-sm">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Stepper Footer -->
                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="button" @click="switchTab('pricing')" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-sm w-full sm:w-auto">
                        <span>Next: Pricing & Stock</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- Tab: Pricing & Stock -->
            <div x-show="activeTab === 'pricing'" x-cloak class="space-y-6">
                <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200/80 space-y-5">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-base font-bold text-gray-900">Pricing & Commercials</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Configure regular prices and promotional prices.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Base Price ({{ $currencySymbol }}) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">{{ $currencySymbol }}</span>
                                <input type="number" step="0.01" name="price" x-model="price" value="{{ old('price') }}" required placeholder="0.00" class="input pl-8 font-bold text-gray-900 py-2.5">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Promotional / Sale Price ({{ $currencySymbol }})</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">{{ $currencySymbol }}</span>
                                <input type="number" step="0.01" name="sale_price" x-model="salePrice" value="{{ old('sale_price') }}" placeholder="0.00" class="input pl-8 font-bold text-emerald-700 py-2.5">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200/80 space-y-5">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-base font-bold text-gray-900">Stock Inventory</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Manage stock count and low stock alerts.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Initial Stock Quantity <span class="text-rose-500">*</span></label>
                            <input type="number" name="quantity" value="{{ old('quantity', 10) }}" required class="input font-bold text-sm py-2.5" placeholder="10">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Low Stock Alert Threshold</label>
                            <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 5) }}" required class="input text-sm py-2.5" placeholder="5">
                        </div>
                    </div>

                    <!-- Stepper Footer -->
                    <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-2 justify-between">
                        <button type="button" @click="switchTab('info')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                            ← Back: Product Info
                        </button>
                        <button type="button" @click="switchTab('media')" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-1.5">
                            <span>Next: Media</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab: Media -->
            <div x-show="activeTab === 'media'" x-cloak class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200/80 space-y-5">
                <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Product Gallery</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Upload high resolution product images.</p>
                    </div>
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-full border border-indigo-100" x-text="images.length + ' Photos Uploaded'"></span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4" x-show="images.length > 0">
                    <template x-for="(image, index) in images" :key="index">
                        <div class="group relative aspect-square border border-gray-200 rounded-2xl bg-gray-50 overflow-hidden shadow-xs">
                            <img :src="image.url" class="w-full h-full object-cover">
                            <div class="absolute inset-x-0 bottom-0 bg-slate-900/60 p-1 text-[9px] text-white text-center font-bold uppercase tracking-wider" x-text="index === 0 ? 'Primary Cover' : 'Gallery Image'"></div>
                            <button type="button" @click="removeImage(index)" class="absolute top-1.5 right-1.5 w-6 h-6 bg-rose-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-md">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="relative border-2 border-dashed border-gray-200 hover:border-indigo-400 rounded-2xl p-8 hover:bg-indigo-50/20 transition-all text-center group cursor-pointer">
                    <input type="file" name="images[]" @change="handleFileUpload" multiple accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                    <div class="space-y-2">
                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto text-indigo-600 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-xs font-bold text-gray-900 uppercase tracking-wider">Click or Drag Product Photos Here</p>
                        <p class="text-[10px] text-gray-400 font-medium">JPG, PNG, WebP supported. Recommended: 800x800px</p>
                    </div>
                </div>

                <!-- Stepper Footer -->
                <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-2 justify-between">
                    <button type="button" @click="switchTab('pricing')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                        ← Back: Pricing
                    </button>
                    <button type="button" @click="switchTab('variants')" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-1.5">
                        <span>Next: Variants</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- Tab: Variants -->
            <div x-show="activeTab === 'variants'" x-cloak class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-gray-200/80 space-y-5">
                @include('seller.products.partials._variant-builder')

                <!-- Stepper Footer -->
                <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row gap-2 justify-between">
                    <button type="button" @click="switchTab('media')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                        ← Back: Media
                    </button>
                    <button type="submit" form="product-create-form" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Finalize & Create Item</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- Right Column: Sidebar -->
        <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-6">
            
            <!-- Category & Brand Card -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-200/80 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3">Category & Attributes</h3>

                <div class="space-y-4">
                    <template x-for="(catLevel, index) in categoryLevels" :key="index">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5" x-text="index === 0 ? 'Main Category *' : 'Sub Category'"></label>
                            <select x-model="catLevel.selected" @change="handleCategoryChange(index)" class="input text-xs font-semibold cursor-pointer py-2.5" :required="index === 0">
                                <option value="">Select Category</option>
                                <template x-for="category in catLevel.list" :key="category.id">
                                    <option :value="category.id" x-text="category.name"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Brand</label>
                        <select name="brand_id" class="input text-xs font-semibold py-2.5">
                            <option value="">No Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Measurement Unit <span class="text-rose-500">*</span></label>
                        <select name="unit_id" required class="input text-xs font-semibold py-2.5">
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->short_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Operations & Live Preview Card -->
            <div class="bg-gradient-to-br from-slate-900 to-indigo-950 rounded-2xl p-5 text-white shadow-md border border-indigo-900/40 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <span class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Item Status</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                </div>

                <div class="space-y-2">
                    <p class="text-base font-extrabold text-white truncate" x-text="productName || 'New Product Name'"></p>
                    <div class="flex items-center gap-2 text-xs text-slate-300 font-mono">
                        <span>SKU: <strong class="text-amber-400" x-text="sku || 'SKU-001'"></strong></span>
                    </div>
                    <div class="pt-2 flex items-baseline gap-2 border-t border-slate-800">
                        <span class="text-xl font-black text-emerald-400" x-text="'{{ $currencySymbol }}' + (price || '0.00')"></span>
                        <span class="text-xs text-slate-400 line-through" x-show="salePrice" x-text="'{{ $currencySymbol }}' + salePrice"></span>
                    </div>
                </div>

                <button type="submit" form="product-create-form" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl shadow-lg transition-all text-xs uppercase tracking-wider flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Create & Publish Item</span>
                </button>
            </div>

        </div>
    </form>

    <!-- Mobile Floating Bottom Action Bar -->
    <div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-gray-200 p-3 shadow-xl sm:hidden flex items-center justify-between gap-2">
        <div class="flex items-center gap-1.5">
            <span class="px-2.5 py-1 bg-indigo-100 text-indigo-800 font-extrabold rounded-lg text-[10px] uppercase tracking-wider truncate max-w-[110px]" x-text="activeTab"></span>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" form="product-create-form" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save Item</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function productForm() {
    return {
        images: [],
        handleFileUpload(event) {
            const files = Array.from(event.target.files);
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.images.push({ url: e.target.result, file: file });
                };
                reader.readAsDataURL(file);
            });
        },
        removeImage(index) { this.images.splice(index, 1); },
        categoryLevels: [],
        finalCategoryId: '',
        async init() {
            try {
                const response = await fetch('{{ route('api.categories.roots') }}');
                const roots = await response.json();
                this.categoryLevels.push({ selected: '', list: roots.map(c => ({id: String(c.id), name: c.name})) });
            } catch (error) { console.error('Failed to load root categories', error); }
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
                        this.categoryLevels.push({ selected: '', list: subcategories.map(c => ({id: String(c.id), name: c.name})) });
                    }
                } catch (error) { console.error('Failed to load subcategories', error); }
            }
        },
        updateFinalCategoryId() {
            let lastId = '';
            for (let i = this.categoryLevels.length - 1; i >= 0; i--) {
                if (this.categoryLevels[i].selected) { lastId = this.categoryLevels[i].selected; break; }
            }
            this.finalCategoryId = lastId;
        }
    };
}
</script>
@endpush
@endsection

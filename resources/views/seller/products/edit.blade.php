@extends('seller.layouts.app')

@section('title', 'Edit Product: ' . $product->name)

@section('content')
<div class="max-w-7xl mx-auto" x-data="{ 
    activeTab: 'info',
    ...productForm({{ json_encode($product->images->map(fn($img) => ['url' => storage_url($img->image)])) }})
}">
    <!-- Compact Page Header -->
    <div class="mb-6 border-b border-gray-100 pb-6">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <a href="{{ route('seller.products.index') }}" class="group w-8 h-8 rounded border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                    <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 leading-tight">Edit Product</h1>
                    <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Modify product details and stock</p>
                </div>
            </div>
            <div>
                <button type="submit" form="product-edit-form" class="inline-flex items-center px-8 py-2 bg-indigo-600 border border-transparent rounded text-[10px] font-black text-white hover:bg-indigo-700 uppercase tracking-widest transition-all shadow-sm">
                    Update
                </button>
            </div>
        </div>

        <!-- Horizontal Tabs -->
        <div class="flex items-center gap-1 bg-gray-100/50 p-1 rounded-lg border border-gray-200 w-fit">
            <button type="button" @click="activeTab = 'info'" :class="activeTab === 'info' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2 text-[10px] font-black uppercase tracking-widest rounded transition-all">
                Product Info
            </button>
            <button type="button" @click="activeTab = 'pricing'" :class="activeTab === 'pricing' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2 text-[10px] font-black uppercase tracking-widest rounded transition-all">
                Inventory/Price
            </button>
            <button type="button" @click="activeTab = 'media'" :class="activeTab === 'media' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2 text-[10px] font-black uppercase tracking-widest rounded transition-all">
                Product Media
            </button>
            <button type="button" @click="activeTab = 'variants'" :class="activeTab === 'variants' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2 text-[10px] font-black uppercase tracking-widest rounded transition-all">
                Variants
            </button>
        </div>
    </div>

    <form id="product-edit-form" action="{{ route('seller.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="category_id" x-model="finalCategoryId">

        <!-- Left Column: Primary Data -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Tab: Product Info -->
            <div x-show="activeTab === 'info'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-200">
                        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Product Info</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Product Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">SKU <span class="text-rose-500">*</span></label>
                                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="block w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded text-sm font-mono tracking-tighter cursor-not-allowed" readonly>
                                <p class="text-[9px] text-gray-400 italic">SKU is unique and cannot be changed.</p>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Short Summary</label>
                            <textarea name="short_description" rows="2" class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('short_description', $product->short_description) }}</textarea>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Product Description</label>
                            <textarea name="description" rows="5" class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Pricing & Inventory -->
            <div x-show="activeTab === 'pricing'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Commercials -->
                <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                    <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-[10px] font-black text-gray-700 uppercase tracking-widest">Pricing</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Regular Price ({{ $currencySymbol }})</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-xs font-bold">{{ $currencySymbol }}</div>
                                <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="block w-full pl-7 pr-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 font-bold">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Discounted Price</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-xs font-bold">{{ $currencySymbol }}</div>
                                <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="block w-full pl-7 pr-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 font-bold text-emerald-700">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock -->
                <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                    <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-[10px] font-black text-gray-700 uppercase tracking-widest">Inventory</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Current Stock Level</label>
                            <input type="number" name="quantity" value="{{ old('quantity', $product->quantity) }}" class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 font-bold">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Alert Threshold</label>
                            <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" class="block w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Media Management -->
            <div x-show="activeTab === 'media'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Product Gallery</h3>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest" x-text="images.length + ' Photos'"></span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-3 sm:grid-cols-5 gap-4 mb-6">
                            <template x-for="(image, index) in images" :key="index">
                                <div class="group relative aspect-square border border-gray-200 rounded bg-gray-50 overflow-hidden">
                                    <img :src="image.url" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all">
                                    <div class="absolute inset-x-0 bottom-0 bg-gray-900/40 p-1 text-[8px] text-white text-center font-bold tracking-tighter" x-text="index === 0 ? 'PRIMARY' : 'GALLERY'"></div>
                                    <button type="button" @click="removeImage(index)" class="absolute top-1 right-1 w-5 h-5 bg-rose-600 text-white rounded flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                            <label class="aspect-square border-2 border-dashed border-gray-200 rounded flex flex-col items-center justify-center cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="file" name="images[]" @change="handleFileUpload" multiple accept="image/*" class="sr-only">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Variants -->
            <div x-show="activeTab === 'variants'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden p-6">
                    @include('seller.products.partials._variant-builder')
                </div>
            </div>
        </div>

        <!-- Right Column: Metadata -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Category & Classification -->
            <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Category Selection</h3>
                </div>
                <div class="p-5 space-y-5">
                    <template x-for="(catLevel, index) in categoryLevels" :key="index">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase" x-text="index === 0 ? 'Root Category' : 'Sub Category'"></label>
                            <select x-model="catLevel.selected" @change="handleCategoryChange(index)" class="block w-full px-3 py-2 border border-gray-300 rounded text-xs font-bold text-gray-700 focus:ring-indigo-500 shadow-sm" :required="index === 0">
                                <option value="">Select Level</option>
                                <template x-for="category in catLevel.list" :key="category.id">
                                    <option :value="category.id" x-text="category.name"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <div class="space-y-1.5 pt-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase">Select Brand</label>
                        <select name="brand_id" class="block w-full px-3 py-2 border border-gray-300 rounded text-xs font-bold text-gray-700">
                            <option value="">No Brand Selected</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase">Select Unit</label>
                        <select name="unit_id" required class="block w-full px-3 py-2 border border-gray-300 rounded text-xs font-bold text-gray-700">
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->short_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Operations Controller -->
            <div class="bg-white border border-gray-200 rounded-sm shadow-sm p-5 space-y-6">
                <div class="flex items-center justify-between">
                    <div class="space-y-0.5">
                        <label class="text-xs font-bold text-gray-900 uppercase tracking-tight">Status</label>
                        <p class="text-[10px] text-gray-400">Visibility in store catalog</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>
                
                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" form="product-edit-form" class="w-full bg-gray-900 text-white rounded py-3 text-xs font-black uppercase tracking-[0.2em] hover:bg-black transition-all shadow-lg active:scale-95">
                        Update
                    </button>
                    <p class="text-center text-[9px] text-gray-400 mt-3 font-medium">Last Modified: {{ $product->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
function productForm(existingImages = []) {
    return {
        images: existingImages,
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
        finalCategoryId: '{{ $product->category_id }}',
        currentCategoryId: '{{ $product->category_id }}',
        async init() {
            if (this.currentCategoryId) { await this.loadCategoryChain(this.currentCategoryId); } 
            else { await this.loadInitialRoots(); }
        },
        async loadInitialRoots() {
            try {
                const response = await fetch('{{ route('api.categories.roots') }}');
                const roots = await response.json();
                this.categoryLevels.push({ selected: '', list: roots.map(c => ({id: String(c.id), name: c.name})) });
            } catch (error) { console.error('Failed to load root categories', error); }
        },
        async loadCategoryChain(categoryId) {
            try {
                const response = await fetch(`/internal-api/categories/chain/${categoryId}`);
                const data = await response.json();
                this.categoryLevels = data.map(level => ({ selected: '', list: level.list.map(c => ({id: String(c.id), name: c.name})) }));
                await new Promise(resolve => setTimeout(resolve, 100));
                data.forEach((level, index) => { if (this.categoryLevels[index]) { this.categoryLevels[index].selected = String(level.selected); } });
                this.$nextTick(() => { this.updateFinalCategoryId(); });
            } catch (error) { console.error('Failed to load category chain', error); await this.loadInitialRoots(); }
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

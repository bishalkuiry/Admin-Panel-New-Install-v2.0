@extends('admin.layouts.app')
@section('title', 'Edit Commission Rule')
@section('content')

<form action="{{ route('admin.commissions.update', $commission) }}" method="POST" x-data="commissionForm()" class="space-y-6">
    @csrf
    @method('PUT')

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.commissions.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-500 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-wide">Edit Commission Rule</h1>
                <p class="text-sm text-gray-500 mt-1">Modify <span class="font-semibold text-gray-900">{{ $commission->name }}</span></p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.commissions.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-bold uppercase tracking-wider transition-all shadow-sm">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-wider hover:bg-black transition-all shadow-md active:scale-[0.98] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save Changes
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Core Configuration -->
        <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6 h-fit">
            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Commission Configuration</h3>
            
            <div class="space-y-5">
                {{-- Commission Type (Store or Delivery Partner) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Commission For</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer" :class="appliesTo === 'store' ? 'ring-2 ring-purple-500' : ''" @click="appliesTo = 'store'">
                            <input type="radio" name="applies_to" value="store" x-model="appliesTo" class="sr-only">
                            <div class="flex items-center gap-3 p-4 border rounded-xl transition-all" :class="appliesTo === 'store' ? 'border-purple-300 bg-purple-50' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="appliesTo === 'store' ? 'bg-purple-200' : 'bg-gray-100'">
                                    <svg class="w-5 h-5" :class="appliesTo === 'store' ? 'text-purple-700' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm" :class="appliesTo === 'store' ? 'text-purple-900' : 'text-gray-700'">Store</p>
                                    <p class="text-xs" :class="appliesTo === 'store' ? 'text-purple-600' : 'text-gray-400'">From store orders</p>
                                </div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer" :class="appliesTo === 'delivery_partner' ? 'ring-2 ring-orange-500' : ''" @click="appliesTo = 'delivery_partner'">
                            <input type="radio" name="applies_to" value="delivery_partner" x-model="appliesTo" class="sr-only">
                            <div class="flex items-center gap-3 p-4 border rounded-xl transition-all" :class="appliesTo === 'delivery_partner' ? 'border-orange-300 bg-orange-50' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="appliesTo === 'delivery_partner' ? 'bg-orange-200' : 'bg-gray-100'">
                                    <svg class="w-5 h-5" :class="appliesTo === 'delivery_partner' ? 'text-orange-700' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm" :class="appliesTo === 'delivery_partner' ? 'text-orange-900' : 'text-gray-700'">Delivery Partner</p>
                                    <p class="text-xs" :class="appliesTo === 'delivery_partner' ? 'text-orange-600' : 'text-gray-400'">From deliveries</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rule Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $commission->name) }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Calculation Type & Value --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Calculation <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="calculation_type" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer" x-model="calcType" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount ({{ $currencySymbol }})</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Value <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-sm" x-text="calcType === 'percentage' ? '%' : '{{ $currencySymbol }}'"></span>
                            <input type="number" name="value" value="{{ number_format(old('value', $commission->value), $priceDecimals, '.', '') }}" class="w-full px-8 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold" step="{{ $priceStep }}" min="0" required>
                        </div>
                        @error('value')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Applicability Rules -->
        <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6 h-fit">
            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Scope</h3>

            {{-- Store-specific --}}
            <div x-show="appliesTo === 'store'" x-transition class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Apply to Store</label>
                    <div class="relative">
                        <select name="store_id" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer" x-model="selectedStoreId" @change="loadCategories()">
                            <option value="">All Stores</option>
                            <template x-for="store in storesList" :key="store.id">
                                <option :value="store.id" x-text="store.name" :selected="store.id == selectedStoreId"></option>
                            </template>
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Category (optional)</label>
                    <div class="relative">
                        <select name="store_category_id" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer" x-model="selectedCategoryId" :disabled="!selectedStoreId || loadingCategories">
                            <option value="">
                                <span x-text="!selectedStoreId ? 'Select a store first' : (loadingCategories ? 'Loading...' : 'All categories')"></span>
                            </option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name" :selected="cat.id == selectedCategoryId"></option>
                            </template>
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Leave empty to apply commission to all products.</p>
                </div>
            </div>

            {{-- Delivery Partner --}}
            <div x-show="appliesTo === 'delivery_partner'" x-transition class="space-y-4">
                <div class="p-4 bg-gray-50 rounded-xl border border-dashed border-gray-200 text-center">
                    <p class="text-xs text-gray-500">Partner commissions apply globally to all delivery partners.</p>
                </div>
            </div>

            <input type="hidden" name="is_active" value="{{ $commission->is_active ? 1 : 0 }}">
        </div>
    </div>
</form>

<script>
function commissionForm() {
    return {
        appliesTo: '{{ $commission->applies_to }}',
        calcType: '{{ $commission->calculation_type }}',
        selectedStoreId: '{{ $commission->store_id ?? '' }}',
        selectedCategoryId: '{{ $commission->store_category_id ?? '' }}',
        storesList: @json($stores),
        categories: [],
        loadingCategories: false,

        init() {
            if (this.selectedStoreId) {
                this.loadCategories();
            }
        },

        loadCategories() {
            if (!this.selectedStoreId) {
                this.categories = [];
                return;
            }
            this.loadingCategories = true;
            this.categories = [];
            fetch(`{{ url('admin/commissions-stores') }}/${this.selectedStoreId}/categories`)
                .then(r => r.json())
                .then(data => { this.categories = data; })
                .finally(() => { this.loadingCategories = false; });
        },
    };
}
</script>
@endsection

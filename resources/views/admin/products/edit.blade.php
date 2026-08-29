@extends('admin.layouts.app')
@section('title', 'Edit Product')
@section('content')
<div x-data="productForm()" x-on:switch-tab.window="activeTab = $event.detail" class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ $product->store_id ? route('admin.products.seller') : route('admin.products.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Edit {{ $product->store_id ? 'Seller' : 'In-house' }} Product</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $product->name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="badge {{ $product->is_active ? 'badge-green' : 'badge-gray' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
            <button type="button" onclick="if(confirm('Are you sure you want to delete this product? This action cannot be undone.')) document.getElementById('delete-form').submit()" class="btn-secondary text-red-600 hover:text-red-700 hover:bg-red-50">Delete</button>
            <button type="submit" form="product-form" class="btn-primary">Update Product</button>
        </div>
    </div>

    <form id="product-form" action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        
        <!-- Hidden input for final category selection -->
        <input type="hidden" name="category_id" x-model="finalCategoryId">

        <!-- Tabs -->
        <div class="flex gap-2 border-b border-gray-200 bg-white rounded-t-xl px-6">
            <button type="button" @click="activeTab = 'basic'" :class="activeTab === 'basic' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium transition-colors">Basic Info</button>
            <button type="button" @click="activeTab = 'pricing'" :class="activeTab === 'pricing' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium transition-colors">Pricing & Inventory</button>
            <button type="button" @click="activeTab = 'variants'" :class="activeTab === 'variants' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium transition-colors">Variants</button>
            <button type="button" @click="activeTab = 'media'" :class="activeTab === 'media' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium transition-colors">Media</button>
            <button type="button" @click="activeTab = 'organization'" :class="activeTab === 'organization' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium transition-colors">Organization</button>
            <button type="button" @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium transition-colors">SEO</button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Basic Info Tab -->
                <div x-show="activeTab === 'basic'" class="card">
                    <div class="card-header">
                        <h3 class="card-title">Basic Information</h3>
                        <p class="text-sm text-gray-500 mt-1">Essential product details</p>
                    </div>
                    <div class="card-body space-y-5">
                        <div class="flex items-end gap-3">
                            <div class="flex-1">
                                <label class="label">Product Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="product_name" value="{{ old('name', $product->name) }}" class="input" required placeholder="e.g., Fresh Organic Tomatoes">
                            </div>
                            <button type="button" @click="window.dispatchEvent(new CustomEvent('open-ai-assistant', { detail: { title: document.getElementById('product_name').value } }))" class="btn-secondary h-10 px-4 mb-[2px] border-orange-200 text-orange-600 hover:bg-orange-50 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L14.85 9.15L22 12L14.85 14.85L12 22L9.15 14.85L2 12L9.15 9.15L12 2Z"/></svg>
                                AI Assistant
                            </button>
                        </div>
                        @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">SKU <span class="text-red-500">*</span></label>
                                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="input" required placeholder="PRD-001">
                                <p class="text-xs text-gray-500 mt-1">Unique product identifier</p>
                                @error('sku')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="label">Barcode</label>
                                <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="input" placeholder="8901234567890">
                                <p class="text-xs text-gray-500 mt-1">EAN-13 or UPC</p>
                            </div>
                        </div>

                        <div>
                            <label class="label">Short Description</label>
                            <textarea name="short_description" rows="3" class="input" placeholder="Brief product description for listings...">{{ old('short_description', $product->short_description) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Shown in product cards and search results</p>
                        </div>

                        <div>
                            <label class="label">Full Description</label>
                            <textarea name="description" rows="6" class="input" placeholder="Detailed product description, features, benefits...">{{ old('description', $product->description) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Detailed information shown on product page</p>
                        </div>

                        <!-- Dynamic Module Specific Fields -->
                        @if(($moduleType ?? 'grocery') === 'food')
                        <div class="p-4 bg-orange-50/60 rounded-xl border border-orange-100 space-y-4">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="label text-xs">Veg / Non-Veg Flag</label>
                                        <select name="is_veg" class="input text-xs">
                                            <option value="none" {{ old('is_veg', $product->is_veg === null ? 'none' : '') == 'none' ? 'selected' : '' }}>None (Not Applicable)</option>
                                            <option value="1" {{ old('is_veg', $product->is_veg) == '1' ? 'selected' : '' }}>Veg (Vegetarian)</option>
                                            <option value="0" {{ old('is_veg', $product->is_veg) === '0' || old('is_veg', $product->is_veg) === 0 ? 'selected' : '' }}>Non-Veg / Egg</option>
                                        </select>
                                    </div>
                                        <div>
                                            <label class="label text-xs">Min Prep Time</label>
                                            <input type="number" name="prep_time_min" value="{{ old('prep_time_min', $product->prep_time_min ?? 15) }}" class="input text-xs" placeholder="15">
                                        </div>
                                        <div>
                                            <label class="label text-xs">Max Prep Time & Unit</label>
                                            <div class="flex gap-2">
                                                <input type="number" name="prep_time_max" value="{{ old('prep_time_max', $product->prep_time_max ?? 25) }}" class="input text-xs flex-1" placeholder="25">
                                                <select name="prep_time_unit" class="input text-xs w-28">
                                                    <option value="minutes" {{ old('prep_time_unit', $product->prep_time_unit) == 'minutes' ? 'selected' : '' }}>Minutes</option>
                                                    <option value="hours" {{ old('prep_time_unit', $product->prep_time_unit) == 'hours' ? 'selected' : '' }}>Hours</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="label text-xs">Available Time Starts</label>
                                            <input type="time" name="available_time_starts" value="{{ old('available_time_starts', $product->available_time_starts ?? '09:00') }}" class="input text-xs">
                                        </div>
                                        <div>
                                            <label class="label text-xs">Available Time Ends</label>
                                            <input type="time" name="available_time_ends" value="{{ old('available_time_ends', $product->available_time_ends ?? '23:00') }}" class="input text-xs">
                                        </div>
                                        <div class="flex items-center justify-between p-2.5 bg-white rounded-xl border border-gray-200 mt-5">
                                            <span class="font-bold text-gray-900 text-xs">Halal Certified</span>
                                            <label class="toggle">
                                                <input type="checkbox" name="is_halal" value="1" {{ old('is_halal', $product->is_halal) ? 'checked' : '' }}>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="label text-xs">Nutrition Info</label>
                                            <input type="text" name="nutrition_info" value="{{ old('nutrition_info', $product->nutrition_info) }}" class="input text-xs" placeholder="e.g. 350 kcal, 18g Protein, 12g Fat">
                                        </div>
                                        <div>
                                            <label class="label text-xs">Search Tags</label>
                                            <input type="text" name="search_tags" value="{{ old('search_tags', $product->search_tags) }}" class="input text-xs" placeholder="e.g. burger, spicy, combo, fast food">
                                        </div>
                                    </div>

                                    <!-- Multi Food Variation System Builder -->
                                    @php
                                    $existingGroups = $product->foodVariationGroups->map(function($group) {
                                        return [
                                            'name' => $group->name,
                                            'is_required' => (bool)$group->is_required,
                                            'selection_type' => $group->selection_type,
                                            'min_selection' => $group->min_selection,
                                            'max_selection' => $group->max_selection,
                                            'options' => $group->options->map(function($opt) {
                                                return [
                                                    'option_name' => $opt->option_name,
                                                    'price' => (float)$opt->price,
                                                ];
                                            })->toArray(),
                                        ];
                                    })->toArray();
                                    @endphp
                                    <div x-data="{
                                        groups: {{ json_encode($existingGroups) }},
                                        addGroup() {
                                            this.groups.push({
                                                name: '',
                                                is_required: false,
                                                selection_type: 'single',
                                                min_selection: 0,
                                                max_selection: 1,
                                                options: [{ option_name: '', price: 0 }]
                                            });
                                        },
                                        removeGroup(index) {
                                            this.groups.splice(index, 1);
                                        },
                                        addOption(groupIndex) {
                                            this.groups[groupIndex].options.push({ option_name: '', price: 0 });
                                        },
                                        removeOption(groupIndex, optIndex) {
                                            this.groups[groupIndex].options.splice(optIndex, 1);
                                        }
                                    }" class="p-4 bg-white rounded-xl border border-orange-200 space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider">Multi Food Variation System</h4>
                                                <p class="text-[11px] text-gray-500">Manage customizable variation groups (e.g. Crusts, Toppings, Portion Size)</p>
                                            </div>
                                            <button type="button" @click="addGroup()" class="px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-xs font-semibold flex items-center gap-1 transition-colors">
                                                + Add Variation Group
                                            </button>
                                        </div>

                                        <template x-for="(group, gIndex) in groups" :key="gIndex">
                                            <div class="p-3 bg-orange-50/50 rounded-lg border border-orange-100 space-y-3">
                                                <div class="flex items-center justify-between gap-3">
                                                    <div class="flex-1">
                                                        <label class="label text-[11px] font-bold">Variation Group Name <span class="text-red-500">*</span></label>
                                                        <input type="text" :name="'food_variation_groups['+gIndex+'][name]'" x-model="group.name" class="input text-xs" placeholder="e.g. Choose Crust / Select Toppings">
                                                    </div>
                                                    <div class="w-32">
                                                        <label class="label text-[11px] font-bold">Selection Type</label>
                                                        <select :name="'food_variation_groups['+gIndex+'][selection_type]'" x-model="group.selection_type" class="input text-xs">
                                                            <option value="single">Single (Radio)</option>
                                                            <option value="multiple">Multiple (Checkbox)</option>
                                                        </select>
                                                    </div>
                                                    <div class="flex items-center gap-2 pt-5">
                                                        <label class="flex items-center gap-1.5 text-xs font-semibold cursor-pointer">
                                                            <input type="checkbox" :name="'food_variation_groups['+gIndex+'][is_required]'" value="1" x-model="group.is_required" class="rounded text-orange-600">
                                                            <span>Required</span>
                                                        </label>
                                                    </div>
                                                    <button type="button" @click="removeGroup(gIndex)" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg mt-5" title="Remove Group">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>

                                                <div class="grid grid-cols-2 gap-3" x-show="group.selection_type === 'multiple'">
                                                    <div>
                                                        <label class="label text-[10px]">Min Selection</label>
                                                        <input type="number" :name="'food_variation_groups['+gIndex+'][min_selection]'" x-model="group.min_selection" class="input text-xs" placeholder="0">
                                                    </div>
                                                    <div>
                                                        <label class="label text-[10px]">Max Selection</label>
                                                        <input type="number" :name="'food_variation_groups['+gIndex+'][max_selection]'" x-model="group.max_selection" class="input text-xs" placeholder="5">
                                                    </div>
                                                </div>

                                                <!-- Options List -->
                                                <div class="space-y-2 pt-1 border-t border-orange-200/60">
                                                    <div class="flex items-center justify-between text-[11px] font-bold text-gray-700">
                                                        <span>Variation Options</span>
                                                        <button type="button" @click="addOption(gIndex)" class="text-orange-600 hover:underline">+ Add Option</button>
                                                    </div>
                                                    <template x-for="(opt, oIndex) in group.options" :key="oIndex">
                                                        <div class="flex items-center gap-2">
                                                            <input type="text" :name="'food_variation_groups['+gIndex+'][options]['+oIndex+'][option_name]'" x-model="opt.option_name" class="input text-xs flex-1" placeholder="Option Name (e.g. Cheese Burst)">
                                                            <div class="w-32 relative">
                                                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">₹</span>
                                                                <input type="number" step="0.01" :name="'food_variation_groups['+gIndex+'][options]['+oIndex+'][price]'" x-model="opt.price" class="input text-xs pl-6" placeholder="0.00">
                                                            </div>
                                                            <button type="button" @click="removeOption(gIndex, oIndex)" class="p-1 text-gray-400 hover:text-red-500">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        <div x-show="groups.length === 0" class="p-4 text-center text-xs text-gray-400 border border-dashed border-gray-200 rounded-lg">
                                            No food variations added yet. Click "+ Add Variation Group" to add crusts, toppings, sizes etc.
                                        </div>
                                    </div>

                                    <!-- Food Add-ons Selection -->
                                    <div class="p-3 bg-white rounded-xl border border-gray-200 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="font-bold text-gray-900 text-xs">Select Food Add-ons (Drinks, Sauce, Toppings)</label>
                                            <a href="{{ route('admin.food-addons.index') }}" target="_blank" class="text-[11px] text-orange-600 font-semibold hover:underline">+ Manage Add-ons</a>
                                        </div>
                                        @php $selectedAddonIds = $product->foodAddons->pluck('id')->toArray(); @endphp
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-1">
                                            @foreach($foodAddons as $addon)
                                                <label class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg border border-gray-100 text-xs cursor-pointer hover:bg-orange-50">
                                                    <input type="checkbox" name="food_addon_ids[]" value="{{ $addon->id }}" {{ in_array($addon->id, $selectedAddonIds) ? 'checked' : '' }} class="rounded text-orange-600">
                                                    <span class="font-medium text-gray-800">{{ $addon->name }}</span>
                                                    <span class="text-gray-400 font-mono text-[10px] ml-auto">+{{ number_format($addon->price, 2) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="label text-xs">Generic / Chemical Name</label>
                                            <input type="text" name="generic_name" value="{{ old('generic_name', $product->generic_name) }}" class="input text-xs" placeholder="e.g. Paracetamol 500mg IP / Organic">
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-gray-200">
                                            <div>
                                                <p class="font-bold text-gray-900 text-xs">Prescription Required (Doctor Rx)</p>
                                                <p class="text-[10px] text-gray-500">Require customer Rx upload on checkout</p>
                                            </div>
                                            <label class="toggle">
                                                <input type="checkbox" name="is_prescription_required" value="1" {{ old('is_prescription_required', $product->is_prescription_required) ? 'checked' : '' }}>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                 </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Pricing & Inventory Tab -->
                <div x-show="activeTab === 'pricing'" class="space-y-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Pricing</h3>
                            <p class="text-sm text-gray-500 mt-1">Set product pricing</p>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-2 gap-5">
                                <div>
                                    <label class="label">Selling Price <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">{{ $currencySymbol }}</span>
                                        <input type="number" name="price" value="{{ old('price', number_format((float)$product->price, $priceDecimals, '.', '')) }}" step="{{ $priceStep }}" class="input pl-8" required placeholder="{{ number_format(0, $priceDecimals) }}">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Customer pays this price</p>
                                </div>
                                <div>
                                    <label class="label">MRP (Compare Price)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">{{ $currencySymbol }}</span>
                                        <input type="number" name="compare_price" value="{{ old('compare_price', $product->compare_price ? number_format((float)$product->compare_price, $priceDecimals, '.', '') : '') }}" step="{{ $priceStep }}" class="input pl-8" placeholder="{{ number_format(0, $priceDecimals) }}">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Original price (shown with strikethrough)</p>
                                </div>
                            </div>
                            @if($product->compare_price && $product->compare_price > $product->price)
                            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                <p class="text-sm text-green-800">
                                    <span class="font-semibold">{{ $product->discount_percentage }}% OFF</span> • 
                                    Customers save <x-currency :amount="$product->compare_price - $product->price" />
                                </p>
                            </div>
                            @endif

                            @if($product->store_id)
                            <div class="mt-5 border-t border-gray-100 pt-5">
                                <label class="label">Admin Commission (%)</label>
                                <div class="relative">
                                    <input type="number" name="commission" value="{{ old('commission', $product->commission) }}" step="0.01" min="0" max="100" class="input pr-8" placeholder="e.g. 10">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium">%</span>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-1.5 leading-relaxed">
                                    <span class="font-medium text-indigo-600">Hierarchy:</span> 
                                    This Product Rate <svg class="inline w-2.5 h-2.5 mx-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg> 
                                    Store Category Rate <svg class="inline w-2.5 h-2.5 mx-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg> 
                                    Global Commission
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Inventory</h3>
                            <p class="text-sm text-gray-500 mt-1">Manage stock levels</p>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-2 gap-5">
                                <div>
                                    <label class="label">Quantity in Stock</label>
                                    <input type="number" name="quantity" value="{{ old('quantity', $product->quantity) }}" class="input" min="0" placeholder="0">
                                    <p class="text-xs text-gray-500 mt-1">Available units</p>
                                    @if($product->isLowStock())
                                    <p class="text-xs text-orange-600 mt-1 font-medium">⚠️ Low stock alert</p>
                                    @endif
                                </div>
                                <div>
                                    <label class="label">Low Stock Alert</label>
                                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" class="input" min="0" placeholder="5">
                                    <p class="text-xs text-gray-500 mt-1">Alert when stock falls below</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-5 mt-5">
                                <div>
                                    <label class="label">Unit</label>
                                    <select name="unit" class="input">
                                        @foreach($units as $unit)
                                        <option value="{{ $unit->short_name }}" {{ $product->unit == $unit->short_name ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->short_name }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="label">Weight (grams)</label>
                                    <input type="number" name="weight" value="{{ old('weight', $product->weight) }}" class="input" placeholder="0" step="0.01">
                                    <p class="text-xs text-gray-500 mt-1">For shipping calculations</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variants Tab -->
                <div x-show="activeTab === 'variants'" class="space-y-6">
                    @include('admin.products.partials._variant-builder-simple')
                </div>


                <!-- Media Tab -->
                <div x-show="activeTab === 'media'" class="space-y-6">
                    @include('admin.products.partials._image-manager', ['product' => $product])

                    <!-- Product Video Engine -->
                    <div class="card p-5 space-y-4">
                        <h3 class="card-title text-sm font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>Product Video (Every Module)</span>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label text-xs">Video Source Type</label>
                                <select name="video_type" class="input text-xs">
                                    <option value="youtube" {{ old('video_type', $product->video_type) == 'youtube' ? 'selected' : '' }}>YouTube Link</option>
                                    <option value="vimeo" {{ old('video_type', $product->video_type) == 'vimeo' ? 'selected' : '' }}>Vimeo Link</option>
                                    <option value="url" {{ old('video_type', $product->video_type) == 'url' ? 'selected' : '' }}>Direct Video URL (.mp4 / .webm)</option>
                                    <option value="upload" {{ old('video_type', $product->video_type) == 'upload' ? 'selected' : '' }}>Uploaded File</option>
                                </select>
                            </div>
                            <div>
                                <label class="label text-xs">Video Link / URL</label>
                                <input type="text" name="video_url" value="{{ old('video_url', $product->video_url) }}" class="input text-xs" placeholder="e.g. https://www.youtube.com/watch?v=xyz">
                            </div>
                        </div>
                    </div>

                    <!-- Return, Replacement & Warranty Policies -->
                    <div class="card p-5 space-y-4">
                        <h3 class="card-title text-sm font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Return, Replacement & Warranty Policies</span>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="label text-xs">Return Period (Days)</label>
                                <input type="number" name="return_period_days" value="{{ old('return_period_days', $product->return_period_days ?? 7) }}" class="input text-xs" placeholder="7">
                            </div>
                            <div>
                                <label class="label text-xs">Replacement Period (Days)</label>
                                <input type="number" name="replacement_period_days" value="{{ old('replacement_period_days', $product->replacement_period_days ?? 7) }}" class="input text-xs" placeholder="7">
                            </div>
                            <div>
                                <label class="label text-xs">Delivered By Lead Hours</label>
                                <input type="number" name="delivered_by_lead_hours" value="{{ old('delivered_by_lead_hours', $product->delivered_by_lead_hours ?? 24) }}" class="input text-xs" placeholder="24">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="label text-xs mb-0 font-bold">Warranty Summary</label>
                                    @if(isset($existingWarranties) && count($existingWarranties) > 0)
                                        <select onchange="if(this.value) document.getElementById('edit_warranty_input').value = this.value" class="text-[10px] py-0.5 px-2 bg-gray-50 border border-gray-200 rounded text-gray-600 cursor-pointer">
                                            <option value="">Quick Select Existing...</option>
                                            @foreach($existingWarranties as $w)
                                                <option value="{{ $w }}">{{ $w }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                <input type="text" id="edit_warranty_input" name="warranty_summary" value="{{ old('warranty_summary', $product->warranty_summary) }}" class="input text-xs" placeholder="e.g. 1 Year Brand Warranty">
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="label text-xs mb-0 font-bold">Guarantee Summary</label>
                                    @if(isset($existingGuarantees) && count($existingGuarantees) > 0)
                                        <select onchange="if(this.value) document.getElementById('edit_guarantee_input').value = this.value" class="text-[10px] py-0.5 px-2 bg-gray-50 border border-gray-200 rounded text-gray-600 cursor-pointer">
                                            <option value="">Quick Select Existing...</option>
                                            @foreach($existingGuarantees as $g)
                                                <option value="{{ $g }}">{{ $g }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                <input type="text" id="edit_guarantee_input" name="guarantee_summary" value="{{ old('guarantee_summary', $product->guarantee_summary) }}" class="input text-xs" placeholder="e.g. 100% Freshness Guarantee">
                            </div>
                        </div>
                    </div>

                    <!-- Extended Specifications & Dates -->
                    <div class="card p-5 space-y-4">
                        <h3 class="card-title text-sm font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Dates, Shelf Life & Detailed Specifications</span>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="label text-xs">Manufacture Date</label>
                                <input type="date" name="manufacture_date" value="{{ old('manufacture_date', $product->manufacture_date ? $product->manufacture_date->format('Y-m-d') : '') }}" class="input text-xs">
                            </div>
                            <div>
                                <label class="label text-xs">Expiry Date</label>
                                <input type="date" name="expiry_date" value="{{ old('expiry_date', $product->expiry_date ? $product->expiry_date->format('Y-m-d') : '') }}" class="input text-xs">
                            </div>
                            <div>
                                <label class="label text-xs">Shelf Life (Days)</label>
                                <input type="number" name="shelf_life_days" value="{{ old('shelf_life_days', $product->shelf_life_days) }}" class="input text-xs" placeholder="e.g. 365">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                            <div>
                                <label class="label text-xs">Vendor SKU</label>
                                <input type="text" name="vendor_sku" value="{{ old('vendor_sku', $product->vendor_sku) }}" class="input text-xs" placeholder="Vendor Product ID">
                            </div>
                            <div>
                                <label class="label text-xs">Generic Name / Title</label>
                                <input type="text" name="generic_name" value="{{ old('generic_name', $product->generic_name) }}" class="input text-xs" placeholder="e.g. Paracetamol 500mg">
                            </div>
                            <div>
                                <label class="label text-xs">Search Tags</label>
                                <input type="text" name="search_tags" value="{{ old('search_tags', $product->search_tags) }}" class="input text-xs" placeholder="Comma separated tags">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="label text-xs">Nutritional / Attribute Information</label>
                                <textarea name="nutrition_info" rows="2" class="input text-xs" placeholder="e.g. Calories: 250 kcal, Protein: 10g, Carbs: 35g, Fat: 5g">{{ old('nutrition_info', $product->nutrition_info) }}</textarea>
                            </div>
                            <div class="space-y-3 pt-4">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-800">
                                    <input type="checkbox" name="is_prescription_required" value="1" {{ old('is_prescription_required', $product->is_prescription_required) ? 'checked' : '' }} class="rounded text-orange-600">
                                    <span>Prescription Required (Pharmacy Rx)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-800">
                                    <input type="checkbox" name="is_halal" value="1" {{ old('is_halal', $product->is_halal) ? 'checked' : '' }} class="rounded text-emerald-600">
                                    <span>Halal Certified</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Organization Tab -->
                <div x-show="activeTab === 'organization'" class="card">
                    <div class="card-header">
                        <h3 class="card-title">Category & Brand</h3>
                        <p class="text-sm text-gray-500 mt-1">Organize your product</p>
                    </div>
                    <div class="card-body space-y-5">
                        <!-- Dynamic Category Selection -->
                        <div class="space-y-4">
                            <template x-for="(catLevel, index) in categoryLevels" :key="index">
                                <div class="space-y-2 animate-fade-in">
                                    <label class="label text-xs uppercase tracking-wider text-gray-400" x-text="index === 0 ? 'Main Category' : 'Sub Category'"></label>
                                    <select 
                                        x-model="catLevel.selected" 
                                        @change="handleCategoryChange(index)" 
                                        class="input appearance-none cursor-pointer"
                                        :required="index === 0"
                                    >
                                        <option value="">Select Category</option>
                                        <template x-for="category in catLevel.list" :key="category.id">
                                            <option :value="category.id" x-text="category.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            <p class="text-[10px] text-gray-400 mt-1 uppercase italic" x-show="finalCategoryId">Selection: <span x-text="finalCategoryId" class="font-bold"></span></p>
                            @error('category_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="label">Brand</label>
                            <select name="brand_id" class="input">
                                <option value="">Select brand</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Select from available brands</p>
                        </div>

                        <div>
                            <label class="label">Store / Seller</label>
                            @if(!$product->store_id)
                                <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-500 italic">
                                    In-house product (No store assignment)
                                </div>
                                <input type="hidden" name="store_id" value="">
                            @else
                                <select name="store_id" class="input" required>
                                    @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ old('store_id', $product->store_id) == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Belongs to store: {{ $product->store?->name }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- SEO Tab -->
                <div x-show="activeTab === 'seo'" class="card">
                    <div class="card-header">
                        <h3 class="card-title">Search Engine Optimization</h3>
                        <p class="text-sm text-gray-500 mt-1">Improve product visibility</p>
                    </div>
                    <div class="card-body space-y-5">
                        <div>
                            <label class="label">Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" class="input" placeholder="Product name - Brand | Store Name">
                            <p class="text-xs text-gray-500 mt-1">Shown in search results (50-60 characters)</p>
                        </div>

                        <div>
                            <label class="label">Meta Description</label>
                            <textarea name="meta_description" rows="3" class="input" placeholder="Brief description for search engines...">{{ old('meta_description', $product->meta_description) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Shown in search results (150-160 characters)</p>
                        </div>

                        <div>
                            <label class="label">SEO Image (Open Graph)</label>
                            <div class="mt-2 p-4 border-2 border-dashed border-gray-200 rounded-lg bg-gray-50">
                                <div class="flex items-center gap-4">
                                    <div class="w-24 h-24 bg-gray-200 rounded-lg flex items-center justify-center overflow-hidden" id="seo-image-preview">
                                        @if($product->meta_image)
                                            <img src="{{ $product->meta_image }}" alt="SEO Preview" class="w-full h-full object-cover">
                                        @elseif($product->primaryImage)
                                <img src="{{ storage_url($product->primaryImage->image) }}" alt="SEO Preview" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-600 font-medium">Social Media Preview Image</p>
                                        <p class="text-xs text-gray-400 mt-1">Used when sharing on Facebook, Twitter, LinkedIn. Recommended: 1200x630px</p>
                                        <div class="mt-3 flex items-center gap-2">
                                            @if($product->meta_image || $product->primaryImage)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                    Synced with primary image
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                    No image - add a primary image
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="meta_image" value="{{ old('meta_image', $product->meta_image) }}">
                        </div>

                        <div>
                            <label class="label">URL Slug</label>
                            <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="input" placeholder="product-name-slug">
                            <p class="text-xs text-gray-500 mt-1">Current: {{ $product->slug }}</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Status</h3>
                    </div>
                    <div class="card-body space-y-3">
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class="checkbox mt-0.5">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 text-sm">Active</p>
                                <p class="text-xs text-gray-500 mt-0.5">Product visible to customers</p>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" name="is_featured" value="1" {{ $product->is_featured ? 'checked' : '' }} class="checkbox mt-0.5">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 text-sm">Featured</p>
                                <p class="text-xs text-gray-500 mt-0.5">Show on homepage and featured sections</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="card bg-gray-50 border-gray-200">
                    <div class="card-body space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Created</p>
                            <p class="text-sm text-gray-900 font-medium">{{ \App\Helpers\DateHelper::format($product->created_at) }}</p>
                            <p class="text-xs text-gray-500">{{ \App\Helpers\DateHelper::formatTime($product->created_at) }}</p>
                        </div>
                        <div class="border-t border-gray-200 pt-4">
                            <p class="text-xs text-gray-500 mb-1">Last Updated</p>
                            <p class="text-sm text-gray-900 font-medium">{{ \App\Helpers\DateHelper::format($product->updated_at) }}</p>
                            <p class="text-xs text-gray-500">{{ $product->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="delete-form" action="{{ route('admin.products.destroy', $product) }}" method="POST" class="hidden">
        @csrf @method('DELETE')
    </form>
</div>

@include('admin.products.partials._ai-assistant')

<script>
function productForm() {
    return {
        activeTab: 'basic',

        // Category logic
        categoryLevels: [],
        finalCategoryId: '{{ $product->category_id }}',
        currentCategoryId: '{{ $product->category_id }}',

        async init() {
            // Load category chain if editing
            if (this.currentCategoryId) {
                await this.loadCategoryChain(this.currentCategoryId);
            } else {
                await this.loadInitialRoots();
            }

            window.addEventListener('invalid', (e) => {
                const invalidEl = e.target;
                const tabPane = invalidEl.closest('[x-show*="activeTab"]');
                if (tabPane) {
                    const tabMatch = tabPane.getAttribute('x-show').match(/'([^']+)'/);
                    if (tabMatch && tabMatch[1]) {
                        this.activeTab = tabMatch[1];
                    }
                }
            }, true);
        },

        async loadInitialRoots() {
            try {
                const response = await fetch('{{ route('api.categories.roots') }}');
                const roots = await response.json();
                this.categoryLevels.push({ 
                    selected: '', 
                    list: roots.map(c => ({id: String(c.id), name: c.name})) 
                });
            } catch (error) {
                console.error('Failed to load root categories', error);
            }
        },

        async loadCategoryChain(categoryId) {
            try {
                const response = await fetch(`/internal-api/categories/chain/${categoryId}`);
                if (!response.ok) throw new Error('Failed to fetch chain');
                const data = await response.json();
                
                // stage 1: prepare levels with lists but no selections
                this.categoryLevels = data.map(level => ({
                    selected: '',
                    list: level.list.map(c => ({id: String(c.id), name: c.name}))
                }));
                
                // wait for DOM/Alpine to render selects and options
                await new Promise(resolve => setTimeout(resolve, 100));
                
                // stage 2: apply the actual selections
                data.forEach((level, index) => {
                    if (this.categoryLevels[index]) {
                        this.categoryLevels[index].selected = String(level.selected);
                    }
                });

                this.$nextTick(() => {
                    this.updateFinalCategoryId();
                });
            } catch (error) {
                console.error('Failed to load category chain', error);
                await this.loadInitialRoots();
            }
        },

        async handleCategoryChange(level) {
            const selectedId = this.categoryLevels[level].selected;
            
            // Remove all levels after this one
            this.categoryLevels = this.categoryLevels.slice(0, level + 1);
            this.updateFinalCategoryId();

            if (selectedId) {
                try {
                    const response = await fetch(`/internal-api/categories/${selectedId}/subcategories`);
                    const subcategories = await response.json();
                    
                    if (subcategories.length > 0) {
                        this.categoryLevels.push({ 
                            selected: '', 
                            list: subcategories.map(c => ({id: String(c.id), name: c.name})) 
                        });
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
        }
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@endsection

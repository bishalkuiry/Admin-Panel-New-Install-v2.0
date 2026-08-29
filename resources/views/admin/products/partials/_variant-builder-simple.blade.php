@php
    $decimalPlaces = (int) \App\Models\Setting::get('currency_decimal_places', 0);
    $initialVariants = [];
    if (old('variants')) {
        foreach (old('variants') as $v) {
            $normalizedAttrs = [];
            if (isset($v['attributes']) && is_array($v['attributes'])) {
                foreach ($v['attributes'] as $attrId => $valueId) {
                    // Try to find the attribute name and value name from the already loaded $attributes collection
                    $attr = $attributes->firstWhere('id', $attrId);
                    $valName = 'Unknown';
                    if ($attr) {
                        $val = $attr->values->firstWhere('id', $valueId);
                        $valName = $val ? $val->value : 'Unknown';
                    }
                    
                    $normalizedAttrs[$attr ? $attr->name : "Attr $attrId"] = [
                        'id' => $valueId,
                        'attribute_id' => $attrId,
                        'value' => $valName
                    ];
                }
            }
            
            $initialVariants[] = [
                'sku' => $v['sku'] ?? '',
                'mrp' => isset($v['mrp']) && $v['mrp'] !== '' ? number_format((float)$v['mrp'], $decimalPlaces, '.', '') : '',
                'selling_price' => isset($v['selling_price']) && $v['selling_price'] !== '' ? number_format((float)$v['selling_price'], $decimalPlaces, '.', '') : '',
                'unit_value' => $v['unit_value'] ?? '',
                'weight' => $v['weight'] ?? '',
                'quantity' => $v['quantity'] ?? 0,
                'is_active' => isset($v['is_active']),
                'attributes' => $normalizedAttrs,
                'displayName' => implode(' / ', array_column($normalizedAttrs, 'value')) ?: 'Variant'
            ];
        }
    } elseif (isset($product) && $product->variants->count()) {
        foreach ($product->variants as $v) {
            $attrs = [];
            $displayParts = [];
            foreach ($v->attributeValues as $av) {
                $attrs[$av->attribute->name] = [
                    'id' => $av->id,
                    'attribute_id' => $av->attribute_id,
                    'value' => $av->value
                ];
                $displayParts[] = $av->value;
            }
            $initialVariants[] = [
                'id' => $v->id,
                'sku' => $v->sku,
                'mrp' => $v->mrp !== null ? number_format((float)$v->mrp, $decimalPlaces, '.', '') : '',
                'selling_price' => number_format((float)$v->selling_price, $decimalPlaces, '.', ''),
                'unit_value' => $v->unit_value,
                'weight' => $v->weight,
                'quantity' => $v->quantity,
                'is_active' => (bool)$v->is_active,
                'attributes' => $attrs,
                'displayName' => implode(' / ', $displayParts)
            ];
        }
    }
@endphp

<div x-data="simpleVariantBuilder({{ json_encode($initialVariants) }})" x-init="init()" x-on:apply-ai-variants.window="handleAiVariants($event.detail)" class="space-y-6">
    
    <!-- Card 1: Attribute Selection -->
    @if($attributes->count())
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Attribute Selection</h3>
            <p class="text-sm text-gray-500 mt-1">Choose which attributes to use for variants</p>
        </div>
        <div class="card-body space-y-4">
            <div class="grid grid-cols-2 gap-3">
                @foreach($attributes as $attr)
                <div x-data="{ open: false }" class="relative">
                    <div class="rounded-lg border transition p-3"
                        :class="selectedAttributes.find(a => a.id === {{ $attr->id }}) ? 'border-indigo-300 bg-indigo-50/30' : 'border-gray-200 bg-white'">
                        <!-- Top row: checkbox + name + dropdown toggle -->
                        <div class="flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                :checked="selectedAttributes.find(a => a.id === {{ $attr->id }})"
                                @change="toggleAttribute({{ $attr->id }}, '{{ $attr->name }}', {{ $attr->values->toJson() }})"
                                class="checkbox-indigo"
                            >
                            <span class="text-sm font-semibold text-gray-800 flex-1">{{ $attr->name }}</span>
                            <button type="button" 
                                x-show="selectedAttributes.find(a => a.id === {{ $attr->id }})"
                                @click="open = !open"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 transition">
                                <span x-text="selectedValues.filter(sv => sv.attribute_id === {{ $attr->id }}).length > 0 ? selectedValues.filter(sv => sv.attribute_id === {{ $attr->id }}).length + ' selected' : 'Select values'"></span>
                                <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>
                        <!-- Selected value tags -->
                        <div class="flex flex-wrap gap-1 mt-2" x-show="selectedValues.filter(sv => sv.attribute_id === {{ $attr->id }}).length > 0">
                            <template x-for="sv in selectedValues.filter(sv => sv.attribute_id === {{ $attr->id }})" :key="'sv-' + sv.value_id">
                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-100 text-indigo-700">
                                    <span x-text="sv.value"></span>
                                    <button type="button" @click.stop="toggleValue(sv.attribute_id, sv.value_id, sv.value)" class="hover:text-red-500 ml-0.5">&times;</button>
                                </span>
                            </template>
                        </div>
                    </div>
                    <!-- Dropdown Panel -->
                    <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-20 mt-1 left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-3 py-2 bg-gray-50 border-b border-gray-200">
                            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">{{ $attr->name }} Values</span>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="selectAllValues({{ $attr->id }})" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-medium">All</button>
                                <span class="text-gray-300">|</span>
                                <button type="button" @click="deselectAllValues({{ $attr->id }})" class="text-[10px] text-gray-500 hover:text-gray-700 font-medium">Clear</button>
                            </div>
                        </div>
                        <!-- Values List -->
                        <div class="max-h-48 overflow-y-auto py-1">
                            @foreach($attr->values as $val)
                            <label class="flex items-center gap-2.5 px-3 py-1.5 cursor-pointer hover:bg-indigo-50 transition-colors">
                                <input type="checkbox" class="checkbox-indigo"
                                    :checked="isValueSelected({{ $attr->id }}, {{ $val->id }})"
                                    @change="toggleValue({{ $attr->id }}, {{ $val->id }}, '{{ $val->value }}')">
                                <span class="text-sm text-gray-700">{{ $val->value }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Generate Variants Button -->
            <div x-show="hasSelectedValues()" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div>
                    <p class="text-sm font-medium text-gray-900">
                        <span x-text="totalSelectedValues()"></span> value(s) across <span x-text="selectedAttributes.length"></span> attribute(s)
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Will generate <span x-text="estimatedVariantCount()" class="font-semibold text-indigo-600"></span> variant(s)</p>
                </div>
                <button 
                    type="button" 
                    @click="generateVariants()" 
                    class="btn-primary"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Generate Variants
                </button>
            </div>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Attribute Selection</h3>
            <p class="text-sm text-gray-500 mt-1">Create attributes to enable product variants</p>
        </div>
        <div class="card-body">
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <h4 class="text-base font-semibold text-gray-900">No Attributes Found</h4>
                <p class="text-sm text-gray-500 mt-2 max-w-sm mx-auto">To generate variants, you first need to create attributes like <strong>"Color"</strong> (e.g., Red) or <strong>"Size"</strong>.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.attributes.create') }}" target="_blank" class="btn-primary inline-flex items-center gap-2 px-6">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Create Your First Attribute
                    </a>
                    <p class="text-[10px] text-gray-400 mt-3 uppercase tracking-wider italic">Close the new tab once created to see it here</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Card 2: Generated Variants -->
    <div x-show="variants.length > 0" class="card">
        <div class="card-header flex items-center justify-between">
            <div>
                <h3 class="card-title">Generated Variants (<span x-text="variants.length"></span>)</h3>
                <p class="text-sm text-gray-500 mt-1">Manage pricing, stock, and status for each variant</p>
            </div>
            <button 
                type="button" 
                @click="clearVariants()" 
                class="text-sm text-red-600 hover:text-red-700 font-medium"
            >
                Clear All
            </button>
        </div>
        <div class="card-body space-y-4">
            <!-- Quick Fill -->
            <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Quick Fill</span>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">₹</span>
                        <input type="number" x-model="bulkPrice" step="0.01" class="input py-1.5 text-sm pl-7 w-28" placeholder="Price">
                    </div>
                    <button type="button" @click="applyBulkPrice()" class="btn-secondary py-1.5 text-xs">Apply</button>
                </div>
                <div class="w-px h-6 bg-gray-300"></div>
                <div class="flex items-center gap-2">
                    <input type="number" x-model="bulkStock" class="input py-1.5 text-sm w-20" placeholder="Stock" min="0">
                    <button type="button" @click="applyBulkStock()" class="btn-secondary py-1.5 text-xs">Apply</button>
                </div>
            </div>

            <!-- Variants Table -->
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Variant</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">SKU</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Selling Price</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">MRP</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-wider w-14">Active</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(variant, index) in variants" :key="'variant-' + index + '-' + (variant.id || 'new')">
                            <tr class="hover:bg-gray-50/50">
                                <!-- Variant Name & Hidden Fields -->
                                <td class="px-4 py-2.5">
                                    <template x-if="variant.id">
                                        <input type="hidden" :name="'variants[' + index + '][id]'" :value="variant.id">
                                    </template>
                                    <span class="text-sm font-semibold text-gray-900 block" x-text="variant.displayName"></span>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        <template x-for="(attrValue, attrName) in variant.attributes" :key="'attr-tag-' + index + '-' + attrName">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-600">
                                                <span x-text="attrName"></span>: <span x-text="attrValue.value" class="ml-0.5 font-semibold"></span>
                                            </span>
                                        </template>
                                    </div>
                                    <template x-for="(attrValue, attrName) in variant.attributes" :key="'attr-field-' + index + '-' + attrName">
                                        <input type="hidden" :name="'variants[' + index + '][attributes][' + attrValue.attribute_id + ']'" :value="attrValue.id">
                                    </template>
                                </td>

                                <!-- SKU -->
                                <td class="px-3 py-2.5">
                                    <input type="text" :name="'variants[' + index + '][sku]'" x-model="variant.sku" class="input py-1.5 text-sm w-28 font-mono" placeholder="SKU">
                                </td>

                                <!-- Selling Price -->
                                <td class="px-3 py-2.5">
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">{{ $currencySymbol }}</span>
                                        <input type="number" :name="'variants[' + index + '][selling_price]'" x-model="variant.selling_price" step="{{ $priceStep }}" class="input py-1.5 text-sm pl-7 w-28" placeholder="{{ number_format(0, $priceDecimals) }}" required>
                                    </div>
                                </td>

                                <!-- MRP -->
                                <td class="px-3 py-2.5">
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">{{ $currencySymbol }}</span>
                                        <input type="number" :name="'variants[' + index + '][mrp]'" x-model="variant.mrp" step="{{ $priceStep }}" class="input py-1.5 text-sm pl-7 w-28 bg-gray-50" placeholder="{{ number_format(0, $priceDecimals) }}">
                                    </div>
                                </td>

                                <!-- Stock -->
                                <td class="px-3 py-2.5">
                                    <input type="number" :name="'variants[' + index + '][quantity]'" x-model="variant.quantity" class="input py-1.5 text-sm w-20" placeholder="0" min="0">
                                </td>

                                <!-- Active -->
                                <td class="px-3 py-2.5 text-center">
                                    <input type="checkbox" :name="'variants[' + index + '][is_active]'" value="1" x-model="variant.is_active" class="checkbox-indigo">
                                </td>

                                <!-- Delete -->
                                <td class="px-2 py-2.5 text-center">
                                    <button type="button" @click="removeVariant(index)" class="p-1 text-gray-400 hover:text-red-500 rounded transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Empty State (no variants generated yet, but attributes available) -->
    <div x-show="variants.length === 0 && selectedAttributes.length === 0 && {{ $attributes->count() ? 'true' : 'false' }}" class="card">
        <div class="card-body">
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-sm text-gray-500 mt-4">No variants created yet</p>
                <p class="text-xs text-gray-400 mt-1">Select attributes above to generate variants</p>
            </div>
        </div>
    </div>
</div>

<script>
function simpleVariantBuilder(initialData = []) {
    return {
        selectedAttributes: [],
        selectedValues: [],
        variants: initialData,
        bulkPrice: '',
        bulkStock: '',
        
        init() {
            // If we have initial data, we need to mark the corresponding attributes as selected
            if (this.variants.length > 0) {
                this.variants.forEach(v => {
                    for (const attrName in v.attributes) {
                        const attr = v.attributes[attrName];
                        const attrId = attr.attribute_id;
                        
                        // Check if already in selectedAttributes
                        if (!this.selectedAttributes.find(a => a.id === attrId)) {
                            // Find the full attribute data from the page if possible
                            // For simplicity, we can just push the ID and name
                            this.selectedAttributes.push({ 
                                id: attrId, 
                                name: attrName, 
                                values: [] // Values list isn't strictly needed for display
                            });
                        }
                    }
                });
            }
        },

        handleAiVariants(aiVariants) {
            if (!aiVariants || aiVariants.length === 0) return;
            
            // STRICT CHECK: Only generate variants if the user has selected attributes
            // This ensures variants are "BASED ON ATTRIBUTES" as requested
            if (this.selectedAttributes.length === 0) {
                alert('Please select at least one Attribute (like Color or Size) in the Variants tab first. \n\nAI needs to know which attributes your system uses before it can suggest variations.');
                // Switch to variants tab so they can see where to check
                window.dispatchEvent(new CustomEvent('switch-tab', { detail: 'variants' }));
                return;
            }

            // Filter AI variants to only include those that match our selected attributes
            const validVariants = aiVariants.filter(v => {
                if (!v.attributes || v.attributes.length === 0) return false;
                
                // Every attribute in this AI variant must be one of our selected attributes
                return v.attributes.every(aiAttr => 
                    this.selectedAttributes.some(selected => selected.id === aiAttr.attribute_id)
                );
            });

            if (validVariants.length === 0) {
                alert('The AI suggested variants for attributes you haven\'t selected. \n\nPlease select the matching attributes (e.g. Size, Color) first, then try again!');
                return;
            }
            
            this.variants = validVariants.map(v => {
                const attributes = {};
                const displayParts = [];
                
                if (v.attributes) {
                    v.attributes.forEach(attr => {
                        attributes[attr.attribute_name] = {
                            id: attr.value_id,
                            attribute_id: attr.attribute_id,
                            value: attr.value_name
                        };
                        displayParts.push(attr.value_name);
                    });
                }
                
                return {
                    displayName: displayParts.join(' / '),
                    attributes: attributes,
                    selling_price: v.selling_price || '',
                    mrp: v.mrp || '',
                    sku: v.sku || 'QK-V-' + Math.random().toString(36).substr(2, 6).toUpperCase(),
                    quantity: v.quantity || 0,
                    is_active: true
                };
            });
        },
        
        toggleAttribute(id, name, values) {
            const index = this.selectedAttributes.findIndex(a => a.id === id);
            if (index > -1) {
                this.selectedAttributes.splice(index, 1);
                // Also remove selected values for this attribute
                this.selectedValues = this.selectedValues.filter(sv => sv.attribute_id !== id);
            } else {
                this.selectedAttributes.push({ id, name, values });
                // Auto-select all values when attribute is first toggled on
                values.forEach(val => {
                    this.selectedValues.push({ attribute_id: id, value_id: val.id, value: val.value });
                });
            }
        },

        toggleValue(attributeId, valueId, valueName) {
            const index = this.selectedValues.findIndex(sv => sv.attribute_id === attributeId && sv.value_id === valueId);
            if (index > -1) {
                this.selectedValues.splice(index, 1);
            } else {
                this.selectedValues.push({ attribute_id: attributeId, value_id: valueId, value: valueName });
            }
        },

        isValueSelected(attributeId, valueId) {
            return this.selectedValues.some(sv => sv.attribute_id === attributeId && sv.value_id === valueId);
        },

        selectAllValues(attributeId) {
            const attr = this.selectedAttributes.find(a => a.id === attributeId);
            if (!attr) return;
            this.selectedValues = this.selectedValues.filter(sv => sv.attribute_id !== attributeId);
            attr.values.forEach(val => {
                this.selectedValues.push({ attribute_id: attributeId, value_id: val.id, value: val.value });
            });
            this.$nextTick(() => this.syncSelectElement(attributeId));
        },

        deselectAllValues(attributeId) {
            this.selectedValues = this.selectedValues.filter(sv => sv.attribute_id !== attributeId);
            this.$nextTick(() => this.syncSelectElement(attributeId));
        },

        syncDropdownValues(attributeId, selectEl) {
            // Read selected options from the <select> and sync to selectedValues
            this.selectedValues = this.selectedValues.filter(sv => sv.attribute_id !== attributeId);
            Array.from(selectEl.selectedOptions).forEach(opt => {
                this.selectedValues.push({
                    attribute_id: attributeId,
                    value_id: parseInt(opt.value),
                    value: opt.dataset.name
                });
            });
        },

        syncSelectElement(attributeId) {
            // Sync the <select> DOM to match selectedValues (used when tags are removed)
            const selects = this.$el.querySelectorAll('select[multiple]');
            selects.forEach(sel => {
                // Check if this select belongs to the given attribute by checking option values
                const options = Array.from(sel.options);
                let belongsToAttr = false;
                options.forEach(opt => {
                    const valId = parseInt(opt.value);
                    const isSelected = this.isValueSelected(attributeId, valId);
                    if (isSelected || this.selectedValues.some(sv => sv.value_id === valId && sv.attribute_id === attributeId)) {
                        belongsToAttr = true;
                    }
                    opt.selected = isSelected;
                });
            });
        },

        hasSelectedValues() {
            return this.selectedAttributes.length > 0 && this.selectedValues.length > 0;
        },

        totalSelectedValues() {
            return this.selectedValues.length;
        },

        estimatedVariantCount() {
            if (this.selectedAttributes.length === 0) return 0;
            let count = 1;
            this.selectedAttributes.forEach(attr => {
                const valuesForAttr = this.selectedValues.filter(sv => sv.attribute_id === attr.id).length;
                if (valuesForAttr > 0) count *= valuesForAttr;
            });
            return count;
        },
        
        generateVariants() {
            if (!this.hasSelectedValues()) {
                alert('Please select at least one attribute and its values');
                return;
            }
            
            // Build value arrays per attribute, using only selected values
            const valueArrays = this.selectedAttributes
                .map(attr => {
                    const selectedForAttr = this.selectedValues
                        .filter(sv => sv.attribute_id === attr.id)
                        .map(sv => ({ 
                            attribute_id: attr.id,
                            attribute_name: attr.name, 
                            id: sv.value_id,
                            value: sv.value
                        }));
                    return selectedForAttr;
                })
                .filter(arr => arr.length > 0);

            if (valueArrays.length === 0) {
                alert('Please select values for at least one attribute');
                return;
            }
            
            // Generate combinations from selected values only
            const combinations = this.cartesianProduct(valueArrays);
            
            // Read main product prices from Pricing tab
            const priceInput = document.querySelector('input[name="price"]');
            const compareInput = document.querySelector('input[name="compare_price"]');
            const mainPrice = priceInput && priceInput.value ? priceInput.value : '';
            const mainMrp = compareInput && compareInput.value ? compareInput.value : '';

            // Create variant objects
            this.variants = combinations.map(combo => {
                const attributes = {};
                const displayParts = [];
                
                combo.forEach(item => {
                    attributes[item.attribute_name] = {
                        id: item.id,
                        attribute_id: item.attribute_id,
                        value: item.value
                    };
                    displayParts.push(item.value);
                });
                
                return {
                    displayName: displayParts.join(' / '),
                    attributes: attributes,
                    selling_price: mainPrice,
                    mrp: mainMrp,
                    sku: this.generateVariantSku(displayParts),
                    quantity: 0,
                    is_active: true
                };
            });
        },

        generateVariantSku(displayParts) {
            // Try to get parent SKU from productForm if available
            let parentSku = 'QK';
            const nameInput = document.querySelector('input[name="sku"]');
            if (nameInput && nameInput.value) {
                parentSku = nameInput.value;
            }
            
            const variantSuffix = displayParts.join('-').replace(/\s+/g, '').toUpperCase();
            return `${parentSku}-${variantSuffix}`;
        },
        
        cartesianProduct(arrays) {
            return arrays.reduce((acc, curr) => {
                return acc.flatMap(a => curr.map(c => [].concat(a, c)));
            }, [[]]);
        },
        
        removeVariant(index) {
            if (confirm('Remove this variant?')) {
                this.variants.splice(index, 1);
            }
        },
        
        clearVariants() {
            if (confirm('Clear all variants? This cannot be undone.')) {
                this.variants = [];
                this.selectedAttributes = [];
            }
        },
        
        applyBulkPrice() {
            if (!this.bulkPrice) {
                alert('Please enter a price');
                return;
            }
            this.variants.forEach(v => v.selling_price = this.bulkPrice);
        },
        
        applyBulkStock() {
            if (this.bulkStock === '') {
                alert('Please enter a stock quantity');
                return;
            }
            this.variants.forEach(v => v.quantity = this.bulkStock);
        }
    }
}
</script>

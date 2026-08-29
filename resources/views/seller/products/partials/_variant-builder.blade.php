@php
    $initialVariants = [];
    if (old('variants')) {
        foreach (old('variants') as $v) {
            $normalizedAttrs = [];
            if (isset($v['attributes']) && is_array($v['attributes'])) {
                foreach ($v['attributes'] as $attrId => $valueId) {
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
                'mrp' => $v['mrp'] ?? '',
                'selling_price' => $v['selling_price'] ?? '',
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
                'mrp' => $v->mrp,
                'selling_price' => $v->selling_price,
                'quantity' => $v->quantity,
                'is_active' => (bool)$v->is_active,
                'attributes' => $attrs,
                'displayName' => implode(' / ', $displayParts)
            ];
        }
    }
@endphp

<div x-data="simpleVariantBuilder({{ json_encode($initialVariants) }})" x-init="init()" class="space-y-6">
    <!-- Attribute Selection -->
    @if($attributes->count())
    <div class="bg-indigo-50/50 border border-indigo-100 rounded p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 bg-indigo-600 rounded flex items-center justify-center text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
            <div>
                <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest">Select Variant Attributes</h4>
                <p class="text-[10px] text-gray-500 font-medium">Choose options like Size or Color to generate variants</p>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-3">
            @foreach($attributes as $attr)
            <label class="group relative flex items-center gap-2 px-4 py-2 rounded border bg-white cursor-pointer transition-all hover:border-indigo-300"
                :class="selectedAttributes.find(a => a.id === {{ $attr->id }}) ? 'border-indigo-600 ring-4 ring-indigo-50' : 'border-gray-200'">
                <input 
                    type="checkbox" 
                    :checked="selectedAttributes.find(a => a.id === {{ $attr->id }})"
                    @change="toggleAttribute({{ $attr->id }}, '{{ $attr->name }}', {{ $attr->values->toJson() }})"
                    class="sr-only"
                >
                <div class="w-4 h-4 rounded border flex items-center justify-center transition-colors"
                    :class="selectedAttributes.find(a => a.id === {{ $attr->id }}) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 bg-white'">
                    <svg x-show="selectedAttributes.find(a => a.id === {{ $attr->id }})" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                </div>
                <span class="text-xs font-bold text-gray-700 uppercase tracking-tight">{{ $attr->name }}</span>
            </label>
            @endforeach
        </div>

        <div x-show="selectedAttributes.length > 0" x-transition class="mt-6 flex items-center justify-between p-4 bg-white rounded border border-indigo-100 shadow-sm">
            <div class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest">
                <span x-text="selectedAttributes.length"></span> attribute(s) ready to generate
            </div>
            <button type="button" @click="generateVariants()" class="bg-indigo-600 text-white px-6 py-2 rounded text-[10px] font-black uppercase tracking-[0.2em] hover:bg-indigo-700 transition-all shadow-md active:scale-95">
                Generate Variants
            </button>
        </div>
    </div>
    @else
    <div class="text-center py-12 bg-gray-50 rounded border-2 border-dashed border-gray-200">
        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">No Global Attributes Available</p>
        <p class="text-[10px] text-gray-400 mt-1">Please contact admin to add attributes like Color/Size</p>
    </div>
    @endif

    <!-- Variants List -->
    <div x-show="variants.length > 0" x-transition class="space-y-4">
        <div class="flex items-center justify-between px-2">
            <h4 class="text-xs font-black text-gray-900 uppercase tracking-[0.1em]">
                Active Variants (<span x-text="variants.length"></span>)
            </h4>
            <button type="button" @click="clearVariants()" class="text-[10px] font-bold text-rose-600 uppercase tracking-widest hover:underline">
                Clear All
            </button>
        </div>

        <div class="bg-white border border-gray-200 rounded overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-[10px] font-black text-gray-500 uppercase tracking-widest">Variant Details</th>
                        <th class="px-4 py-3 text-[10px] font-black text-gray-500 uppercase tracking-widest">SKU</th>
                        <th class="px-4 py-3 text-[10px] font-black text-gray-500 uppercase tracking-widest">Price ({{ $currencySymbol }})</th>
                        <th class="px-4 py-3 text-[10px] font-black text-gray-500 uppercase tracking-widest">Stock</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(variant, index) in variants" :key="index">
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-1.5">
                                    <template x-if="variant.id">
                                        <input type="hidden" :name="'variants['+index+'][id]'" :value="variant.id">
                                    </template>
                                    <span class="text-xs font-bold text-gray-900 uppercase tracking-tight" x-text="variant.displayName"></span>
                                    <div class="flex flex-wrap gap-1">
                                        <template x-for="(attrValue, attrName) in variant.attributes" :key="attrName">
                                            <div class="px-1.5 py-0.5 bg-gray-100 text-[9px] font-bold text-gray-500 rounded border border-gray-200">
                                                <span x-text="attrName"></span>: <span class="text-indigo-600" x-text="attrValue.value"></span>
                                                <input type="hidden" :name="'variants['+index+'][attributes]['+attrValue.attribute_id+']'" :value="attrValue.id">
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <input type="text" :name="'variants['+index+'][sku]'" x-model="variant.sku" class="w-24 px-2 py-1.5 border border-gray-200 rounded text-[10px] font-mono tracking-tighter uppercase focus:ring-1 focus:ring-indigo-500 transition-all">
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="relative">
                                        <input type="number" step="0.01" :name="'variants['+index+'][selling_price]'" x-model="variant.selling_price" class="w-24 pl-5 pr-2 py-1.5 border border-gray-200 rounded text-[10px] font-black text-indigo-600 focus:ring-1 focus:ring-indigo-500 transition-all" placeholder="0.00">
                                        <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-[9px] text-gray-400">{{ $currencySymbol }}</span>
                                    </div>
                                    <div class="relative">
                                        <input type="number" step="0.01" :name="'variants['+index+'][mrp]'" x-model="variant.mrp" class="w-24 pl-5 pr-2 py-1 border-transparent rounded text-[9px] font-bold text-gray-400 focus:bg-white focus:border-gray-200 transition-all" placeholder="MRP">
                                        <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-[8px] text-gray-300">{{ $currencySymbol }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <input type="number" :name="'variants['+index+'][quantity]'" x-model="variant.quantity" class="w-16 px-2 py-1.5 border border-gray-200 rounded text-[10px] font-bold text-gray-700 focus:ring-1 focus:ring-indigo-500 transition-all">
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" :name="'variants['+index+'][is_active]'" value="1" x-model="variant.is_active" class="sr-only peer">
                                        <div class="w-7 h-4 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-500"></div>
                                    </label>
                                    <button type="button" @click="variants.splice(index, 1)" class="text-gray-300 hover:text-rose-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Bulk Tools -->
        <div class="bg-gray-50 border border-gray-200 rounded p-4 flex flex-wrap gap-6 items-center">
            <div class="flex items-center gap-3">
                <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest whitespace-nowrap">Apply Price to All</span>
                <div class="relative">
                    <input type="number" x-model="bulkPrice" class="w-24 pl-5 pr-2 py-1.5 border border-gray-200 rounded text-[10px] font-black outline-none focus:border-indigo-500" placeholder="0.00">
                    <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-[9px] text-gray-400">{{ $currencySymbol }}</span>
                </div>
                <button type="button" @click="applyBulkPrice()" class="text-[10px] font-black text-indigo-600 uppercase hover:underline">Apply</button>
            </div>
            <div class="h-6 w-px bg-gray-200"></div>
            <div class="flex items-center gap-3">
                <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest whitespace-nowrap">Apply Stock to All</span>
                <input type="number" x-model="bulkStock" class="w-20 px-2 py-1.5 border border-gray-200 rounded text-[10px] font-black outline-none focus:border-indigo-500" placeholder="0">
                <button type="button" @click="applyBulkStock()" class="text-[10px] font-black text-indigo-600 uppercase hover:underline">Apply</button>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div x-show="variants.length === 0 && selectedAttributes.length === 0" class="py-16 text-center">
        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
            <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Build Variation System</p>
        <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-tight">Select attributes from the list above to start generating variants</p>
    </div>
</div>

<script>
function simpleVariantBuilder(initialData = []) {
    return {
        selectedAttributes: [],
        variants: initialData,
        bulkPrice: '',
        bulkStock: '',
        
        init() {
            if (this.variants.length > 0) {
                this.variants.forEach(v => {
                    for (const attrName in v.attributes) {
                        const attr = v.attributes[attrName];
                        if (!this.selectedAttributes.find(a => a.id === attr.attribute_id)) {
                            this.selectedAttributes.push({ 
                                id: attr.attribute_id, 
                                name: attrName, 
                                values: [] 
                            });
                        }
                    }
                });
            }
        },
        
        toggleAttribute(id, name, values) {
            const index = this.selectedAttributes.findIndex(a => a.id === id);
            if (index > -1) {
                this.selectedAttributes.splice(index, 1);
            } else {
                this.selectedAttributes.push({ id, name, values });
            }
        },
        
        generateVariants() {
            if (this.selectedAttributes.length === 0) return;
            
            const combinations = this.cartesianProduct(
                this.selectedAttributes.map(attr => 
                    attr.values.map(val => ({ 
                        attribute_id: attr.id,
                        attribute_name: attr.name, 
                        ...val 
                    }))
                )
            );
            
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
                
                const displayName = displayParts.join(' / ');
                return {
                    displayName: displayName,
                    attributes: attributes,
                    selling_price: '',
                    mrp: '',
                    sku: this.generateVariantSku(displayParts),
                    quantity: 0,
                    is_active: true
                };
            });
        },

        generateVariantSku(displayParts) {
            const parentSku = document.querySelector('input[name="sku"]')?.value || 'VAR';
            const suffix = displayParts.join('-').replace(/\s+/g, '').toUpperCase();
            return `${parentSku}-${suffix}`;
        },
        
        cartesianProduct(arrays) {
            return arrays.reduce((acc, curr) => {
                return acc.flatMap(a => curr.map(c => [].concat(a, c)));
            }, [[]]);
        },
        
        clearVariants() {
            if (confirm('Clear all variants?')) {
                this.variants = [];
                this.selectedAttributes = [];
            }
        },
        
        applyBulkPrice() {
            if (this.bulkPrice) this.variants.forEach(v => v.selling_price = this.bulkPrice);
        },
        
        applyBulkStock() {
            if (this.bulkStock !== '') this.variants.forEach(v => v.quantity = this.bulkStock);
        }
    }
}
</script>

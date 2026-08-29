<!-- AI Floating Assistant -->
<div x-data="aiAssistant()" x-on:open-ai-assistant.window="openModal = true; title = $event.detail.title || title" x-init="init()" class="fixed bottom-8 right-8 z-[60]">
    <!-- Floating Button -->
    <button type="button" @click="openModal = true" class="w-16 h-16 bg-gradient-to-tr from-indigo-600 to-violet-700 rounded-full shadow-2xl flex items-center justify-center text-white hover:scale-110 active:scale-95 transition-all group relative">
        <svg class="w-8 h-8 group-hover:rotate-12 transition-transform" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2L14.85 9.15L22 12L14.85 14.85L12 22L9.15 14.85L2 12L9.15 9.15L12 2Z"/>
        </svg>
        <div class="absolute -top-2 -right-2 px-2 py-1 bg-orange-500 text-[10px] font-bold rounded-lg animate-bounce">AI Magic</div>
    </button>

    <!-- Modal Backdrop -->
    <div x-show="openModal" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-[70]" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <!-- Modal Content -->
        <div @click.away="!loading && (openModal = false)" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-slide-up">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L14.85 9.15L22 12L14.85 14.85L12 22L9.15 14.85L2 12L9.15 9.15L12 2Z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg leading-none">AI Product Assistant</h3>
                        <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Generate full product profile</p>
                    </div>
                </div>
                <button type="button" @click="openModal = false" :disabled="loading" class="text-gray-400 hover:text-gray-600 disabled:opacity-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-8 space-y-6">
                <!-- Title Field -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Product Title <span class="text-red-500">*</span></label>
                    <input type="text" x-model="title" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all outline-none" placeholder="e.g., Organic Avocado Oil 250ml">
                </div>

                <!-- Hint Field -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Hints / Detail (Optional)</label>
                    <textarea x-model="hint" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all outline-none resize-none" placeholder="Cold pressed, extra virgin, keto friendly..."></textarea>
                </div>

                <!-- Image Upload & Variants Toggle -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Reference Image</label>
                        <div x-data="{ preview: null }" class="relative">
                            <div @click="$refs.aiFile.click()" class="h-24 border-2 border-dashed border-gray-200 rounded-2xl flex flex-col items-center justify-center gap-1 hover:border-indigo-400 hover:bg-slate-50 transition-all cursor-pointer relative overflow-hidden">
                                <template x-if="!image">
                                    <div class="text-center">
                                        <svg class="w-6 h-6 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-xs text-gray-500 font-medium tracking-tight">Image</span>
                                    </div>
                                </template>
                                <template x-if="image">
                                    <img :src="image" class="absolute inset-0 w-full h-full object-cover">
                                </template>
                            </div>
                            <template x-if="image">
                                <button type="button" @click="image = null" class="absolute -top-1 -right-1 bg-red-500 text-white p-1 rounded-full shadow-lg">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </template>
                        </div>
                    </div>
                    <div class="flex flex-col justify-end pb-1">
                        <label class="flex items-center gap-3 p-4 border border-gray-100 rounded-2xl cursor-pointer hover:bg-slate-50 transition-all">
                            <input type="checkbox" x-model="createVariants" class="checkbox-indigo w-5 h-5">
                            <div class="flex-1">
                                <span class="block text-sm font-bold text-gray-700">Create Variants</span>
                                <span class="block text-[10px] text-gray-500">Size, Color, etc.</span>
                            </div>
                        </label>
                    </div>
                    <input type="file" x-ref="aiFile" @change="handleFileUpload" class="hidden" accept="image/*">
                </div>

                <!-- Action Button -->
                <div class="pt-4 border-t border-gray-100">
                    <button type="button" @click="generate()" :disabled="!title || loading" class="w-full h-14 bg-gradient-to-r from-indigo-600 to-violet-700 text-white rounded-2xl font-bold flex items-center justify-center gap-3 shadow-xl shadow-indigo-200 hover:scale-[1.02] active:scale-95 transition-all disabled:grayscale disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="!loading">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L14.85 9.15L22 12L14.85 14.85L12 22L9.15 14.85L2 12L9.15 9.15L12 2Z"/></svg>
                                <span>Magic Generate</span>
                            </div>
                        </template>
                        <template x-if="loading">
                            <div class="flex items-center gap-3">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Thinking...</span>
                            </div>
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function aiAssistant() {
    return {
        openModal: false,
        loading: false,
        title: '',
        hint: '',
        image: null,
        createVariants: false,
        
        init() {
            // Watch for changes in product name to sync with assistant
            setInterval(() => {
                if (!this.openModal && !this.title) {
                    const nameInput = document.getElementById('product_name');
                    if (nameInput && nameInput.value) {
                        this.title = nameInput.value;
                    }
                }
            }, 5000);
        },

        handleFileUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = (f) => {
                this.image = f.target.result;
            };
            reader.readAsDataURL(file);
        },

        async generate() {
            if (!this.title) return;
            
            this.loading = true;
            try {
                // Find selected attribute IDs from the simpleVariantBuilder component if it exists
                const variantBuilder = document.querySelector('[x-data^="simpleVariantBuilder"]');
                let selectedAttributeIds = [];
                if (variantBuilder && variantBuilder.__x) {
                    selectedAttributeIds = variantBuilder.__x.$data.selectedAttributes.map(a => a.id);
                } else {
                    // Fallback: search for checked checkboxes if Alpine data isn't directly accessible
                    const checked = document.querySelectorAll('input[type="checkbox"].checkbox-indigo:checked');
                    selectedAttributeIds = Array.from(checked).map(i => i.value).filter(v => !isNaN(v));
                }

                const response = await axios.post('{{ route("admin.settings.ai.generate-info") }}', {
                    title: this.title,
                    hint: this.hint,
                    image: this.image,
                    selected_attribute_ids: selectedAttributeIds
                });

                if (response.data.success) {
                    const data = response.data.data;
                    this.populateForm(data);
                    this.openModal = false;
                    
                    // Show success toast
                    window.dispatchEvent(new CustomEvent('toast', { 
                        detail: { message: 'Product profile generated perfectly!', type: 'success' } 
                    }));
                }
            } catch (error) {
                console.error(error);
                alert('Assistant failed to process request. Please check AI settings.');
            } finally {
                this.loading = false;
            }
        },

        populateForm(data) {
            // Basic Info
            const nameField = document.getElementById('product_name');
            if (nameField) nameField.value = data.name || this.title;
            
            this.setFieldValue('textarea[name="short_description"]', data.short_description);
            this.setFieldValue('textarea[name="description"]', data.description);
            
            // Pricing & Inventory
            this.setFieldValue('input[name="price"]', data.price);
            this.setFieldValue('input[name="compare_price"]', data.compare_price);
            this.setFieldValue('input[name="quantity"]', data.quantity);
            
            // Unit Handling
            if (data.unit) {
                const unitSelect = document.querySelector('select[name="unit"]');
                if (unitSelect) {
                    const option = Array.from(unitSelect.options).find(opt => 
                        opt.value.toLowerCase() === data.unit.toLowerCase() || 
                        opt.text.toLowerCase().includes(data.unit.toLowerCase())
                    );
                    if (option) unitSelect.value = option.value;
                }
            }
            
            // Category
            if (data.category_id) {
                const catSelect = document.querySelector('select[name="category_id"]');
                if (catSelect && catSelect.querySelector(`option[value="${data.category_id}"]`)) {
                    catSelect.value = data.category_id;
                }
            }
            
            // SEO - Generated from Name and Short Description to save AI costs
            const metaTitle = data.name || this.title;
            const metaDescription = data.short_description || '';
            
            this.setFieldValue('input[name="meta_title"]', metaTitle);
            this.setFieldValue('textarea[name="meta_description"]', metaDescription.substring(0, 160));
            
            // Images - Generated professional shots
            const aiImages = [];
            if (data.primary_shot) aiImages.push(data.primary_shot);
            if (data.info_shot) aiImages.push(data.info_shot);
            
            if (aiImages.length > 0) {
                window.dispatchEvent(new CustomEvent('add-ai-images', { detail: aiImages }));
                // Show a small success message for images
                if (typeof showToast === 'function') {
                    showToast(`${aiImages.length} professional shots generated and added to Images tab!`);
                }
                // Switch to media tab to show images
                window.dispatchEvent(new CustomEvent('switch-tab', { detail: 'media' }));
            }

            
            // Variants
            if (this.createVariants && data.variants && data.variants.length > 0) {
                window.dispatchEvent(new CustomEvent('apply-ai-variants', { detail: data.variants }));
                window.dispatchEvent(new CustomEvent('switch-tab', { detail: 'variants' }));
            }
        },

        setFieldValue(selector, value) {
            if (value === undefined || value === null) return;
            const el = document.querySelector(selector);
            if (el) el.value = value;
        }
    }
}
</script>

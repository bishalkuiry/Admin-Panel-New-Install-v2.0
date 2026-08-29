@extends('admin.layouts.app')

@section('title', 'App Modules')

@section('content')
<div x-data="homeHeaderManager()" class="space-y-6 max-w-7xl mx-auto pb-16">
    
    <!-- 🏠 Sleek Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-100 shadow-xs">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">App Modules</h1>
                <span class="px-3 py-1 bg-orange-100 text-orange-700 text-xs font-black rounded-full uppercase tracking-wider" 
                      x-text="tabs.length + ' Active'"></span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Manage vertical business modules, feature types, wallpapers & quick access shortcuts</p>
        </div>

        <button type="button" 
                @click="openCreateModuleModal()"
                class="px-5 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-500/20 hover:shadow-orange-500/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span>Create New Module</span>
        </button>
    </div>

    <!-- 🎛️ Compact Global Toggles -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Category Tabs -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-xs flex items-center justify-between cursor-pointer"
             @click="toggleSetting('tabs_active')">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" :class="settings.tabs_active ? 'bg-orange-50 text-orange-600' : 'bg-gray-100 text-gray-400'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-xs">Module Switcher Bar</p>
                    <p class="text-[11px] text-gray-400">Display top module tabs in user app</p>
                </div>
            </div>
            <label class="toggle" @click.stop>
                <input type="checkbox" :checked="settings.tabs_active" @change="toggleSetting('tabs_active')">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <!-- Background Media -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-xs flex items-center justify-between cursor-pointer"
             @click="toggleSetting('background_active')">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" :class="settings.background_active ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-400'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-xs">Background Wallpapers</p>
                    <p class="text-[11px] text-gray-400">Show module header images & gifs</p>
                </div>
            </div>
            <label class="toggle" @click.stop>
                <input type="checkbox" :checked="settings.background_active" @change="toggleSetting('background_active')">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <!-- Quick Cards -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-xs flex items-center justify-between cursor-pointer"
             @click="toggleSetting('cards_active')">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" :class="settings.cards_active ? 'bg-purple-50 text-purple-600' : 'bg-gray-100 text-gray-400'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-xs">Quick Access Cards</p>
                    <p class="text-[11px] text-gray-400">Enable quick shortcut pills</p>
                </div>
            </div>
            <label class="toggle" @click.stop>
                <input type="checkbox" :checked="settings.cards_active" @change="toggleSetting('cards_active')">
                <span class="toggle-slider"></span>
            </label>
        </div>
    </div>

    <!-- 🎨 Module Icon Style Switcher Bar -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <span>🎨</span> Header Module Icon Style Switcher
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">Control how top header modules/tabs display icons in the User App</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" 
                    @click="setModuleIconStyle('image_only')"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 cursor-pointer transition text-xs font-bold"
                    :class="(settings.module_icon_style || 'image_and_name') === 'image_only' ? 'bg-orange-50 border-orange-500 text-orange-700 shadow-xs' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                <span>🖼️ Style 1: Only Module Image</span>
            </button>
            <button type="button" 
                    @click="setModuleIconStyle('image_and_name')"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 cursor-pointer transition text-xs font-bold"
                    :class="(settings.module_icon_style || 'image_and_name') === 'image_and_name' ? 'bg-orange-50 border-orange-500 text-orange-700 shadow-xs' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'">
                <span>🖼️🔤 Style 2: Module Image & Name</span>
            </button>
        </div>
    </div>

    <!-- 📦 Sleek Module Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="(tab, index) in tabs" :key="tab.id">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all overflow-hidden flex flex-col justify-between group">
                <div>
                    <!-- Module Wallpaper Preview Header -->
                    <div class="relative h-24 bg-slate-900 p-4 flex items-end justify-between overflow-hidden">
                        <template x-if="tab.background_url">
                            <img :src="tab.background_url" class="absolute inset-0 w-full h-full object-cover opacity-60">
                        </template>
                        <div class="relative z-10 flex items-center gap-2">
                            <template x-if="tab.icon_url">
                                <img :src="tab.icon_url" class="w-8 h-8 rounded-lg object-cover border border-white/20 shadow-sm">
                            </template>
                            <span class="px-2.5 py-1 bg-black/60 backdrop-blur-md text-white text-[10px] font-black rounded-lg uppercase tracking-wider border border-white/10"
                                  x-text="tab.module_type || 'grocery'"></span>
                        </div>

                        <!-- Top Floating Toggle -->
                        <div class="relative z-10">
                            <label class="toggle" @click.stop>
                                <input type="checkbox" :checked="tab.is_active" @change="toggleTabActive(tab)">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Module Title & Stats -->
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-black text-gray-900 group-hover:text-orange-600 transition-colors" x-text="tab.name || 'Module #' + tab.id"></h3>
                            <span class="text-xs font-bold text-gray-400" x-text="'#' + (index + 1)"></span>
                        </div>

                        <div class="flex items-center gap-4 text-xs text-gray-500 mt-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                <span class="font-semibold text-gray-700" x-text="(tab.all_cards?.length || 0) + ' Quick Cards'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="p-4 bg-gray-50/70 border-t border-gray-100 flex items-center justify-between gap-2">
                    <a :href="'/admin/home-header/tabs/' + tab.id + '/edit'"
                       class="flex-1 py-2 px-4 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs rounded-xl shadow-xs flex items-center justify-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 012.828 0L20 7m-2.828-4L11 11.828V15h3.172l9.828-9.828z"/></svg>
                        <span>Edit Module & Cards</span>
                    </a>

                    <button type="button" @click="deleteTab(tab)"
                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- ➕ Clean Create New Module Modal -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCreateModal" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showCreateModal" x-transition.scale.origin.center class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
                <div class="bg-gray-900 text-white px-6 py-4 flex items-center justify-between">
                    <h3 class="font-bold text-base">Create New Module</h3>
                    <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>

                <div class="p-6 space-y-4">
                    <!-- Module Name -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Module Name *</label>
                        <input type="text" x-model="newModuleForm.name" placeholder="e.g. Grocery, Pharmacy, Food Delivery" class="w-full h-10 px-3.5 text-sm border border-gray-200 rounded-xl outline-none focus:border-orange-500">
                    </div>

                    <!-- Module Vertical Type -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Module Vertical Type *</label>
                        <select x-model="newModuleForm.module_type" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-xl outline-none focus:border-orange-500">
                            <option value="grocery">Grocery (Units: kg/g/pcs, Shelf Life)</option>
                            <option value="food">Food Delivery (Veg/Non-Veg, Add-ons, Prep Time)</option>
                            <option value="pharmacy">Pharmacy (Prescription Doctor Rx Upload)</option>
                            <option value="ecommerce">Ecommerce / General Store (Color/Size Specs)</option>
                            <option value="cosmetic">Cosmetic & Beauty Products (Shades, Skincare Specs)</option>
                            <option value="flower">Flowers & Gift Items (Occasions, Bouquets)</option>
                            <option value="ride">Ride Sharing (Taxi, Auto, Bike Fares)</option>
                            <option value="parcel">Parcel / Courier Delivery (Weight, Addresses)</option>
                            <option value="service">Home Services & Repairs</option>
                        </select>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900">Cancel</button>
                    <button type="button" @click="submitNewModule()" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-sm cursor-pointer">Create Module</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function homeHeaderManager() {
    return {
        tabs: @json($tabs),
        settings: @json($settings),
        showCreateModal: false,
        newModuleForm: { name: '', module_type: 'grocery', background_url: '', background_type: 'image', is_active: true },

        openCreateModuleModal() {
            this.newModuleForm = { name: '', module_type: 'grocery', background_url: '', background_type: 'image', is_active: true };
            this.showCreateModal = true;
        },

        async submitNewModule() {
            if (!this.newModuleForm.name) return alert('Please enter module name');
            try {
                const response = await fetch("{{ route('admin.home-header.tabs.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.newModuleForm)
                });
                const res = await response.json();
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'Error creating module');
                }
            } catch (e) {
                alert('Error: ' + e.message);
            }
        },

        async toggleTabActive(tab) {
            tab.is_active = !tab.is_active;
            try {
                await fetch(`/admin/home-header/tabs/${tab.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ is_active: tab.is_active })
                });
            } catch (e) {
                alert('Failed to update status');
            }
        },

        async toggleSetting(key) {
            this.settings[key] = !this.settings[key];
            try {
                await fetch("{{ route('admin.home-header.settings.update') }}", {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.settings)
                });
            } catch (e) {
                alert('Failed to update global setting');
            }
        },

        async setModuleIconStyle(val) {
            this.settings.module_icon_style = val;
            try {
                const res = await fetch("{{ route('admin.home-header.settings.update') }}", {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.settings)
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    alert(data.message || 'Failed to update module style');
                }
            } catch (e) {
                alert('Failed to update icon style: ' + e.message);
            }
        },

        async deleteTab(tab) {
            if (!confirm(`Are you sure you want to delete "${tab.name}"?`)) return;
            try {
                const response = await fetch(`/admin/home-header/tabs/${tab.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const res = await response.json();
                if (res.success) {
                    this.tabs = this.tabs.filter(t => t.id !== tab.id);
                }
            } catch (e) {
                alert('Error deleting module');
            }
        }
    };
}
</script>
@endsection

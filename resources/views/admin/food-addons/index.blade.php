@extends('admin.layouts.app')

@section('title', 'Food Add-ons')

@section('content')
<div x-data="foodAddonApp()" class="space-y-6 max-w-7xl mx-auto pb-12">
    
    <!-- Top Header Navigation Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-100 shadow-xs">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Food Add-ons & Toppings</h1>
            <p class="text-xs text-gray-500 mt-0.5">Manage extra toppings, side drinks, sauces, and combo choices for food products</p>
        </div>

        <button type="button" @click="openAddModal()" 
                class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-orange-600/20 flex items-center gap-2 transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add New Add-on</span>
        </button>
    </div>

    <!-- Add-ons List Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50">
                        <th class="py-3.5 px-4 rounded-l-xl">Add-on Name</th>
                        <th class="py-3.5 px-4">Price</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right rounded-r-xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($addons as $addon)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="py-4 px-4 font-bold text-gray-900">
                                {{ $addon->name }}
                            </td>
                            <td class="py-4 px-4 font-mono font-bold text-orange-600">
                                {{ number_format($addon->price, 2) }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase {{ $addon->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $addon->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click='openEditModal(@json($addon))'
                                            class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 012.828 0L20 7m-2.828-4L11 11.828V15h3.172l9.828-9.828z"/></svg>
                                    </button>
                                    <button type="button" @click="deleteAddon({{ $addon->id }})"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                <p class="font-bold text-gray-700">No Food Add-ons Found</p>
                                <p class="text-xs text-gray-400 mt-1">Click "Add New Add-on" to create extra toppings like Cheese, Drinks, Sauce, Water.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $addons->links() }}
        </div>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showModal" x-transition.scale.origin.center class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
                <div class="bg-gray-900 text-white px-6 py-4 flex items-center justify-between">
                    <h3 class="font-bold text-base" x-text="form.id ? 'Edit Food Add-on' : 'Add New Food Add-on'"></h3>
                    <button type="button" @click="showModal = false" class="text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Add-on Name *</label>
                        <input type="text" x-model="form.name" placeholder="e.g. Extra Cheese, Cold Drink, Chili Dip" class="w-full h-10 px-3.5 text-sm border border-gray-200 rounded-xl outline-none focus:border-orange-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Price *</label>
                        <input type="number" step="0.01" x-model="form.price" placeholder="0.00" class="w-full h-10 px-3.5 text-sm border border-gray-200 rounded-xl outline-none focus:border-orange-500 font-mono">
                    </div>

                    <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                        <span class="text-xs font-semibold text-gray-700">Active Status</span>
                        <label class="toggle">
                            <input type="checkbox" x-model="form.is_active">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900">Cancel</button>
                    <button type="button" @click="saveAddon()" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer">Save Add-on</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function foodAddonApp() {
    return {
        showModal: false,
        form: { id: null, name: '', price: '0.00', is_active: true },

        openAddModal() {
            this.form = { id: null, name: '', price: '0.00', is_active: true };
            this.showModal = true;
        },

        openEditModal(addon) {
            this.form = { ...addon };
            this.showModal = true;
        },

        async saveAddon() {
            if (!this.form.name) return alert('Please enter add-on name');
            try {
                const isEdit = Boolean(this.form.id);
                const url = isEdit ? `/admin/food-addons/${this.form.id}` : "{{ route('admin.food-addons.store') }}";
                const method = isEdit ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                const res = await response.json();
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'Error saving add-on');
                }
            } catch (e) {
                alert('Error: ' + e.message);
            }
        },

        async deleteAddon(id) {
            if (!confirm('Are you sure you want to delete this add-on?')) return;
            try {
                const response = await fetch(`/admin/food-addons/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const res = await response.json();
                if (res.success) {
                    location.reload();
                }
            } catch (e) {
                alert('Error deleting add-on');
            }
        }
    };
}
</script>
@endsection

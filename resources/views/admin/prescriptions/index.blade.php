@extends('admin.layouts.app')

@section('title', 'Prescription Orders & Medicine Workflow')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Prescription Upload & Medicine Workflow</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Review uploaded prescriptions, prepare prescribed medicines list & send price estimates for customer approval</p>
        </div>
    </div>

    <!-- Prescriptions Table -->
    <div class="card overflow-hidden p-4 sm:p-5 space-y-3">
        <div class="overflow-x-auto">
            <table class="table w-full text-xs min-w-[650px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Prescription ID</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Customer</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Prescription File</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Estimated Price</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Status</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($prescriptions as $rx)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-4 font-bold text-gray-900">#RX-{{ $rx->id }}</td>
                            <td class="py-3 px-4 font-medium text-gray-800">{{ $rx->user?->name ?? 'User #'.$rx->user_id }}</td>
                            <td class="py-3 px-4">
                                <a href="{{ storage_url($rx->prescription_file) }}" target="_blank" class="inline-flex items-center gap-1 text-orange-600 font-bold hover:underline">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>View Document</span>
                                </a>
                            </td>
                            <td class="py-3 px-4 font-extrabold text-green-700">₹{{ number_format($rx->estimated_price, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $rx->status === 'customer_approved' || $rx->status === 'ordered' ? 'bg-green-100 text-green-700' : ($rx->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ str_replace('_', ' ', $rx->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button type="button" @click="$dispatch('open-prep-modal', {{ json_encode($rx) }})" class="btn btn-secondary text-xs py-1 px-2.5">
                                    Prepare Medicines
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-400">No prescription uploads found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Prepare Medicines Modal -->
<div x-data="{ open: false, rx: null, medicines: [{ name: '', quantity: 1, price: 0 }] }" 
     @open-prep-modal.window="open = true; rx = $event.detail; if(rx && rx.prescribed_medicines && rx.prescribed_medicines.length) medicines = rx.prescribed_medicines;" 
     x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-sm text-gray-900" x-text="'Prepare Medicines for RX #' + (rx ? rx.id : '')"></h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <template x-if="rx">
                <form :action="'{{ url('admin/prescriptions') }}/' + rx.id + '/prepare'" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="label font-bold">Prescribed Medicines List</label>
                        <template x-for="(med, idx) in medicines" :key="idx">
                            <div class="flex items-center gap-2">
                                <input type="text" :name="'medicines[' + idx + '][name]'" x-model="med.name" class="input text-xs flex-1" placeholder="Medicine Name" required>
                                <input type="number" :name="'medicines[' + idx + '][quantity]'" x-model="med.quantity" class="w-16 input text-xs text-center" min="1" placeholder="Qty" required>
                                <input type="number" step="0.01" :name="'medicines[' + idx + '][price]'" x-model="med.price" class="w-20 input text-xs text-center" placeholder="Price ₹" required>
                                <button type="button" @click="medicines.splice(idx, 1)" class="text-red-500 hover:text-red-700 p-1">✕</button>
                            </div>
                        </template>
                        <button type="button" @click="medicines.push({ name: '', quantity: 1, price: 0 })" class="btn btn-secondary text-[10px] py-1 px-2">
                            + Add Medicine Item
                        </button>
                    </div>

                    <div>
                        <label class="label font-bold">Pharmacist / Admin Notes</label>
                        <textarea name="pharmacist_notes" x-model="rx.pharmacist_notes" rows="2" class="input text-xs" placeholder="Dosage instructions, substitution notes..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" @click="open = false" class="btn btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn btn-primary text-xs">Save & Send for Customer Approval</button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'eKYC Form Builder & Verification')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Dynamic eKYC Form Builder</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Configure required verification documents for Vendors and Riders</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.kyc.index', ['role' => 'vendor']) }}" class="btn text-xs font-semibold flex items-center gap-1.5 {{ $role === 'vendor' ? 'btn-primary' : 'btn-secondary' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Vendor eKYC</span>
            </a>
            <a href="{{ route('admin.kyc.index', ['role' => 'rider']) }}" class="btn text-xs font-semibold flex items-center gap-1.5 {{ $role === 'rider' ? 'btn-primary' : 'btn-secondary' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Rider eKYC</span>
            </a>
        </div>
    </div>

    <!-- Field Builder Card -->
    <div class="card p-4 sm:p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
            <h3 class="text-sm font-bold text-gray-900">Configured Document Requirements ({{ ucfirst($role) }}s)</h3>
            <button type="button" @click="$dispatch('open-add-field-modal')" class="btn btn-primary text-xs flex items-center justify-center gap-1.5 w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Document Requirement</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @forelse($fields as $f)
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between text-xs">
                    <div>
                        <p class="font-bold text-gray-900">{{ $f->field_label }}</p>
                        <p class="text-[10px] text-gray-500 font-mono">Key: {{ $f->field_name }} | Type: {{ strtoupper($f->field_type) }}</p>
                        @if($f->is_required)
                            <span class="inline-block px-1.5 py-0.5 mt-1 bg-red-100 text-red-700 font-bold text-[9px] rounded">Required</span>
                        @endif
                    </div>
                    <form action="{{ route('admin.kyc.fields.destroy', $f->id) }}" method="POST" onsubmit="return confirm('Delete this eKYC requirement?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            @empty
                <div class="col-span-full p-6 text-center text-xs text-gray-400 border border-dashed rounded-lg">
                    No custom fields configured for {{ $role }}s yet. Click "Add Document Requirement".
                </div>
            @endforelse
        </div>
    </div>

    <!-- eKYC Submissions Table -->
    <div class="card overflow-hidden p-4 sm:p-5 space-y-3">
        <h3 class="text-sm font-bold text-gray-900">Submitted Verification Requests</h3>
        <div class="overflow-x-auto">
            <table class="table w-full text-xs min-w-[600px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Applicant</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Role</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Submission Date</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Status</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($submissions as $sub)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-4">
                                <p class="font-bold text-gray-900">{{ $sub->user?->name ?? 'User #'.$sub->user_id }}</p>
                                <p class="text-[10px] text-gray-500">{{ $sub->user?->email }} | {{ $sub->user?->phone }}</p>
                            </td>
                            <td class="py-3 px-4 uppercase font-bold text-[10px] text-gray-600">{{ $sub->role }}</td>
                            <td class="py-3 px-4 text-gray-500">{{ $sub->created_at?->format('d M Y, h:i A') }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $sub->status === 'verified' ? 'bg-green-100 text-green-700' : ($sub->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ $sub->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right flex items-center justify-end gap-2">
                                @if($sub->role === 'rider')
                                    <a href="{{ route('admin.delivery-partners.show', $sub->user_id) }}" class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Review Driver Docs
                                    </a>
                                @elseif($sub->role === 'vendor')
                                    @php
                                        $store = \App\Models\Store::where('owner_id', $sub->user_id)->first();
                                    @endphp
                                    @if($store)
                                        <a href="{{ route('admin.stores.show', $store) }}" class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Review Store Docs
                                        </a>
                                    @else
                                        <button type="button" @click="$dispatch('open-verify-modal', {{ json_encode($sub) }})" class="btn btn-secondary text-xs py-1 px-2.5">
                                            Review Docs
                                        </button>
                                    @endif
                                @else
                                    <button type="button" @click="$dispatch('open-verify-modal', {{ json_encode($sub) }})" class="btn btn-secondary text-xs py-1 px-2.5">
                                        Review Docs
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-400">
                                No eKYC submissions pending review.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Field Modal -->
<div x-data="{ open: false }" @open-add-field-modal.window="open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-sm text-gray-900">Add Custom eKYC Document Requirement</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.kyc.fields.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="target_role" value="{{ $role }}">
                <div>
                    <label class="label font-bold">Document Label</label>
                    <input type="text" name="field_label" class="input text-xs" required placeholder="e.g. Aadhaar Card Number / Driving License Photo">
                </div>
                <div>
                    <label class="label font-bold">System Key (Field Name)</label>
                    <input type="text" name="field_name" class="input text-xs" required placeholder="e.g. aadhaar_number / dl_photo">
                </div>
                <div>
                    <label class="label font-bold">Field Type</label>
                    <select name="field_type" class="input text-xs">
                        <option value="text">Short Text</option>
                        <option value="number">Number</option>
                        <option value="file">File / Photo Upload</option>
                        <option value="date">Expiration Date</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_required" value="1" checked id="is_req" class="rounded text-orange-500">
                    <label for="is_req" class="font-bold text-gray-800">Mandatory / Required Field</label>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="open = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Add Requirement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Customer Complaints & Penalty System')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Customer Complaints System</h1>
            <p class="text-sm text-gray-500 mt-1">Review photo/video evidence, approve refunds, issue vendor wallet penalties & account suspensions</p>
        </div>
        <a href="{{ route('admin.complaints.settings') }}" class="btn btn-secondary text-xs flex items-center justify-center gap-1.5 w-full sm:w-auto">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Complaint Settings</span>
        </a>
    </div>

    <!-- Complaints Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Ticket #</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Customer</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Category</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Store</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Status</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Action Taken</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($complaints as $c)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-4 font-bold text-gray-900">{{ $c->ticket_number }}</td>
                            <td class="py-3 px-4 font-medium text-gray-800">{{ $c->user?->name ?? 'User #'.$c->user_id }}</td>
                            <td class="py-3 px-4 font-semibold text-gray-700">{{ $c->category }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $c->store?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $c->status === 'approved' ? 'bg-green-100 text-green-700' : ($c->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ $c->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-gray-600">
                                {{ ucfirst(str_replace('_', ' ', $c->action_taken)) }}
                                @if($c->penalty_amount > 0)
                                    <span class="text-red-600 font-bold">(₹{{ number_format($c->penalty_amount, 2) }})</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button type="button" @click="$dispatch('open-complaint-modal', {{ json_encode($c) }})" class="btn btn-secondary text-xs py-1 px-2.5">
                                    Resolve Ticket
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400">
                                No customer complaints filed yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($complaints->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $complaints->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Complaint Resolution Modal -->
<div x-data="{ open: false, cmp: null }" 
     @open-complaint-modal.window="open = true; cmp = $event.detail" 
     x-show="open" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-base text-gray-900" x-text="'Resolve Ticket #' + (cmp ? cmp.ticket_number : '')"></h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <template x-if="cmp">
                <form :action="'{{ url('admin/complaints') }}/' + cmp.id + '/resolve'" method="POST" class="space-y-4">
                    @csrf
                    <div class="p-3 bg-gray-50 rounded-xl space-y-2">
                        <p><strong>Description:</strong> <span x-text="cmp.description"></span></p>
                        <template x-if="cmp.attachments && cmp.attachments.length > 0">
                            <div>
                                <p class="font-bold text-gray-700 mb-1">Attached Evidence Photos:</p>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(img, idx) in cmp.attachments" :key="idx">
                                        <a :href="img" target="_blank" class="block w-16 h-16 rounded-lg overflow-hidden border border-gray-200 hover:opacity-80">
                                            <img :src="img" class="w-full h-full object-cover" alt="Evidence">
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label class="label font-bold">Ticket Status</label>
                        <select name="status" x-model="cmp.status" class="input text-xs">
                            <option value="open">Open / Pending Review</option>
                            <option value="approved">Approve Complaint</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Reject Complaint</option>
                        </select>
                    </div>

                    <div>
                        <label class="label font-bold">Action to Enforce</label>
                        <select name="action_taken" x-model="cmp.action_taken" class="input text-xs">
                            <option value="none">No Financial Action</option>
                            <option value="refund_customer">Refund Customer (User Wallet Credit)</option>
                            <option value="penalize_vendor">Penalize Vendor (Deduct Vendor Wallet)</option>
                            <option value="suspend_account">Suspend Vendor Account</option>
                        </select>
                    </div>

                    <div>
                        <label class="label font-bold">Penalty / Refund Amount (₹)</label>
                        <input type="number" step="0.01" name="penalty_amount" x-model="cmp.penalty_amount" class="input text-xs" placeholder="0.00">
                    </div>

                    <div>
                        <label class="label font-bold">Admin Response / Note</label>
                        <textarea name="admin_response" x-model="cmp.admin_response" rows="2" class="input text-xs" placeholder="Resolution explanation..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" @click="open = false" class="btn btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn btn-primary text-xs">Enforce & Resolve</button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection

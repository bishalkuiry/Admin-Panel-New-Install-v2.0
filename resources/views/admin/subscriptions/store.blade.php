@extends('admin.layouts.app')

@section('title', 'Store Subscriptions & Packages')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Store Subscription Plans</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Configure Monthly, Yearly & Trial subscription packages for sellers with product limits & commission rates</p>
        </div>
        <button type="button" @click="$dispatch('open-add-plan-modal')" class="btn btn-primary text-xs flex items-center justify-center gap-1.5 w-full sm:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add Store Plan</span>
        </button>
    </div>

    <!-- Store Subscription Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @forelse($plans as $p)
            <div class="card p-5 space-y-3 flex flex-col justify-between border">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-base text-gray-900">{{ $p->name }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $p->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $p->is_active ? 'Active' : 'Disabled' }}</span>
                    </div>
                    <p class="text-2xl font-extrabold text-orange-600">₹{{ number_format($p->price, 2) }} <span class="text-xs text-gray-400 font-normal">/ {{ $p->duration_days }} Days</span></p>
                    <div class="space-y-1 text-xs text-gray-600 pt-2 border-t">
                        <p><strong>Max Products Limit:</strong> {{ number_format($p->max_products) }} items</p>
                        <p><strong>Commission Rate:</strong> {{ $p->commission_rate }}%</p>
                    </div>
                </div>

                <div class="pt-3 border-t flex justify-end">
                    <form action="{{ route('admin.subscriptions.store-plans.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Delete this subscription plan?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-bold">Delete Plan</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full card p-8 text-center text-xs text-gray-400">No store subscription plans configured yet.</div>
        @endforelse
    </div>

    <!-- Subscriptions Log -->
    <div class="card overflow-hidden p-4 sm:p-5 space-y-3">
        <h3 class="font-bold text-sm text-gray-900">Active Store Subscriptions</h3>
        <div class="overflow-x-auto">
            <table class="table w-full text-xs min-w-[600px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Store Name</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Plan</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Started</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Expires</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-4 font-bold text-gray-900">{{ $sub->store?->name }}</td>
                            <td class="py-3 px-4 font-semibold text-orange-600">{{ $sub->plan?->name }}</td>
                            <td class="py-3 px-4 text-gray-500 font-mono text-[10px]">{{ $sub->starts_at?->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-gray-500 font-mono text-[10px]">{{ $sub->expires_at?->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $sub->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $sub->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-400">No active store subscriptions.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Plan Modal -->
<div x-data="{ open: false }" @open-add-plan-modal.window="open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-sm text-gray-900">Add Store Subscription Plan</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form action="{{ route('admin.subscriptions.store-plans.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="label font-bold">Plan Name</label>
                    <input type="text" name="name" class="input text-xs" required placeholder="e.g. Pro Vendor Plan / Premium Store">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="label font-bold">Price (₹)</label>
                        <input type="number" step="0.01" name="price" class="input text-xs" required value="999.00">
                    </div>
                    <div>
                        <label class="label font-bold">Duration (Days)</label>
                        <input type="number" name="duration_days" class="input text-xs" required value="30">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="label font-bold">Max Products Limit</label>
                        <input type="number" name="max_products" class="input text-xs" required value="500">
                    </div>
                    <div>
                        <label class="label font-bold">Commission Rate (%)</label>
                        <input type="number" step="0.01" name="commission_rate" class="input text-xs" required value="8.00">
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" value="1" checked id="act_p" class="rounded text-orange-500">
                    <label for="act_p" class="font-bold text-gray-800">Plan Active</label>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="open = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Save Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

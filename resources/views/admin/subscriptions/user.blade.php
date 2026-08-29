@extends('admin.layouts.app')

@section('title', 'Customer VIP Memberships')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Customer VIP Membership System</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Configure VIP Membership plans with cashback %, free delivery perks, discounts & page builder</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.subscriptions.user-plans.builder') }}" class="btn btn-secondary text-xs flex items-center justify-center gap-1.5 shadow-xs">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>🎨 Visual Page Builder</span>
            </a>
            <button type="button" @click="$dispatch('open-add-user-plan-modal')" class="btn btn-primary text-xs flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add VIP Membership Plan</span>
            </button>
        </div>
    </div>

    <!-- Analytics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 space-y-1 bg-gradient-to-br from-orange-50 to-amber-50/30 border border-orange-100">
            <p class="text-[10px] font-bold uppercase tracking-wider text-orange-700">Active VIP Members</p>
            <p class="text-2xl font-black text-orange-900">{{ number_format($stats['total_members'] ?? 0) }}</p>
        </div>
        <div class="card p-4 space-y-1 bg-gradient-to-br from-green-50 to-emerald-50/30 border border-green-100">
            <p class="text-[10px] font-bold uppercase tracking-wider text-green-700">Membership Revenue</p>
            <p class="text-2xl font-black text-green-900">₹{{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
        </div>
        <div class="card p-4 space-y-1 bg-gradient-to-br from-blue-50 to-indigo-50/30 border border-blue-100">
            <p class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Free Deliveries Used</p>
            <p class="text-2xl font-black text-blue-900">{{ number_format($stats['total_free_deliveries'] ?? 0) }}</p>
        </div>
        <div class="card p-4 space-y-1 bg-gradient-to-br from-purple-50 to-violet-50/30 border border-purple-100">
            <p class="text-[10px] font-bold uppercase tracking-wider text-purple-700">Total Customer Savings</p>
            <p class="text-2xl font-black text-purple-900">₹{{ number_format($stats['total_savings_provided'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Membership Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @forelse($plans as $p)
            <div class="card p-5 space-y-3 flex flex-col justify-between border">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-base text-gray-900 flex items-center gap-1.5">
                            <span>{{ $p->badge_icon ?? '👑' }}</span>
                            <span>{{ $p->name }}</span>
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $p->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $p->is_active ? 'Active' : 'Disabled' }}</span>
                    </div>
                    <p class="text-2xl font-extrabold text-orange-600">₹{{ number_format($p->price, 2) }} <span class="text-xs text-gray-400 font-normal">/ {{ $p->duration_days }} Days</span></p>
                    <div class="space-y-1 text-xs text-gray-600 pt-2 border-t">
                        <p><strong>Wallet Cashback:</strong> {{ $p->cashback_percentage }}% (Max ₹{{ $p->max_cashback_per_month }}/mo)</p>
                        <p><strong>Extra Member Discount:</strong> {{ $p->extra_discount_percentage > 0 ? $p->extra_discount_percentage . '%' : 'None' }}</p>
                        <p><strong>Free Delivery Perk:</strong> {{ $p->free_delivery ? 'Yes (Min order ₹' . number_format($p->min_order_for_free_delivery, 2) . ')' : 'No' }}</p>
                        <p><strong>Priority Support:</strong> {{ $p->priority_support ? 'Yes (24/7 VIP Line)' : 'No' }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t flex justify-end">
                    <form action="{{ route('admin.subscriptions.user-plans.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Delete or deactivate this VIP membership plan?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-bold">Remove Plan</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full card p-8 text-center text-xs text-gray-400">No VIP membership plans configured yet.</div>
        @endforelse
    </div>

    <!-- Active Memberships -->
    <div class="card overflow-hidden p-4 sm:p-5 space-y-3">
        <h3 class="font-bold text-sm text-gray-900">Active Customer VIP Memberships</h3>
        <div class="overflow-x-auto">
            <table class="table w-full text-xs min-w-[600px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Customer</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">VIP Plan</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Started</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Expires</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($memberships as $m)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-4 font-bold text-gray-900">{{ $m->user?->name }}</td>
                            <td class="py-3 px-4 font-semibold text-orange-600">{{ $m->plan?->name }}</td>
                            <td class="py-3 px-4 text-gray-500 font-mono text-[10px]">{{ $m->starts_at?->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-gray-500 font-mono text-[10px]">{{ $m->expires_at?->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $m->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $m->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-400">No active customer memberships.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add VIP Plan Modal -->
<div x-data="{ open: false }" @open-add-user-plan-modal.window="open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-sm text-gray-900">Add Customer VIP Membership Plan</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form action="{{ route('admin.subscriptions.user-plans.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="label font-bold">VIP Plan Name</label>
                    <input type="text" name="name" class="input text-xs" required placeholder="e.g. Crown VIP Member / Gold VIP">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="label font-bold">Price (₹)</label>
                        <input type="number" step="0.01" name="price" class="input text-xs" required value="299.00">
                    </div>
                    <div>
                        <label class="label font-bold">Duration (Days)</label>
                        <input type="number" name="duration_days" class="input text-xs" required value="30">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="label font-bold">Wallet Cashback (%)</label>
                        <input type="number" step="0.01" name="cashback_percentage" class="input text-xs" required value="5.00">
                    </div>
                    <div>
                        <label class="label font-bold">Extra Member Discount (%)</label>
                        <input type="number" step="0.01" name="extra_discount_percentage" class="input text-xs" value="10.00">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="label font-bold">Min Order for Free Delivery (₹)</label>
                        <input type="number" step="0.01" name="min_order_for_free_delivery" class="input text-xs" value="199.00">
                    </div>
                    <div>
                        <label class="label font-bold">Max Free Orders/Mo</label>
                        <input type="number" name="max_free_deliveries_per_month" class="input text-xs" value="10">
                    </div>
                </div>
                <div class="space-y-1 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="free_delivery" value="1" checked class="rounded text-orange-500">
                        <span class="font-bold text-gray-800">Free Delivery Perk</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="priority_support" value="1" checked class="rounded text-orange-500">
                        <span class="font-bold text-gray-800">24/7 Priority Support</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded text-orange-500">
                        <span class="font-bold text-gray-800">Plan Active</span>
                    </label>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="open = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Save VIP Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

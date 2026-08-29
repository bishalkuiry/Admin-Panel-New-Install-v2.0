@extends('admin.layouts.app')
@section('title', 'Seller Details')
@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.sellers.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-wide">{{ $seller->name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Seller Profile</span>
                    <span class="text-gray-300">&bull;</span>
                    <span class="text-xs font-medium text-gray-500">ID: #{{ str_pad($seller->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.sellers.toggle-status', $seller) }}" method="POST">
                @csrf
                <x-permission-btn 
                    permission="sellers.update" 
                    type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2.5 {{ $seller->is_active ? 'bg-red-50 border-red-200 text-red-700 hover:bg-red-100 focus:ring-red-50' : 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100 focus:ring-green-50' }} border rounded-lg text-sm font-semibold transition-all focus:ring-4" 
                    label="{{ $seller->is_active ? 'Deactivate Account' : 'Activate Account' }}"
                    icon='{!! $seller->is_active 
                        ? "<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636\"/></svg>"
                        : "<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\"/></svg>"
                    !!}'
                />
            </form>
        </div>
    </div>

    <!-- Assigned Stores -->
    <div class="card bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Assigned Stores ({{ $seller->ownedStores->count() }})</h3>
            <x-permission-btn 
                permission="stores.update" 
                onclick="openAssignStoreModal()" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-black transition-colors" 
                label="Assign Store"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>'
            />
        </div>
        <div class="p-6 divide-y divide-gray-100">
            @forelse($seller->ownedStores as $store)
                <div class="flex flex-col md:flex-row items-center gap-8 py-6 first:pt-0 last:pb-0">
                    <div class="w-24 h-24 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-200 shrink-0 shadow-sm">
                        @if($store->logo)
                            <img src="{{ storage_url($store->logo) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-3xl font-bold text-gray-300 uppercase">{{ substr($store->name, 0, 2) }}</span>
                        @endif
                    </div>
                    <div class="flex-1 text-center md:text-left space-y-2">
                        <h4 class="text-xl font-bold text-gray-900 tracking-tight">{{ $store->name }}</h4>
                        <p class="text-sm font-medium text-gray-500">{{ $store->city }}, {{ $store->state }}</p>
                        <div class="flex items-center justify-center md:justify-start gap-2 pt-1">
                            @php
                                $statusClass = [
                                    'active' => 'bg-green-100 text-green-700 border-green-200',
                                    'pending' => 'bg-orange-100 text-orange-700 border-orange-200',
                                    'suspended' => 'bg-red-100 text-red-700 border-red-200'
                                ];
                                $storeStatus = $store->status instanceof \App\Enums\StoreStatus ? $store->status->value : $store->status;
                                $badgeClass = $statusClass[$storeStatus] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                            @endphp
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $badgeClass }}">{{ ucfirst($storeStatus) }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                        <a href="{{ route('admin.stores.show', $store) }}" class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                            View Store
                        </a>
                        <form action="{{ route('admin.sellers.unassign-store', ['seller' => $seller]) }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <input type="hidden" name="store_id" value="{{ $store->id }}">
                            <x-permission-btn 
                                permission="stores.update" 
                                type="submit"
                                class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 bg-white border border-red-200 text-red-600 rounded-lg text-sm font-semibold hover:bg-red-50 transition-all shadow-sm group" 
                                label="Unassign"
                                onclick="return confirm('Are you sure you want to unassign this store?')"
                                icon='<svg class="w-4 h-4 text-red-500 group-hover:text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>'
                            />
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-dashed border-gray-200">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 uppercase tracking-widest">No Store Assigned</h4>
                    <p class="text-sm text-gray-500 mt-2">Link this seller to a store to enable management.</p>
                    <x-permission-btn 
                        permission="stores.update" 
                        onclick="openAssignStoreModal()" 
                        class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-black transition-all shadow-lg shadow-gray-200" 
                        label="Assign Store"
                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>'
                    />
                </div>
            @endforelse
        </div>
    </div>

    <!-- Profile & Security Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Card -->
        <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6 text-center">
            <div class="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center text-3xl font-bold text-gray-500 mb-4 border-4 border-white shadow-sm">
                {{ substr($seller->name, 0, 1) }}
            </div>
            <h2 class="text-xl font-bold text-gray-900">{{ $seller->name }}</h2>
            <div class="mt-8 space-y-5 text-left border-t border-gray-100 pt-6">
                <div class="group">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-primary-600 transition-colors">Email Address</label>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $seller->email }}</p>
                </div>
                <div class="group">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-primary-600 transition-colors">Phone Number</label>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $seller->phone ?? 'Not provided' }}</p>
                </div>
                <div class="group">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-primary-600 transition-colors">Joined Date</label>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ \App\Helpers\DateHelper::format($seller->created_at) }}</p>
                </div>
            </div>
        </div>

        <!-- Security Setup -->
        <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Security Settings</h3>
            <form action="{{ route('admin.sellers.update', $seller) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $seller->name }}">
                <input type="hidden" name="email" value="{{ $seller->email }}">
                <input type="hidden" name="phone" value="{{ $seller->phone }}">

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">New Password</label>
                    <input type="password" name="password" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:bg-white transition-all" placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:bg-white transition-all" placeholder="••••••••">
                </div>
                
                <x-permission-btn 
                    permission="sellers.update" 
                    type="submit"
                    class="w-full py-3 bg-gray-900 text-white rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-black transition-all shadow-md active:scale-[0.98]" 
                    label="Update Password"
                />
            </form>
        </div>
    </div>
    
    <!-- Recent Activity -->
    @if($seller->orders->isNotEmpty())
    <div class="card bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Recent Activity</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead class="bg-white border-b border-gray-100 text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                    <tr>
                        <th class="px-6 py-4 text-left">Order</th>
                        <th class="px-6 py-4 text-left">Date</th>
                        <th class="px-6 py-4 text-left">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($seller->orders->take(5) as $order)
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-6 py-4 font-bold text-gray-900 text-xs">#{{ $order->order_number }}</td>
                        <td class="px-6 py-4 text-gray-500 text-xs font-medium">{{ \App\Helpers\DateHelper::format($order->created_at) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded border border-gray-100 text-[10px] font-bold uppercase tracking-wider bg-gray-50 text-gray-600">{{ $order->status->label() }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-primary-600 hover:text-primary-700 text-[10px] font-black uppercase tracking-wider hover:underline">View Details</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Assign Store Modal -->
<div id="assignStoreModal" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-100 transform transition-all">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
            <h3 class="text-lg font-bold text-gray-900 tracking-tight">Assign Store</h3>
            <button onclick="closeAssignStoreModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 hover:bg-gray-100 rounded-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.sellers.assign-store', $seller) }}" method="POST" class="p-6">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Select Store</label>
                    <div class="relative">
                        <select name="store_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:bg-white transition-all appearance-none">
                            <option value="">-- Choose Available Store --</option>
                            @foreach($availableStores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }} ({{ $store->city }})</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    @if($availableStores->isEmpty())
                    <p class="text-xs font-medium text-red-500 mt-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        All active stores are currently assigned.
                    </p>
                    @endif
                </div>
                <div class="flex gap-3 pt-2">
                    <x-permission-btn 
                        permission="stores.update" 
                        type="submit"
                        class="flex-1 py-3.5 bg-gray-900 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-black transition-all shadow-lg shadow-gray-200 active:transform active:scale-[0.98]" 
                        label="Confirm Assignment"
                        disabled="{{ $availableStores->isEmpty() ? 'true' : 'false' }}"
                    />
                    <button type="button" onclick="closeAssignStoreModal()" class="flex-1 py-3.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-gray-50 transition-all">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignStoreModal() {
    const modal = document.getElementById('assignStoreModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    // Animate in
    const content = modal.querySelector('div');
    content.classList.remove('scale-95', 'opacity-0');
    content.classList.add('scale-100', 'opacity-100');
}

function closeAssignStoreModal() {
    const modal = document.getElementById('assignStoreModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection

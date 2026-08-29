@extends('admin.layouts.app')
@section('title', 'Revenue & Commissions')
@section('content')
<div class="space-y-6" x-data="{ tab: '{{ request('tab', 'rules') }}' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-semibold text-gray-900">Revenue & Commissions</h1>
                <div x-data="{ open: false }" class="relative inline-block">
                    <button @click="open = !open" @click.away="open = false" class="text-gray-400 hover:text-indigo-600 transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         style="display: none;"
                         class="absolute left-0 mt-2 w-72 bg-white border border-gray-100 rounded-xl shadow-xl z-50 p-4 ring-1 ring-black/5">
                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-3">Commission Hierarchy Flow</h4>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="w-5 h-5 rounded-full bg-indigo-50 flex items-center justify-center text-[10px] font-bold text-indigo-600">1</span>
                                <span class="text-xs font-semibold text-gray-700">Product Specific</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-5 h-5 rounded-full bg-indigo-50 flex items-center justify-center text-[10px] font-bold text-indigo-600">2</span>
                                <span class="text-xs font-semibold text-gray-700">Store-Category Rule</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-5 h-5 rounded-full bg-indigo-50 flex items-center justify-center text-[10px] font-bold text-indigo-600">3</span>
                                <span class="text-xs font-semibold text-gray-700">Store Wide Rate</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-5 h-5 rounded-full bg-indigo-50 flex items-center justify-center text-[10px] font-bold text-indigo-600">4</span>
                                <span class="text-xs font-semibold text-gray-700">Global Default</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-50">
                            <p class="text-[10px] text-gray-500 italic leading-relaxed">
                                * The system uses the first rule it finds starting from level 1. If none found, it falls back to Global Default.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-1">Manage all commission levels from a single command center</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.commissions.create', ['type' => 'delivery_partner']) }}" class="btn-outline text-sm">
                Partner Rules
            </a>
            <a href="{{ route('admin.commissions.create', ['type' => 'store']) }}" class="btn-primary text-sm px-4">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Category Rule
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-1 border-b border-gray-200">
        <button @click="tab = 'rules'" :class="tab === 'rules' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-2 border-b-2 font-medium text-sm transition-colors">
            Advanced Rules
        </button>
        <button @click="tab = 'stores'" :class="tab === 'stores' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-2 border-b-2 font-medium text-sm transition-colors">
            Store Base Rates
        </button>
        <button @click="tab = 'global'" :class="tab === 'global' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-2 border-b-2 font-medium text-sm transition-colors">
            Global Default
        </button>
    </div>

    <!-- Tab 1: Advanced Rules -->
    <div x-show="tab === 'rules'" class="space-y-6">


        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Custom Category Rules</h3>
                        <p class="text-xs text-gray-500">Tier 2: Specific rules that override base rates</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="table-header">Name</th>
                            <th class="table-header">Store</th>
                            <th class="table-header">Category</th>
                            <th class="table-header text-center">Commission</th>
                            <th class="table-header text-center">Status</th>
                            <th class="table-header text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($storeCommissions as $rule)
                        <tr class="hover:bg-gray-50/50">
                            <td class="table-cell font-medium text-gray-900">{{ $rule->name }}</td>
                            <td class="table-cell">{{ $rule->store?->name ?? 'All Stores' }}</td>
                            <td class="table-cell text-gray-500">{{ $rule->storeCategory?->name ?? 'All Categories' }}</td>
                            <td class="table-cell text-center font-bold text-purple-700">{{ $rule->value_label }}</td>
                            <td class="table-cell text-center">
                                <form action="{{ route('admin.commissions.toggle', $rule) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $rule->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $rule->is_active ? 'Active' : 'Disabled' }}
                                    </button>
                                </form>
                            </td>
                            <td class="table-cell text-center">
                                <a href="{{ route('admin.commissions.edit', $rule) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-12 text-center text-gray-400">No custom category rules found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Partner Delivery Commissions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="table-header">Name</th>
                            <th class="table-header text-center">Type</th>
                            <th class="table-header text-center">Commission</th>
                            <th class="table-header text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($partnerCommissions as $rule)
                        <tr>
                            <td class="table-cell font-medium">{{ $rule->name }}</td>
                            <td class="table-cell text-center"><span class="badge badge-gray">{{ ucfirst($rule->calculation_type) }}</span></td>
                            <td class="table-cell text-center font-bold text-orange-700">{{ $rule->value_label }}</td>
                            <td class="table-cell text-center">
                                <a href="{{ route('admin.commissions.edit', $rule) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-8 text-center text-gray-400">No partner rules found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 2: Store Base Rates -->
    <div x-show="tab === 'stores'" class="space-y-4">
        <div class="card p-6 border-0 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Store-Wide Base Rates</h3>
                    <p class="text-sm text-gray-500">Tier 3: Standard rates applied if no category-rule exists</p>
                </div>
                <form action="{{ route('admin.commissions.index') }}" method="GET" class="relative group">
                    <input type="hidden" name="tab" value="stores">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search stores..." class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-full md:w-64 text-sm transition-all">
                </form>
            </div>

            <div class="overflow-x-auto border border-gray-100 rounded-xl">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-bold text-gray-700 uppercase tracking-wider text-[10px]">Store</th>
                            <th class="px-6 py-3 text-left font-bold text-gray-700 uppercase tracking-wider text-[10px]">Base Rate (%)</th>
                            <th class="px-6 py-3 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($stores as $store)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $store->logo }}" class="w-8 h-8 rounded-full border border-gray-100" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($store->name) }}&color=7F9CF5&background=EBF4FF'">
                                    <span class="font-semibold text-gray-900">{{ $store->name }}</span>
                                </div>
                            </td>
                            <form action="{{ route('admin.commissions.update-store-base', $store) }}" method="POST">
                                @csrf
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <input type="number" name="commission_percent" value="{{ (int)old('commission_percent', $store->commission_percent) }}" min="0" max="100" class="w-20 px-2 py-1 border border-gray-200 rounded text-center font-bold">
                                        <span class="text-gray-400 font-bold">%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="submit" class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-md text-xs font-bold hover:bg-indigo-100 transition-colors">Save</button>
                                </td>
                            </form>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $stores->links() }}
            </div>
        </div>
    </div>

    <!-- Tab 3: Global Default -->
    <div x-show="tab === 'global'" class="max-w-xl">
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Platform Global Default</h3>
                    <p class="text-xs text-gray-500">Tier 4: The final fallback for the entire platform</p>
                </div>
            </div>
            
            <div class="p-6">
                <form action="{{ route('admin.commissions.update-global') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Default Commission Percentage</label>
                        <div class="flex items-center gap-3">
                            <div class="relative flex-1">
                                <input type="number" 
                                       name="default_commission_percent" 
                                       value="{{ (int)old('default_commission_percent', $globalCommission) }}" 
                                       min="0" max="100" 
                                       class="w-full pl-8 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-bold text-gray-900 transition-all outline-none">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-400">%</span>
                            </div>
                            <button type="submit" class="bg-gray-900 text-white px-6 py-2 rounded-lg font-bold text-sm hover:bg-gray-800 transition-all shadow-sm">
                                Save Global Rate
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-3 bg-indigo-50 rounded-lg border border-indigo-100 flex items-start gap-2">
                        <svg class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-[11px] text-indigo-700 leading-relaxed">
                            This rate is applied ONLY if no Product, Category, or Store commission is found. Changing this affects new orders immediately.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

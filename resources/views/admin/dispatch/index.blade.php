@extends('admin.layouts.app')

@section('title', 'Dispatch Management')

@section('content')
<div x-data="{ 
    selectedItem: null,
    drawerOpen: false,
    openDetails(item) {
        if (item.module_type === 'ecommerce') {
            window.location.href = item.view_url;
            return;
        }
        this.selectedItem = item;
        this.drawerOpen = true;
    }
}" class="space-y-5">

    {{-- Top Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Dispatch Management Center</h1>
            <p class="text-sm text-gray-500 mt-1">Real-time dispatch overview across all InAllCart modules and active plugins</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.location.reload()" class="btn-secondary flex items-center gap-2 text-xs">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Refresh Feed</span>
            </button>
        </div>
    </div>

    {{-- KPI Summary Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div class="stat-label">Total Dispatch Orders</div>
                <div class="stat-icon bg-orange-50 text-orange-600 text-xl">🌐</div>
            </div>
            <div class="stat-value mt-3">{{ number_format($totalOrdersCount) }}</div>
            <div class="text-xs text-gray-500 mt-2 font-medium">Revenue: <span class="text-gray-900 font-semibold">${{ number_format($totalRevenue, 2) }}</span></div>
        </div>

        @foreach($activeModulesList as $modInfo)
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div class="stat-label">{{ $modInfo['name'] }}</div>
                <div class="stat-icon bg-slate-50 text-slate-700 text-xl">{{ $modInfo['icon'] }}</div>
            </div>
            <div class="stat-value mt-3">{{ number_format($modInfo['count']) }}</div>
            <div class="text-xs text-gray-500 mt-2 font-medium">Revenue: <span class="text-gray-900 font-semibold">${{ number_format($modInfo['revenue'], 2) }}</span></div>
        </div>
        @endforeach
    </div>

    {{-- Filter Form & Dynamic Module Tabs --}}
    <form method="GET" action="{{ route('admin.dispatch.index') }}" class="card p-4">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.dispatch.index', ['module' => 'all', 'search' => request('search')]) }}" class="px-3.5 py-2 rounded-lg text-xs font-semibold transition-all {{ $selectedModule === 'all' ? 'bg-orange-500 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All Modules ({{ number_format($totalOrdersCount) }})
                </a>

                @foreach($activeModulesList as $modInfo)
                <a href="{{ route('admin.dispatch.index', ['module' => $modInfo['type'], 'search' => request('search')]) }}" class="px-3.5 py-2 rounded-lg text-xs font-semibold transition-all {{ $selectedModule === $modInfo['type'] ? 'bg-orange-500 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <span>{{ $modInfo['icon'] }} {{ $modInfo['name'] }}</span>
                    <span class="ml-1 text-[11px] opacity-80">({{ number_format($modInfo['count']) }})</span>
                </a>
                @endforeach
            </div>

            <div class="flex items-center gap-2 w-full lg:w-auto">
                <input type="hidden" name="module" value="{{ $selectedModule }}">
                <div class="relative flex-1 lg:w-64">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order #, customer..." class="input pl-10 h-9 text-xs">
                </div>
                <button type="submit" class="btn-secondary btn-sm">Search</button>
                @if(request('search'))
                    <a href="{{ route('admin.dispatch.index', ['module' => $selectedModule]) }}" class="btn-secondary btn-sm">Clear</a>
                @endif
            </div>
        </div>
    </form>

    {{-- Orders Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Module</th>
                        <th class="table-header">Order Number</th>
                        <th class="table-header">Customer / Rider</th>
                        <th class="table-header">Store / Agent</th>
                        <th class="table-header">Date &amp; Time</th>
                        <th class="table-header">Total Amount</th>
                        <th class="table-header">Payment</th>
                        <th class="table-header">Status</th>
                        <th class="table-header text-center w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($unifiedOrders as $item)
                    <tr class="hover:bg-gray-50/60 transition-colors cursor-pointer" @click="openDetails({{ json_encode($item) }})">
                        {{-- Module Badge --}}
                        <td class="table-cell whitespace-nowrap">
                            <span class="badge {{ $item->module_badge }} font-medium">
                                <span class="mr-1">{{ $item->icon }}</span>
                                {{ $item->module_name }}
                            </span>
                        </td>

                        {{-- Order Number --}}
                        <td class="table-cell whitespace-nowrap">
                            <span class="font-mono font-semibold text-gray-900">{{ $item->order_number }}</span>
                        </td>

                        {{-- Customer --}}
                        <td class="table-cell whitespace-nowrap">
                            <div class="flex items-center gap-2.5">
                                <div class="avatar avatar-sm bg-slate-200 text-slate-700 text-xs font-bold uppercase">
                                    {{ substr($item->customer_name ?? 'C', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 text-xs">{{ $item->customer_name }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $item->customer_email }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Provider / Store --}}
                        <td class="table-cell whitespace-nowrap text-gray-700 text-xs font-medium">
                            {{ $item->provider_name }}
                        </td>

                        {{-- Date & Time --}}
                        <td class="table-cell whitespace-nowrap text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($item->created_at)->format('M d, Y • h:i A') }}
                        </td>

                        {{-- Total Amount --}}
                        <td class="table-cell whitespace-nowrap font-bold text-gray-900">
                            ${{ number_format($item->total_amount, 2) }}
                        </td>

                        {{-- Payment Status --}}
                        <td class="table-cell whitespace-nowrap">
                            @if($item->payment_status === 'paid')
                                <span class="badge badge-green">Paid ({{ $item->payment_method }})</span>
                            @else
                                <span class="badge badge-orange">{{ ucfirst($item->payment_status) }} ({{ $item->payment_method }})</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="table-cell whitespace-nowrap">
                            @php
                                $statusBadge = match($item->status) {
                                    'completed', 'delivered' => 'badge-green',
                                    'cancelled', 'rejected'  => 'badge-red',
                                    'confirmed', 'accepted'   => 'badge-blue',
                                    default                  => 'badge-orange',
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }} uppercase font-bold tracking-wider text-[10px]">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>

                        {{-- Action --}}
                        <td class="table-cell whitespace-nowrap text-center" @click.stop>
                            <a href="{{ $item->view_url }}" class="action-btn action-btn-view" title="View Full Order Details">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="empty-state">
                            <div class="empty-icon">📦</div>
                            <h3 class="empty-title">No dispatch orders found</h3>
                            <p class="empty-text">Orders placed across any activated plugin modules will automatically display here.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Slide-Over Quick Details Drawer --}}
    <div x-show="drawerOpen" class="fixed inset-0 z-[99999] overflow-hidden" x-cloak>
        <div x-show="drawerOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-black/40 backdrop-blur-xs" @click="drawerOpen = false"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div 
                x-show="drawerOpen" 
                x-transition:enter="transform transition ease-in-out duration-300" 
                x-transition:enter-start="translate-x-full" 
                x-transition:enter-end="translate-x-0" 
                x-transition:leave="transform transition ease-in-out duration-300" 
                x-transition:leave-start="translate-x-0" 
                x-transition:leave-end="translate-x-full"
                class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between"
            >
                {{-- Drawer Header --}}
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl" x-text="selectedItem?.icon || '📦'"></span>
                        <div>
                            <h3 class="text-base font-bold text-gray-900" x-text="selectedItem?.order_number"></h3>
                            <p class="text-xs text-gray-500" x-text="selectedItem?.module_name"></p>
                        </div>
                    </div>
                    <button @click="drawerOpen = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Drawer Body --}}
                <div class="p-6 space-y-6 flex-1 overflow-y-auto" x-if="selectedItem">
                    {{-- Status Banner --}}
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-gray-500 font-medium block">Current Status</span>
                            <span class="font-bold text-gray-900 uppercase text-xs tracking-wider" x-text="selectedItem?.status"></span>
                        </div>
                        <span class="text-lg font-black text-orange-600" x-text="'$' + Number(selectedItem?.total_amount || 0).toFixed(2)"></span>
                    </div>

                    {{-- Customer & Provider --}}
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Parties Involved</h4>
                        <div class="bg-white p-3.5 rounded-xl border border-gray-100 space-y-3 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Customer / Rider:</span>
                                <span class="font-semibold text-gray-900" x-text="selectedItem?.customer_name"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Store / Agent:</span>
                                <span class="font-semibold text-gray-900" x-text="selectedItem?.provider_name"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Created At:</span>
                                <span class="font-semibold text-gray-700" x-text="selectedItem?.created_at"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Key Metadata Breakdown --}}
                    <div class="space-y-3" x-show="selectedItem?.details">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Order Metadata &amp; Pricing</h4>
                        <div class="bg-white p-3.5 rounded-xl border border-gray-100 space-y-2.5 text-xs">
                            <template x-for="(val, key) in (selectedItem?.details || {})" :key="key">
                                <div class="flex items-start justify-between gap-4">
                                    <span class="text-gray-500 flex-shrink-0" x-text="key + ':'"></span>
                                    <span class="font-semibold text-gray-900 text-right" x-text="val"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Drawer Footer --}}
                <div class="p-6 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between gap-3">
                    <button @click="drawerOpen = false" class="btn-secondary flex-1 text-center">Close</button>
                    <a :href="selectedItem?.view_url" class="btn-primary flex-1 text-center">View Full Order Page</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Seller Advertising & Monetization System')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Paid Seller Advertisements</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Manage Banner Ads, Featured Store Listings & Sponsored Products</p>
        </div>
        <button type="button" @click="$dispatch('open-add-ad-modal')" class="btn btn-primary text-xs flex items-center justify-center gap-1.5 w-full sm:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Create Paid Campaign</span>
        </button>
    </div>

    <!-- Ads Table -->
    <div class="card overflow-hidden p-4 sm:p-5 space-y-3">
        <div class="overflow-x-auto">
            <table class="table w-full text-xs min-w-[650px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Campaign Title</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Ad Type</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Store / Seller</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Price Paid</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Validity Period</th>
                        <th class="text-left py-3 px-4 font-bold text-gray-700">Status</th>
                        <th class="text-right py-3 px-4 font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($advertisements as $ad)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-4 font-bold text-gray-900">{{ $ad->title }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ad->ad_type === 'banner' ? 'bg-purple-100 text-purple-700' : ($ad->ad_type === 'featured_store' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                    {{ str_replace('_', ' ', $ad->ad_type) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-medium text-gray-800">{{ $ad->store?->name }}</td>
                            <td class="py-3 px-4 font-bold text-green-700">₹{{ number_format($ad->price, 2) }}</td>
                            <td class="py-3 px-4 text-gray-500 font-mono text-[10px]">
                                {{ $ad->starts_at?->format('d/m/Y') }} - {{ $ad->ends_at?->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ad->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $ad->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('admin.ads.update-status', $ad->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" class="input text-[10px] py-0.5 px-2">
                                        <option value="active" {{ $ad->status === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="expired" {{ $ad->status === 'expired' ? 'selected' : '' }}>Expired</option>
                                        <option value="rejected" {{ $ad->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400">
                                No active ad campaigns found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Ad Modal -->
<div x-data="{ open: false }" @open-add-ad-modal.window="open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-bold text-sm text-gray-900">Create Paid Ad Campaign</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.ads.store') }}" method="POST" class="space-y-3">
                @csrf
                <div x-data="{
                    search: '',
                    selectedId: '{{ $stores->first()?->id }}',
                    selectedName: '{{ addslashes($stores->first()?->name ?? "Select Seller / Store") }}',
                    openDropdown: false,
                    stores: [
                        @foreach($stores as $s)
                            { id: '{{ $s->id }}', name: '{{ addslashes($s->name) }}', seller: '{{ addslashes($s->seller?->name ?? "Store #" . $s->id) }}' },
                        @endforeach
                    ],
                    get filteredStores() {
                        if (!this.search) return this.stores;
                        return this.stores.filter(s => s.name.toLowerCase().includes(this.search.toLowerCase()) || s.seller.toLowerCase().includes(this.search.toLowerCase()));
                    }
                }" class="relative">
                    <label class="label font-bold">Store / Seller (Searchable)</label>
                    <input type="hidden" name="store_id" :value="selectedId" required>
                    <button type="button" @click="openDropdown = !openDropdown" class="input text-xs w-full flex items-center justify-between text-left bg-white font-medium border border-gray-300 rounded-lg px-3 py-2">
                        <span x-text="selectedName" class="truncate text-gray-900 font-semibold"></span>
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openDropdown" @click.away="openDropdown = false" class="absolute z-30 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl p-2 space-y-2 text-xs" style="display: none;">
                        <div class="relative">
                            <input type="text" x-model="search" placeholder="Type store name or seller to search..." class="input text-xs pl-8 py-1.5 w-full bg-gray-50 focus:bg-white" autofocus>
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <div class="max-h-48 overflow-y-auto divide-y divide-gray-100 rounded-lg border border-gray-100">
                            <template x-for="s in filteredStores" :key="s.id">
                                <button type="button" @click="selectedId = s.id; selectedName = s.name; openDropdown = false; search = ''" class="w-full text-left px-3 py-2 hover:bg-amber-50 flex items-center justify-between transition group">
                                    <div>
                                        <div class="font-bold text-gray-900 group-hover:text-amber-700" x-text="s.name"></div>
                                        <div class="text-[10px] text-gray-500" x-text="s.seller"></div>
                                    </div>
                                    <span x-show="selectedId == s.id" class="text-amber-600 font-bold">✓</span>
                                </button>
                            </template>
                            <div x-show="filteredStores.length === 0" class="p-3 text-center text-gray-400 text-[11px]">
                                No matching seller found.
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="label font-bold">Ad Type</label>
                    <select name="ad_type" class="input text-xs">
                        <option value="banner">Home Banner Ad</option>
                        <option value="featured_store">Featured Store Listing</option>
                        <option value="sponsored_product">Sponsored Product</option>
                    </select>
                </div>
                <div>
                    <label class="label font-bold">Campaign Title</label>
                    <input type="text" name="title" class="input text-xs" required placeholder="e.g. Mega Food Sale Banner">
                </div>
                <div>
                    <label class="label font-bold">Ad Image URL</label>
                    <input type="text" name="image_url" class="input text-xs" placeholder="e.g. https://domain.com/banner.jpg">
                </div>
                <div>
                    <label class="label font-bold">Price Charged (₹)</label>
                    <input type="number" step="0.01" name="price" class="input text-xs" required placeholder="1000.00">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                        <label class="label font-bold">Starts At</label>
                        <input type="date" name="starts_at" class="input text-xs" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="label font-bold">Ends At</label>
                        <input type="date" name="ends_at" class="input text-xs" required value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="open = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Publish Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

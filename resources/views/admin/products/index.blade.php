@extends('admin.layouts.app')
@section('title', 'Products')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ request()->routeIs('admin.products.seller') ? 'Seller Products' : 'In-house Products' }} List</h1>
            <p class="text-sm text-gray-500 mt-1">{{ request()->routeIs('admin.products.seller') ? 'Manage products from your multi-vendor stores' : 'Manage your own product inventory and listings' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-permission-btn 
                permission="products.create" 
                href="{{ route('admin.products.import', ['type' => request()->routeIs('admin.products.seller') ? 'seller' : 'inhouse']) }}" 
                class="btn-secondary" 
                label="Import"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>'
            />
            
            <div class="relative" x-data="{ open: false }">
                <x-permission-btn 
                    permission="products.view" 
                    @click="open = !open" 
                    @click.outside="open = false" 
                    class="btn-secondary flex items-center gap-1" 
                    label="Export"
                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>'
                />
                <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-100" style="display: none;">
                    <a href="{{ route('admin.products.export', ['type' => 'csv', 'context' => request()->routeIs('admin.products.seller') ? 'seller' : 'inhouse']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export CSV</a>
                    <a href="{{ route('admin.products.export', ['type' => 'xlsx', 'context' => request()->routeIs('admin.products.seller') ? 'seller' : 'inhouse']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export Excel</a>
                </div>
            </div>

            <x-permission-btn 
                permission="products.create" 
                href="{{ route('admin.products.create', ['type' => request()->routeIs('admin.products.seller') ? 'seller' : 'inhouse']) }}" 
                class="btn-primary" 
                label="Add Product"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
            />
        </div>
    </div>

    <!-- Filters -->
    <form id="filterForm" action="{{ url()->current() }}" class="card" onsubmit="event.preventDefault(); applyFilters();">
        <div class="p-4 flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products by name, SKU..." class="input pl-10" id="product-search" autocomplete="off" onkeypress="if(event.key === 'Enter') { event.preventDefault(); applyFilters(); }">
                <div id="search-suggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden z-50 max-h-60 overflow-y-auto"></div>
            </div>
            @if(isset($stores))
            <select name="store_id" class="input lg:w-48" onchange="applyFilters()">
                <option value="">All Stores</option>
                @foreach($stores as $store)
                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                @endforeach
            </select>
            @endif
            <select name="category" class="input lg:w-44" onchange="applyFilters()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="input lg:w-36" onchange="applyFilters()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <select name="stock" class="input lg:w-36" onchange="applyFilters()">
                <option value="">Stock Level</option>
                <option value="in" {{ request('stock') == 'in' ? 'selected' : '' }}>In Stock</option>
                <option value="low" {{ request('stock') == 'low' ? 'selected' : '' }}>Low Stock</option>
                <option value="out" {{ request('stock') == 'out' ? 'selected' : '' }}>Out of Stock</option>
            </select>
            <button type="button" onclick="applyFilters()" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
            </button>
        </div>
    </form>

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Product</th>
                        <th class="table-header">Price</th>
                        <th class="table-header">Stock</th>
                        <th class="table-header">Category</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Featured</th>
                        <th class="table-header text-center w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    @if($product->primaryImage)
                                    <img src="{{ storage_url($product->primaryImage->image) }}" class="w-full h-full object-cover">
                                    @else
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $product->name }}</p>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <p class="text-xs text-gray-500">SKU: {{ $product->sku }}</p>
                                        @if($product->store)
                                        <span class="text-[10px] px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded">Store: {{ $product->store->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <div>
                                <span class="font-semibold text-gray-900"><x-currency :amount="$product->price" /></span>
                                @if($product->compare_price && $product->compare_price > $product->price)
                                <p class="text-xs text-gray-400 line-through"><x-currency :amount="$product->compare_price" /></p>
                                @endif
                            </div>
                        </td>
                        <td class="table-cell">
                            @if($product->quantity <= 0)
                            <span class="badge badge-red">Out of Stock</span>
                            @elseif($product->quantity <= $product->low_stock_threshold)
                            <span class="badge badge-orange">{{ $product->quantity }} Left (Low)</span>
                            @else
                            <span class="badge badge-green">{{ $product->quantity }} In Stock</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($product->category)
                            <span class="badge badge-blue">{{ $product->category->name }}</span>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            <label class="toggle {{ !auth()->user()->hasPermission('products.update') ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}" data-url="{{ route('admin.products.toggle-status', $product) }}">
                                <input type="checkbox" {{ $product->is_active ? 'checked' : '' }} onchange="toggleStatus(this)" {{ !auth()->user()->hasPermission('products.update') ? 'disabled' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td class="table-cell">
                            <label class="toggle {{ !auth()->user()->hasPermission('products.update') ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}" data-url="{{ route('admin.products.toggle-featured', $product) }}">
                                <input type="checkbox" {{ $product->is_featured ? 'checked' : '' }} onchange="toggleFeatured(this)" {{ !auth()->user()->hasPermission('products.update') ? 'disabled' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.products.edit', $product) }}" class="action-btn action-btn-view" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                
                                <x-permission-btn 
                                    permission="products.update" 
                                    href="{{ route('admin.products.edit', $product) }}" 
                                    class="action-btn action-btn-edit" 
                                    icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                                />

                                @if(auth()->user()->hasPermission('products.create'))
                                <form action="{{ route('admin.products.clone', $product) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="action-btn text-purple-600 bg-purple-50 hover:bg-purple-100" title="Clone / Duplicate Item" onclick="return confirm('Clone/Duplicate this product item?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </form>
                                @endif

                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" id="delete-form-{{ $product->id }}">
                                    @csrf @method('DELETE')
                                    <x-permission-btn 
                                        permission="products.delete" 
                                        type="button"
                                        onclick="if(confirm('Delete this product?')) document.getElementById('delete-form-{{ $product->id }}').submit()"
                                        class="action-btn action-btn-delete" 
                                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                                    />
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="empty-icon mx-auto"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                            <p class="font-medium text-gray-900 mt-4">No products found</p>
                            <p class="text-sm text-gray-500 mt-1 mb-4">@if(request()->hasAny(['search', 'category', 'status', 'stock']))Try adjusting your filters@else Get started by adding your first product @endif</p>
                            <a href="{{ route('admin.products.create') }}" class="btn-primary"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Add Product</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $products->appends(request()->query())->links() }}</div>@endif
    </div>
</div>

<script>
function applyFilters() {
    console.log('Applying filters...');
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (const [key, value] of formData.entries()) {
        if (value && value.trim() !== '') {
            params.append(key, value);
        }
    }

    const queryString = params.toString();
    const cleanUrl = "{{ url()->current() }}";

    window.location.href = cleanUrl + (queryString ? '?' + queryString : '');
}

function toggleStatus(checkbox) {
    const toggle = checkbox.closest('.toggle');
    const url = toggle.dataset.url;
    
    toggle.classList.add('loading');
    
    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        toggle.classList.remove('loading');
        if (!data.success) {
            checkbox.checked = !checkbox.checked;
        }
    })
    .catch(() => {
        toggle.classList.remove('loading');
        checkbox.checked = !checkbox.checked;
    });
}

function toggleFeatured(checkbox) {
    const toggle = checkbox.closest('.toggle');
    const url = toggle.dataset.url;
    
    toggle.classList.add('loading');
    
    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        toggle.classList.remove('loading');
        if (!data.success) {
            checkbox.checked = !checkbox.checked;
        }
    })
    .catch(() => {
        toggle.classList.remove('loading');
        checkbox.checked = !checkbox.checked;
    });
}

// Search autocomplete functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('product-search');
    const suggestionsContainer = document.getElementById('search-suggestions');
    let debounceTimer;
    
    if (searchInput && suggestionsContainer) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            
            if (query.length < 2) {
                suggestionsContainer.classList.add('hidden');
                return;
            }
            
            debounceTimer = setTimeout(() => {
                fetch(`{{ route('admin.search.suggestions') }}?q=${encodeURIComponent(query)}&type=products`)
                    .then(res => res.json())
                    .then(suggestions => {
                        if (suggestions.length > 0) {
                            suggestionsContainer.innerHTML = suggestions.map(item => 
                                `<div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" onclick="selectSuggestion('${item.name}')">
                                    <div class="font-medium text-gray-900">${item.name}</div>
                                    <div class="text-xs text-gray-500">SKU: ${item.sku}</div>
                                </div>`
                            ).join('');
                            suggestionsContainer.classList.remove('hidden');
                        } else {
                            suggestionsContainer.classList.add('hidden');
                        }
                    })
                    .catch(() => {
                        suggestionsContainer.classList.add('hidden');
                    });
            }, 300);
        });
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.classList.add('hidden');
            }
        });
    }
});

function selectSuggestion(name) {
    document.getElementById('product-search').value = name;
    document.getElementById('search-suggestions').classList.add('hidden');
    // Auto-submit the form
    applyFilters();
}
</script>
@endsection

@extends('seller.layouts.app')

@section('title', 'Product Inventory')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Compact Page Header -->
    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-lg font-bold text-gray-900 leading-tight">Product Inventory</h1>
            <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Manage catalog and stock levels</p>
        </div>
        <div>
            <a href="{{ route('seller.products.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded text-[10px] font-black text-white hover:bg-indigo-700 uppercase tracking-widest transition-all shadow-sm">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                Add Product
            </a>
        </div>
    </div>

    <!-- Toolbar: Search & Filters -->
    <div class="bg-white border border-gray-200 rounded-sm shadow-sm mb-6">
        <form action="{{ route('seller.products.index') }}" method="GET" class="p-4 flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[300px] relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded text-xs focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium" placeholder="Search by name or SKU...">
            </div>
            
            <div class="w-48">
                <select name="category_id" class="block w-full pl-3 pr-10 py-2 text-xs border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded font-medium">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-gray-900 border border-transparent rounded text-[10px] font-black text-white hover:bg-gray-800 transition-colors uppercase tracking-widest">
                Search
            </button>
            
            @if(request()->anyFilled(['search', 'category_id']))
                <a href="{{ route('seller.products.index') }}" class="text-xs font-bold text-gray-400 hover:text-rose-500 transition-colors uppercase tracking-widest">Clear</a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white border border-gray-200 rounded-sm overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Product Info</th>
                        <th scope="col" class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Catalog</th>
                        <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">Unit Price</th>
                        <th scope="col" class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Inventory</th>
                        <th scope="col" class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">Operations</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 border border-gray-100 bg-gray-50 rounded flex-shrink-0">
                                    @if($product->primaryImage)
                                        <img src="{{ storage_url($product->primaryImage->image) }}" class="w-full h-full object-cover rounded">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate max-w-[200px]">{{ $product->name }}</div>
                                    <div class="text-[10px] text-gray-500 font-mono tracking-tighter uppercase">SKU: {{ $product->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded">{{ $product->category->name ?? 'Default' }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-right">
                            <div class="text-sm font-black text-gray-900">{{\App\Helpers\CurrencyHelper::format($product->price)}}</div>
                            @if($product->sale_price)
                                <div class="text-[10px] text-emerald-600 font-bold uppercase tracking-widest mt-0.5">Sale</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            <div class="inline-flex items-center gap-2">
                                <span class="text-sm font-bold {{ $product->quantity > $product->low_stock_threshold ? 'text-gray-900' : 'text-rose-600' }}">
                                    {{ $product->quantity }}
                                </span>
                                <div class="w-1.5 h-1.5 rounded-full {{ $product->quantity > $product->low_stock_threshold ? 'bg-emerald-500' : 'bg-rose-500 animate-pulse' }}"></div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            <form action="{{ route('seller.products.toggle-status', $product) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" onchange="this.form.submit()" {{ $product->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-8 h-4 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-600 shadow-inner"></div>
                                </label>
                            </form>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('seller.products.edit', $product) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded text-[10px] font-bold text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-all uppercase tracking-wider shadow-sm">
                                    Edit
                                </a>
                                <form action="{{ route('seller.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Permanently delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded text-[10px] font-bold text-gray-700 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-all uppercase tracking-wider shadow-sm">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-20 text-center">
                            <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 grayscale opacity-50">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase">Catalog is empty</h3>
                            <p class="text-xs text-gray-400 mt-1">No products match your current search criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                        Showing <span class="text-gray-900">{{ $products->firstItem() }}</span> to <span class="text-gray-900">{{ $products->lastItem() }}</span> of <span class="text-gray-900">{{ $products->total() }}</span> Results
                    </p>
                </div>
                <div class="seller-pagination">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

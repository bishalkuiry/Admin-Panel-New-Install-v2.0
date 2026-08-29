@extends('admin.layouts.app')
@section('title', $store->name . ' - Products')
@section('content')
<div class="space-y-5">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.stores.show', $store) }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ $store->name }} - Products</h1>
            <p class="text-sm text-gray-500 mt-1">View store products</p>
        </div>
    </div>

    @include('admin.stores._tabs', ['store' => $store])

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="table-header">Product</th>
                        <th class="table-header">Price</th>
                        <th class="table-header">Stock</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                                    @if($product->primaryImage)
                                    <img src="{{ storage_url($product->primaryImage->image) }}" class="w-full h-full object-cover">
                                    @else
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500">SKU: {{ $product->sku }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell font-semibold text-gray-900"><x-currency :amount="$product->price" /></td>
                        <td class="table-cell">
                            @if($product->quantity <= 0)<span class="badge badge-red">Out of Stock</span>
                            @elseif($product->quantity <= $product->low_stock_threshold)<span class="badge badge-orange">{{ $product->quantity }} Left</span>
                            @else<span class="badge badge-green">{{ $product->quantity }} In Stock</span>@endif
                        </td>
                        <td class="table-cell">
                            @if($product->is_active)<span class="badge badge-green">Active</span>@else<span class="badge badge-gray">Inactive</span>@endif
                        </td>
                        <td class="table-cell text-gray-500 text-sm">{{ \App\Helpers\DateHelper::format($product->created_at) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <p class="font-medium text-gray-900">No products yet</p>
                            <p class="text-sm text-gray-500 mt-1">This store hasn't added any products</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $products->links() }}</div>@endif
    </div>
</div>
@endsection

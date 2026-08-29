@extends('seller.layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Compact Page Header -->
    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('seller.orders.index') }}" class="group w-8 h-8 rounded border border-gray-200 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-bold text-gray-900 leading-tight">Order #{{ $order->order_number }}</h1>
                    @if(($order->order_type ?? 'delivery') === 'pickup')
                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 font-extrabold text-[10px] rounded uppercase">🛍️ Store Pickup</span>
                    @else
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 font-extrabold text-[10px] rounded uppercase">🚚 Home Delivery</span>
                    @endif
                </div>
                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Placed: {{ $order->created_at->format('M d, Y • h:i A') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-300 rounded text-[10px] font-bold text-gray-700 hover:bg-gray-50 uppercase tracking-wider transition-all shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
            <form action="{{ route('seller.orders.update-status', $order) }}" method="POST" class="flex gap-1.5">
                @csrf
                @method('PATCH')
                <select name="status" class="block w-full pl-2 pr-8 py-1.5 text-[10px] border border-gray-300 focus:outline-none focus:ring-indigo-500 rounded font-bold uppercase tracking-wider">
                    @foreach(\App\Enums\OrderStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ $order->status->value === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="inline-flex items-center px-4 py-1.5 bg-indigo-600 border border-transparent rounded text-[10px] font-black text-white hover:bg-indigo-700 uppercase tracking-widest transition-all shadow-sm">
                    Status
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Column: Details -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Items Table Card -->
            <div class="bg-white border border-gray-200 rounded-sm overflow-hidden shadow-sm">
                <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-200">
                    <h3 class="text-xs font-bold text-gray-700 uppercase">Product Details</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th scope="col" class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider">Qty</th>
                                <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->items as $item)
                            <tr>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 border border-gray-100 bg-gray-50 rounded flex-shrink-0">
                                            @php $img = $item->product ? ($item->product->primaryImage->image ?? null) : null; @endphp
                                            @if($img)
                                                <img src="{{ storage_url($img) }}" class="w-full h-full object-cover rounded">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900">{{ $item->product_name ?? 'Deleted Product' }}</div>
                                            <div class="text-[10px] text-gray-500 uppercase tracking-tighter">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                            @if($item->prescription_image)
                                                <div class="mt-1">
                                                    <a href="{{ storage_url($item->prescription_image) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-blue-600 font-bold hover:underline">
                                                        📋 View Prescription
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                    {{\App\Helpers\CurrencyHelper::format($item->price)}}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-900">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                    {{\App\Helpers\CurrencyHelper::format($item->total)}}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Financial Summary -->
                <div class="p-6 bg-gray-50 flex justify-end">
                    <div class="w-full max-w-xs space-y-2">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span class="font-medium">{{\App\Helpers\CurrencyHelper::format($order->subtotal)}}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Delivery Fee</span>
                            <span class="font-medium">{{\App\Helpers\CurrencyHelper::format($order->delivery_fee)}}</span>
                        </div>
                        @if(($order->driver_tip ?? 0) > 0)
                        <div class="flex justify-between text-sm text-emerald-700 font-semibold">
                            <span>🛵 Rider Tip</span>
                            <span>+{{\App\Helpers\CurrencyHelper::format($order->driver_tip)}}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Tax (Calculated)</span>
                            <span class="font-medium">{{\App\Helpers\CurrencyHelper::format($order->tax)}}</span>
                        </div>
                        @if($order->discount > 0)
                        <div class="flex justify-between text-sm text-emerald-600">
                            <span>Discount Applied</span>
                            <span class="font-medium">-{{\App\Helpers\CurrencyHelper::format($order->discount)}}</span>
                        </div>
                        @endif
                        <div class="pt-3 border-t border-gray-200 mt-2 flex justify-between items-center">
                            <span class="text-base font-bold text-gray-900">Grand Total</span>
                            <span class="text-xl font-bold text-indigo-700">{{\App\Helpers\CurrencyHelper::format($order->total)}}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Detail Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Notes -->
                <div class="bg-white border border-gray-200 rounded-sm p-5">
                    <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-3">Order Instruction / Notes</h4>
                    <p class="text-sm text-gray-600 leading-relaxed italic">
                        {{ $order->notes ? '"' . $order->notes . '"' : 'No instruction provided by the customer.' }}
                    </p>
                </div>
                
                <!-- Payment Summary -->
                <div class="bg-white border border-gray-200 rounded-sm p-5">
                    <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-3">Transaction Details</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">Method:</span>
                            <span class="text-xs font-bold text-gray-900 uppercase bg-gray-100 px-2 py-0.5 rounded">{{ $order->payment_method }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">Status:</span>
                            <span class="text-xs font-bold uppercase {{ $order->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $order->payment_status }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">Reference:</span>
                            <span class="text-[10px] font-mono font-bold text-gray-400">{{ $order->payment_id ?? 'AUTO_GENERATED' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Logistics -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Customer Card -->
            <div class="bg-white border border-gray-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-200">
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Customer Information</h3>
                </div>
                <div class="p-4 space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded border border-gray-200 bg-gray-50 flex items-center justify-center text-xs font-bold text-gray-400">
                            {{ substr($order->user->name ?? 'G', 0, 1) }}
                        </div>
                        <p class="text-sm font-bold text-gray-900">{{ $order->user->name ?? 'Guest Customer' }}</p>
                    </div>
                    <p class="text-[11px] text-gray-500 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $order->user->email ?? 'N/A' }}
                    </p>
                    <p class="text-[11px] text-gray-500 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $order->user->phone ?? 'N/A' }}
                    </p>
                </div>
            </div>

            {{-- Prescription Card for Seller --}}
            @if($order->prescription || $order->prescription_image)
            @php $prescriptionUrl = storage_url($order->prescription ?? $order->prescription_image); @endphp
            <div class="bg-white border-2 border-blue-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-blue-50 border-b border-blue-100 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Doctor's Prescription
                    </h3>
                    <span class="px-2 py-0.5 bg-blue-600 text-white text-[9px] font-black uppercase rounded">Rx Required</span>
                </div>
                <div class="p-4 space-y-3">
                    <a href="{{ $prescriptionUrl }}" target="_blank" class="block border border-blue-200 rounded overflow-hidden group">
                        <img src="{{ $prescriptionUrl }}" alt="Doctor's Prescription" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300">
                    </a>
                    <div>
                        <p class="text-[11px] text-gray-600 font-medium">Verify prescription medicines before packing order.</p>
                        <a href="{{ $prescriptionUrl }}" target="_blank" class="inline-flex items-center gap-1 mt-2 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold rounded uppercase tracking-wider transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Open Full Resolution Photo
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Shipping Address Card -->
            <div class="bg-white border border-gray-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-200">
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Shipping Destination</h3>
                </div>
                <div class="p-4">
                    <div class="text-[11px] uppercase text-gray-400 font-bold mb-2">Delivery Address</div>
                    @if($order->address)
                        <div class="text-sm text-gray-900 font-bold mb-1">{{ $order->address->name }}</div>
                        <div class="text-xs text-gray-600 leading-relaxed font-medium">
                            {{ $order->address->address }}<br>
                            @if($order->address->landmark) <span class="text-gray-400 font-normal">Near {{ $order->address->landmark }}</span><br> @endif
                            {{ $order->address->city }}, {{ $order->address->state }}<br>
                            {{ $order->address->pincode }}
                        </div>
                    @else
                        <div class="text-xs text-rose-500 italic">No delivery address specified.</div>
                    @endif
                </div>
            </div>

            <!-- Logistics / Pickup Assignment Info -->
            @if(($order->order_type ?? 'delivery') === 'pickup')
            <div class="bg-purple-50 border border-purple-200 rounded-sm shadow-sm p-4 space-y-2">
                <h3 class="text-xs font-bold text-purple-900 uppercase tracking-wider flex items-center gap-1.5">
                    🛍️ Store Pickup Order
                </h3>
                <p class="text-[11px] text-purple-700 leading-relaxed font-medium">
                    The customer chose to pick up this order directly from your store. No delivery driver is required. Please pack the items and complete customer handover directly.
                </p>
            </div>
            @elseif($order->deliveryPartner)
            <div class="bg-white border border-indigo-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-indigo-50 border-b border-indigo-100">
                    <h3 class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Logistics Assignment</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full border border-indigo-200 bg-indigo-50 flex items-center justify-center text-xs font-bold text-indigo-600">
                            {{ substr($order->deliveryPartner->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 leading-none">{{ $order->deliveryPartner->name }}</p>
                            <p class="text-[10px] text-indigo-600 mt-1 uppercase font-bold tracking-widest">{{ $order->status === \App\Enums\OrderStatus::OUT_FOR_DELIVERY ? 'Out for Delivery' : 'Partner Assigned' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif


            
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .sidebar, .top-nav, button, select, form, nav[aria-label="Breadcrumb"] { display: none !important; }
        .bg-white { border: 1px solid #ddd !important; box-shadow: none !important; }
        .bg-gray-50 { background-color: transparent !important; border-bottom: 1px solid #eee !important; }
        body { background: white !important; font-family: 'Segoe UI', Arial, sans-serif !important; }
        .grid { display: block !important; }
        .lg\:col-span-8, .lg\:col-span-4 { width: 100% !important; margin-bottom: 2rem; }
    }
</style>
@endpush
@endsection

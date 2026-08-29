@extends('admin.layouts.app')
@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.orders.index') }}" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold text-gray-900">Order #{{ $order->order_number }}</h1>
                    <span data-order-status="{{ $order->getRawOriginal('status') }}"
                          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider {{ $order->status->color() }} transition-all duration-300">
                        {{ $order->status->label() }}
                    </span>
                    @if(($order->order_type ?? 'delivery') === 'pickup')
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-extrabold uppercase tracking-wider">
                            🛍️ Store Pickup
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-extrabold uppercase tracking-wider">
                            🚚 Home Delivery
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-900 border border-amber-200 rounded-full text-xs font-mono font-bold shadow-2xs">
                        🔑 Delivery OTP: <span class="text-orange-600 font-extrabold tracking-wider">{{ $order->delivery_otp }}</span>
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Placed on {{ \App\Helpers\DateHelper::format($order->created_at, true) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @php $existingInvoice = \App\Models\Invoice::where('order_id', $order->id)->first(); @endphp
            @if($existingInvoice)
                <a href="{{ route('admin.invoices.show', $existingInvoice) }}" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>View Invoice</span>
                </a>
                <a href="{{ route('admin.invoices.print', $existingInvoice) }}" target="_blank" class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Invoice</span>
                </a>
            @else
                <form action="{{ route('admin.orders.generate-invoice', $order) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Generate Invoice</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Visual Order Status Timeline Progress --}}
    @php
        $rawStatus = $order->getRawOriginal('status');
        $steps = [
            'pending'          => ['label' => 'Placed', 'icon' => '📝'],
            'confirmed'        => ['label' => 'Confirmed', 'icon' => '✅'],
            'packed'           => ['label' => 'Packed', 'icon' => '📦'],
            'picked_up'        => ['label' => 'Picked Up', 'icon' => '🛵'],
            'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => '🚚'],
            'delivered'        => ['label' => 'Delivered', 'icon' => '🎉'],
        ];
        $orderKeys = array_keys($steps);
        $currentIndex = array_search($rawStatus, $orderKeys);
        if ($currentIndex === false && $rawStatus === 'cancelled') {
            $currentIndex = -1;
        }
    @endphp

    <div class="card p-5">
        <div class="flex items-center justify-between overflow-x-auto gap-2 py-2">
            @foreach($steps as $key => $step)
                @php
                    $stepIndex = array_search($key, $orderKeys);
                    $isPassed = $currentIndex !== -1 && $stepIndex <= $currentIndex;
                    $isCurrent = $currentIndex !== -1 && $stepIndex === $currentIndex;
                @endphp
                <div class="flex items-center gap-2 flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 {{ $isCurrent ? 'bg-orange-500 text-white shadow-md shadow-orange-500/30 scale-110 ring-4 ring-orange-100' : ($isPassed ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400') }}">
                            @if($isPassed && !$isCurrent) ✓ @else {{ $step['icon'] }} @endif
                        </div>
                        <span class="text-xs font-semibold {{ $isCurrent ? 'text-orange-600 font-extrabold' : ($isPassed ? 'text-gray-900' : 'text-gray-400') }}">
                            {{ $step['label'] }}
                        </span>
                    </div>
                    @if(!$loop->last)
                        <div class="w-10 h-0.5 mx-1 {{ $isPassed && $currentIndex > $stepIndex ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Main 2-Column Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Left Column (Items, Update Status, Delivery Partner) --}}
        <div class="lg:col-span-2 space-y-5">
            {{-- Order Items Card --}}
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="card-title">Order Items ({{ $order->items->count() }})</h3>
                    @if($order->store)
                        <span class="text-xs text-gray-500 font-medium">Store: <strong class="text-gray-900">{{ $order->store->name }}</strong></span>
                    @endif
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                    <div class="p-4 flex items-center gap-4 hover:bg-gray-50/50 transition-colors">
                        <div class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0 border border-gray-200">
                            @if($item->product && $item->product->primaryImage)
                                <img src="{{ storage_url($item->product->primaryImage->image) }}" class="w-full h-full object-cover">
                            @elseif($item->product && $item->product->meta_image)
                                <img src="{{ $item->product->meta_image }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm leading-snug">{{ $item->product_name }}</p>
                            @if($item->variant_name)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $item->variant_name }}</p>
                            @endif
                            @if($item->commission > 0)
                                <p class="text-[11px] text-orange-600 font-bold mt-1">Platform Commission: {{ \App\Helpers\CurrencyHelper::format($item->commission) }}</p>
                            @endif

                            @if($item->prescription_image)
                                <div class="mt-1.5 flex items-center gap-2">
                                    <a href="{{ storage_url($item->prescription_image) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-md text-xs font-bold transition-colors">
                                        📋 View Item Prescription
                                    </a>
                                </div>
                            @endif

                            @php
                                $itemReturn = \App\Models\ProductReturn::where('order_item_id', $item->id)->latest()->first();
                            @endphp
                            @if($itemReturn)
                                <div class="mt-1.5 flex items-center gap-1.5 flex-wrap">
                                    @if($itemReturn->status === 'refunded' || ($itemReturn->type === 'return' && in_array($itemReturn->status, ['approved', 'refunded'])))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                            💰 REFUNDED ({{ \App\Helpers\CurrencyHelper::format($itemReturn->refund_amount) }})
                                        </span>
                                    @elseif($itemReturn->type === 'replacement')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-900 border border-indigo-300">
                                            📦 REPLACEMENT ({{ strtoupper(str_replace('_', ' ', $itemReturn->status)) }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-900 border border-blue-300">
                                            🔄 {{ strtoupper($itemReturn->type) }} ({{ strtoupper(str_replace('_', ' ', $itemReturn->status)) }})
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-extrabold text-gray-900 text-sm">{{ \App\Helpers\CurrencyHelper::format($item->price * $item->quantity) }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ \App\Helpers\CurrencyHelper::format($item->price) }} × {{ $item->quantity }}</p>
                            <form action="{{ route('admin.orders.items.remove', ['order' => $order, 'item' => $item]) }}" method="POST" class="mt-2">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 font-extrabold uppercase tracking-wider transition-colors" onclick="return confirm('Are you sure you want to remove this item? This will adjust the bill.')">
                                    Remove Item
                                </button>
                            </form>

                            <div class="mt-2 flex items-center justify-end gap-1.5">
                                @if(!$itemReturn)
                                    <form action="{{ route('admin.returns.store') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                                        <input type="hidden" name="order_item_id" value="{{ $item->id }}">
                                        <input type="hidden" name="type" value="return">
                                        <input type="hidden" name="reason" value="Admin Initiated Return & Refund">
                                        <button type="submit" class="px-2 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 rounded text-[10px] font-bold" onclick="return confirm('Initiate Return & Wallet Refund for this item?')">
                                            Return &amp; Refund
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.returns.store') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                                        <input type="hidden" name="order_item_id" value="{{ $item->id }}">
                                        <input type="hidden" name="type" value="replacement">
                                        <input type="hidden" name="reason" value="Admin Initiated Replacement">
                                        <button type="submit" class="px-2 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded text-[10px] font-bold" onclick="return confirm('Initiate Replacement for this item?')">
                                            Replacement
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.returns.index', ['search' => $itemReturn->return_number]) }}" class="px-2 py-1 bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300 rounded text-[10px] font-bold inline-flex items-center gap-1">
                                        View Ticket #{{ $itemReturn->return_number }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Uploaded Prescription Image Card --}}
            @if($order->prescription_image)
            <div class="card border border-blue-200 bg-blue-50/40">
                <div class="card-header border-b border-blue-100 flex items-center justify-between">
                    <h3 class="card-title text-blue-950 font-bold flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Customer Uploaded Prescription (Rx)</span>
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-xs font-bold uppercase">Required Rx</span>
                </div>
                <div class="card-body p-4 flex flex-col sm:flex-row items-center gap-4">
                    <a href="{{ storage_url($order->prescription_image) }}" target="_blank" class="block w-32 h-32 rounded-xl overflow-hidden border border-blue-300 shadow-sm hover:opacity-90 transition-opacity flex-shrink-0">
                        <img src="{{ storage_url($order->prescription_image) }}" class="w-full h-full object-cover" alt="Prescription Image">
                    </a>
                    <div>
                        <p class="text-xs text-gray-700 font-medium">Uploaded by customer for verification before item dispatch.</p>
                        <a href="{{ storage_url($order->prescription_image) }}" target="_blank" class="inline-flex items-center gap-1 mt-3 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            View Full Resolution Prescription
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Update Status Card --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Update Order Status</h3></div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="flex gap-3">
                        @csrf @method('PATCH')
                        @php $currentStatus = $order->getRawOriginal('status'); @endphp
                        <select name="status" class="input flex-1">
                            @foreach(['pending', 'confirmed', 'packed', 'picked_up', 'out_for_delivery', 'delivered', 'cancelled'] as $status)
                                <option value="{{ $status }}" {{ $currentStatus === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary">Update Status</button>
                    </form>
                </div>
            </div>

            {{-- Delivery Partner Assignment Card --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Delivery & Logistics</h3></div>
                <div class="card-body">
                    @if(($order->order_type ?? 'delivery') === 'pickup')
                        <div class="p-4 bg-purple-50 border border-purple-200 rounded-xl space-y-2">
                            <div class="flex items-center gap-2 text-purple-900 font-bold text-sm">
                                <span>🛍️ Store Pickup Order</span>
                            </div>
                            <p class="text-xs text-purple-700">This order is set for direct store pickup by the customer. No delivery driver is assigned. The store vendor handles packing and customer handover directly.</p>
                        </div>
                    @else
                        @if($order->deliveryPartner)
                        <div class="flex items-center gap-3 p-3.5 bg-green-50 border border-green-200 rounded-xl mb-4">
                            @if($order->deliveryPartner->avatar)
                                <img src="{{ storage_url($order->deliveryPartner->avatar) }}" alt="{{ $order->deliveryPartner->name }}" class="avatar">
                            @else
                                <div class="avatar bg-green-100 text-green-700 font-bold">
                                    {{ substr($order->deliveryPartner->name, 0, 2) }}
                                </div>
                            @endif
                            <div class="flex-1">
                                <p class="font-bold text-gray-900 text-sm">{{ $order->deliveryPartner->name }}</p>
                                <p class="text-xs text-gray-600 font-medium mt-0.5">📞 {{ $order->deliveryPartner->phone }}</p>
                                <p class="text-[11px] text-gray-400">{{ $order->deliveryPartner->email }}</p>
                            </div>
                            <span class="badge badge-green font-bold">Assigned</span>
                        </div>
                        
                        <form action="{{ route('admin.orders.assign-delivery-partner', $order) }}" method="POST" class="space-y-3">
                            @csrf
                            <label class="label text-xs">Reassign to different partner</label>
                            <div class="flex gap-3">
                                <select name="delivery_partner_id" class="input flex-1 text-xs">
                                    <option value="">Select delivery partner...</option>
                                    @foreach($availablePartners as $partner)
                                        <option value="{{ $partner->id }}" {{ $order->delivery_partner_id == $partner->id ? 'selected' : '' }}>
                                            {{ $partner->name }} - {{ $partner->phone }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-primary btn-sm">Reassign</button>
                            </div>
                        </form>

                        <form action="{{ route('admin.orders.unassign-delivery-partner', $order) }}" method="POST" class="mt-3">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-secondary btn-sm w-full" onclick="return confirm('Remove delivery partner from this order?')">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Remove Delivery Partner</span>
                            </button>
                        </form>
                    @else
                        <div class="text-center py-4 mb-4">
                            <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-xl">🚚</div>
                            <p class="text-sm font-semibold text-gray-800">No delivery partner assigned yet</p>
                            <p class="text-xs text-gray-400 mt-0.5">Assign a rider to dispatch this order.</p>
                        </div>

                        <form action="{{ route('admin.orders.assign-delivery-partner', $order) }}" method="POST" class="space-y-3">
                            @csrf
                            <label class="label text-xs">Assign delivery partner</label>
                            <div class="flex gap-3">
                                <select name="delivery_partner_id" class="input flex-1 text-xs" required>
                                    <option value="">Select delivery partner...</option>
                                    @foreach($availablePartners as $partner)
                                        <option value="{{ $partner->id }}">
                                            {{ $partner->name }} - {{ $partner->phone }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-primary">Assign</button>
                            </div>
                            @if($availablePartners->isEmpty())
                                <p class="text-xs text-gray-500">
                                    No active delivery partners available. 
                                    <a href="{{ route('admin.delivery-partners.create') }}" class="text-orange-600 hover:text-orange-700 font-bold">Add One</a>
                                </p>
                            @endif
                        </form>
                    @endif
                    @endif
                </div>
            </div>

            <!-- Real-Time Live Driver Tracking Map Card -->
            <div class="card p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Real-Time Live Tracking Map</span>
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1 animate-ping"></span> Live Tracking Active
                        </span>
                    </div>
                </div>

                <div id="admin-live-map" class="w-full h-72 rounded-xl bg-gray-100 border border-gray-200 z-10"></div>

                <div class="grid grid-cols-3 gap-2 text-center text-xs pt-1">
                    <div class="p-2 bg-gray-50 rounded-lg">
                        <span class="text-[10px] text-gray-400 block font-bold uppercase">Store Location</span>
                        <span class="font-semibold text-gray-800 truncate block">{{ $order->store?->name ?? 'Store' }}</span>
                    </div>
                    <div class="p-2 bg-orange-50 rounded-lg border border-orange-100">
                        <span class="text-[10px] text-orange-600 block font-bold uppercase">Driver Location</span>
                        <span id="driver-status-text" class="font-extrabold text-orange-600 truncate block">{{ $order->deliveryPartner?->name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="p-2 bg-gray-50 rounded-lg">
                        <span class="text-[10px] text-gray-400 block font-bold uppercase">Customer Address</span>
                        <span class="font-semibold text-gray-800 truncate block">{{ $order->address?->city ?? 'Destination' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column (Summary, Customer, Address, Payment, Chats) --}}
        <div class="space-y-5">
            {{-- Order Summary --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Order Summary</h3></div>
                <div class="card-body space-y-3">
                    <div class="flex justify-between text-xs"><span class="text-gray-500">Subtotal</span><span class="font-semibold text-gray-900">{{ \App\Helpers\CurrencyHelper::format($order->subtotal) }}</span></div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-xs"><span class="text-gray-500">Discount</span><span class="font-bold text-green-600">-{{ \App\Helpers\CurrencyHelper::format($order->discount) }}</span></div>
                    @endif
                    <div class="flex justify-between text-xs"><span class="text-gray-500">Delivery Fee</span><span class="font-semibold text-gray-900">{{ \App\Helpers\CurrencyHelper::format($order->delivery_fee ?? 0) }}</span></div>
                    @if(($order->driver_tip ?? 0) > 0)
                        <div class="flex justify-between text-xs"><span class="text-emerald-700 font-medium">🛵 Rider Tip</span><span class="font-bold text-emerald-700">+{{ \App\Helpers\CurrencyHelper::format($order->driver_tip) }}</span></div>
                    @endif
                    @if($order->tax > 0)
                        <div class="flex justify-between text-xs"><span class="text-gray-500">Tax</span><span class="font-semibold text-gray-900">{{ \App\Helpers\CurrencyHelper::format($order->tax) }}</span></div>
                    @endif
                    
                    @php $totalCommission = $order->items->sum('commission'); @endphp
                    @if($totalCommission > 0)
                        <div class="flex justify-between text-xs pt-2 border-t border-dashed"><span class="text-orange-600 font-medium">Platform Commission</span><span class="text-orange-600 font-bold">{{ \App\Helpers\CurrencyHelper::format($totalCommission) }}</span></div>
                    @endif

                    <div class="border-t pt-3 flex justify-between text-sm font-black">
                        <span class="text-gray-900">Total Bill</span>
                        <span class="text-orange-600 text-base">{{ \App\Helpers\CurrencyHelper::format($order->total) }}</span>
                    </div>
                </div>
            </div>

            {{-- Customer Card --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Customer Details</h3></div>
                <div class="card-body space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="avatar bg-orange-100 text-orange-700 font-bold">
                            {{ substr($order->user->name ?? $order->address->name ?? 'G', 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-gray-900 text-sm truncate">{{ $order->user->name ?? $order->address->name ?? 'Guest Customer' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $order->user->email ?? $order->email ?? 'No email' }}</p>
                        </div>
                    </div>
                    
                    @php 
                        $customerPhone = $order->phone ?? $order->address->phone ?? $order->user->phone ?? null; 
                    @endphp
                    @if($customerPhone)
                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                            <span class="text-gray-500">Phone Number:</span>
                            <span class="text-gray-900 font-bold">{{ $customerPhone }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Shipping Address Card --}}
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="card-title">Shipping Address</h3>
                    @if($order->address && $order->address->type)
                        <span class="badge badge-gray uppercase text-[10px] font-bold">{{ $order->address->type }}</span>
                    @endif
                </div>
                <div class="card-body text-xs text-gray-700 space-y-1.5">
                    @if($order->address)
                        @if($order->address->name)
                            <p class="font-bold text-gray-900 text-sm mb-1">{{ $order->address->name }}</p>
                        @endif
                        <p class="font-medium text-gray-800">{{ $order->address->address_line_1 }}</p>
                        @if($order->address->address_line_2)
                            <p class="text-gray-600">{{ $order->address->address_line_2 }}</p>
                        @endif
                        <p class="font-medium text-gray-800">{{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->postal_code }}</p>
                        <p class="text-gray-500 font-semibold">{{ $order->address->country }}</p>
                        
                        @if($order->address->phone)
                            <div class="pt-2 mt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                                <span class="text-gray-500">Recipient Contact:</span>
                                <span class="font-bold text-gray-900">{{ $order->address->phone }}</span>
                            </div>
                        @endif
                    @else
                        <p class="text-gray-400 italic">No shipping address attached</p>
                    @endif
                </div>
            </div>

            {{-- Payment Card --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Payment Details</h3></div>
                <div class="card-body space-y-2.5 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Payment Method</span>
                        <span class="font-bold text-gray-900 uppercase">{{ strtoupper($order->payment_method ?? 'COD') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Payment Status</span>
                        @if($order->payment_status === 'paid')
                            <span class="badge badge-green font-bold">Paid</span>
                        @elseif($order->payment_status === 'pending')
                            <span class="badge badge-orange font-bold">Pending</span>
                        @else
                            <span class="badge badge-red font-bold">{{ ucfirst($order->payment_status) }}</span>
                        @endif
                    </div>
                    @if(($order->driver_tip ?? 0) > 0)
                        <div class="flex justify-between items-center text-emerald-700 font-bold border-t border-emerald-100 pt-2">
                            <span>🛵 Customer Rider Tip</span>
                            <span>+{{ \App\Helpers\CurrencyHelper::format($order->driver_tip) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Order Chats --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Order Chats</h3></div>
                <div class="card-body space-y-3">
                    @php
                        $deliveryChat = \App\Models\OrderChat::where('order_id', $order->id)
                            ->where('chat_type', 'customer_delivery')
                            ->first();
                        $sellerChat = \App\Models\OrderChat::where('order_id', $order->id)
                            ->where('chat_type', 'customer_seller')
                            ->first();
                    @endphp

                    @if($deliveryChat)
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span class="font-bold text-gray-900 text-xs">Customer ↔ Delivery</span>
                                </div>
                                @if($deliveryChat->unread_count_customer + $deliveryChat->unread_count_participant > 0)
                                    <span class="badge badge-orange text-[10px]">{{ $deliveryChat->unread_count_customer + $deliveryChat->unread_count_participant }} new</span>
                                @endif
                            </div>
                            @if($deliveryChat->last_message)
                                <p class="text-xs text-gray-600 mb-2">{{ Str::limit($deliveryChat->last_message, 50) }}</p>
                            @endif
                            <a href="{{ route('admin.orders.chat', ['order' => $order, 'chatType' => 'customer_delivery']) }}" 
                               class="btn-secondary btn-sm w-full text-center">
                                View Chat
                            </a>
                        </div>
                    @else
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-center">
                            <p class="text-xs text-gray-500">No delivery partner chat yet</p>
                        </div>
                    @endif

                    @if($sellerChat)
                        <div class="p-3 bg-green-50 border border-green-200 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span class="font-bold text-gray-900 text-xs">Customer ↔ Seller</span>
                                </div>
                                @if($sellerChat->unread_count_customer + $sellerChat->unread_count_participant > 0)
                                    <span class="badge badge-orange text-[10px]">{{ $sellerChat->unread_count_customer + $sellerChat->unread_count_participant }} new</span>
                                @endif
                            </div>
                            @if($sellerChat->last_message)
                                <p class="text-xs text-gray-600 mb-2">{{ Str::limit($sellerChat->last_message, 50) }}</p>
                            @endif
                            <a href="{{ route('admin.orders.chat', ['order' => $order, 'chatType' => 'customer_seller']) }}" 
                               class="btn-secondary btn-sm w-full text-center">
                                View Chat
                            </a>
                        </div>
                    @else
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-center">
                            <p class="text-xs text-gray-500">No seller chat yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const ORDER_ID      = {{ $order->id }};
    const SSE_URL       = "{{ route('admin.orders.stream',  $order) }}";
    const POLL_URL      = "{{ route('admin.orders.status', $order) }}";
    const CURRENT_STATUS = "{{ $order->getRawOriginal('status') }}";

    function showToast(label, newStatus) {
        const colors = {
            confirmed:        '#3b82f6',
            packed:           '#6366f1',
            picked_up:        '#8b5cf6',
            out_for_delivery: '#f97316',
            delivered:        '#10b981',
            cancelled:        '#ef4444',
        };
        const color = colors[newStatus] || '#111827';

        const toast = document.createElement('div');
        toast.style.cssText = `
            position:fixed; bottom:24px; right:24px; z-index:9999;
            background:#fff; border:1px solid #e5e7eb; border-left:4px solid ${color};
            border-radius:10px; padding:14px 18px; box-shadow:0 8px 24px rgba(0,0,0,.12);
            display:flex; align-items:center; gap:12px; max-width:340px;
            font-family:inherit; animation:slideIn .25s ease;
        `;
        toast.innerHTML = `
            <svg width="20" height="20" fill="none" stroke="${color}" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div style="flex:1">
              <p style="margin:0;font-size:13px;font-weight:700;color:#111">Order Status Updated</p>
              <p style="margin:2px 0 0;font-size:12px;color:#6b7280">Now: <strong style="color:${color}">${label}</strong></p>
            </div>
            <button onclick="this.parentElement.remove()"
              style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:18px;line-height:1">×</button>
        `;

        if (!document.getElementById('toast-keyframes')) {
            const s = document.createElement('style');
            s.id = 'toast-keyframes';
            s.textContent = '@keyframes slideIn{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}';
            document.head.appendChild(s);
        }

        document.body.appendChild(toast);
        setTimeout(() => toast.style.opacity = '0', 5000);
        setTimeout(() => toast.remove(), 5300);
    }

    let knownStatus = CURRENT_STATUS;

    function applyStatusUpdate(newStatus, label) {
        if (newStatus === knownStatus) return;
        knownStatus = newStatus;

        document.querySelectorAll('[data-order-status]').forEach(el => {
            el.textContent = label;
            el.dataset.orderStatus = newStatus;
        });

        const sel = document.querySelector('select[name="status"]');
        if (sel) sel.value = newStatus;

        showToast(label, newStatus);
    }

    function startSSE() {
        const es = new EventSource(SSE_URL);

        es.addEventListener('order-status', function (e) {
            try {
                const d = JSON.parse(e.data);
                if (d.order_id == ORDER_ID && d.new_status) {
                    applyStatusUpdate(d.new_status, d.label);
                }
            } catch (_) {}
        });

        es.addEventListener('reconnect', function () {
            es.close();
            setTimeout(startSSE, 1000);
        });

        es.onerror = function () {
            es.close();
            startPolling();
        };
    }

    let pollTimer = null;
    let lastUpdatedAt = null;

    function startPolling() {
        if (pollTimer) return;
        pollTimer = setInterval(async function () {
            try {
                const r = await fetch(POLL_URL, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!r.ok) return;
                const d = await r.json();
                if (lastUpdatedAt && d.updated_at !== lastUpdatedAt && d.status !== knownStatus) {
                    applyStatusUpdate(d.status, d.label);
                }
                lastUpdatedAt = d.updated_at;
            } catch (_) {}
        }, 8000);
    }

    if (typeof EventSource !== 'undefined') {
        startSSE();
    } else {
        startPolling();
    }
})();
</script>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const storeLat = {{ $order->store?->latitude ?? 12.9716 }};
    const storeLng = {{ $order->store?->longitude ?? 77.5946 }};
    const userLat = {{ $order->address?->latitude ?? 12.9352 }};
    const userLng = {{ $order->address?->longitude ?? 77.6245 }};
    const trackingUrl = '/api/v1/orders/{{ $order->id }}/tracking';

    if (document.getElementById('admin-live-map')) {
        const map = L.map('admin-live-map').setView([storeLat, storeLng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Store Marker
        L.marker([storeLat, storeLng]).addTo(map)
            .bindPopup('<b>Store: {{ addslashes($order->store?->name ?? "Store") }}</b>');

        // Customer Address Marker
        L.marker([userLat, userLng]).addTo(map)
            .bindPopup('<b>Customer: {{ addslashes($order->address?->name ?? "Delivery Address") }}</b>');

        let driverMarker = null;
        let polyline = null;

        function fetchLiveTracking() {
            fetch(trackingUrl, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    if (data && data.current_location && data.current_location.latitude) {
                        const dLat = data.current_location.latitude;
                        const dLng = data.current_location.longitude;

                        if (!driverMarker) {
                            driverMarker = L.marker([dLat, dLng]).addTo(map)
                                .bindPopup('<b>Driver: ' + (data.delivery_partner?.name || 'Assigned Driver') + '</b>');
                        } else {
                            driverMarker.setLatLng([dLat, dLng]);
                        }

                        if (polyline) map.removeLayer(polyline);
                        polyline = L.polyline([[storeLat, storeLng], [dLat, dLng], [userLat, userLng]], { color: '#f97316', weight: 4, opacity: 0.8 }).addTo(map);

                        const bounds = L.latLngBounds([[storeLat, storeLng], [dLat, dLng], [userLat, userLng]]);
                        map.fitBounds(bounds, { padding: [30, 30] });

                        if (document.getElementById('driver-status-text')) {
                            document.getElementById('driver-status-text').innerText = (data.delivery_partner?.name || 'Driver') + ' (Live)';
                        }
                    } else {
                        if (polyline) map.removeLayer(polyline);
                        polyline = L.polyline([[storeLat, storeLng], [userLat, userLng]], { color: '#94a3b8', weight: 3, dashArray: '5, 10' }).addTo(map);
                        map.fitBounds(L.latLngBounds([[storeLat, storeLng], [userLat, userLng]]), { padding: [30, 30] });
                    }
                })
                .catch(e => console.error('Tracking fetch error', e));
        }

        fetchLiveTracking();
        setInterval(fetchLiveTracking, 5000);
    }
});
</script>
@endpush

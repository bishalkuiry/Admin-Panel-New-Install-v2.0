@extends('admin.layouts.app')
@section('title', 'Invoice ' . $invoice->invoice_number)
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.invoices.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $invoice->invoice_number }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Order #{{ $invoice->order?->order_number ?? 'N/A' }} •
                    @if($invoice->status === 'paid')<span class="text-green-600 font-medium">Paid</span>
                    @elseif($invoice->status === 'issued')<span class="text-blue-600 font-medium">Issued</span>
                    @elseif($invoice->status === 'draft')<span class="text-orange-600 font-medium">Draft</span>
                    @else<span class="text-red-600 font-medium">Cancelled</span>@endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            <a href="{{ route('admin.invoices.print', $invoice) }}" target="_blank" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </a>
        </div>
    </div>

    <!-- Invoice Preview Card -->
    <div class="card p-8 max-w-4xl mx-auto">
        <!-- Header -->
        @php
            $invoiceLogo = \App\Models\Setting::get('invoice_logo', '');
            $showLogo = \App\Models\Setting::get('invoice_show_logo', '1') === '1';
        @endphp
        <div class="flex justify-between items-start mb-8 pb-6 border-b border-gray-200">
            <div class="flex items-start gap-4">
                @if($showLogo && $invoiceLogo)
                    <img src="{{ storage_url($invoiceLogo) }}" alt="Logo" class="w-14 h-14 object-contain rounded-lg border border-gray-100">
                @endif
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $invoice->business_name ?? 'Business Name' }}</h2>
                    @if($invoice->business_address)<p class="text-sm text-gray-600 mt-1 max-w-xs">{{ $invoice->business_address }}</p>@endif
                    @if($invoice->business_phone)<p class="text-sm text-gray-600">{{ $invoice->business_phone }}</p>@endif
                    @if($invoice->business_email)<p class="text-sm text-gray-600">{{ $invoice->business_email }}</p>@endif
                    @if($invoice->business_gstin)<p class="text-sm text-gray-600 mt-1 font-medium">GSTIN: {{ $invoice->business_gstin }}</p>@endif
                </div>
            </div>
            <div class="text-right">
                <h1 class="text-3xl font-black text-gray-300 uppercase tracking-widest">Invoice</h1>
                <p class="text-sm font-semibold text-gray-900 mt-2">{{ $invoice->invoice_number }}</p>
                <p class="text-sm text-gray-500">Date: {{ $invoice->issued_at ? $invoice->issued_at->format('d M Y') : $invoice->created_at->format('d M Y') }}</p>
                @if($invoice->due_date)<p class="text-sm text-gray-500">Due: {{ $invoice->due_date->format('d M Y') }}</p>@endif
            </div>
        </div>

        <!-- Bill To -->
        <div class="mb-8">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Bill To</h3>
            <p class="font-semibold text-gray-900">{{ $invoice->customer_name }}</p>
            @if($invoice->customer_email)<p class="text-sm text-gray-600">{{ $invoice->customer_email }}</p>@endif
            @if($invoice->customer_phone)<p class="text-sm text-gray-600">{{ $invoice->customer_phone }}</p>@endif
            @if($invoice->shipping_address)<p class="text-sm text-gray-600 mt-1">{{ $invoice->shipping_address }}</p>@endif
        </div>

        <!-- Items Table -->
        <div class="mb-8">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="text-left py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="text-center py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="text-right py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Unit Price</th>
                        <th class="text-right py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @if($invoice->order && $invoice->order->items)
                        @foreach($invoice->order->items as $item)
                        <tr>
                            <td class="py-3">
                                <p class="font-medium text-gray-900 text-sm">{{ $item->product_name }}</p>
                                @if($item->variant_name)<p class="text-xs text-gray-500">{{ $item->variant_name }}</p>@endif
                            </td>
                            <td class="py-3 text-center text-sm text-gray-700">{{ $item->quantity }}</td>
                            <td class="py-3 text-right text-sm text-gray-700">{{ \App\Helpers\CurrencyHelper::format($item->price) }}</td>
                            <td class="py-3 text-right text-sm font-semibold text-gray-900">{{ \App\Helpers\CurrencyHelper::format($item->price * $item->quantity) }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="py-6 text-center text-sm text-gray-400">No items available</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end">
            <div class="w-72 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="text-gray-900">{{ \App\Helpers\CurrencyHelper::format($invoice->subtotal) }}</span>
                </div>
                @if($invoice->discount > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Discount</span>
                    <span class="text-green-600">-{{ \App\Helpers\CurrencyHelper::format($invoice->discount) }}</span>
                </div>
                @endif
                @if($invoice->delivery_fee > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Delivery Fee</span>
                    <span class="text-gray-900">{{ \App\Helpers\CurrencyHelper::format($invoice->delivery_fee) }}</span>
                </div>
                @endif
                @if(($invoice->driver_tip ?? $invoice->order?->driver_tip ?? 0) > 0)
                <div class="flex justify-between text-sm text-emerald-700 font-semibold">
                    <span>🛵 Rider Tip</span>
                    <span>+{{ \App\Helpers\CurrencyHelper::format($invoice->driver_tip ?? $invoice->order?->driver_tip) }}</span>
                </div>
                @endif
                @if($invoice->tax > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Tax</span>
                    <span class="text-gray-900">{{ \App\Helpers\CurrencyHelper::format($invoice->tax) }}</span>
                </div>
                @endif
                <div class="border-t-2 border-gray-900 pt-3 flex justify-between">
                    <span class="font-bold text-gray-900 text-lg">Total</span>
                    <span class="font-bold text-gray-900 text-lg">{{ \App\Helpers\CurrencyHelper::format($invoice->total) }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Notes</h3>
            <p class="text-sm text-gray-600">{{ $invoice->notes }}</p>
        </div>
        @endif

        <!-- Payment Status Badge -->
        <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between">
            <div class="text-xs text-gray-400">Generated on {{ $invoice->created_at->format('d M Y, h:i A') }}</div>
            <div>
                @if($invoice->status === 'paid')
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-green-100 text-green-700 uppercase tracking-wider">✓ Paid</span>
                @elseif($invoice->status === 'issued')
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-blue-100 text-blue-700 uppercase tracking-wider">Issued</span>
                @elseif($invoice->status === 'draft')
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-orange-100 text-orange-700 uppercase tracking-wider">Draft</span>
                @else
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-red-100 text-red-700 uppercase tracking-wider">Cancelled</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

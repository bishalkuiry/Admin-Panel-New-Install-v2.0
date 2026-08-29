@extends('admin.layouts.app')
@section('title', 'Edit Invoice ' . $invoice->invoice_number)
@section('content')
<div class="space-y-5">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.invoices.show', $invoice) }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900 tracking-wide">Edit Invoice</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $invoice->invoice_number }} • Order #{{ $invoice->order?->order_number ?? 'N/A' }}</p>
        </div>
    </div>

    <form action="{{ route('admin.invoices.update', $invoice) }}" method="POST">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Business Information -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Business Information</h3>
                    <div class="space-y-5">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                            <input type="text" name="business_name" value="{{ old('business_name', $invoice->business_name) }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            @error('business_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Business Address</label>
                            <textarea name="business_address" rows="2" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">{{ old('business_address', $invoice->business_address) }}</textarea>
                            @error('business_address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="form-group">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="text" name="business_phone" value="{{ old('business_phone', $invoice->business_phone) }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            </div>
                            <div class="form-group">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="business_email" value="{{ old('business_email', $invoice->business_email) }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">GSTIN / Tax ID</label>
                            <input type="text" name="business_gstin" value="{{ old('business_gstin', $invoice->business_gstin) }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all" placeholder="e.g. 22AAAAA0000A1Z5">
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Customer Information</h3>
                    <div class="space-y-5">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name', $invoice->customer_name) }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="form-group">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="customer_email" value="{{ old('customer_email', $invoice->customer_email) }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            </div>
                            <div class="form-group">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="text" name="customer_phone" value="{{ old('customer_phone', $invoice->customer_phone) }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Address</label>
                            <textarea name="shipping_address" rows="2" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">{{ old('shipping_address', $invoice->shipping_address) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Additional Notes</h3>
                    <div class="form-group">
                        <textarea name="notes" rows="3" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all" placeholder="Add any notes for the customer (e.g., bank details, payment terms, thank you message)">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Status & Dates -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Invoice Settings</h3>
                    <div class="space-y-5">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                            <select name="status" required class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer">
                                <option value="draft" {{ old('status', $invoice->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="issued" {{ old('status', $invoice->status) === 'issued' ? 'selected' : '' }}>Issued</option>
                                <option value="paid" {{ old('status', $invoice->status) === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="cancelled" {{ old('status', $invoice->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                            <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Amount Summary (Read-only) -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Amount Summary</h3>
                    <div class="space-y-3">
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
                        @if($invoice->tax > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tax</span>
                            <span class="text-gray-900">{{ \App\Helpers\CurrencyHelper::format($invoice->tax) }}</span>
                        </div>
                        @endif
                        <div class="border-t pt-3 flex justify-between font-semibold">
                            <span>Total</span>
                            <span>{{ \App\Helpers\CurrencyHelper::format($invoice->total) }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-4">Amounts are synced from the order and cannot be changed here.</p>
                </div>

                <!-- Order Items Preview -->
                <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-4 border-b border-gray-100 pb-4">Order Items</h3>
                    <div class="space-y-3">
                        @if($invoice->order && $invoice->order->items)
                            @foreach($invoice->order->items as $item)
                            <div class="flex items-center justify-between text-sm">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->quantity }} × {{ \App\Helpers\CurrencyHelper::format($item->price) }}</p>
                                </div>
                                <span class="font-semibold text-gray-900">{{ \App\Helpers\CurrencyHelper::format($item->price * $item->quantity) }}</span>
                            </div>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-400">No items</p>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full py-3 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-widest hover:bg-black transition-all shadow-md active:scale-[0.98] text-center">
                        Update Invoice
                    </button>
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="w-full py-3 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-bold uppercase tracking-widest text-center transition-all">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

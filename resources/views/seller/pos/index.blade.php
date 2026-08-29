@extends('seller.layouts.app')

@section('title', 'Seller POS Terminal')

@section('content')
<div x-data="{
    cart: [],
    products: {{ json_encode($products) }},
    searchQuery: '',
    storeId: '{{ $store->id }}',
    customerId: '{{ $customers->first()?->id }}',
    discount: 0,
    tax: 0,
    paymentMethod: 'cash',
    splitCash: 0,
    splitCard: 0,
    isProcessing: false,

    filterProducts() {
        if (this.searchQuery.trim().length === 0) {
            location.reload();
            return;
        }
        fetch('{{ route('seller.pos.search') }}?q=' + encodeURIComponent(this.searchQuery), {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) this.products = res.data;
        });
    },

    addToCart(product) {
        let existing = this.cart.find(i => i.product_id === product.id);
        if (existing) {
            existing.quantity++;
        } else {
            this.cart.push({
                product_id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1
            });
        }
    },
    removeFromCart(index) {
        this.cart.splice(index, 1);
    },
    get subtotal() {
        return this.cart.reduce((sum, i) => sum + (i.price * i.quantity), 0);
    },
    get grandTotal() {
        return Math.max(0, (this.subtotal - parseFloat(this.discount || 0)) + parseFloat(this.tax || 0));
    },
    submitOrder() {
        if (this.cart.length === 0) {
            alert('Cart is empty!');
            return;
        }
        this.isProcessing = true;
        fetch('{{ route('seller.pos.checkout') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                customer_id: this.customerId,
                items: this.cart,
                discount: this.discount,
                tax: this.tax,
                payment_method: this.paymentMethod,
                split_cash_amount: this.splitCash,
                split_card_amount: this.splitCard
            })
        })
        .then(r => r.json())
        .then(res => {
            this.isProcessing = false;
            if (res.success) {
                alert('Seller POS Order Completed!');
                window.open(res.data.receipt_url, '_blank');
                this.cart = [];
            } else {
                alert(res.message || 'Checkout failed');
            }
        })
        .catch(e => {
            this.isProcessing = false;
            alert('Checkout Error: ' + e.message);
        });
    }
}" class="flex flex-col lg:flex-row items-start gap-4 w-full max-w-full">

    <!-- Left Column: Products Catalog -->
    <div class="flex-1 w-full min-w-0 card p-4 sm:p-5 flex flex-col space-y-4 max-h-[calc(100vh-140px)] overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3 flex-shrink-0">
            <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Store POS Product Catalog ({{ $store->name }})</span>
            </h2>
        </div>

        <!-- Search Barcode / Name -->
        <div class="relative flex-shrink-0">
            <input type="text" x-model="searchQuery" @input.debounce.300ms="filterProducts()" class="input text-xs pl-9" placeholder="Type product name, SKU, or scan barcode...">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <!-- Products Grid Container -->
        <div class="flex-1 overflow-y-auto min-h-[350px] max-h-[calc(100vh-260px)] grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 pr-1">
            <template x-for="p in products" :key="p.id">
                <div @click="addToCart(p)" class="p-3 bg-gray-50 hover:bg-orange-50/60 rounded-xl border border-gray-200 cursor-pointer transition-all flex flex-col justify-between space-y-2 group">
                    <div class="aspect-square bg-gray-200 rounded-lg overflow-hidden flex items-center justify-center">
                        <template x-if="p.primary_image">
                            <img :src="'/storage/' + p.primary_image.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        </template>
                        <template x-if="!p.primary_image">
                            <span class="text-gray-400 text-xs">No Image</span>
                        </template>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 text-xs line-clamp-1" x-text="p.name"></p>
                        <p class="text-orange-600 font-bold text-xs mt-0.5" x-text="'₹' + parseFloat(p.price).toFixed(2)"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Right Column: Checkout -->
    <div class="w-full lg:w-96 flex-shrink-0 card p-4 sm:p-5 flex flex-col justify-between space-y-4 max-h-[calc(100vh-140px)] overflow-y-auto">
        <div>
            <h3 class="font-bold text-sm text-gray-900 border-b pb-2 flex items-center justify-between">
                <span>Seller POS Register</span>
                <span class="text-xs text-orange-600 font-mono" x-text="cart.length + ' Items'"></span>
            </h3>

            <!-- Customer -->
            <div class="mt-3">
                <label class="label text-[11px] font-bold">Select Customer</label>
                <select x-model="customerId" class="input text-xs">
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone ?? $c->email }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Cart Items -->
            <div class="mt-3 space-y-2 max-h-48 overflow-y-auto border-t border-b py-2">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="flex items-center justify-between gap-2 p-2 bg-gray-50 rounded-lg text-xs">
                        <div class="flex-1">
                            <p class="font-bold text-gray-900" x-text="item.name"></p>
                            <p class="text-[10px] text-gray-500 font-mono" x-text="'₹' + item.price.toFixed(2) + ' x ' + item.quantity"></p>
                        </div>
                        <div class="flex items-center gap-1">
                            <input type="number" x-model="item.quantity" class="w-12 input text-center text-xs p-1" min="1">
                            <button type="button" @click="removeFromCart(index)" class="text-red-500 hover:text-red-700 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Payment & Split Payment -->
        <div class="space-y-3 pt-2">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="label text-[10px] font-bold">Discount (₹)</label>
                    <input type="number" step="0.01" x-model="discount" class="input text-xs" placeholder="0.00">
                </div>
                <div>
                    <label class="label text-[10px] font-bold">Tax (₹)</label>
                    <input type="number" step="0.01" x-model="tax" class="input text-xs" placeholder="0.00">
                </div>
            </div>

            <div>
                <label class="label text-[10px] font-bold">Payment Method</label>
                <select x-model="paymentMethod" class="input text-xs">
                    <option value="cash">Cash Payment</option>
                    <option value="card">Card Terminal</option>
                    <option value="wallet">Customer Wallet</option>
                    <option value="split">Split Payment (Cash + Card)</option>
                </select>
            </div>

            <div x-show="paymentMethod === 'split'" class="grid grid-cols-2 gap-2 p-2 bg-gray-50 rounded-lg border text-xs">
                <div>
                    <label class="label text-[9px] font-bold">Cash Portion (₹)</label>
                    <input type="number" step="0.01" x-model="splitCash" class="input text-xs" placeholder="0.00">
                </div>
                <div>
                    <label class="label text-[9px] font-bold">Card Portion (₹)</label>
                    <input type="number" step="0.01" x-model="splitCard" class="input text-xs" placeholder="0.00">
                </div>
            </div>

            <div class="p-3 bg-orange-50 rounded-xl border border-orange-200 flex items-center justify-between">
                <span class="font-bold text-gray-800 text-xs">Grand Total:</span>
                <span class="font-extrabold text-orange-600 text-base" x-text="'₹' + grandTotal.toFixed(2)"></span>
            </div>

            <button type="button" @click="submitOrder()" :disabled="isProcessing || cart.length === 0" class="w-full btn btn-primary text-xs font-bold py-3 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span x-show="!isProcessing">Complete & Print Receipt</span>
                <span x-show="isProcessing">Processing...</span>
            </button>
        </div>
    </div>
</div>
@endsection

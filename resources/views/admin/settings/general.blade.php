@extends('admin.layouts.app')

@section('title', 'General Settings')

@section('content')
@php
    $decimals = (int)($settings['currency_decimal_places'] ?? 0);
    $step = $decimals > 0 ? pow(10, -$decimals) : 1;
@endphp
<div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="min-w-0">
            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">General Settings</h1>
            <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">Configure core application settings</p>
        </div>
        <a href="{{ route('admin.settings.mobile-app') }}" class="btn-secondary text-sm flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="hidden sm:inline">Back to Mobile App Settings</span>
            <span class="sm:hidden">Back</span>
        </a>
    </div>

    <form action="{{ route('admin.settings.general.update') }}" method="POST" id="generalSettingsForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Vendor Mode -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-900 text-lg">Vendor Mode</h3>
                        <p class="text-sm text-gray-500">Choose how products and orders are managed</p>
                    </div>
                    
                    <!-- Info Banner -->
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex gap-2">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-xs text-blue-800">
                                <p class="font-medium mb-1">How These Settings Work Together:</p>
                                <ul class="space-y-1 ml-4 list-disc">
                                    <li><strong>Single Vendor + Single Store Cart:</strong> Traditional e-commerce (one seller, one order)</li>
                                    <li><strong>Multi-Vendor + Single Store Cart:</strong> Marketplace where customers shop from one seller at a time</li>
                                    <li><strong>Multi-Vendor + Multi-Store Cart:</strong> Full marketplace like Amazon (multiple sellers, multiple orders)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <!-- Single Vendor -->
                        <label class="cursor-pointer">
                            <input type="radio" name="vendor_mode" value="single" class="peer sr-only" {{ ($settings['vendor_mode'] ?? 'single') == 'single' ? 'checked' : '' }} onchange="updateVendorModeInfo()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-medium text-gray-900">Single Vendor</span>
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded">SIMPLE</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-2">Admin manages all products and orders centrally</p>
                                        <ul class="text-xs text-gray-500 space-y-1">
                                            <li class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                All products have store_id = NULL
                                            </li>
                                            <li class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                All orders managed by admin
                                            </li>
                                            <li class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Global delivery charges and taxes
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Multi Vendor -->
                        <label class="cursor-pointer">
                            <input type="radio" name="vendor_mode" value="multi" class="peer sr-only" {{ ($settings['vendor_mode'] ?? 'single') == 'multi' ? 'checked' : '' }} onchange="updateVendorModeInfo()">
                            <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-medium text-gray-900">Multi-Vendor</span>
                                            <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-medium rounded">MARKETPLACE</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-2">Multiple sellers can manage their own products and orders</p>
                                        <ul class="text-xs text-gray-500 space-y-1">
                                            <li class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Admin + Store products (store_id assigned)
                                            </li>
                                            <li class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Orders routed to respective sellers
                                            </li>
                                            <li class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Store-specific charges and commissions
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Delivery Charges -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-900 text-lg">Global Delivery Charge</h3>
                        <p class="text-sm text-gray-500">Default delivery fee when store-specific charges are not set</p>
                    </div>

                    <div class="max-w-md">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Fee</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                            </div>
                            <input type="number" name="global_delivery_charge" step="{{ $step }}" min="0" class="w-full h-10 pl-10 pr-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" value="{{ number_format((float)($settings['global_delivery_charge'] ?? 5.99), $decimals, '.', '') }}" placeholder="{{ number_format(5.99, $decimals, '.', '') }}">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">This applies when products don't have store-specific delivery charges</p>
                    </div>
                </div>


                <!-- Single Store Cart -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg">Single Store Cart</h3>
                            <p class="text-sm text-gray-500">Restrict customers to add items from only one store at a time</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="single_store_cart" value="1" id="single_store_cart" {{ ($settings['single_store_cart'] ?? '1') == '1' ? 'checked' : '' }} onchange="updateSingleStoreCartInfo()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div id="single_store_cart_info" class="space-y-3">
                        <!-- ON State (Single Store) -->
                        <div id="cart_single_info" class="{{ ($settings['single_store_cart'] ?? '1') == '1' ? '' : 'hidden' }} p-3 bg-amber-50 border border-amber-200 rounded-lg">
                            <div class="flex gap-2">
                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div class="text-sm text-amber-800">
                                    <p class="font-medium mb-1">Single Store Mode (ON)</p>
                                    <p class="text-xs">Users can only add items from ONE store at a time. Adding items from a different store will be blocked.</p>
                                </div>
                            </div>
                        </div>

                        <!-- OFF State (Multi Store) -->
                        <div id="cart_multi_info" class="{{ ($settings['single_store_cart'] ?? '1') == '1' ? 'hidden' : '' }} p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex gap-2">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="text-sm text-green-800">
                                    <p class="font-medium mb-1">Multi-Store Mode (OFF)</p>
                                    <p class="text-xs">Users can add items from MULTIPLE stores. Separate orders will be created for each store at checkout.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <!-- Driver COD UPI QR Code Collection -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg">Driver COD UPI QR Collection</h3>
                            <p class="text-sm text-gray-500">Allow delivery drivers to show Razorpay / UPI QR code to collect COD payments online</p>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" name="driver_upi_qr_enabled" value="1" id="driver_upi_qr_enabled" {{ ($settings['driver_upi_qr_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                        When enabled, delivery partners will see a "Pay via UPI QR" option during cash collection for COD orders. Customer scans QR code via Google Pay/PhonePe/Paytm and the order marks completed automatically upon payment.
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white border border-gray-200 rounded-lg p-6 sticky top-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Current Configuration</h3>
                    
                    <div class="space-y-4">
                        <!-- Vendor Mode Status -->
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Vendor Mode</p>
                            <div id="vendor_mode_status" class="flex items-center gap-2">
                                @if(($settings['vendor_mode'] ?? 'single') == 'single')
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded">Single Vendor</span>
                                @else
                                <span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs font-medium rounded">Multi-Vendor</span>
                                @endif
                            </div>
                        </div>

                        <!-- Delivery Charge Status -->
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Global Delivery</p>
                            <p class="font-medium text-gray-900"><x-currency>{{ $settings['global_delivery_charge'] ?? '5.99' }}</x-currency></p>
                        </div>

                        <!-- Single Store Cart Status -->
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Cart Mode</p>
                            <div id="cart_status">
                                @if(($settings['single_store_cart'] ?? '1') == '1')
                                <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-medium rounded">Single Store</span>
                                @else
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded">Multi Store</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <x-permission-btn 
                            permission="settings.manage" 
                            type="submit"
                            class="btn-primary w-full justify-center" 
                            label="Save Settings"
                            icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                        />
                    </div>

                    <!-- Info Box -->
                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex gap-2">
                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-blue-800">
                                These settings affect how your entire marketplace operates. Changes will apply immediately after saving.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function updateVendorModeInfo() {
    const vendorMode = document.querySelector('input[name="vendor_mode"]:checked').value;
    const statusDiv = document.getElementById('vendor_mode_status');
    
    if (vendorMode === 'single') {
        statusDiv.innerHTML = '<span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded">Single Vendor</span>';
    } else {
        statusDiv.innerHTML = '<span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs font-medium rounded">Multi-Vendor</span>';
    }
}

function updateSingleStoreCartInfo() {
    const enabled = document.getElementById('single_store_cart').checked;
    const cartSingleInfo = document.getElementById('cart_single_info');
    const cartMultiInfo = document.getElementById('cart_multi_info');
    const cartStatus = document.getElementById('cart_status');
    
    if (enabled) {
        cartSingleInfo.classList.remove('hidden');
        cartMultiInfo.classList.add('hidden');
        cartStatus.innerHTML = '<span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-medium rounded">Single Store</span>';
    } else {
        cartSingleInfo.classList.add('hidden');
        cartMultiInfo.classList.remove('hidden');
        cartStatus.innerHTML = '<span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded">Multi Store</span>';
    }
}

// Form submission
document.getElementById('generalSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            alert.textContent = data.message || 'Settings saved successfully';
            document.body.appendChild(alert);
            
            setTimeout(() => alert.remove(), 3000);
        } else {
            throw new Error(data.message || 'Failed to save settings');
        }
    })
    .catch(error => {
        const alert = document.createElement('div');
        alert.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        alert.textContent = error.message || 'An error occurred';
        document.body.appendChild(alert);
        
        setTimeout(() => alert.remove(), 3000);
    });
});
</script>
@endpush
@endsection

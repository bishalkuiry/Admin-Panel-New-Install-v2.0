@extends('admin.layouts.app')

@section('title', 'Payment Settings')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Payment Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Configure payment gateways and methods</p>
        </div>
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary-600">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('admin.settings.index') }}" class="hover:text-primary-600">Settings</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-medium">Payment</span>
        </nav>
    </div>

    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-green-800">{{ session('success') }}</p>
            <button type="button" onclick="this.parentElement.remove()" class="ml-auto text-green-600 hover:text-green-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    <!-- Mobile Gateway Selector -->
    <div class="lg:hidden mb-4">
        <label for="mobile-gateway-select" class="block text-sm font-medium text-gray-700 mb-2">Select Payment Gateway</label>
        <select id="mobile-gateway-select" class="w-full h-11 px-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none">
            <option value="razorpay">Razorpay</option>
            <option value="stripe">Stripe</option>
            <option value="paypal">PayPal</option>
            <option value="paystack">Paystack</option>
            <option value="phonepe">PhonePe</option>
            <option value="paytm">Paytm</option>
            <option value="flutterwave">Flutterwave</option>
            <option value="midtrans">Midtrans</option>
            <option value="myfatoorah">MyFatoorah</option>
            <option value="instamojo">Instamojo</option>
            <option value="bank-transfer">Bank Transfer</option>
            <option value="cod">Cash on Delivery</option>
        </select>
    </div>

    <!-- Main Content -->
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Sidebar Navigation -->
        <aside class="hidden lg:block w-64 flex-shrink-0">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden sticky top-4">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Payment Gateways</h3>
                </div>
                <nav class="p-2">
                    <a href="#razorpay" data-gateway="razorpay" class="gateway-nav-item active flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M22.436 0l-11.91 7.773-1.174 4.276 6.625-4.297L11.65 24h4.391l6.395-24z"/></svg>
                        </div>
                        <span>Razorpay</span>
                    </a>
                    <a href="#stripe" data-gateway="stripe" class="gateway-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 24 24"><path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.594-7.305h.003z"/></svg>
                        </div>
                        <span>Stripe</span>
                    </a>
                    <a href="#paypal" data-gateway="paypal" class="gateway-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106z"/></svg>
                        </div>
                        <span>PayPal</span>
                    </a>
                    <a href="#paystack" data-gateway="paystack" class="gateway-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-cyan-600" fill="currentColor" viewBox="0 0 24 24"><path d="M0 12.5C0 9.462 2.462 7 5.5 7h13C21.538 7 24 9.462 24 12.5S21.538 18 18.5 18h-13C2.462 18 0 15.538 0 12.5z"/></svg>
                        </div>
                        <span>Paystack</span>
                    </a>
                    <a href="#phonepe" data-gateway="phonepe" class="gateway-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 24 24"><path d="M19.5 3h-15A1.5 1.5 0 003 4.5v15A1.5 1.5 0 004.5 21h15a1.5 1.5 0 001.5-1.5v-15A1.5 1.5 0 0019.5 3z"/></svg>
                        </div>
                        <span>PhonePe</span>
                    </a>
                    <a href="#paytm" data-gateway="paytm" class="gateway-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>
                        </div>
                        <span>Paytm</span>
                    </a>
                    <a href="#flutterwave" data-gateway="flutterwave" class="gateway-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-orange-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>
                        </div>
                        <span>Flutterwave</span>
                    </a>
                    <a href="#midtrans" data-gateway="midtrans" class="gateway-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-teal-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>
                        </div>
                        <span>Midtrans</span>
                    </a>
                    <a href="#myfatoorah" data-gateway="myfatoorah" class="gateway-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>
                        </div>
                        <span>MyFatoorah</span>
                    </a>
                    <a href="#instamojo" data-gateway="instamojo" class="gateway-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <div class="w-8 h-8 bg-pink-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-pink-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>
                        </div>
                        <span>Instamojo</span>
                    </a>
                    
                    <div class="my-2 border-t border-gray-200"></div>
                    
                    <a href="#bank-transfer" data-gateway="bank-transfer" class="gateway-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                        </div>
                        <span>Bank Transfer</span>
                    </a>
                    <a href="#cod" data-gateway="cod" class="gateway-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <span>Cash on Delivery</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Content Area -->
        <main class="flex-1 min-w-0">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <!-- Razorpay -->
                <div id="razorpay" class="gateway-content active">
                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="gateway" value="razorpay">
                        @include('admin.settings.partials._razorpay-form')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <x-permission-btn
                                permission="settings.manage"
                                type="submit"
                                class="btn-primary w-full justify-center shadow-lg"
                                label="Update Razorpay Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </form>
                </div>

                <!-- Stripe -->
                <div id="stripe" class="gateway-content hidden">
                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="gateway" value="stripe">
                        @include('admin.settings.partials._stripe-form')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <x-permission-btn
                                permission="settings.manage"
                                type="submit"
                                class="btn-primary w-full justify-center shadow-lg"
                                label="Update Stripe Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </form>
                </div>

                <!-- PayPal -->
                <div id="paypal" class="gateway-content hidden">
                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="gateway" value="paypal">
                        @include('admin.settings.partials._paypal-form')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <x-permission-btn
                                permission="settings.manage"
                                type="submit"
                                class="btn-primary w-full justify-center shadow-lg"
                                label="Update PayPal Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </form>
                </div>

                <!-- Paystack -->
                <div id="paystack" class="gateway-content hidden">
                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="gateway" value="paystack">
                        @include('admin.settings.partials._paystack-form')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <x-permission-btn
                                permission="settings.manage"
                                type="submit"
                                class="btn-primary w-full justify-center shadow-lg"
                                label="Update Paystack Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </form>
                </div>

                <!-- PhonePe -->
                <div id="phonepe" class="gateway-content hidden">
                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="gateway" value="phonepe">
                        @include('admin.settings.partials._phonepe-form')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <x-permission-btn
                                permission="settings.manage"
                                type="submit"
                                class="btn-primary w-full justify-center shadow-lg"
                                label="Update PhonePe Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </form>
                </div>

                <!-- Paytm -->
                <div id="paytm" class="gateway-content hidden">
                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="gateway" value="paytm">
                        @include('admin.settings.partials._paytm-form')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <x-permission-btn
                                permission="settings.manage"
                                type="submit"
                                class="btn-primary w-full justify-center shadow-lg"
                                label="Update Paytm Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </form>
                </div>

                <!-- Flutterwave -->
                <div id="flutterwave" class="gateway-content hidden">
                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="gateway" value="flutterwave">
                        @include('admin.settings.partials._flutterwave-form')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <x-permission-btn
                                permission="settings.manage"
                                type="submit"
                                class="btn-primary w-full justify-center shadow-lg"
                                label="Update Flutterwave Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </form>
                </div>

                <!-- Midtrans -->
                <div id="midtrans" class="gateway-content hidden">
                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="gateway" value="midtrans">
                        @include('admin.settings.partials._midtrans-form')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <x-permission-btn
                                permission="settings.manage"
                                type="submit"
                                class="btn-primary w-full justify-center shadow-lg"
                                label="Update Midtrans Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </form>
                </div>

                <!-- MyFatoorah -->
                <div id="myfatoorah" class="gateway-content hidden">
                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="gateway" value="myfatoorah">
                        @include('admin.settings.partials._myfatoorah-form')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <x-permission-btn
                                permission="settings.manage"
                                type="submit"
                                class="btn-primary w-full justify-center shadow-lg"
                                label="Update MyFatoorah Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </form>
                </div>

                <!-- Instamojo -->
                <div id="instamojo" class="gateway-content hidden">
                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="gateway" value="instamojo">
                        @include('admin.settings.partials._instamojo-form')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <x-permission-btn
                                permission="settings.manage"
                                type="submit"
                                class="btn-primary w-full justify-center shadow-lg"
                                label="Update Instamojo Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </form>
                </div>

                <!-- Bank Transfer -->
                <div id="bank-transfer" class="gateway-content hidden">
                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="gateway" value="bank_transfer">
                        @include('admin.settings.partials._bank-transfer-form')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <x-permission-btn
                                permission="settings.manage"
                                type="submit"
                                class="btn-primary w-full justify-center shadow-lg"
                                label="Update Bank Transfer Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </form>
                </div>

                <!-- COD -->
                <div id="cod" class="gateway-content hidden">
                    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="gateway" value="cod">
                        @include('admin.settings.partials._cod-form')
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <x-permission-btn
                                permission="settings.manage"
                                type="submit"
                                class="btn-primary w-full justify-center shadow-lg"
                                label="Update COD Settings"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                            />
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.gateway-nav-item {
    color: #6b7280;
}

.gateway-nav-item:hover {
    background-color: #f3f4f6;
    color: #111827;
}

.gateway-nav-item.active {
    background-color: #eff6ff;
    color: #2563eb;
    font-weight: 600;
}

.gateway-nav-item.active .bg-blue-100 {
    background-color: #dbeafe;
}

.gateway-content {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Desktop navigation
    const navItems = document.querySelectorAll('.gateway-nav-item');
    const contentSections = document.querySelectorAll('.gateway-content');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            
            // Update nav items
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            
            // Update content
            contentSections.forEach(section => {
                section.classList.add('hidden');
                section.classList.remove('active');
            });
            
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.classList.remove('hidden');
                targetSection.classList.add('active');
            }
            
            // Update mobile select
            const mobileSelect = document.getElementById('mobile-gateway-select');
            if (mobileSelect) {
                mobileSelect.value = targetId;
            }
            
            // Scroll to top on mobile
            if (window.innerWidth < 1024) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });
    
    // Mobile select
    const mobileSelect = document.getElementById('mobile-gateway-select');
    if (mobileSelect) {
        mobileSelect.addEventListener('change', function() {
            const targetId = this.value;
            
            // Update content
            contentSections.forEach(section => {
                section.classList.add('hidden');
                section.classList.remove('active');
            });
            
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.classList.remove('hidden');
                targetSection.classList.add('active');
            }
            
            // Scroll to content
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    
    // Handle hash in URL
    if (window.location.hash) {
        const targetId = window.location.hash.substring(1);
        const targetNav = document.querySelector(`[href="#${targetId}"]`);
        if (targetNav) {
            targetNav.click();
        }
    }
});
</script>
@endsection

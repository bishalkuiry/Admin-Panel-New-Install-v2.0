<div class="space-y-5">
    <!-- Enable/Disable Toggle -->
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <label for="paypal_enabled" class="font-medium text-gray-900 cursor-pointer">Enable PayPal</label>
                    <p class="text-xs text-gray-500">Accept payments through PayPal gateway</p>
                </div>
            </div>
            <label class="toggle">
                <input class="toggle-input" type="checkbox" name="enabled" id="paypal_enabled" 
                       value="1" {{ $paymentConfig['gateways']['paypal']['enabled'] ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <!-- Configuration Fields -->
        <div class="grid grid-cols-1 gap-5">
            <div>
                <label for="paypal_mode" class="block text-sm font-medium text-gray-700 mb-2">
                    Environment Mode
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    </div>
                    <select class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all appearance-none" 
                            id="paypal_mode" name="mode">
                        <option value="sandbox" {{ $paymentConfig['gateways']['paypal']['mode'] === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                        <option value="live" {{ $paymentConfig['gateways']['paypal']['mode'] === 'live' ? 'selected' : '' }}>Live (Production)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Use sandbox for testing, live for production</p>
            </div>

            <div>
                <label for="paypal_business_email" class="block text-sm font-medium text-gray-700 mb-2">
                    Business Email
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <input type="email" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="paypal_business_email" name="business_email" 
                           value="{{ $paymentConfig['gateways']['paypal']['business_email'] }}" 
                           placeholder="seller@somedomain.com">
                </div>
                <p class="text-xs text-gray-500 mt-1">Your PayPal business account email</p>
            </div>

            <div>
                <label for="paypal_client_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Client ID
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <input type="text" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="paypal_client_id" name="client_id" 
                           value="{{ $paymentConfig['gateways']['paypal']['client_id'] }}" 
                           placeholder="AWZ8FEaCNEbFXCy8oeWOGHKc0dDXZ8YBaoNmI2yMuJzWvrEr7METlQ4kE2xVfugYgVajLKy5Q5I7o">
                </div>
                <p class="text-xs text-gray-500 mt-1">Get from PayPal Developer Dashboard → My Apps & Credentials</p>
            </div>

            <div>
                <label for="paypal_secret" class="block text-sm font-medium text-gray-700 mb-2">
                    Secret Key
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <input type="password" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="paypal_secret" name="secret" 
                           value="{{ $paymentConfig['gateways']['paypal']['secret'] }}" 
                           placeholder="Ek6U1UR--xAn2yJt5exYpo8Y3GxRko5aKflu2lZgrSuUuGq5X9qXBj7Jh88w3vcLl8Kqw0PjQbDrf4D">
                </div>
                <p class="text-xs text-gray-500 mt-1">Keep this secret and never share publicly</p>
            </div>

            <div>
                <label for="paypal_currency" class="block text-sm font-medium text-gray-700 mb-2">
                    Currency Code
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <select class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all appearance-none" 
                            id="paypal_currency" name="currency">
                        <option value="USD" {{ $paymentConfig['gateways']['paypal']['currency'] === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                        <option value="EUR" {{ $paymentConfig['gateways']['paypal']['currency'] === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        <option value="GBP" {{ $paymentConfig['gateways']['paypal']['currency'] === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                        <option value="INR" {{ $paymentConfig['gateways']['paypal']['currency'] === 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Currency for PayPal transactions</p>
            </div>

            <div>
                <label for="paypal_notification_url" class="block text-sm font-medium text-gray-700 mb-2">
                    IPN Notification URL
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <input type="text" class="w-full h-11 pl-10 pr-10 text-sm border border-gray-300 rounded-lg bg-gray-50 outline-none" 
                           id="paypal_notification_url" name="notification_url" 
                           value="{{ $paymentConfig['gateways']['paypal']['notification_url'] ?? url('/api/v1/payment/paypal/ipn') }}" 
                           readonly>
                    <button type="button" onclick="copyToClipboard('paypal_notification_url')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Copy and set this as IPN notification URL in your PayPal account</p>
            </div>
        </div>
</div>


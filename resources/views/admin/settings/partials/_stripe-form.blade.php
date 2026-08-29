<div class="space-y-5">
    <!-- Enable/Disable Toggle -->
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <label for="stripe_enabled" class="font-medium text-gray-900 cursor-pointer">Enable Stripe</label>
                <p class="text-xs text-gray-500">Accept payments through Stripe gateway</p>
            </div>
        </div>
        <label class="toggle">
            <input class="toggle-input" type="checkbox" name="enabled" id="stripe_enabled"
                   value="1" {{ $paymentConfig['gateways']['stripe']['enabled'] ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
        </label>
    </div>

    <!-- Configuration Fields -->
    <div class="grid grid-cols-1 gap-5">
        <div>
            <label for="stripe_publishable_key" class="block text-sm font-medium text-gray-700 mb-2">
                Publishable Key <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <input type="text" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                       id="stripe_publishable_key" name="publishable_key"
                       value="{{ $paymentConfig['gateways']['stripe']['publishable_key'] }}"
                       placeholder="pk_test_...">
            </div>
            <p class="text-xs text-gray-500 mt-1">Safe to share publicly (starts with pk_)</p>
        </div>

        <div>
            <label for="stripe_secret_key" class="block text-sm font-medium text-gray-700 mb-2">
                Secret Key <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input type="password" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                       id="stripe_secret_key" name="secret_key"
                       value="{{ $paymentConfig['gateways']['stripe']['secret_key'] }}"
                       placeholder="sk_test_...">
            </div>
            <p class="text-xs text-gray-500 mt-1">Keep this secret (starts with sk_)</p>
        </div>

        <div>
            <label for="stripe_webhook_secret" class="block text-sm font-medium text-gray-700 mb-2">
                Webhook Secret
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                </div>
                <input type="text" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all"
                       id="stripe_webhook_secret" name="webhook_secret"
                       value="{{ $paymentConfig['gateways']['stripe']['webhook_secret'] }}"
                       placeholder="whsec_...">
            </div>
            <p class="text-xs text-gray-500 mt-1">For webhook signature verification (starts with whsec_)</p>
        </div>
    </div>
</div>

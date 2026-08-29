<div class="space-y-5">
    <!-- Enable/Disable Toggle -->
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <label for="phonepe_enabled" class="font-medium text-gray-900 cursor-pointer">Enable PhonePe</label>
                    <p class="text-xs text-gray-500">Accept payments through PhonePe gateway</p>
                </div>
            </div>
            <label class="toggle">
                <input class="toggle-input" type="checkbox" name="enabled" id="phonepe_enabled" 
                       value="1" {{ $paymentConfig['gateways']['phonepe']['enabled'] ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <!-- Configuration Fields -->
        <div class="grid grid-cols-1 gap-5">
            <div>
                <label for="phonepe_merchant_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Merchant ID
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <input type="text" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="phonepe_merchant_id" name="merchant_id" 
                           value="{{ $paymentConfig['gateways']['phonepe']['merchant_id'] }}" 
                           placeholder="MERCHANTUAT">
                </div>
                <p class="text-xs text-gray-500 mt-1">Your unique merchant identifier from PhonePe</p>
            </div>

            <div>
                <label for="phonepe_salt_key" class="block text-sm font-medium text-gray-700 mb-2">
                    Salt Key
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <input type="password" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="phonepe_salt_key" name="salt_key" 
                           value="{{ $paymentConfig['gateways']['phonepe']['salt_key'] }}" 
                           placeholder="099eb0cd-02cf-4e2a-8aca-3e6c6aff0399">
                </div>
                <p class="text-xs text-gray-500 mt-1">Salt key for request signature generation</p>
            </div>

            <div>
                <label for="phonepe_salt_index" class="block text-sm font-medium text-gray-700 mb-2">
                    Salt Index
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                    </div>
                    <input type="number" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="phonepe_salt_index" name="salt_index" 
                           value="{{ $paymentConfig['gateways']['phonepe']['salt_index'] }}" 
                           placeholder="1" min="1">
                </div>
                <p class="text-xs text-gray-500 mt-1">Salt index number (usually 1)</p>
            </div>

            <div>
                <label for="phonepe_environment" class="block text-sm font-medium text-gray-700 mb-2">
                    Environment
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    </div>
                    <select class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all appearance-none" 
                            id="phonepe_environment" name="environment">
                        <option value="sandbox" {{ $paymentConfig['gateways']['phonepe']['environment'] === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                        <option value="production" {{ $paymentConfig['gateways']['phonepe']['environment'] === 'production' ? 'selected' : '' }}>Production</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Use sandbox for testing, production for live transactions</p>
            </div>
        </div>
</div>


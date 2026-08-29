<div class="space-y-5">
    <!-- Enable/Disable Toggle -->
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <label for="instamojo_enabled" class="font-medium text-gray-900 cursor-pointer">Enable Instamojo</label>
                    <p class="text-xs text-gray-500">Accept payments through Instamojo gateway</p>
                </div>
            </div>
            <label class="toggle">
                <input class="toggle-input" type="checkbox" name="enabled" id="instamojo_enabled" 
                       value="1" {{ $paymentConfig['gateways']['instamojo']['enabled'] ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <!-- Configuration Fields -->
        <div class="grid grid-cols-1 gap-5">
            <div>
                <label for="instamojo_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                    API Key
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <input type="password" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="instamojo_api_key" name="api_key" 
                           value="{{ $paymentConfig['gateways']['instamojo']['api_key'] }}"
                           placeholder="test_...">
                </div>
                <p class="text-xs text-gray-500 mt-1">Get from Instamojo Dashboard → Settings → API & Plugins</p>
            </div>

            <div>
                <label for="instamojo_auth_token" class="block text-sm font-medium text-gray-700 mb-2">
                    Auth Token
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <input type="password" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="instamojo_auth_token" name="auth_token" 
                           value="{{ $paymentConfig['gateways']['instamojo']['auth_token'] }}"
                           placeholder="test_...">
                </div>
                <p class="text-xs text-gray-500 mt-1">Authentication token for API requests</p>
            </div>

            <div>
                <label for="instamojo_salt" class="block text-sm font-medium text-gray-700 mb-2">
                    Salt
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    </div>
                    <input type="password" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="instamojo_salt" name="salt" 
                           value="{{ $paymentConfig['gateways']['instamojo']['salt'] }}"
                           placeholder="...">
                </div>
                <p class="text-xs text-gray-500 mt-1">Salt key for webhook verification</p>
            </div>

            <div>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <label for="instamojo_test_mode" class="font-medium text-gray-900 cursor-pointer">Test Mode</label>
                            <p class="text-xs text-gray-500">Enable for testing transactions</p>
                        </div>
                    </div>
                    <label class="toggle">
                        <input class="toggle-input" type="checkbox" name="test_mode" id="instamojo_test_mode" 
                               value="1" {{ $paymentConfig['gateways']['instamojo']['test_mode'] ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>
</div>


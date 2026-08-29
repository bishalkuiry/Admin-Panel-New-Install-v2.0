<div class="space-y-5">
    <!-- Enable/Disable Toggle -->
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <label for="myfatoorah_enabled" class="font-medium text-gray-900 cursor-pointer">Enable MyFatoorah</label>
                    <p class="text-xs text-gray-500">Accept payments through MyFatoorah gateway</p>
                </div>
            </div>
            <label class="toggle">
                <input class="toggle-input" type="checkbox" name="enabled" id="myfatoorah_enabled" 
                       value="1" {{ $paymentConfig['gateways']['myfatoorah']['enabled'] ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <!-- Configuration Fields -->
        <div class="grid grid-cols-1 gap-5">
            <div>
                <label for="myfatoorah_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                    API Key
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <input type="password" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="myfatoorah_api_key" name="api_key" 
                           value="{{ $paymentConfig['gateways']['myfatoorah']['api_key'] }}"
                           placeholder="rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL">
                </div>
                <p class="text-xs text-gray-500 mt-1">Get from MyFatoorah Dashboard → Integration Settings</p>
            </div>

            <div>
                <label for="myfatoorah_country_code" class="block text-sm font-medium text-gray-700 mb-2">
                    Country
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <select class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all appearance-none" 
                            id="myfatoorah_country_code" name="country_code">
                        <option value="KWT" {{ $paymentConfig['gateways']['myfatoorah']['country_code'] === 'KWT' ? 'selected' : '' }}>Kuwait</option>
                        <option value="SAU" {{ $paymentConfig['gateways']['myfatoorah']['country_code'] === 'SAU' ? 'selected' : '' }}>Saudi Arabia</option>
                        <option value="ARE" {{ $paymentConfig['gateways']['myfatoorah']['country_code'] === 'ARE' ? 'selected' : '' }}>UAE</option>
                        <option value="QAT" {{ $paymentConfig['gateways']['myfatoorah']['country_code'] === 'QAT' ? 'selected' : '' }}>Qatar</option>
                        <option value="BHR" {{ $paymentConfig['gateways']['myfatoorah']['country_code'] === 'BHR' ? 'selected' : '' }}>Bahrain</option>
                        <option value="OMN" {{ $paymentConfig['gateways']['myfatoorah']['country_code'] === 'OMN' ? 'selected' : '' }}>Oman</option>
                        <option value="JOR" {{ $paymentConfig['gateways']['myfatoorah']['country_code'] === 'JOR' ? 'selected' : '' }}>Jordan</option>
                        <option value="EGY" {{ $paymentConfig['gateways']['myfatoorah']['country_code'] === 'EGY' ? 'selected' : '' }}>Egypt</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Select your MyFatoorah account country</p>
            </div>

            <div>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <label for="myfatoorah_test_mode" class="font-medium text-gray-900 cursor-pointer">Test Mode</label>
                            <p class="text-xs text-gray-500">Enable for testing transactions</p>
                        </div>
                    </div>
                    <label class="toggle">
                        <input class="toggle-input" type="checkbox" name="test_mode" id="myfatoorah_test_mode" 
                               value="1" {{ $paymentConfig['gateways']['myfatoorah']['test_mode'] ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>
</div>


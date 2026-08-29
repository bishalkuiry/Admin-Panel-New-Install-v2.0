<div class="space-y-5">
    <!-- Enable/Disable Toggle -->
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <label for="cod_enabled" class="font-medium text-gray-900 cursor-pointer">Enable Cash On Delivery</label>
                    <p class="text-xs text-gray-500">Allow customers to pay with cash upon delivery</p>
                </div>
            </div>
            <label class="toggle">
                <input class="toggle-input" type="checkbox" name="enabled" id="cod_enabled" 
                       value="1" {{ $paymentConfig['cod']['enabled'] ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <!-- Configuration Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="cod_min_amount" class="block text-sm font-medium text-gray-700 mb-2">
                    Minimum Order Amount
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <input type="number" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="cod_min_amount" name="min_amount" 
                           value="{{ $paymentConfig['cod']['min_amount'] }}" 
                           placeholder="100" min="0" step="0.01">
                </div>
                <p class="text-xs text-gray-500 mt-1">Minimum order value for COD (0 for no limit)</p>
            </div>

            <div>
                <label for="cod_max_amount" class="block text-sm font-medium text-gray-700 mb-2">
                    Maximum Order Amount
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <input type="number" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="cod_max_amount" name="max_amount" 
                           value="{{ $paymentConfig['cod']['max_amount'] }}" 
                           placeholder="5000" min="0" step="0.01">
                </div>
                <p class="text-xs text-gray-500 mt-1">Maximum order value for COD (0 for no limit)</p>
            </div>
        </div>

        <!-- Info Box -->
        <div class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="text-sm text-blue-800">
                <p class="font-medium mb-1">About Cash On Delivery</p>
                <p>COD allows customers to pay with cash when their order is delivered. Set minimum and maximum order amounts to control when this option is available.</p>
            </div>
        </div>
</div>


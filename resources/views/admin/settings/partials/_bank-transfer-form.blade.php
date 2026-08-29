<div class="space-y-5">
    <!-- Enable/Disable Toggle -->
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <label for="bank_transfer_enabled" class="font-medium text-gray-900 cursor-pointer">Enable Bank Transfer</label>
                    <p class="text-xs text-gray-500">Allow customers to pay via direct bank transfer</p>
                </div>
            </div>
            <label class="toggle">
                <input class="toggle-input" type="checkbox" name="enabled" id="bank_transfer_enabled" 
                       value="1" {{ $paymentConfig['bank_transfer']['enabled'] ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <!-- Configuration Fields -->
        <div class="grid grid-cols-1 gap-5">
            <div>
                <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Bank Name
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <input type="text" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="bank_name" name="bank_name" 
                           value="{{ $paymentConfig['bank_transfer']['bank_name'] }}" 
                           placeholder="State Bank of India">
                </div>
                <p class="text-xs text-gray-500 mt-1">Name of your bank</p>
            </div>

            <div>
                <label for="account_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Account Name
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <input type="text" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                           id="account_name" name="account_name" 
                           value="{{ $paymentConfig['bank_transfer']['account_name'] }}" 
                           placeholder="InAllCart Pvt Ltd">
                </div>
                <p class="text-xs text-gray-500 mt-1">Account holder name</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Account Number
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                        </div>
                        <input type="text" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                               id="account_number" name="account_number" 
                               value="{{ $paymentConfig['bank_transfer']['account_number'] }}" 
                               placeholder="1234567890">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Bank account number</p>
                </div>

                <div>
                    <label for="ifsc_code" class="block text-sm font-medium text-gray-700 mb-2">
                        IFSC Code
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l3 3-3 3M6 16l-3-3 3-3"/></svg>
                        </div>
                        <input type="text" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                               id="ifsc_code" name="ifsc_code" 
                               value="{{ $paymentConfig['bank_transfer']['ifsc_code'] }}" 
                               placeholder="SBIN0123456">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Indian Financial System Code</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="swift_code" class="block text-sm font-medium text-gray-700 mb-2">
                        SWIFT Code
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <input type="text" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                               id="swift_code" name="swift_code" 
                               value="{{ $paymentConfig['bank_transfer']['swift_code'] }}" 
                               placeholder="SBININBB123">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">For international transfers (optional)</p>
                </div>

                <div>
                    <label for="bank_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Bank Code
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        <input type="text" class="w-full h-11 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                               id="bank_code" name="bank_code" 
                               value="{{ $paymentConfig['bank_transfer']['bank_code'] }}" 
                               placeholder="SBI0123456789">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Additional bank identifier (optional)</p>
                </div>
            </div>

            <div>
                <label for="bank_notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Instructions
                </label>
                <div class="relative">
                    <textarea class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 outline-none transition-all" 
                              id="bank_notes" name="notes" rows="4" 
                              placeholder="Please do not forget to upload the bank transfer receipt upon sending / depositing money to the above-mentioned account. Once the amount deposit is confirmed the order will be processed further. To upload the receipt go to your order details page or screen and find a form to upload the receipt.">{{ $paymentConfig['bank_transfer']['notes'] }}</textarea>
                </div>
                <p class="text-xs text-gray-500 mt-1">Instructions shown to customers when they select bank transfer</p>
            </div>
        </div>
</div>


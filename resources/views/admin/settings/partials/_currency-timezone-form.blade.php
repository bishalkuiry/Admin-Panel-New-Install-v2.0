<div class="space-y-6">
    <!-- Currency Settings -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-semibold text-gray-900">Currency Settings</h3>
                <p class="text-sm text-gray-500">Configure system default currency</p>
            </div>
            <label class="toggle">
                <input type="checkbox" name="multi_currency_enabled" value="1" {{ ($settings['multi_currency_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="space-y-4">
            <!-- Default Currency -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Default Currency</label>
                <select name="default_currency" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                    <option value="INR" {{ ($settings['default_currency'] ?? 'INR') == 'INR' ? 'selected' : '' }}>INR - Indian Rupee (₹)</option>
                    <option value="USD" {{ ($settings['default_currency'] ?? 'INR') == 'USD' ? 'selected' : '' }}>USD - US Dollar ($)</option>
                    <option value="EUR" {{ ($settings['default_currency'] ?? 'INR') == 'EUR' ? 'selected' : '' }}>EUR - Euro (€)</option>
                    <option value="GBP" {{ ($settings['default_currency'] ?? 'INR') == 'GBP' ? 'selected' : '' }}>GBP - British Pound (£)</option>
                    <option value="AUD" {{ ($settings['default_currency'] ?? 'INR') == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar (A$)</option>
                    <option value="CAD" {{ ($settings['default_currency'] ?? 'INR') == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar (C$)</option>
                    <option value="SGD" {{ ($settings['default_currency'] ?? 'INR') == 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar (S$)</option>
                    <option value="AED" {{ ($settings['default_currency'] ?? 'INR') == 'AED' ? 'selected' : '' }}>AED - UAE Dirham (د.إ)</option>
                    <option value="SAR" {{ ($settings['default_currency'] ?? 'INR') == 'SAR' ? 'selected' : '' }}>SAR - Saudi Riyal (﷼)</option>
                    <option value="ZAR" {{ ($settings['default_currency'] ?? 'INR') == 'ZAR' ? 'selected' : '' }}>ZAR - South African Rand (R)</option>
                    <option value="NGN" {{ ($settings['default_currency'] ?? 'INR') == 'NGN' ? 'selected' : '' }}>NGN - Nigerian Naira (₦)</option>
                    <option value="KES" {{ ($settings['default_currency'] ?? 'INR') == 'KES' ? 'selected' : '' }}>KES - Kenyan Shilling (KSh)</option>
                    <option value="BDT" {{ ($settings['default_currency'] ?? 'INR') == 'BDT' ? 'selected' : '' }}>BDT - Bangladeshi Taka (৳)</option>
                    <option value="PKR" {{ ($settings['default_currency'] ?? 'INR') == 'PKR' ? 'selected' : '' }}>PKR - Pakistani Rupee (₨)</option>
                    <option value="LKR" {{ ($settings['default_currency'] ?? 'INR') == 'LKR' ? 'selected' : '' }}>LKR - Sri Lankan Rupee (Rs)</option>
                    <option value="MYR" {{ ($settings['default_currency'] ?? 'INR') == 'MYR' ? 'selected' : '' }}>MYR - Malaysian Ringgit (RM)</option>
                    <option value="IDR" {{ ($settings['default_currency'] ?? 'INR') == 'IDR' ? 'selected' : '' }}>IDR - Indonesian Rupiah (Rp)</option>
                    <option value="PHP" {{ ($settings['default_currency'] ?? 'INR') == 'PHP' ? 'selected' : '' }}>PHP - Philippine Peso (₱)</option>
                    <option value="THB" {{ ($settings['default_currency'] ?? 'INR') == 'THB' ? 'selected' : '' }}>THB - Thai Baht (฿)</option>
                    <option value="VND" {{ ($settings['default_currency'] ?? 'INR') == 'VND' ? 'selected' : '' }}>VND - Vietnamese Dong (₫)</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">All prices will be displayed in this currency</p>
            </div>

            <!-- Currency Symbol Position -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Currency Symbol Position</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="currency_symbol_position" value="left" class="peer sr-only" {{ ($settings['currency_symbol_position'] ?? 'left') == 'left' ? 'checked' : '' }}>
                        <div class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all">
                            <p class="font-medium text-gray-900 text-sm">₹1,234.56</p>
                            <p class="text-xs text-gray-500">Symbol before amount</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="currency_symbol_position" value="right" class="peer sr-only" {{ ($settings['currency_symbol_position'] ?? 'left') == 'right' ? 'checked' : '' }}>
                        <div class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all">
                            <p class="font-medium text-gray-900 text-sm">1,234.56₹</p>
                            <p class="text-xs text-gray-500">Symbol after amount</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Decimal Places -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Decimal Places</label>
                <select name="currency_decimal_places" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                    <option value="0" {{ ($settings['currency_decimal_places'] ?? '0') == '0' ? 'selected' : '' }}>0 (1234)</option>
                    <option value="2" {{ ($settings['currency_decimal_places'] ?? '0') == '2' ? 'selected' : '' }}>2 (1234.56)</option>
                    <option value="3" {{ ($settings['currency_decimal_places'] ?? '0') == '3' ? 'selected' : '' }}>3 (1234.567)</option>
                </select>
            </div>

            <!-- Thousand Separator -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Thousand Separator</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="currency_thousand_separator" value="," class="peer sr-only" {{ ($settings['currency_thousand_separator'] ?? ',') == ',' ? 'checked' : '' }}>
                        <div class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all text-center">
                            <p class="font-medium text-gray-900 text-sm">1,234</p>
                            <p class="text-xs text-gray-500">Comma</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="currency_thousand_separator" value="." class="peer sr-only" {{ ($settings['currency_thousand_separator'] ?? ',') == '.' ? 'checked' : '' }}>
                        <div class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all text-center">
                            <p class="font-medium text-gray-900 text-sm">1.234</p>
                            <p class="text-xs text-gray-500">Period</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="currency_thousand_separator" value=" " class="peer sr-only" {{ ($settings['currency_thousand_separator'] ?? ',') == ' ' ? 'checked' : '' }}>
                        <div class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all text-center">
                            <p class="font-medium text-gray-900 text-sm">1 234</p>
                            <p class="text-xs text-gray-500">Space</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Exchange Rate Settings -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-semibold text-gray-900">Exchange Rate Updates</h3>
                <p class="text-sm text-gray-500">Automatic currency conversion rates</p>
            </div>
            <label class="toggle">
                <input type="checkbox" name="auto_exchange_rate_update" value="1" {{ ($settings['auto_exchange_rate_update'] ?? '1') == '1' ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="space-y-4">
            <!-- Exchange Rate API -->
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-blue-900 text-sm">Using ExchangeRate-API</p>
                        <p class="text-xs text-blue-700 mt-1">Free tier: 1,500 requests/month • Updates daily</p>
                        <p class="text-xs text-blue-600 mt-2">No API key required for basic usage</p>
                    </div>
                </div>
            </div>

            <!-- Update Frequency -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Update Frequency</label>
                <select name="exchange_rate_update_frequency" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                    <option value="daily" {{ ($settings['exchange_rate_update_frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily (Recommended)</option>
                    <option value="hourly" {{ ($settings['exchange_rate_update_frequency'] ?? 'daily') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                    <option value="weekly" {{ ($settings['exchange_rate_update_frequency'] ?? 'daily') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                </select>
            </div>

            <!-- Last Update Info -->
            @if(isset($settings['exchange_rate_last_update']))
            <div class="p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Last Updated</p>
                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($settings['exchange_rate_last_update'])->diffForHumans() }}</p>
                    </div>
                    <button type="button" onclick="updateExchangeRates()" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                        Update Now
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Timezone Settings -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="mb-4">
            <h3 class="font-semibold text-gray-900">Timezone Settings</h3>
            <p class="text-sm text-gray-500">Configure system timezone for dates and times</p>
        </div>

        <div class="space-y-4">
            <!-- Timezone Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">System Timezone</label>
                <select name="system_timezone" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                    <optgroup label="Asia">
                        <option value="Asia/Kolkata" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Kolkata' ? 'selected' : '' }}>India (IST - UTC+5:30)</option>
                        <option value="Asia/Dubai" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Dubai' ? 'selected' : '' }}>Dubai (GST - UTC+4:00)</option>
                        <option value="Asia/Singapore" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Singapore' ? 'selected' : '' }}>Singapore (SGT - UTC+8:00)</option>
                        <option value="Asia/Hong_Kong" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Hong_Kong' ? 'selected' : '' }}>Hong Kong (HKT - UTC+8:00)</option>
                        <option value="Asia/Tokyo" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo (JST - UTC+9:00)</option>
                        <option value="Asia/Shanghai" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Shanghai' ? 'selected' : '' }}>Shanghai (CST - UTC+8:00)</option>
                        <option value="Asia/Bangkok" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Bangkok' ? 'selected' : '' }}>Bangkok (ICT - UTC+7:00)</option>
                        <option value="Asia/Jakarta" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Jakarta' ? 'selected' : '' }}>Jakarta (WIB - UTC+7:00)</option>
                        <option value="Asia/Manila" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Manila' ? 'selected' : '' }}>Manila (PHT - UTC+8:00)</option>
                        <option value="Asia/Karachi" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Karachi' ? 'selected' : '' }}>Karachi (PKT - UTC+5:00)</option>
                        <option value="Asia/Dhaka" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Dhaka' ? 'selected' : '' }}>Dhaka (BST - UTC+6:00)</option>
                        <option value="Asia/Colombo" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Colombo' ? 'selected' : '' }}>Colombo (IST - UTC+5:30)</option>
                        <option value="Asia/Kuala_Lumpur" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Asia/Kuala_Lumpur' ? 'selected' : '' }}>Kuala Lumpur (MYT - UTC+8:00)</option>
                    </optgroup>
                    <optgroup label="Europe">
                        <option value="Europe/London" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Europe/London' ? 'selected' : '' }}>London (GMT - UTC+0:00)</option>
                        <option value="Europe/Paris" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Europe/Paris' ? 'selected' : '' }}>Paris (CET - UTC+1:00)</option>
                        <option value="Europe/Berlin" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Europe/Berlin' ? 'selected' : '' }}>Berlin (CET - UTC+1:00)</option>
                        <option value="Europe/Rome" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Europe/Rome' ? 'selected' : '' }}>Rome (CET - UTC+1:00)</option>
                        <option value="Europe/Madrid" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Europe/Madrid' ? 'selected' : '' }}>Madrid (CET - UTC+1:00)</option>
                    </optgroup>
                    <optgroup label="America">
                        <option value="America/New_York" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'America/New_York' ? 'selected' : '' }}>New York (EST - UTC-5:00)</option>
                        <option value="America/Chicago" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'America/Chicago' ? 'selected' : '' }}>Chicago (CST - UTC-6:00)</option>
                        <option value="America/Los_Angeles" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'America/Los_Angeles' ? 'selected' : '' }}>Los Angeles (PST - UTC-8:00)</option>
                        <option value="America/Toronto" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'America/Toronto' ? 'selected' : '' }}>Toronto (EST - UTC-5:00)</option>
                        <option value="America/Sao_Paulo" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'America/Sao_Paulo' ? 'selected' : '' }}>São Paulo (BRT - UTC-3:00)</option>
                    </optgroup>
                    <optgroup label="Africa">
                        <option value="Africa/Cairo" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Africa/Cairo' ? 'selected' : '' }}>Cairo (EET - UTC+2:00)</option>
                        <option value="Africa/Lagos" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Africa/Lagos' ? 'selected' : '' }}>Lagos (WAT - UTC+1:00)</option>
                        <option value="Africa/Nairobi" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Africa/Nairobi' ? 'selected' : '' }}>Nairobi (EAT - UTC+3:00)</option>
                        <option value="Africa/Johannesburg" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Africa/Johannesburg' ? 'selected' : '' }}>Johannesburg (SAST - UTC+2:00)</option>
                    </optgroup>
                    <optgroup label="Australia">
                        <option value="Australia/Sydney" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Australia/Sydney' ? 'selected' : '' }}>Sydney (AEDT - UTC+11:00)</option>
                        <option value="Australia/Melbourne" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Australia/Melbourne' ? 'selected' : '' }}>Melbourne (AEDT - UTC+11:00)</option>
                        <option value="Australia/Perth" {{ ($settings['system_timezone'] ?? 'Asia/Kolkata') == 'Australia/Perth' ? 'selected' : '' }}>Perth (AWST - UTC+8:00)</option>
                    </optgroup>
                </select>
                <p class="text-xs text-gray-500 mt-1">All timestamps will be displayed in this timezone</p>
            </div>

            <!-- Current Time Preview -->
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Current System Time</p>
                        <p class="text-xs text-gray-500 mt-1" id="current-time">{{ now()->format('l, F j, Y - g:i:s A') }}</p>
                    </div>
                    <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Date Format -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date Format</label>
                <select name="date_format" class="w-full h-10 px-3 text-sm border border-gray-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none">
                    <option value="d/m/Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY ({{ now()->format('d/m/Y') }})</option>
                    <option value="m/d/Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY ({{ now()->format('m/d/Y') }})</option>
                    <option value="Y-m-d" {{ ($settings['date_format'] ?? 'd/m/Y') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD ({{ now()->format('Y-m-d') }})</option>
                    <option value="d M, Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'd M, Y' ? 'selected' : '' }}>DD Mon, YYYY ({{ now()->format('d M, Y') }})</option>
                    <option value="M d, Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'M d, Y' ? 'selected' : '' }}>Mon DD, YYYY ({{ now()->format('M d, Y') }})</option>
                </select>
            </div>

            <!-- Time Format -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Time Format</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="time_format" value="12" class="peer sr-only" {{ ($settings['time_format'] ?? '12') == '12' ? 'checked' : '' }}>
                        <div class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all text-center">
                            <p class="font-medium text-gray-900 text-sm">{{ now()->format('g:i A') }}</p>
                            <p class="text-xs text-gray-500">12-hour format</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="time_format" value="24" class="peer sr-only" {{ ($settings['time_format'] ?? '12') == '24' ? 'checked' : '' }}>
                        <div class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300 transition-all text-center">
                            <p class="font-medium text-gray-900 text-sm">{{ now()->format('H:i') }}</p>
                            <p class="text-xs text-gray-500">24-hour format</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateExchangeRates() {
    if (confirm('Update exchange rates now? This will fetch the latest rates from the API.')) {
        fetch('{{ route("admin.settings.update-exchange-rates") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Exchange rates updated successfully!');
                location.reload();
            } else {
                alert('Failed to update exchange rates: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error updating exchange rates: ' + error.message);
        });
    }
}

// Update current time every second
setInterval(() => {
    const now = new Date();
    document.getElementById('current-time').textContent = now.toLocaleString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}, 1000);
</script>

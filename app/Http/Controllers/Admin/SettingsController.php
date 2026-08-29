<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Tag;
use App\Models\Unit;
use App\Services\BroadcastService;
use App\Services\CurrencyExchangeService;
use App\Services\FirebaseSetupService;
use App\Services\CloudflareService;
use App\Services\StorageService;
use App\Services\Sms\SmsGatewayService;
use App\Services\DemoResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function __construct(
        private BroadcastService $broadcastService,
        private CurrencyExchangeService $currencyService,
        private FirebaseSetupService $firebaseSetup,
        private CloudflareService $cloudflareService,
        private StorageService $storage,
    ) {}

    /**
     * Show settings page
     */
    public function index()
    {
        $groups = [
            'general' => Setting::query()->where('group', 'general')->get(),
            'broadcast' => Setting::query()->where('group', 'broadcast')->get(),
            'cache' => Setting::query()->where('group', 'cache')->get(),
            'queue' => Setting::query()->where('group', 'queue')->get(),
            'mobile_app' => Setting::query()->where('group', 'mobile_app')->get(),
        ];

        $driverInfo = $this->broadcastService->getDriverInfo();

        return view('admin.settings.index', compact('groups', 'driverInfo'));
    }

    /**
     * Show payment settings page
     */
    public function paymentSettings()
    {
        $paymentConfig = config('payment');
        
        return view('admin.settings.payment', compact('paymentConfig'));
    }

    /**
     * Update payment settings
     */
    public function updatePaymentSettings(Request $request)
    {
        $request->validate([
            'gateway' => 'required|string',
        ]);

        $gateway = $request->input('gateway');
        $settings = $request->except(['_token', '_method', 'gateway']);

        $this->updateEnvFile($gateway, $settings);

        Cache::flush();

        try {
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            // Ignore in environments where Artisan is restricted
        }

        return back()->with('success', ucwords(str_replace(['-', '_'], ' ', $gateway)) . ' settings updated successfully');
    }

    /**
     * Update environment file
     *
     * All keys and values are sanitized to prevent newline injection,
     * .env syntax corruption, or arbitrary key spoofing.
     */
    private function updateEnvFile(string $gateway, array $settings): void
    {
        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            return;
        }

        $envContent = file_get_contents($envFile);

        // Sanitize gateway prefix — only uppercase letters, digits, and underscores.
        $gatewayUpper = $this->sanitizeEnvKey(strtoupper($gateway));
        if ($gatewayUpper === '') {
            return;
        }

        foreach ($settings as $key => $value) {
            // Reject non-string keys outright.
            if (!is_string($key) || $key === '') {
                continue;
            }

            $envKey = $this->sanitizeEnvKey($gatewayUpper . '_' . strtoupper($key));
            if ($envKey === '') {
                continue;
            }

            $escapedValue = $this->escapeEnvValue($value);

            // preg_quote prevents the key from being interpreted as a regex pattern.
            $pattern     = '/^' . preg_quote($envKey, '/') . '=.*/m';
            $replacement = "{$envKey}={$escapedValue}";

            if (preg_match($pattern, $envContent)) {
                // Use preg_replace_callback so preg replacement backreferences
                // in the value (e.g. $1, \0) cannot be interpreted.
                $envContent = preg_replace_callback(
                    $pattern,
                    static fn () => $replacement,
                    $envContent,
                    1
                );
            } else {
                // Ensure a trailing newline separator.
                if ($envContent !== '' && !str_ends_with($envContent, "\n")) {
                    $envContent .= "\n";
                }
                $envContent .= $replacement . "\n";
            }
        }

        file_put_contents($envFile, $envContent, LOCK_EX);
    }

    /**
     * Sanitize an environment variable key.
     *
     * Only [A-Z0-9_] are permitted, and it cannot start with a digit.
     * Returns an empty string for invalid keys.
     */
    private function sanitizeEnvKey(string $key): string
    {
        $key = preg_replace('/[^A-Z0-9_]/', '', strtoupper($key)) ?? '';
        if ($key === '' || preg_match('/^[0-9]/', $key)) {
            return '';
        }
        return $key;
    }

    /**
     * Escape an environment variable value so it survives a round-trip
     * through the .env parser without allowing newline injection or
     * breaking out of its key.
     */
    private function escapeEnvValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return '""';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        // Anything else must be stringifiable.
        $value = (string) $value;

        // Strip control characters that would corrupt the .env file
        // (newline/carriage return injection is the primary risk).
        $value = preg_replace('/[\r\n\x00\x1a]/', '', $value) ?? '';

        // If the value is empty, contains whitespace, quotes, #, = or backslash,
        // wrap it in double quotes and escape embedded backslashes/quotes.
        if ($value === '' || preg_match('/[\s"\'#=\\\\]/', $value)) {
            $escaped = str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value);
            return '"' . $escaped . '"';
        }

        return $value;
    }

    /**
     * Show Authentication settings page
     */
    public function indexAuth()
    {
        $settings = Setting::getAll();
        return view('admin.settings.auth', compact('settings'));
    }

    /**
     * Update Authentication settings
     */
    public function updateAuth(Request $request)
    {
        // Validation rules based on enabled features
        $rules = [
            'auth_manual_login_enabled' => 'nullable|in:0,1',
            'auth_phone_otp_enabled' => 'nullable|in:0,1',
            'auth_email_otp_enabled' => 'nullable|in:0,1',
            'auth_otp_length' => 'required|in:4,6',
            'auth_otp_expiry_minutes' => 'required|integer|min:1',
            'auth_otp_max_attempts' => 'required|integer|min:1',
            'auth_otp_resend_cooldown' => 'required|integer|min:30',
            'auth_sms_gateway' => 'nullable|string',
            'auth_account_deletion_grace_period' => 'required|integer|min:0|max:365',
        ];

        // Add validation for SMS gateways if selected
        if ($request->auth_sms_gateway === 'twilio') {
            $rules['auth_twilio_sid'] = 'required|string';
            $rules['auth_twilio_token'] = 'required|string';
            $rules['auth_twilio_verify_sid'] = 'required|string';
        } elseif ($request->auth_sms_gateway === 'msg91') {
            $rules['auth_msg91_auth_key'] = 'required|string';
            $rules['auth_msg91_template_id'] = 'required|string';
        } elseif ($request->auth_sms_gateway === '2factor') {
            $rules['auth_2factor_api_key'] = 'required|string';
        } elseif ($request->auth_sms_gateway === 'nexmo') {
            $rules['auth_nexmo_api_key'] = 'required|string';
            $rules['auth_nexmo_api_secret'] = 'required|string';
        }

        $validated = $request->validate($rules);

        // Update settings in database
        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        if ($request->has('auth_manual_login_enabled')) {
            Setting::set('auth_manual_login_enabled', $request->input('auth_manual_login_enabled') == '1' ? '1' : '0');
        } else {
            Setting::set('auth_manual_login_enabled', '0');
        }
        if (!$request->has('auth_phone_otp_enabled')) Setting::set('auth_phone_otp_enabled', '0');
        if (!$request->has('auth_email_otp_enabled')) Setting::set('auth_email_otp_enabled', '0');

        Setting::set('auth_user_app_forgot_password_enabled', $request->has('auth_user_app_forgot_password_enabled') ? '1' : '0');
        Setting::set('auth_store_app_forgot_password_enabled', $request->has('auth_store_app_forgot_password_enabled') ? '1' : '0');
        Setting::set('auth_driver_app_forgot_password_enabled', $request->has('auth_driver_app_forgot_password_enabled') ? '1' : '0');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Authentication settings updated successfully',
            ]);
        }

        return back()->with('success', 'Authentication settings updated successfully.');
    }

    /**
     * Test SMS Gateway Configuration
     */
    public function testAuthSms(Request $request, SmsGatewayService $smsService)
    {
        $request->validate([
            'phone' => 'required|string',
            'gateway' => 'required|string',
        ]);

        $gatewayKey = $request->input('gateway');
        $provider = $smsService->getProvider($gatewayKey);

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid gateway selected.',
            ], 400);
        }

        if (!$provider->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($gatewayKey) . ' is not properly configured. Please enter and save valid API credentials first.',
            ], 400);
        }

        // Generate a random 6 digit OTP for the test
        $testOtp = (string) rand(100000, 999999);

        // Real API Call
        try {
            $result = $provider->sendOtp($request->input('phone'), $testOtp);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Success (Live): SMS sent via ' . ucfirst($gatewayKey),
                    'details' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Provider Error: ' . ($result['message'] ?? 'Unknown error'),
                'details' => $result
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show mobile app settings
     */
    public function mobileApp()
    {
        $settings = Setting::where('group', 'mobile_app')->pluck('value', 'key')->toArray();
        
        // Also load general settings for the General tab
        $generalSettings = Setting::where('group', 'general')->pluck('value', 'key')->toArray();
        $settings = array_merge($settings, $generalSettings);
        
        $onboardingScreens = json_decode($settings['onboarding_screens'] ?? '[]', true);
        
        // Load payment configuration for partials
        $paymentConfig = config('payment');

        return view('admin.settings.mobile-app', compact('settings', 'onboardingScreens', 'paymentConfig'));
    }

    /**
     * Update mobile app settings
     */
    public function updateMobileApp(Request $request)
    {
        try {
            // Build dynamic validation rules for payment gateways
            $validationRules = [
                'onboarding_enabled' => 'nullable|boolean',
                'map_provider' => 'nullable|in:google,osm',
                'google_maps_api_key' => 'nullable|string|max:255',
                'push_enabled' => 'nullable|boolean',
                'push_order_updates' => 'nullable|boolean',
                'push_promotions' => 'nullable|boolean',
                'push_new_products' => 'nullable|boolean',
                'firebase_server_key' => 'nullable|string',
                'firebase_project_id' => 'nullable|string',
                'firebase_service_account' => 'nullable|string',
                'firebase_api_key' => 'nullable|string',
                'firebase_auth_domain' => 'nullable|string',
                'firebase_storage_bucket' => 'nullable|string',
                'firebase_messaging_sender_id' => 'nullable|string',
                'firebase_app_id' => 'nullable|string',
                'cloudflare_email' => 'nullable|email',
                'cloudflare_api_key' => 'nullable|string',
                // Currency settings
                'multi_currency_enabled' => 'nullable|boolean',
                'default_currency' => 'nullable|string|max:3',
                'currency_symbol_position' => 'nullable|in:left,right',
                'currency_decimal_places' => 'nullable|integer|min:0|max:3',
                'currency_thousand_separator' => 'nullable|string|max:1',
                'auto_exchange_rate_update' => 'nullable|boolean',
                'exchange_rate_update_frequency' => 'nullable|in:hourly,daily,weekly',
                // Timezone settings
                'system_timezone' => 'nullable|timezone',
                'date_format' => 'nullable|string|max:20',
                'time_format' => 'nullable|in:12,24',
                // General settings
                'vendor_mode' => 'nullable|in:single,multi',
                'global_delivery_charge' => 'nullable|numeric|min:0',
                'single_store_cart' => 'nullable|boolean',
                'order_confirmation_by' => 'nullable|in:store,driver',
                'global_zone_enabled' => 'nullable|boolean',
                'default_commission_percent' => 'nullable|numeric|min:0|max:100',
            ];

            // Add validation rules for all payment gateways
            $paymentGateways = ['razorpay', 'paystack', 'stripe', 'paypal', 'flutterwave', 'paytm', 'phonepe', 'midtrans', 'myfatoorah', 'instamojo', 'cod', 'bank_transfer'];
            foreach ($paymentGateways as $gateway) {
                $validationRules[$gateway . '_enabled'] = 'nullable|boolean';
                $validationRules[$gateway . '_*'] = 'nullable'; // Allow any additional fields for each gateway
            }

            $request->validate($validationRules);

            // Check if this is a payment gateway-only update (AJAX toggle)
            $isPaymentGatewayUpdate = $request->ajax() && $this->isPaymentGatewayRequest($request);

            if (!$isPaymentGatewayUpdate) {
                // Only update settings that are actually present in the request
                $settingsToUpdate = [];
                
                // Onboarding settings
                if ($request->has('onboarding_enabled')) {
                    $settingsToUpdate['onboarding_enabled'] = $request->input('onboarding_enabled') === '1' ? '1' : '0';
                }
                
                // Map settings
                if ($request->has('map_provider')) {
                    $settingsToUpdate['map_provider'] = $request->input('map_provider', 'osm');
                }
                if ($request->has('google_maps_api_key')) {
                    $settingsToUpdate['google_maps_api_key'] = $request->input('google_maps_api_key');
                }
                
                // Push notification settings
                if ($request->has('push_enabled')) {
                    $settingsToUpdate['push_enabled'] = $request->input('push_enabled') === '1' ? '1' : '0';
                }
                if ($request->has('push_order_updates')) {
                    $settingsToUpdate['push_order_updates'] = $request->input('push_order_updates') === '1' ? '1' : '0';
                }
                if ($request->has('push_promotions')) {
                    $settingsToUpdate['push_promotions'] = $request->input('push_promotions') === '1' ? '1' : '0';
                }
                if ($request->has('push_new_products')) {
                    $settingsToUpdate['push_new_products'] = $request->input('push_new_products') === '1' ? '1' : '0';
                }
                
                // Firebase settings
                if ($request->has('firebase_server_key')) {
                    $settingsToUpdate['firebase_server_key'] = $request->input('firebase_server_key');
                }
                if ($request->has('firebase_project_id')) {
                    $settingsToUpdate['firebase_project_id'] = $request->input('firebase_project_id');
                }
                if ($request->has('firebase_service_account')) {
                    $settingsToUpdate['firebase_service_account'] = $request->input('firebase_service_account');
                }
                // Firebase Web SDK config (used by admin live chat)
                foreach (['firebase_api_key', 'firebase_auth_domain', 'firebase_database_url', 'firebase_storage_bucket', 'firebase_messaging_sender_id', 'firebase_app_id'] as $webKey) {
                    if ($request->has($webKey)) {
                        $settingsToUpdate[$webKey] = $request->input($webKey);
                    }
                }
                
                // Cloudflare settings
                if ($request->has('cloudflare_email')) {
                    $settingsToUpdate['cloudflare_email'] = $request->input('cloudflare_email');
                }
                if ($request->has('cloudflare_api_key')) {
                    $settingsToUpdate['cloudflare_api_key'] = $request->input('cloudflare_api_key');
                }
                
                // Currency settings
                if ($request->has('multi_currency_enabled')) {
                    $settingsToUpdate['multi_currency_enabled'] = $request->input('multi_currency_enabled') === '1' ? '1' : '0';
                }
                if ($request->has('default_currency')) {
                    $settingsToUpdate['default_currency'] = $request->input('default_currency', 'INR');
                }
                if ($request->has('currency_symbol_position')) {
                    $settingsToUpdate['currency_symbol_position'] = $request->input('currency_symbol_position', 'left');
                }
                if ($request->has('currency_decimal_places')) {
                    $settingsToUpdate['currency_decimal_places'] = $request->input('currency_decimal_places', '2');
                }
                if ($request->has('currency_thousand_separator')) {
                    $settingsToUpdate['currency_thousand_separator'] = $request->input('currency_thousand_separator', ',');
                }
                if ($request->has('auto_exchange_rate_update')) {
                    $settingsToUpdate['auto_exchange_rate_update'] = $request->input('auto_exchange_rate_update') === '1' ? '1' : '0';
                }
                if ($request->has('exchange_rate_update_frequency')) {
                    $settingsToUpdate['exchange_rate_update_frequency'] = $request->input('exchange_rate_update_frequency', 'daily');
                }
                
                // Timezone settings
                if ($request->has('system_timezone')) {
                    $settingsToUpdate['system_timezone'] = $request->input('system_timezone', 'Asia/Kolkata');
                    config(['app.timezone' => $settingsToUpdate['system_timezone']]);
                    $this->updateEnvVariable('APP_TIMEZONE', $settingsToUpdate['system_timezone']);
                }
                if ($request->has('date_format')) {
                    $settingsToUpdate['date_format'] = $request->input('date_format', 'd/m/Y');
                }
                if ($request->has('time_format')) {
                    $settingsToUpdate['time_format'] = $request->input('time_format', '12');
                }
                
                // General settings
                if ($request->has('vendor_mode')) {
                    $settingsToUpdate['vendor_mode'] = $request->input('vendor_mode', 'single');
                }
                if ($request->has('global_delivery_charge')) {
                    $settingsToUpdate['global_delivery_charge'] = $request->input('global_delivery_charge', '5.99');
                }
                if ($request->has('default_commission_percent')) {
                    $settingsToUpdate['default_commission_percent'] = $request->input('default_commission_percent', '10');
                }
                if ($request->has('single_store_cart')) {
                    $settingsToUpdate['single_store_cart'] = $request->input('single_store_cart') === '1' ? '1' : '0';
                }
                if ($request->has('order_confirmation_by')) {
                    $confirmByValue = $request->input('order_confirmation_by', 'store');
                    // Keep both keys in sync
                    $settingsToUpdate['order_confirmation_by'] = $confirmByValue;
                    $settingsToUpdate['order_confirm_by']      = $confirmByValue;
                }

                if ($request->has('auth_account_deletion_grace_period')) {
                    $settingsToUpdate['auth_account_deletion_grace_period'] = $request->input('auth_account_deletion_grace_period', '7');
                }

                if ($request->has('global_zone_enabled')) {
                    // Check logic: input is present means checkbox is checked (value '1')
                    // But if it was unchecked, it might not be present at all in standard form sub, 
                    // EXCEPT the JS might be sending it. 
                    // Let's rely on standard '1' or '0' logic.
                    // If checkbox is unchecked, browser doesn't send it. 
                    // But our update logic only updates if $request->has().
                    // This means we can't disable it?? 
                    // Wait, the validation handles nullable.
                    // Let's enforce it:
                    $settingsToUpdate['global_zone_enabled'] = $request->input('global_zone_enabled') ? '1' : '0';
                }
                
                // Save only the settings that were sent
                foreach ($settingsToUpdate as $key => $value) {
                    $group = 'mobile_app';
                    if (in_array($key, ['vendor_mode', 'global_delivery_charge', 'single_store_cart', 'order_confirmation_by', 'global_zone_enabled', 'default_commission_percent'])) {
                        $group = 'general';
                    }
                    
                    Setting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value, 'group' => $group]
                    );
                }
            }

            // Handle payment gateway settings - only update gateways that have data in the request
            $gatewaysToUpdate = $this->getGatewaysFromRequest($request);
            
            foreach ($gatewaysToUpdate as $gateway) {
                $this->updatePaymentGateway($gateway, $request);
            }

            Cache::flush();
            $this->cloudflareServicePurgeHelper();

            try {
                Artisan::call('config:clear');
            } catch (\Exception $e) {
                // Ignore
            }

            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                // Add debug info
                $debugInfo = [];
                if ($request->has('multi_currency_enabled')) {
                    $debugInfo['multi_currency_received'] = $request->input('multi_currency_enabled');
                    $debugInfo['multi_currency_converted'] = $request->input('multi_currency_enabled') === '1' ? 'true' : 'false';
                    $debugInfo['multi_currency_in_db'] = Setting::where('key', 'multi_currency_enabled')->value('value');
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Settings updated successfully',
                    'debug' => $debugInfo
                ]);
            }

            return back()->with('success', 'Mobile app settings updated successfully');
        } catch (\Exception $e) {
            \Log::error('Mobile app settings update error: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update settings: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Check if this is a payment gateway-only request
     */
    private function isPaymentGatewayRequest(Request $request): bool
    {
        // If it has currency, mobile app, or onboarding specific fields, it's NOT a payment gateway request
        if ($request->has('multi_currency_enabled') || 
            $request->has('default_currency') || 
            $request->has('system_timezone') ||
            $request->has('date_format') ||
            $request->has('time_format') ||
            $request->has('currency_symbol_position') ||
            $request->has('currency_decimal_places') ||
            $request->has('currency_thousand_separator') ||
            $request->has('auto_exchange_rate_update') ||
            $request->has('exchange_rate_update_frequency') ||
            $request->has('map_provider') || 
            $request->has('push_enabled') ||
            $request->has('firebase_server_key') ||
            $request->has('firebase_project_id') ||
            $request->has('firebase_service_account') ||
            $request->has('onboarding_enabled')) {
            return false;
        }
        
        // Check if it has payment gateway specific fields
        $paymentGateways = ['razorpay', 'paystack', 'stripe', 'paypal', 'flutterwave', 'paytm', 'phonepe', 'midtrans', 'myfatoorah', 'instamojo', 'cod', 'bank_transfer'];
        
        foreach ($request->all() as $key => $value) {
            if ($key === '_token' || $key === '_method') {
                continue;
            }
            
            foreach ($paymentGateways as $gateway) {
                if (str_starts_with($key, $gateway . '_')) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Get list of gateways that have data in the request
     */
    private function getGatewaysFromRequest(Request $request): array
    {
        $allGateways = ['razorpay', 'paystack', 'stripe', 'paypal', 'flutterwave', 'paytm', 'phonepe', 'midtrans', 'myfatoorah', 'instamojo', 'cod', 'bank_transfer'];
        $gatewaysInRequest = [];
        
        foreach ($allGateways as $gateway) {
            $prefix = $gateway . '_';
            foreach ($request->all() as $key => $value) {
                if (str_starts_with($key, $prefix)) {
                    $gatewaysInRequest[] = $gateway;
                    break;
                }
            }
        }
        
        return array_unique($gatewaysInRequest);
    }

    /**
     * Update payment gateway settings in .env file
     */
    private function updatePaymentGateway(string $gateway, Request $request): void
    {
        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            return;
        }
        
        // Use file locking to prevent race conditions
        $lockFile = base_path('.env.lock');
        $lock = fopen($lockFile, 'w');
        
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return;
        }
        
        try {
            $envContent = file_get_contents($envFile);
            
            // Get all fields for this gateway from request
            $prefix = $gateway . '_';
            $fields = [];
            
            foreach ($request->all() as $key => $value) {
                if (str_starts_with($key, $prefix)) {
                    $fields[$key] = $value;
                }
            }

            // Always handle the enabled field - if not present in request, it means checkbox is unchecked
            $enabledKey = $gateway . '_enabled';
            if (!isset($fields[$enabledKey])) {
                $fields[$enabledKey] = '0';
            }

            // Update .env file
            foreach ($fields as $key => $value) {
                if (!is_string($key) || $key === '') {
                    continue;
                }

                $envKey = $this->sanitizeEnvKey(strtoupper($key));
                if ($envKey === '') {
                    continue;
                }

                // Normalize booleans before escaping.
                if (is_bool($value) || $value === true || $value === false) {
                    $envValue = $value ? 'true' : 'false';
                } elseif ($value === '1' || $value === 1 || $value === 'true') {
                    $envValue = 'true';
                } elseif ($value === '0' || $value === 0 || $value === 'false') {
                    $envValue = 'false';
                } else {
                    $envValue = $this->escapeEnvValue($value);
                }

                $pattern     = '/^' . preg_quote($envKey, '/') . '=.*/m';
                $replacement = "{$envKey}={$envValue}";

                if (preg_match($pattern, $envContent)) {
                    $envContent = preg_replace_callback(
                        $pattern,
                        static fn () => $replacement,
                        $envContent,
                        1
                    );
                } else {
                    if ($envContent !== '' && !str_ends_with($envContent, "\n")) {
                        $envContent .= "\n";
                    }
                    $envContent .= $replacement . "\n";
                }
            }

            file_put_contents($envFile, $envContent);
        } finally {
            // Always release the lock
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * Manage onboarding screens
     */
    public function onboardingScreens()
    {
        $screensJson = Setting::where('key', 'onboarding_screens')->value('value');
        $screens = json_decode($screensJson ?? '[]', true);
        $onboardingEnabled = Setting::where('key', 'onboarding_enabled')->value('value') ?? '1';

        return view('admin.settings.onboarding', compact('screens', 'onboardingEnabled'));
    }

    /**
     * Save onboarding screens
     */
    public function saveOnboardingScreens(Request $request)
    {
        $request->validate([
            'screens' => 'array',
            'screens.*.title' => 'required|string|max:255',
            'screens.*.subtitle' => 'required|string|max:500',
            'screens.*.image_url' => 'required|string',
            'screens.*.order' => 'required|integer|min:1',
        ]);

        $screens = collect($request->input('screens', []))
            ->map(function ($screen, $index) {
                $imageUrl = $screen['image_url'];
                
                // Handle base64 image upload
                if (str_starts_with($imageUrl, 'data:image/')) {
                    $imageUrl = $this->saveBase64Image($imageUrl, 'onboarding');
                }
                
                return [
                    'id' => $index + 1,
                    'title' => $screen['title'],
                    'subtitle' => $screen['subtitle'],
                    'image_url' => $imageUrl,
                    'order' => (int) $screen['order'],
                ];
            })
            ->sortBy('order')
            ->values()
            ->toArray();

        Setting::updateOrCreate(
            ['key' => 'onboarding_screens'],
            ['value' => json_encode($screens), 'group' => 'mobile_app']
        );

        Cache::flush();

        return back()->with('success', 'Onboarding screens saved successfully');
    }

    /**
     * Save base64 image to storage
     */
    private function saveBase64Image(string $base64Image, string $folder): string
    {
        preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches);
        $extension = $matches[1] ?? 'png';
        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64Image));
        $filename = $folder . '/' . uniqid() . '_' . time() . '.' . $extension;

        // Save to active storage disk (local public or S3)
        $this->storage->put($filename, $imageData);

        // Return relative path (no /storage/ prefix) — URL generation is done by storage_url()
        return $filename;
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $settings = $request->except(['_token', '_method']);

        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
            }
        }

        // Clear all caches
        Cache::flush();
        $this->broadcastService->clearConfigCache();

        return back()->with('success', 'Settings updated successfully');
    }

    /**
     * Test broadcast connection
     */
    public function testBroadcast(Request $request)
    {
        $driver = $request->input('driver', 'pusher');
        $result = $this->broadcastService->testConnection($driver);

        return response()->json($result);
    }

    /**
     * Send test notification
     */
    public function sendTestNotification()
    {
        $this->broadcastService->broadcast('admin', 'test', [
            'title' => 'Test Notification',
            'message' => 'Broadcasting is working! Driver: ' . $this->broadcastService->getDriverInfo()['current_driver'],
            'time' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent',
            'driver' => $this->broadcastService->getDriverInfo()['current_driver'],
        ]);
    }

    /**
     * Clear all caches
     */
    public function clearCache()
    {
        Cache::flush();
        
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
        } catch (\Exception $e) {
            // Ignore if artisan not available
        }

        // Clear PHP OPcache so updated PHP files take effect immediately
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Also Purge Cloudflare
        try {
            $this->cloudflareServicePurgeHelper();
            $message = 'All server caches cleared & Cloudflare edge purged';
        } catch (\Exception $e) {
            $message = 'Server cache cleared (Cloudflare purge skipped: ' . $e->getMessage() . ')';
        }

        return back()->with('success', $message);
    }

    /**
     * Run storage:link — creates the public/storage symlink.
     * Uses --force to replace an existing real directory with a proper symlink.
     */
    public function storageLink(): \Illuminate\Http\JsonResponse
    {
        $linkPath = public_path('storage');

        // Already a symlink — nothing to do
        if (is_link($linkPath)) {
            return response()->json([
                'success' => true,
                'status'  => 'exists',
                'message' => 'Storage symlink already exists and is active.',
            ]);
        }

        try {
            Artisan::call('storage:link', ['--force' => true]);
            $output = trim(Artisan::output());

            return response()->json([
                'success' => true,
                'status'  => 'linked',
                'message' => 'Storage linked successfully.' . ($output ? ' ' . $output : ''),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'Failed to create storage link: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system status
     */
    public function systemStatus()
    {
        $status = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'broadcast' => $this->broadcastService->getDriverInfo(),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'session_driver' => config('session.driver'),
            'redis_available' => $this->isRedisAvailable(),
            'pusher_configured' => $this->broadcastService->getDriverInfo()['pusher_configured'],
            'disk_free' => disk_free_space('/'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];

        return response()->json($status);
    }

    private function isRedisAvailable(): bool
    {
        try {
            \Illuminate\Support\Facades\Redis::ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Test S3 connection with provided credentials
     */
    public function testS3(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $config = [
                'driver'                  => 's3',
                'key'                     => $request->input('aws_access_key_id', config('filesystems.disks.s3.key')),
                'secret'                  => $request->input('aws_secret_access_key', config('filesystems.disks.s3.secret')),
                'region'                  => $request->input('aws_default_region', config('filesystems.disks.s3.region', 'us-east-1')),
                'bucket'                  => $request->input('aws_bucket', config('filesystems.disks.s3.bucket')),
                'url'                     => $request->input('aws_url', config('filesystems.disks.s3.url')) ?: null,
                'endpoint'                => $request->input('aws_endpoint', config('filesystems.disks.s3.endpoint')) ?: null,
                'use_path_style_endpoint' => $request->has('aws_use_path_style_endpoint'),
                'throw'                   => true,
            ];

            if (empty($config['key']) || empty($config['secret']) || empty($config['bucket'])) {
                return response()->json(['success' => false, 'message' => 'Access Key, Secret, and Bucket are required.']);
            }

            // Dynamically register a temporary disk and test it
            config(['filesystems.disks.s3_test' => $config]);
            $disk = \Illuminate\Support\Facades\Storage::disk('s3_test');
            $testFile = '_s3_connection_test_' . time() . '.txt';
            $disk->put($testFile, 'ok');
            $disk->delete($testFile);

            return response()->json(['success' => true, 'message' => 'S3 connection successful! Bucket is accessible.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Sync app name to .env so config('app.name') always reflects the current brand.
     * The value is passed unquoted — updateEnvVariable() handles escaping.
     */
    private function syncAppNameToEnv(string $appName): void
    {
        $this->updateEnvVariable('APP_NAME', $appName);
    }

    /**
     * Update a single environment variable safely.
     *
     * Both the key and value are sanitized to prevent newline injection,
     * quote/escape attacks, and regex metacharacter abuse.
     */
    private function updateEnvVariable(string $key, string $value): void
    {
        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            return;
        }

        $envKey = $this->sanitizeEnvKey($key);
        if ($envKey === '') {
            return;
        }

        $envValue = $this->escapeEnvValue($value);

        $envContent  = file_get_contents($envFile);
        $pattern     = '/^' . preg_quote($envKey, '/') . '=.*/m';
        $replacement = "{$envKey}={$envValue}";

        if (preg_match($pattern, $envContent)) {
            $envContent = preg_replace_callback(
                $pattern,
                static fn () => $replacement,
                $envContent,
                1
            );
        } else {
            if ($envContent !== '' && !str_ends_with($envContent, "\n")) {
                $envContent .= "\n";
            }
            $envContent .= $replacement . "\n";
        }

        file_put_contents($envFile, $envContent, LOCK_EX);
    }

    /**
     * Update exchange rates manually
     */
    public function updateExchangeRates(Request $request)
    {
        try {
            $baseCurrency = Setting::get('default_currency', 'INR');
            $success = $this->currencyService->forceUpdate($baseCurrency);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exchange rates updated successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch exchange rates from API'
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Business Settings page (Identity, Branding, Support, Maintenance)
     */
    public function appSettings()
    {
        $groups = ['app_settings', 'general', 'mobile_app'];
        $settings = Setting::whereIn('group', $groups)->pluck('value', 'key')->toArray();
        return view('admin.settings.app-settings', compact('settings'));
    }

    /**
     * Update Business Settings (delegates to updateGeneralSettings logic)
     */
    public function updateAppSettings(Request $request)
    {
        return $this->updateGeneralSettings($request);
    }

    /**
     * Show General Settings page (Identity, Branding, Support, Maintenance, Operations)
     */
    public function generalSettings()
    {
        $groups = ['app_settings', 'general', 'mobile_app'];
        $settings = Setting::whereIn('group', $groups)->pluck('value', 'key')->toArray();
        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Update General Settings
     */
    public function updateGeneralSettings(Request $request)
    {
        $request->validate([
            'app_name'            => 'nullable|string|max:100',
            'app_copyright'       => 'nullable|string|max:255',
            'company_address'     => 'nullable|string|max:500',
            'support_phone'       => 'nullable|string|max:30',
            'support_email'       => 'nullable|email|max:100',
            'support_whatsapp'    => 'nullable|string|max:30',
            'support_website'     => 'nullable|url|max:255',
            'maintenance_mode'    => 'nullable|in:0,1',
            'maintenance_title'   => 'nullable|string|max:150',
            'maintenance_message' => 'nullable|string|max:500',
            'admin_sidebar_logo'  => 'nullable|image|max:2048',
            'admin_login_logo'    => 'nullable|image|max:2048',
            'app_favicon'         => 'nullable|file|mimes:ico,png,jpg,jpeg,gif,svg|max:512',
            'maintenance_image'   => 'nullable|image|max:2048',
            'vendor_mode'         => 'nullable|in:single,multi',
            'global_delivery_charge' => 'nullable|numeric|min:0',
            'single_store_cart'   => 'nullable|in:0,1',
        ]);

        try {
            $tab = $request->input('_save_tab', 'identity');

            if (\App\Helpers\DemoHelper::isDemoMode()) {
                $restrictedTabs = ['maintenance', 'app-update'];
                if (in_array($tab, $restrictedTabs, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Action Restricted: Maintenance Mode and Force Update settings cannot be modified or deleted in Demo Mode.',
                    ], 403);
                }

                foreach (\App\Helpers\DemoHelper::getRestrictedSettingKeys() as $rKey) {
                    if ($request->has($rKey)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Action Restricted: Maintenance Mode and Force Update settings cannot be modified or deleted in Demo Mode.',
                        ], 403);
                    }
                }
            }
            
            $tabConfigs = [
                'operations'  => ['group' => 'general', 'fields' => ['vendor_mode', 'global_delivery_charge', 'single_store_cart']],
                'identity'    => ['group' => 'app_settings', 'fields' => ['app_name', 'app_copyright', 'company_address']],
                'support'     => ['group' => 'app_settings', 'fields' => ['support_phone', 'support_email', 'support_whatsapp', 'support_website']],
                'maintenance' => ['group' => 'app_settings', 'fields' => ['maintenance_mode', 'maintenance_title', 'maintenance_message']],
                'app-update'  => ['group' => 'app_settings', 'fields' => ['app_min_version', 'app_latest_version', 'app_force_update', 'app_play_store_url', 'app_app_store_url']],
                'branding'    => ['group' => 'app_settings', 'fields' => []],
                'storage'     => ['group' => 'app_settings', 'fields' => []],
            ];

            $config = $tabConfigs[$tab] ?? ['group' => 'general', 'fields' => []];
            $group = $config['group'];

            foreach ($config['fields'] as $field) {
                if ($field === 'maintenance_mode' || $field === 'single_store_cart' || $field === 'app_force_update') {
                    $value = $request->has($field) ? '1' : '0';
                    Setting::updateOrCreate(['key' => $field], ['value' => $value, 'group' => $group]);
                } elseif ($request->has($field)) {
                    $value = $request->input($field);
                    Setting::updateOrCreate(['key' => $field], ['value' => $value, 'group' => $group]);

                    // Keep APP_NAME in .env in sync so config('app.name') always reflects the brand
                    if ($field === 'app_name' && $value) {
                        $this->syncAppNameToEnv($value);
                    }
                }
            }

            if ($tab === 'branding') {
                if ($request->hasFile('admin_sidebar_logo')) {
                    $path = $this->storage->store($request->file('admin_sidebar_logo'), 'app-settings');
                    Setting::updateOrCreate(['key' => 'admin_sidebar_logo'], ['value' => $path, 'group' => 'app_settings']);
                } elseif ($request->input('remove_admin_sidebar_logo') === '1') {
                    $old = Setting::where('key', 'admin_sidebar_logo')->value('value');
                    if ($old) $this->storage->delete($old);
                    Setting::updateOrCreate(['key' => 'admin_sidebar_logo'], ['value' => '', 'group' => 'app_settings']);
                }

                if ($request->hasFile('admin_login_logo')) {
                    $path = $this->storage->store($request->file('admin_login_logo'), 'app-settings');
                    Setting::updateOrCreate(['key' => 'admin_login_logo'], ['value' => $path, 'group' => 'app_settings']);
                } elseif ($request->input('remove_admin_login_logo') === '1') {
                    $old = Setting::where('key', 'admin_login_logo')->value('value');
                    if ($old) $this->storage->delete($old);
                    Setting::updateOrCreate(['key' => 'admin_login_logo'], ['value' => '', 'group' => 'app_settings']);
                }

                if ($request->hasFile('app_favicon')) {
                    $path = $this->storage->store($request->file('app_favicon'), 'app-settings');
                    Setting::updateOrCreate(['key' => 'app_favicon'], ['value' => $path, 'group' => 'app_settings']);
                } elseif ($request->input('remove_app_favicon') === '1') {
                    $old = Setting::where('key', 'app_favicon')->value('value');
                    if ($old) $this->storage->delete($old);
                    Setting::updateOrCreate(['key' => 'app_favicon'], ['value' => '', 'group' => 'app_settings']);
                }
            }

            if ($tab === 'storage') {
                $envMap = [
                    'aws_access_key_id'          => 'AWS_ACCESS_KEY_ID',
                    'aws_secret_access_key'       => 'AWS_SECRET_ACCESS_KEY',
                    'aws_default_region'          => 'AWS_DEFAULT_REGION',
                    'aws_bucket'                  => 'AWS_BUCKET',
                    'aws_url'                     => 'AWS_URL',
                    'aws_endpoint'                => 'AWS_ENDPOINT',
                    'aws_use_path_style_endpoint' => 'AWS_USE_PATH_STYLE_ENDPOINT',
                ];

                foreach ($envMap as $field => $envKey) {
                    if ($field === 'aws_use_path_style_endpoint') {
                        $this->updateEnvVariable($envKey, $request->has($field) ? 'true' : 'false');
                    } elseif ($request->has($field)) {
                        // Pass the raw value — escaping/quoting is handled centrally.
                        $this->updateEnvVariable($envKey, (string) $request->input($field, ''));
                    }
                }

                // Switch FILESYSTEM_DISK based on the toggle
                $disk = $request->has('s3_enabled') ? 's3' : 'local';
                $this->updateEnvVariable('FILESYSTEM_DISK', $disk);
            }

            if ($tab === 'maintenance' && $request->hasFile('maintenance_image')) {
                $path = $this->storage->store($request->file('maintenance_image'), 'app-settings');
                Setting::updateOrCreate(['key' => 'maintenance_image'], ['value' => $path, 'group' => 'app_settings']);
            }

            if ($tab === 'maintenance') {
                $isOn = $request->has('maintenance_mode');
                try {
                    $this->broadcastService->broadcast('admin', 'maintenance_mode', [
                        'enabled'  => $isOn,
                        'title'    => $request->input('maintenance_title', "We'll be back soon!"),
                        'message'  => $request->input('maintenance_message', 'Scheduled maintenance in progress.'),
                        'image'    => Setting::where('key', 'maintenance_image')->value('value') ?? '',
                    ]);
                } catch (\Exception $e) { \Log::warning('Maintenance broadcast failed: ' . $e->getMessage()); }
            }

            Cache::flush();
            try { Artisan::call('config:clear'); } catch (\Exception $e) {}

            $hasNewLogo = $tab === 'branding' && ($request->hasFile('admin_sidebar_logo') || $request->hasFile('admin_login_logo'));

            return response()->json([
                'success'       => true,
                'message'       => 'Settings saved successfully',
                'redirect_logo' => $hasNewLogo,
            ]);

        } catch (\Exception $e) {
            \Log::error('Settings update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Auto-setup Firebase Realtime Database
     */
    public function firebaseAutoSetup(Request $request)
    {
        $request->validate([
            'project_id' => 'required|string',
            'service_account' => 'required|string',
        ]);

        try {
            $serviceAccount = json_decode($request->service_account, true);
            
            if (!$serviceAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid service account JSON',
                ], 400);
            }

            $result = $this->firebaseSetup->autoSetup(
                $serviceAccount,
                $request->project_id
            );

            // Save settings if successful
            if ($result['success']) {
                Setting::updateOrCreate(
                    ['key' => 'firebase_project_id', 'group' => 'mobile_app'],
                    ['value' => $request->project_id]
                );

                Setting::updateOrCreate(
                    ['key' => 'firebase_service_account', 'group' => 'mobile_app'],
                    ['value' => $request->service_account]
                );

                if (isset($result['database_url'])) {
                    Setting::updateOrCreate(
                        ['key' => 'firebase_database_url', 'group' => 'mobile_app'],
                        ['value' => $result['database_url']]
                    );
                }

                Cache::forget('settings_mobile_app');
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Firebase auto-setup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Setup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check Firebase setup status
     */
    public function firebaseStatus(Request $request)
    {
        $request->validate([
            'project_id' => 'required|string',
            'service_account' => 'required|string',
        ]);

        try {
            $serviceAccount = json_decode($request->service_account, true);
            
            if (!$serviceAccount) {
                return response()->json([
                    'configured' => false,
                    'database_exists' => false,
                    'rules_deployed' => false,
                    'connection_ok' => false,
                ]);
            }

            $status = $this->firebaseSetup->getSetupStatus(
                $serviceAccount,
                $request->project_id
            );

            return response()->json($status);

        } catch (\Exception $e) {
            return response()->json([
                'configured' => false,
                'database_exists' => false,
                'rules_deployed' => false,
                'connection_ok' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Complete Firebase setup after manual database creation
     */
    public function firebaseCompleteSetup(Request $request)
    {
        $request->validate([
            'project_id' => 'required|string',
            'service_account' => 'required|string',
            'database_url' => 'nullable|string|url',
        ]);

        try {
            $serviceAccount = json_decode($request->service_account, true);
            
            if (!$serviceAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid service account JSON',
                ], 400);
            }

            $result = $this->firebaseSetup->completeSetup(
                $serviceAccount,
                $request->project_id,
                $request->database_url
            );

            // Save settings if successful
            if ($result['success']) {
                Setting::updateOrCreate(
                    ['key' => 'firebase_project_id', 'group' => 'mobile_app'],
                    ['value' => $request->project_id]
                );

                Setting::updateOrCreate(
                    ['key' => 'firebase_service_account', 'group' => 'mobile_app'],
                    ['value' => $request->service_account]
                );

                if (isset($result['database_url'])) {
                    Setting::updateOrCreate(
                        ['key' => 'firebase_database_url', 'group' => 'mobile_app'],
                        ['value' => $result['database_url']]
                    );
                }

                Cache::forget('settings_mobile_app');
            }

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('Firebase complete setup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Setup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Auto-setup Cloudflare Optimization
     */
    public function cloudflareAutoSetup(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'api_key' => 'required|string',
        ]);

        try {
            $email = $request->email;
            $apiKey = $request->api_key;
            $domain = $request->getHost(); // Or get from settings if configured

            // 1. Get Zone ID
            $zoneId = $this->cloudflareService->getZoneId($email, $apiKey, $domain);

            if (!$zoneId) {
                return response()->json([
                    'success' => false,
                    'message' => "Could not find an active zone for '{$domain}' in your Cloudflare account. Please make sure the domain matches exactly.",
                ], 404);
            }

            // 2. Save settings since we found a valid Zone ID
            Setting::updateOrCreate(
                ['key' => 'cloudflare_email', 'group' => 'mobile_app'],
                ['value' => $email]
            );
            Setting::updateOrCreate(
                ['key' => 'cloudflare_api_key', 'group' => 'mobile_app'],
                ['value' => $apiKey]
            );
            Setting::updateOrCreate(
                ['key' => 'cloudflare_zone_id', 'group' => 'mobile_app'],
                ['value' => $zoneId]
            );
            Cache::forget('settings_mobile_app');

            // 3. Setup Optimization Rule (Best Effort)
            // We pass the domain so the rule matches *domain.com/api/v1/* perfectly.
            $result = $this->cloudflareService->setupOptimization($email, $apiKey, $zoneId, $domain);

            // Even if optimization rules fail (e.g. limit reached), the connection is successful.
            if (!$result['success']) {
                $result['success'] = true; // Connection is good!
                $result['message'] = "Connected successfully! Note: Could not auto-create optimization rule ({$result['message']}). Please ensure a Cache Rule exists for '{$domain}/api/v1/*' in Cloudflare.";
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cloudflare setup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Purge Cloudflare Cache
     */
    public function cloudflarePurgeCache(Request $request)
    {
        try {
            return response()->json($this->cloudflareServicePurgeHelper());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Purge failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to purge CF cache
     */
    protected function cloudflareServicePurgeHelper()
    {
        $email = Setting::where('key', 'cloudflare_email')->value('value');
        $apiKey = Setting::where('key', 'cloudflare_api_key')->value('value');
        $zoneId = Setting::where('key', 'cloudflare_zone_id')->value('value');

        if ($email && $apiKey && $zoneId) {
            $success = $this->cloudflareService->purgeCache($email, $apiKey, $zoneId);
            return [
                'success' => $success,
                'message' => $success ? 'Cloudflare cache purged successfully!' : 'Failed to purge Cloudflare cache.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Cloudflare not configured.'
        ];
    }
    /**
     * Show AI settings page
     */
    public function aiSettings()
    {
        $settings = Setting::where('group', 'ai')->pluck('value', 'key')->toArray();
        return view('admin.settings.ai', compact('settings'));
    }

    /**
     * Update AI settings
     */
    public function updateAiSettings(Request $request)
    {
        $request->validate([
            'ai_provider' => 'required|in:openai,gemini',
            'openai_api_key' => 'nullable|string',
            'openai_model' => 'nullable|string',
            'gemini_api_key' => 'nullable|string',
            'gemini_model' => 'nullable|string',
            'mock_ai_enabled' => 'nullable|boolean',
        ]);

        $settings = $request->only([
            'ai_provider',
            'openai_api_key',
            'openai_model',
            'gemini_api_key',
            'gemini_model',
            'mock_ai_enabled',
        ]);

        // Explicitly handle checkbox being unchecked (not present in request)
        if (!$request->has('mock_ai_enabled')) {
            $settings['mock_ai_enabled'] = '0';
        } else {
            $settings['mock_ai_enabled'] = '1';
        }

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '', 'group' => 'ai']
            );
        }

        Cache::flush();

        return back()->with('success', 'AI settings updated successfully');
    }

    /**
     * Get context data for AI product generation
     */
    public function generateProductContext()
    {
        return response()->json([
            'categories' => Category::where('is_active', 1)->get(['id', 'name']),
            'attributes' => Attribute::with('values')->get(['id', 'name']),
            'tags' => Tag::take(50)->get(['id', 'name']),
            'units' => Unit::active()->get(['id', 'name', 'short_name'])
        ]);
    }

    /**
     * Generate product details using AI
     */
    public function generateProductInfo(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'image' => 'nullable|string', // Base64
            'hint' => 'nullable|string'
        ]);

        $aiService = app(\App\Services\AiService::class);
        
        $context = [
            'categories' => Category::where('is_active', 1)->take(50)->get(['id', 'name'])->toArray(),
            'attributes' => [],
            'tags' => Tag::take(50)->get(['id', 'name'])->toArray(),
            'units' => Unit::active()->get(['id', 'name', 'short_name'])->toArray(),
            'media' => []
        ];

        // Only send specific attributes to AI if the user has selected them
        $selectedIds = $request->input('selected_attribute_ids', []);
        if (!empty($selectedIds)) {
            $context['attributes'] = Attribute::with('values')
                ->whereIn('id', $selectedIds)
                ->get(['id', 'name'])
                ->toArray();
        } else {
            // If they haven't selected any, we tell the AI 
            // "No attributes selected by user, so do not generate variants"
            $context['attributes_status'] = "No attributes selected by user manually. Skip variants.";
        }

        if ($request->image) {
            // Clean base64 string if it has prefix
            $imageData = $request->image;
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
            }
            
            $context['media'][] = [
                'type' => 'image',
                'data' => $imageData,
                'mime_type' => 'image/jpeg'
            ];
        }

        $title = $request->title;
        if ($request->hint) {
            $title .= " (Additional Info: " . $request->hint . ")";
        }

        try {
            $data = $aiService->generateProductDetails($title, $context);
            
            // Generate professional shots if reference image exists
            if ($request->image) {
                $productName = $data['name'] ?? $title;
                
                $primaryShot = $aiService->generateImage("Professional product shot of {$productName} on a clean pure white background, studio lighting, high resolution, 4k", $request->image);
                if ($primaryShot) {
                    $data['primary_shot'] = $this->saveBase64Image('data:image/png;base64,' . $primaryShot, 'products/ai');
                }

                $infoShot = $aiService->generateImage("Lifestyle product shot of {$productName} with clean professional infographic text lines pointing to key features, aesthetic background, high resolution, marketing quality", $request->image);
                if ($infoShot) {
                    $data['info_shot'] = $this->saveBase64Image('data:image/png;base64,' . $infoShot, 'products/ai');
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Show loyalty settings page
     */
    public function loyaltySettings()
    {
        $settings = Setting::where('group', 'loyalty')->pluck('value', 'key')->toArray();
        return view('admin.settings.loyalty', compact('settings'));
    }

    /**
     * Update loyalty settings
     */
    public function updateLoyaltySettings(Request $request)
    {
        $request->validate([
            'cashback_enabled' => 'nullable|boolean',
            'cashback_min_order_amount' => 'required|numeric|min:0',
            'cashback_percentage' => 'required|numeric|min:0|max:100',
            'cashback_points_per_currency' => 'required|integer|min:1',
            'referral_enabled' => 'nullable|boolean',
            'referral_referrer_reward' => 'required|numeric|min:0',
            'referral_referee_reward' => 'required|numeric|min:0',
            'referral_free_deliveries' => 'required|integer|min:0',
        ]);

        $settings = $request->only([
            'cashback_enabled',
            'cashback_min_order_amount',
            'cashback_percentage',
            'cashback_points_per_currency',
            'referral_enabled',
            'referral_referrer_reward',
            'referral_referee_reward',
            'referral_free_deliveries',
        ]);

        // Handle checkboxes
        $settings['cashback_enabled'] = $request->has('cashback_enabled') ? '1' : '0';
        $settings['referral_enabled'] = $request->has('referral_enabled') ? '1' : '0';

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'loyalty']
            );
        }

        Cache::flush();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Loyalty settings updated successfully'
            ]);
        }

        return back()->with('success', 'Loyalty settings updated successfully');
    }

    /**
     * Show order flow settings page
     */
    public function orderFlowSettings()
    {
        $settings = Setting::where('group', 'order_flow')->pluck('value', 'key')->toArray();
        return view('admin.settings.order-flow', compact('settings'));
    }

    /**
     * Update order flow settings
     */
    public function updateOrderFlowSettings(Request $request)
    {
        $request->validate([
            'order_confirm_by' => 'required|in:store,delivery_partner',
            'order_timeout_minutes' => 'required|integer|in:1,2,3,5,10,15,30',
            'order_timeout_action' => 'required|in:auto_cancel,auto_accept',
        ]);

        try {
            $confirmBy = $request->input('order_confirm_by');

            $settings = [
                // Save under both keys so all code paths read the same value
                'order_confirm_by'       => $confirmBy,
                'order_confirmation_by'  => $confirmBy,
                'order_timeout_minutes'  => $request->input('order_timeout_minutes'),
                'order_timeout_action'   => $request->input('order_timeout_action'),
            ];

            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'order_flow']
                );
            }

            Cache::flush();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order flow settings updated successfully'
                ]);
            }

            return back()->with('success', 'Order flow settings updated successfully');
        } catch (\Exception $e) {
            \Log::error('Order flow settings update error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update settings: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Show notification templates settings
     */
    public function notificationTemplates()
    {
        $stored = Setting::where('key', 'notification_templates')->first();
        $templates = $stored ? json_decode($stored->value, true) : [];

        return view('admin.settings.notification-templates', compact('templates'));
    }

    /**
     * Update notification templates
     */
    public function updateNotificationTemplates(Request $request)
    {
        try {
            $templates = $request->input('templates', []);

            Setting::updateOrCreate(
                ['key' => 'notification_templates'],
                ['value' => json_encode($templates), 'group' => 'notification_templates']
            );

            Cache::forget('notification_templates');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification templates saved successfully'
                ]);
            }

            return back()->with('success', 'Notification templates saved successfully');
        } catch (\Exception $e) {
            \Log::error('Notification templates update error: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save templates: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to save templates: ' . $e->getMessage());
        }
    }

    /**
     * Show email settings page.
     */
    public function emailSettings()
    {
        // Load mail config
        $emailSettings = [
            'mail_mailer' => config('mail.default', 'smtp'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_port' => config('mail.mailers.smtp.port', 587),
            'mail_username' => config('mail.mailers.smtp.username'),
            'mail_password' => config('mail.mailers.smtp.password'),
            'mail_encryption' => config('mail.mailers.smtp.encryption', 'tls'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name', config('app.name')),
        ];

        return view('admin.settings.email', compact('emailSettings'));
    }

    /**
     * Update email settings.
     */
    public function updateEmailSettings(Request $request)
    {
        $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        $settings = $request->only([
            'mail_mailer',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
        ]);

        // Update .env file
        $this->updateEnvFile('MAIL', $settings);

        // Also save to database settings for redundancy/fallback if needed (optional, but good for UI persistence)
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'email']
            );
        }

        // Clear config cache
        Artisan::call('config:clear');

        return back()->with('success', 'Email settings updated successfully.');
    }

    /**
     * Auto-configure email settings based on domain.
     */
    public function autoConfigureEmail(Request $request)
    {
        $domain = $request->input('domain');
        if (!$domain) {
            return response()->json(['success' => false, 'message' => 'Domain is required.']);
        }

        // Simple heuristic for common hosts
        $config = [];
        
        if (str_contains($domain, 'gmail.com')) {
            $config = [
                'mail_host' => 'smtp.gmail.com',
                'mail_port' => '465',
                'mail_encryption' => 'ssl',
            ];
        } elseif (str_contains($domain, 'outlook.com') || str_contains($domain, 'hotmail.com')) {
            $config = [
                'mail_host' => 'smtp.office365.com',
                'mail_port' => '587',
                'mail_encryption' => 'tls',
            ];
        } else {
            // cPanel / Standard hosting convention
            $config = [
                'mail_host' => 'mail.' . $domain,
                'mail_port' => '465', // Default to SSL
                'mail_encryption' => 'ssl',
            ];
        }

        return response()->json([
            'success' => true,
            'config' => $config,
            'message' => 'Configuration settings suggested.'
        ]);
    }

    /**
     * Send test email.
     */
    public function sendTestEmail(Request $request)
    {
        $email = $request->input('email');
        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Email is required.']);
        }

        try {
            // Temporarily override config with current settings if they were just saved? 
            // Better to rely on what's in config/env.
            
            \Mail::raw('This is a test email from your application settings.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email Configuration');
            });

            return response()->json(['success' => true, 'message' => 'Test email sent successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to send email: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * System Automator via web (pings both Scheduler and Queue)
     */
    public function webAutomator()
    {
        try {
            // 1. Run the Scheduler (Check if any recurring jobs like currency updates need to be dispatched)
            Artisan::call('schedule:run');
            $scheduleOutput = Artisan::output();

            // 2. Run the Queue Worker (Process the emails/jobs that were just dispatched)
            Artisan::call('queue:work --stop-when-empty --tries=3 --timeout=60');
            $queueOutput = Artisan::output();
            
            return response()->json([
                'status' => 'success',
                'message' => 'System automated successfully',
                'details' => [
                    'scheduler' => $scheduleOutput,
                    'queue' => $queueOutput
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Demo Auto-Reset Settings Page
     */
    public function demoSettings(DemoResetService $demoService)
    {
        $info = $demoService->getBaselineInfo();
        return view('admin.settings.demo', compact('info'));
    }

    /**
     * Update Demo Reset Configuration
     */
    public function updateDemoSettings(Request $request, DemoResetService $demoService)
    {
        if ($demoService->isDemoModeEnabled()) {
            return back()->with('error', 'Action Restricted: Demo reset configuration cannot be modified from web interface while DEMO_MODE is active.');
        }

        $request->validate([
            'demo_reset_enabled'  => 'required|in:0,1',
            'demo_reset_interval' => 'required|in:15,30,60,120,360,720,1440',
        ]);

        Setting::set('demo_reset_enabled', $request->input('demo_reset_enabled'), 'demo');
        Setting::set('demo_reset_interval', $request->input('demo_reset_interval'), 'demo');

        Cache::forget('app_settings');

        return back()->with('success', 'Demo reset settings updated successfully.');
    }

    /**
     * Save Current State as Baseline Snapshot
     */
    public function saveDemoBaseline(DemoResetService $demoService)
    {
        if ($demoService->isDemoModeEnabled()) {
            return back()->with('error', 'Action Restricted: Baseline snapshot cannot be overwritten by demo users. Use CLI command: php artisan demo:save-baseline');
        }

        try {
            $demoService->saveBaseline();
            return back()->with('success', 'Current database and uploaded media successfully captured as pristine baseline snapshot!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to save baseline snapshot: ' . $e->getMessage());
        }
    }

    /**
     * Force Instant Reset to Baseline Snapshot
     */
    public function resetDemoNow(DemoResetService $demoService)
    {
        try {
            $demoService->restoreBaseline();
            return back()->with('success', 'Demo environment successfully restored back to pristine baseline state!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reset demo environment: ' . $e->getMessage());
        }
    }

    /**
     * Show System Update page
     */
    public function systemUpdate(DemoResetService $demoService)
    {
        $systemInfo = [
            'software_version' => env('SOFTWARE_VERSION', '1.0.0'),
            'laravel_version'  => app()->version(),
            'php_version'      => PHP_VERSION,
            'max_upload_size'  => ini_get('upload_max_filesize'),
            'post_max_size'    => ini_get('post_max_size'),
            'memory_limit'     => ini_get('memory_limit'),
            'zip_enabled'      => extension_loaded('zip'),
            'is_demo_mode'     => $demoService->isDemoModeEnabled() || (bool) env('DEMO_MODE', false),
        ];

        return view('admin.settings.system-update', compact('systemInfo'));
    }

    /**
     * Process System Update ZIP Package
     */
    public function processSystemUpdate(Request $request, DemoResetService $demoService)
    {
        if ($demoService->isDemoModeEnabled() || (bool) env('DEMO_MODE', false)) {
            return back()->with('error', 'Action Restricted: System Update is disabled in Demo Mode for security reasons.');
        }

        $request->validate([
            'update_zip' => 'required|file|mimetypes:application/zip,application/x-zip-compressed,multipart/x-zip,application/x-compressed|max:204800',
        ], [
            'update_zip.required'  => 'Please select a system update ZIP file.',
            'update_zip.mimetypes' => 'The uploaded file must be a valid ZIP archive.',
            'update_zip.max'       => 'The update package must not exceed 200 MB.',
        ]);

        if (!extension_loaded('zip')) {
            return back()->with('error', 'PHP ZipExtension is not enabled on this server. Please enable zip extension to perform updates.');
        }

        $file = $request->file('update_zip');
        $zipPath = $file->getRealPath();

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return back()->with('error', 'Failed to open the uploaded ZIP update archive.');
        }

        $basePath = base_path();
        $updatedFiles = [];
        $skippedFiles = [];
        $errors = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $rawFilename = $zip->getNameIndex($i);

            // Normalize backslashes (Windows zip archives) to standard Linux forward slashes
            $filename = str_replace('\\', '/', $rawFilename);
            $filename = ltrim($filename, '/');

            // Strip leading top-level wrapper directory if present (e.g. ADMIN_PANEL/app/... -> app/...)
            if (str_starts_with($filename, 'ADMIN_PANEL/')) {
                $filename = substr($filename, 12);
            }

            // Security checks: Skip directory traversal attempts
            if (empty($filename) || str_contains($filename, '..') || str_starts_with($filename, '/')) {
                $skippedFiles[] = $rawFilename . ' (Insecure or invalid path)';
                continue;
            }

            // Clean up legacy root file with literal backslashes if present on Linux filesystem
            if ($rawFilename !== $filename) {
                $legacyFile = $basePath . '/' . $rawFilename;
                if (file_exists($legacyFile) && !is_dir($legacyFile)) {
                    @unlink($legacyFile);
                }
            }

            // Safely merge .env or .env.update keys without overwriting database credentials
            if ($filename === '.env' || str_ends_with($filename, '/.env') || str_ends_with($filename, '.env.update')) {
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    $mergedKeys = $this->mergeEnvContent($content);
                    $updatedFiles[] = $filename . ' (Merged ' . count($mergedKeys) . ' .env keys: ' . implode(', ', array_slice($mergedKeys, 0, 5)) . ')';
                }
                continue;
            }

            // Skip folder entries
            if (str_ends_with($filename, '/')) {
                continue;
            }

            $targetPath = $basePath . '/' . $filename;
            $targetDir = dirname($targetPath);

            if (!file_exists($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }

            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                if (@file_put_contents($targetPath, $content) !== false) {
                    $updatedFiles[] = $filename;
                } else {
                    $errors[] = "Could not write to path: {$filename}";
                }
            }
        }

        $zip->close();

        // 1. Run Database Migrations
        $migrationOutput = '';
        try {
            Artisan::call('migrate', ['--force' => true]);
            $migrationOutput = trim(Artisan::output());
        } catch (\Exception $e) {
            $migrationOutput = 'Migration Error: ' . $e->getMessage();
            Log::error('System Update Migration Exception: ' . $e->getMessage());
        }

        // 2. Clear System Caches & Optimize
        $cacheLogs = [];
        try {
            Artisan::call('cache:clear');
            $cacheLogs[] = 'Cache cleared: ' . trim(Artisan::output());
        } catch (\Exception $e) {}

        try {
            Artisan::call('view:clear');
            $cacheLogs[] = 'Views cleared: ' . trim(Artisan::output());
        } catch (\Exception $e) {}

        try {
            Artisan::call('config:clear');
            $cacheLogs[] = 'Config cleared: ' . trim(Artisan::output());
        } catch (\Exception $e) {}

        try {
            Artisan::call('route:clear');
            $cacheLogs[] = 'Routes cleared: ' . trim(Artisan::output());
        } catch (\Exception $e) {}

        if (class_exists(\Plugins\Website\Helpers\WebsiteHelper::class)) {
            try {
                \Plugins\Website\Helpers\WebsiteHelper::clearCache();
                $cacheLogs[] = 'Website plugin cache cleared.';
            } catch (\Exception $e) {}
        }

        $report = [
            'updated_count' => count($updatedFiles),
            'updated_files' => $updatedFiles,
            'skipped_files' => $skippedFiles,
            'errors'        => $errors,
            'migration'     => $migrationOutput ?: 'No new migrations to execute.',
            'cache_logs'    => implode("\n", $cacheLogs),
        ];

        session()->flash('update_report', $report);

        return back()->with('success', 'System update successfully applied! ' . count($updatedFiles) . ' files updated, database migrations executed, and caches refreshed.');
    }

    /**
     * Merge incoming .env key-value pairs safely into existing .env file.
     * Preserves critical database and app security credentials.
     */
    private function mergeEnvContent(string $incomingContent): array
    {
        $envFile = base_path('.env');
        if (!file_exists($envFile)) {
            return [];
        }

        $existingContent = file_get_contents($envFile);
        $protectedKeys = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'APP_KEY', 'APP_URL'];
        $mergedKeys = [];

        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $incomingContent));
        foreach ($lines as $line) {
            $line = trim($line);

            // Ignore comments and empty lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);

            if (empty($key)) {
                continue;
            }

            // Protect critical DB and app security keys from being overwritten if already set
            if (in_array(strtoupper($key), $protectedKeys, true) && preg_match('/^' . preg_quote($key, '/') . '=.*/m', $existingContent)) {
                continue;
            }

            $pattern = '/^' . preg_quote($key, '/') . '=.*/m';
            $replacement = "{$key}={$val}";

            if (preg_match($pattern, $existingContent)) {
                $existingContent = preg_replace_callback($pattern, static fn() => $replacement, $existingContent, 1);
            } else {
                if ($existingContent !== '' && !str_ends_with($existingContent, "\n")) {
                    $existingContent .= "\n";
                }
                $existingContent .= $replacement . "\n";
            }

            $mergedKeys[] = "{$key}={$val}";
        }

        file_put_contents($envFile, $existingContent, LOCK_EX);

        return $mergedKeys;
    }
}


@extends('admin.layouts.app')
@section('title', 'Authentication Settings')
@section('content')
<form action="{{ route('admin.settings.auth.update') }}" method="POST" id="authSettingsForm" class="space-y-6">
    @csrf
    @method('PUT')

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-wide">Authentication Settings</h1>
                <p class="text-sm text-gray-500 mt-1">Configure login methods and OTP gateways</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-bold uppercase tracking-wider transition-all shadow-sm">
                Cancel
            </a>
            <x-permission-btn 
                permission="settings.auth" 
                type="submit"
                class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold uppercase tracking-wider hover:bg-black transition-all shadow-md active:scale-[0.98] flex items-center gap-2" 
                label="Save Changes"
                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
            />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Login Configuration -->
        <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6 h-fit">
            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Login Configuration</h3>
            
            <div class="space-y-5">
                <!-- Email & Password -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-primary-200 hover:shadow-sm transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 text-sm">Email & Password</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Traditional login method</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="auth_manual_login_enabled" value="1" id="manual_login_toggle" {{ ($settings['auth_manual_login_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer login-method-toggle">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-900"></div>
                    </label>
                </div>

                <!-- Phone OTP -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-primary-200 hover:shadow-sm transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 text-sm">Phone OTP</h4>
                            <p class="text-xs text-gray-500 mt-0.5">SMS-based passwordless login</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="auth_phone_otp_enabled" value="1" id="phone_otp_toggle" {{ ($settings['auth_phone_otp_enabled'] ?? '1') == '1' ? 'checked' : '' }} onchange="togglePhoneSettings()" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-900"></div>
                    </label>
                </div>

                <!-- Email OTP -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-primary-200 hover:shadow-sm transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 text-sm">Email OTP</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Email-based passwordless login</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="auth_email_otp_enabled" value="1" {{ ($settings['auth_email_otp_enabled'] ?? '0') == '1' ? 'checked' : '' }} onchange="toggleEmailSettings()" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-900"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Forgot Password Controls (App-Wise) -->
        <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6 h-fit col-span-1 lg:col-span-2">
            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">App-Wise Forgot Password Controls</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- User App -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-gray-300 transition-all">
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">User App</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Enable "Forgot Password?" link for Users</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="auth_user_app_forgot_password_enabled" value="1" {{ ($settings['auth_user_app_forgot_password_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-900"></div>
                    </label>
                </div>

                <!-- Store App -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-gray-300 transition-all">
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Store App / Panel</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Enable "Forgot Password?" link for Sellers</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="auth_store_app_forgot_password_enabled" value="1" {{ ($settings['auth_store_app_forgot_password_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-900"></div>
                    </label>
                </div>

                <!-- Driver App -->
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-gray-300 transition-all">
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Driver / Rider App</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Enable "Forgot Password?" link for Drivers</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="auth_driver_app_forgot_password_enabled" value="1" {{ ($settings['auth_driver_app_forgot_password_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-900"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Security Parameters -->
        <div class="card bg-white border border-gray-200 shadow-sm rounded-xl p-6 h-fit">
            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">Security Parameters</h3>

            <div class="grid grid-cols-1 gap-6">
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">OTP Length</label>
                    <div class="relative">
                        <select name="auth_otp_length" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer">
                            <option value="4" {{ ($settings['auth_otp_length'] ?? '6') == '4' ? 'selected' : '' }}>4 Digits</option>
                            <option value="6" {{ ($settings['auth_otp_length'] ?? '6') == '6' ? 'selected' : '' }}>6 Digits</option>
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Time</label>
                    <div class="relative">
                        <select name="auth_otp_expiry_minutes" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer">
                            <option value="5" {{ ($settings['auth_otp_expiry_minutes'] ?? '10') == '5' ? 'selected' : '' }}>5 Minutes</option>
                            <option value="10" {{ ($settings['auth_otp_expiry_minutes'] ?? '10') == '10' ? 'selected' : '' }}>10 Minutes</option>
                            <option value="15" {{ ($settings['auth_otp_expiry_minutes'] ?? '10') == '15' ? 'selected' : '' }}>15 Minutes</option>
                            <option value="30" {{ ($settings['auth_otp_expiry_minutes'] ?? '10') == '30' ? 'selected' : '' }}>30 Minutes</option>
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Attempts</label>
                        <input type="number" name="auth_otp_max_attempts" value="{{ $settings['auth_otp_max_attempts'] ?? '5' }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        <p class="text-[11px] text-gray-400 mt-1">Attempts before block</p>
                    </div>

                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cooldown</label>
                        <div class="relative">
                            <input type="number" name="auth_otp_resend_cooldown" value="{{ $settings['auth_otp_resend_cooldown'] ?? '60' }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all pr-12">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 pointer-events-none">Sec</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Resend waiting time</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance & Account Deletion -->
        <div class="lg:col-span-2 card bg-white border border-gray-200 shadow-sm rounded-xl p-6 h-fit">
            <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Compliance & Account Deletion</h3>
                    <p class="text-xs text-gray-500 mt-0.5">App Store & Play Store Policy Compliance</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                <div class="space-y-4">
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deletion Grace Period (Days)</label>
                        <div class="relative">
                            <input type="number" name="auth_account_deletion_grace_period" value="{{ $settings['auth_account_deletion_grace_period'] ?? '7' }}" min="0" max="365" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all pr-12">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 pointer-events-none">Days</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            The number of days a user account remains "soft-deleted" before being permanently purged from the database. 
                            <strong>Setting this to 0 will delete accounts immediately</strong> (not recommended for good UX).
                        </p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-blue-600 shadow-sm flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 text-xs uppercase tracking-tight">How restoration works</h4>
                            <p class="text-[13px] text-gray-600 mt-2 leading-relaxed">
                                When a user initiates account deletion from the app, their status is set to "deleted" but their data is preserved. 
                                If they log back in within this grace period, their account is <strong>automatically restored</strong>. 
                                After the period expires, a background job permanently deletes all their data.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMS Gateway Settings (Conditional) -->
        <div id="sms_gateway_section" class="lg:col-span-2 card bg-white border border-gray-200 shadow-sm rounded-xl p-6 {{ ($settings['auth_phone_otp_enabled'] ?? '1') == '1' ? '' : 'hidden' }}">
            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-6 border-b border-gray-100 pb-4">SMS Gateway Provider</h3>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div>
                    <!-- Gateway Selection -->
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Provider</label>
                        <div class="relative">
                            <select name="auth_sms_gateway" id="sms_gateway_select" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer" onchange="showGatewayFields()">
                                <option value="firebase" {{ ($settings['auth_sms_gateway'] ?? 'firebase') == 'firebase' ? 'selected' : '' }}>Firebase (Free / Client-side)</option>
                                <option value="twilio" {{ ($settings['auth_sms_gateway'] ?? '') == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                <option value="msg91" {{ ($settings['auth_sms_gateway'] ?? '') == 'msg91' ? 'selected' : '' }}>MSG91</option>
                                <option value="2factor" {{ ($settings['auth_sms_gateway'] ?? '') == '2factor' ? 'selected' : '' }}>2Factor</option>
                                <option value="nexmo" {{ ($settings['auth_sms_gateway'] ?? '') == 'nexmo' ? 'selected' : '' }}>Nexmo (Vonage)</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        
                        <!-- Test Button -->
                        <div id="test_gateway_btn_container" class="mt-3 hidden">
                            <x-permission-btn 
                                permission="settings.auth" 
                                type="button"
                                onclick="openTestModal()"
                                class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1" 
                                label="Test Configuration"
                                icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
                            />
                        </div>

                        <!-- Helpers -->
                        <div id="firebase_note" class="mt-4 p-4 bg-blue-50 text-blue-700 text-sm rounded-xl flex items-start gap-3 border border-blue-100">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Firebase Phone Auth is handled client-side by the app. No additional server API keys are required here. This is the recommended cost-effective option for startups.</span>
                        </div>
                        <div id="gateway_note" class="mt-4 p-4 bg-amber-50 text-amber-700 text-sm rounded-xl flex items-start gap-3 border border-amber-100 hidden">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>
                                <p class="font-bold mb-1">Server-Side Verification Enabled</p>
                                <span>Activating a third-party gateway will verify numbers server-side and replace Firebase Auth. Ensure your API keys are valid and have sufficient credits.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Provider Config Fields -->
                <div class="pt-1">
                    <!-- Twilio -->
                    <div id="twilio_fields" class="gateway-fields hidden space-y-5">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Account SID</label>
                            <input type="text" name="auth_twilio_sid" value="{{ $settings['auth_twilio_sid'] ?? '' }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-mono" placeholder="AC...">
                        </div>
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Auth Token</label>
                            <input type="password" name="auth_twilio_token" value="{{ $settings['auth_twilio_token'] ?? '' }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-mono">
                        </div>
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Verify Service SID</label>
                            <input type="text" name="auth_twilio_verify_sid" value="{{ $settings['auth_twilio_verify_sid'] ?? '' }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-mono" placeholder="VA...">
                        </div>
                    </div>

                    <!-- MSG91 -->
                    <div id="msg91_fields" class="gateway-fields hidden space-y-5">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Auth Key</label>
                            <input type="password" name="auth_msg91_auth_key" value="{{ $settings['auth_msg91_auth_key'] ?? '' }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-mono">
                        </div>
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Template ID</label>
                            <input type="text" name="auth_msg91_template_id" value="{{ $settings['auth_msg91_template_id'] ?? '' }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        </div>
                    </div>

                    <!-- 2Factor -->
                    <div id="2factor_fields" class="gateway-fields hidden space-y-5">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                            <input type="password" name="auth_2factor_api_key" value="{{ $settings['auth_2factor_api_key'] ?? '' }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-mono">
                        </div>
                    </div>

                    <!-- Nexmo -->
                    <div id="nexmo_fields" class="gateway-fields hidden space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                                <input type="text" name="auth_nexmo_api_key" value="{{ $settings['auth_nexmo_api_key'] ?? '' }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-mono">
                            </div>
                            <div class="form-group">
                                <label class="block text-sm font-medium text-gray-700 mb-1">API Secret</label>
                                <input type="password" name="auth_nexmo_api_secret" value="{{ $settings['auth_nexmo_api_secret'] ?? '' }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-mono">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Brand Name</label>
                            <input type="text" name="auth_nexmo_brand_name" value="{{ $settings['auth_nexmo_brand_name'] ?? 'InAllCart' }}" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Test SMS Modal -->
<div id="testSmsModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeTestModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Test SMS Configuration</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4">Enter a phone number to send a test OTP. Ensure you have saved your settings first.</p>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="text" id="test_phone" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500" placeholder="+1234567890">
                            <div id="test_result" class="mt-3 text-sm hidden"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <x-permission-btn 
                    permission="settings.auth" 
                    type="button"
                    id="btn_send_test"
                    onclick="sendTestSms()"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm" 
                    label="Send Test SMS"
                />
                <button type="button" onclick="closeTestModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePhoneSettings() {
    const phoneToggle = document.getElementById('phone_otp_toggle');
    const emailToggle = document.querySelector('input[name="auth_email_otp_enabled"]');
    const section = document.getElementById('sms_gateway_section');
    
    if (phoneToggle.checked) {
        section.classList.remove('hidden');
        // Disable Email OTP if Phone OTP is enabled
        if (emailToggle.checked) {
            emailToggle.checked = false;
        }
    } else {
        section.classList.add('hidden');
    }
}

function toggleEmailSettings() {
    const phoneToggle = document.getElementById('phone_otp_toggle');
    const emailToggle = document.querySelector('input[name="auth_email_otp_enabled"]');
    
    if (emailToggle.checked) {
        // Disable Phone OTP if Email OTP is enabled
        if (phoneToggle.checked) {
            phoneToggle.checked = false;
            // Also hide the gateway section
            document.getElementById('sms_gateway_section').classList.add('hidden');
        }
    }
}

function showGatewayFields() {
    const gateway = document.getElementById('sms_gateway_select').value;
    const firebaseNote = document.getElementById('firebase_note');
    const gatewayNote = document.getElementById('gateway_note');
    const testBtn = document.getElementById('test_gateway_btn_container'); // Added
    
    // Hide all fields first
    document.querySelectorAll('.gateway-fields').forEach(el => el.classList.add('hidden'));
    
    // Show selected
    if (gateway !== 'firebase') {
        const fields = document.getElementById(gateway + '_fields');
        if (fields) fields.classList.remove('hidden');
        
        firebaseNote.classList.add('hidden');
        gatewayNote.classList.remove('hidden');
        if (testBtn) testBtn.classList.remove('hidden'); // Show test btn
    } else {
        firebaseNote.classList.remove('hidden');
        gatewayNote.classList.add('hidden');
        if (testBtn) testBtn.classList.add('hidden'); // Hide test btn
    }
}

// Test Modal Functions
function openTestModal() {
    document.getElementById('testSmsModal').classList.remove('hidden');
    document.getElementById('test_result').classList.add('hidden');
    document.getElementById('test_phone').value = '';
}

function closeTestModal() {
    document.getElementById('testSmsModal').classList.add('hidden');
}

function sendTestSms() {
    const phone = document.getElementById('test_phone').value;
    const gateway = document.getElementById('sms_gateway_select').value;
    const btn = document.getElementById('btn_send_test');
    const resultDiv = document.getElementById('test_result');

    if (!phone) {
        alert('Please enter a phone number');
        return;
    }

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Sending...';
    resultDiv.classList.add('hidden');

    fetch('{{ route("admin.settings.auth.test-sms") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            phone: phone,
            gateway: gateway
        })
    })
    .then(response => response.json())
    .then(data => {
        resultDiv.classList.remove('hidden');
        if (data.success) {
            resultDiv.className = 'mt-3 text-sm text-green-600 font-medium';
            resultDiv.innerHTML = `<svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> ${data.message}`;
        } else {
            resultDiv.className = 'mt-3 text-sm text-red-600 font-medium';
            resultDiv.innerHTML = `<svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> ${data.message}`;
        }
    })
    .catch(error => {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'mt-3 text-sm text-red-600 font-medium';
        resultDiv.innerHTML = 'Error: ' + error.message;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    showGatewayFields();
    
    // Smooth submit feedback
    document.getElementById('authSettingsForm').addEventListener('submit', function(e) {
        const manualLogin = document.getElementById('manual_login_toggle');
        const phoneOtp = document.getElementById('phone_otp_toggle');
        const emailOtp = document.querySelector('input[name="auth_email_otp_enabled"]');
        const googleLogin = document.querySelector('input[name="auth_google_enabled"]');
        const appleLogin = document.querySelector('input[name="auth_apple_enabled"]');

        const activeCount = [
            manualLogin?.checked,
            phoneOtp?.checked,
            emailOtp?.checked,
            googleLogin?.checked,
            appleLogin?.checked,
        ].filter(Boolean).length;

        if (activeCount < 1) {
            alert('Validation Error: You must enable at least 1 login configuration method (e.g. Email & Password, Phone OTP, Email OTP, or Social Login).');
            return;
        }
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        formData.set('auth_manual_login_enabled', manualLogin?.checked ? '1' : '0');
        formData.set('auth_phone_otp_enabled', phoneOtp?.checked ? '1' : '0');
        formData.set('auth_email_otp_enabled', emailOtp?.checked ? '1' : '0');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const toast = document.createElement('div');
                toast.className = 'fixed top-4 right-4 bg-green-900 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-3 animate-fade-in-down';
                toast.innerHTML = `<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>${data.message || 'Saved successfully'}</span>`;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-y-[-10px]');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);

                setTimeout(() => window.location.reload(), 1000);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});
</script>
@endpush
@endsection

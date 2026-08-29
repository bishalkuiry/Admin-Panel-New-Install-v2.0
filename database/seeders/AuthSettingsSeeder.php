<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Authentication Mode Settings
            ['group' => 'auth', 'key' => 'auth_manual_login_enabled', 'value' => '1', 'type' => 'boolean',
             'label' => 'Enable Manual Login', 'description' => 'Allow users to login with email and password'],

            ['group' => 'auth', 'key' => 'auth_phone_otp_enabled', 'value' => '1', 'type' => 'boolean',
             'label' => 'Enable Phone OTP', 'description' => 'Allow users to login/register with phone OTP'],

            ['group' => 'auth', 'key' => 'auth_email_otp_enabled', 'value' => '0', 'type' => 'boolean',
             'label' => 'Enable Email OTP', 'description' => 'Allow users to login/register with email OTP'],

            // SMS Gateway Settings
            ['group' => 'auth', 'key' => 'auth_sms_gateway', 'value' => 'firebase', 'type' => 'select',
             'options' => json_encode(['firebase' => 'Firebase (Default)', 'twilio' => 'Twilio', 'msg91' => 'MSG91', '2factor' => '2Factor', 'nexmo' => 'Nexmo (Vonage)']),
             'label' => 'SMS Gateway', 'description' => 'Select SMS gateway for phone OTP. Firebase is client-side (no API key needed here).'],

            // Twilio Settings
            ['group' => 'auth', 'key' => 'auth_twilio_sid', 'value' => '', 'type' => 'text',
             'label' => 'Twilio Account SID', 'description' => 'Your Twilio Account SID'],

            ['group' => 'auth', 'key' => 'auth_twilio_token', 'value' => '', 'type' => 'password',
             'label' => 'Twilio Auth Token', 'description' => 'Your Twilio Auth Token'],

            ['group' => 'auth', 'key' => 'auth_twilio_verify_sid', 'value' => '', 'type' => 'text',
             'label' => 'Twilio Verify Service SID', 'description' => 'Your Twilio Verify Service SID'],

            // MSG91 Settings
            ['group' => 'auth', 'key' => 'auth_msg91_auth_key', 'value' => '', 'type' => 'password',
             'label' => 'MSG91 Auth Key', 'description' => 'Your MSG91 Authentication Key'],

            ['group' => 'auth', 'key' => 'auth_msg91_template_id', 'value' => '', 'type' => 'text',
             'label' => 'MSG91 Template ID', 'description' => 'Your MSG91 OTP Template ID'],

            // 2Factor Settings
            ['group' => 'auth', 'key' => 'auth_2factor_api_key', 'value' => '', 'type' => 'password',
             'label' => '2Factor API Key', 'description' => 'Your 2Factor.in API Key'],

            // Nexmo (Vonage) Settings
            ['group' => 'auth', 'key' => 'auth_nexmo_api_key', 'value' => '', 'type' => 'text',
             'label' => 'Nexmo API Key', 'description' => 'Your Vonage/Nexmo API Key'],

            ['group' => 'auth', 'key' => 'auth_nexmo_api_secret', 'value' => '', 'type' => 'password',
             'label' => 'Nexmo API Secret', 'description' => 'Your Vonage/Nexmo API Secret'],

            ['group' => 'auth', 'key' => 'auth_nexmo_brand_name', 'value' => 'InAllCart', 'type' => 'text',
             'label' => 'Nexmo Brand Name', 'description' => 'Brand name shown in SMS (alphanumeric sender ID)'],

            // OTP Configuration
            ['group' => 'auth', 'key' => 'auth_otp_length', 'value' => '6', 'type' => 'select',
             'options' => json_encode(['4' => '4 Digits', '6' => '6 Digits']),
             'label' => 'OTP Length', 'description' => 'Number of digits in OTP code'],

            ['group' => 'auth', 'key' => 'auth_otp_expiry_minutes', 'value' => '10', 'type' => 'select',
             'options' => json_encode(['5' => '5 Minutes', '10' => '10 Minutes', '15' => '15 Minutes', '30' => '30 Minutes']),
             'label' => 'OTP Expiry', 'description' => 'OTP code validity duration'],

            ['group' => 'auth', 'key' => 'auth_otp_max_attempts', 'value' => '5', 'type' => 'number',
             'label' => 'Max OTP Attempts', 'description' => 'Maximum verification attempts before OTP expires'],

            ['group' => 'auth', 'key' => 'auth_otp_resend_cooldown', 'value' => '60', 'type' => 'number',
             'label' => 'Resend Cooldown (seconds)', 'description' => 'Minimum time between OTP resends'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsolidatedSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            ['group' => 'general', 'key' => 'vendor_mode', 'value' => 'single', 'type' => 'select',
             'options' => json_encode(['single' => 'Single Vendor (Own Store)', 'multi' => 'Multi Vendor (Marketplace)']),
             'label' => 'Vendor Mode', 'description' => 'Single vendor disables seller registration'],

            ['group' => 'general', 'key' => 'seller_registration', 'value' => '0', 'type' => 'select',
             'options' => json_encode(['0' => 'Disabled', '1' => 'Enabled']),
             'label' => 'Seller Registration', 'description' => 'Allow new sellers to register (Multi-vendor only)'],

            ['group' => 'general', 'key' => 'seller_auto_approve', 'value' => '0', 'type' => 'select',
             'options' => json_encode(['0' => 'Manual Approval', '1' => 'Auto Approve']),
             'label' => 'Seller Auto Approve', 'description' => 'Automatically approve new seller registrations'],

            ['group' => 'general', 'key' => 'default_commission_percent', 'value' => '10', 'type' => 'number',
             'label' => 'Default Commission %', 'description' => 'Default commission for new stores'],

            ['group' => 'general', 'key' => 'global_delivery_charge', 'value' => '5.99', 'type' => 'number',
             'label' => 'Global Delivery Charge', 'description' => 'Default delivery charge'],

            ['group' => 'general', 'key' => 'global_free_delivery', 'value' => '0', 'type' => 'select',
             'options' => json_encode(['0' => 'Paid Delivery', '1' => 'Free Delivery']),
             'label' => 'Global Free Delivery', 'description' => 'Enable free delivery globally'],

            ['group' => 'general', 'key' => 'single_store_cart', 'value' => '1', 'type' => 'select',
             'options' => json_encode(['0' => 'Multi-Store (OFF)', '1' => 'Single Store (ON)']),
             'label' => 'Single Store Cart', 'description' => 'Restrict cart to items from one store at a time'],

            // Broadcast Settings
            ['group' => 'broadcast', 'key' => 'driver', 'value' => 'auto', 'type' => 'select',
             'options' => json_encode(['auto' => 'Auto Detect', 'pusher' => 'Pusher', 'redis' => 'Redis', 'database' => 'Database/SSE']),
             'label' => 'Broadcast Driver', 'description' => 'Auto will use best available'],

            ['group' => 'broadcast', 'key' => 'pusher_app_id', 'value' => '', 'type' => 'text',
             'label' => 'Pusher App ID', 'description' => 'Get from pusher.com dashboard'],

            ['group' => 'broadcast', 'key' => 'pusher_app_key', 'value' => '', 'type' => 'text',
             'label' => 'Pusher App Key', 'description' => 'Public key for client'],

            ['group' => 'broadcast', 'key' => 'pusher_app_secret', 'value' => '', 'type' => 'password',
             'label' => 'Pusher App Secret', 'description' => 'Keep this secret'],

            ['group' => 'broadcast', 'key' => 'pusher_app_cluster', 'value' => 'mt1', 'type' => 'select',
             'options' => json_encode(['mt1' => 'mt1 (N. Virginia)', 'us2' => 'us2 (Ohio)', 'us3' => 'us3 (Oregon)',
                          'eu' => 'eu (Ireland)', 'ap1' => 'ap1 (Singapore)', 'ap2' => 'ap2 (Mumbai)',
                          'ap3' => 'ap3 (Tokyo)', 'ap4' => 'ap4 (Sydney)', 'sa1' => 'sa1 (São Paulo)']),
             'label' => 'Pusher Cluster', 'description' => 'Choose nearest to your users'],

            ['group' => 'broadcast', 'key' => 'redis_host', 'value' => '127.0.0.1', 'type' => 'text',
             'label' => 'Redis Host', 'description' => 'Redis server hostname'],

            ['group' => 'broadcast', 'key' => 'redis_port', 'value' => '6379', 'type' => 'number',
             'label' => 'Redis Port', 'description' => 'Default is 6379'],

            ['group' => 'broadcast', 'key' => 'redis_password', 'value' => '', 'type' => 'password',
             'label' => 'Redis Password', 'description' => 'Leave empty if no password'],

            // Cache Settings
            ['group' => 'cache', 'key' => 'driver', 'value' => 'database', 'type' => 'select',
             'options' => json_encode(['file' => 'File', 'database' => 'Database', 'redis' => 'Redis', 'memcached' => 'Memcached']),
             'label' => 'Cache Driver', 'description' => 'Database works on shared hosting'],

            // Queue Settings
            ['group' => 'queue', 'key' => 'driver', 'value' => 'database', 'type' => 'select',
             'options' => json_encode(['sync' => 'Sync (Immediate)', 'database' => 'Database', 'redis' => 'Redis']),
             'label' => 'Queue Driver', 'description' => 'Database works on shared hosting'],

            // Mobile App - Currency Settings
            ['group' => 'mobile_app', 'key' => 'multi_currency_enabled', 'value' => '0', 'type' => 'boolean',
             'label' => 'Enable Multi-Currency', 'description' => 'Allow users to view prices in different currencies'],

            ['group' => 'mobile_app', 'key' => 'default_currency', 'value' => 'INR', 'type' => 'string',
             'label' => 'Default Currency', 'description' => 'System default currency code (ISO 4217)'],

            ['group' => 'mobile_app', 'key' => 'currency_symbol_position', 'value' => 'left', 'type' => 'string',
             'label' => 'Currency Symbol Position', 'description' => 'Position of currency symbol (left or right)'],

            ['group' => 'mobile_app', 'key' => 'currency_decimal_places', 'value' => '0', 'type' => 'integer',
             'label' => 'Currency Decimal Places', 'description' => 'Number of decimal places for currency display'],

            ['group' => 'mobile_app', 'key' => 'currency_thousand_separator', 'value' => ',', 'type' => 'string',
             'label' => 'Thousand Separator', 'description' => 'Character used to separate thousands'],

            ['group' => 'mobile_app', 'key' => 'auto_exchange_rate_update', 'value' => '1', 'type' => 'boolean',
             'label' => 'Auto Update Exchange Rates', 'description' => 'Automatically update currency exchange rates'],

            ['group' => 'mobile_app', 'key' => 'exchange_rate_update_frequency', 'value' => 'daily', 'type' => 'string',
             'label' => 'Exchange Rate Update Frequency', 'description' => 'How often to update exchange rates (hourly, daily, weekly)'],

            // Mobile App - Timezone Settings
            ['group' => 'mobile_app', 'key' => 'system_timezone', 'value' => 'Asia/Kolkata', 'type' => 'string',
             'label' => 'System Timezone', 'description' => 'Default timezone for the application'],

            ['group' => 'mobile_app', 'key' => 'date_format', 'value' => 'd/m/Y', 'type' => 'string',
             'label' => 'Date Format', 'description' => 'Format for displaying dates'],

            ['group' => 'mobile_app', 'key' => 'time_format', 'value' => '12', 'type' => 'string',
             'label' => 'Time Format', 'description' => '12-hour or 24-hour time format'],

            // Wallet Settings
            ['group' => 'wallet', 'key' => 'enabled', 'value' => '1', 'type' => 'select',
             'options' => json_encode(['0' => 'Disabled', '1' => 'Enabled']),
             'label' => 'Wallet System', 'description' => 'Enable or disable the entire wallet system'],

            ['group' => 'wallet', 'key' => 'signup_bonus_enabled', 'value' => '1', 'type' => 'select',
             'options' => json_encode(['0' => 'Disabled', '1' => 'Enabled']),
             'label' => 'Signup Bonus', 'description' => 'Automatically credit bonus to new customer wallets'],

            ['group' => 'wallet', 'key' => 'signup_bonus_amount', 'value' => '100.00', 'type' => 'number',
             'label' => 'Signup Bonus Amount', 'description' => 'Amount to credit to new customer wallets'],

            ['group' => 'wallet', 'key' => 'min_top_up_amount', 'value' => '10.00', 'type' => 'number',
             'label' => 'Minimum Top-up Amount', 'description' => 'Minimum amount users can add to their wallet'],

            ['group' => 'wallet', 'key' => 'max_top_up_amount', 'value' => '50000.00', 'type' => 'number',
             'label' => 'Maximum Top-up Amount', 'description' => 'Maximum amount users can add to their wallet'],

            ['group' => 'wallet', 'key' => 'min_withdrawal_amount', 'value' => '100.00', 'type' => 'number',
             'label' => 'Minimum Withdrawal Amount', 'description' => 'Minimum amount sellers/delivery boys can withdraw'],

            ['group' => 'wallet', 'key' => 'withdrawal_processing_days', 'value' => '3', 'type' => 'number',
             'label' => 'Withdrawal Processing Days', 'description' => 'Estimated days to process withdrawal requests'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => $setting['group'], 'key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

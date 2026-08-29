<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class WalletSettingsSeeder extends Seeder
{
    /**
     * Run the wallet settings seeder.
     */
    public function run(): void
    {
        $settings = [
            // Signup Bonus Settings
            [
                'key' => 'signup_bonus_enabled',
                'value' => false,
                'type' => 'boolean',
            ],
            [
                'key' => 'signup_bonus_amount',
                'value' => 0,
                'type' => 'number',
            ],
            
            // Top-Up Settings
            [
                'key' => 'min_topup_amount',
                'value' => 10,
                'type' => 'number',
            ],
            [
                'key' => 'max_topup_amount',
                'value' => 10000,
                'type' => 'number',
            ],
            
            // Withdrawal Settings
            [
                'key' => 'min_withdrawal_amount',
                'value' => 50,
                'type' => 'number',
            ],
            [
                'key' => 'withdrawal_processing_days',
                'value' => 3,
                'type' => 'number',
            ],
            
            // Transaction Settings
            [
                'key' => 'max_transaction_amount',
                'value' => 100000,
                'type' => 'number',
            ],
            [
                'key' => 'allow_negative_balance',
                'value' => false,
                'type' => 'boolean',
            ],
            
            // Notification Settings
            [
                'key' => 'send_transaction_notifications',
                'value' => true,
                'type' => 'boolean',
            ],
            [
                'key' => 'send_withdrawal_notifications',
                'value' => true,
                'type' => 'boolean',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'] ?? 'string',
                ]
            );
        }

        $this->command->info('Wallet settings seeded successfully!');
    }
}

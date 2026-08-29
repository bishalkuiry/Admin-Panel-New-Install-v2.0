<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class LoyaltySettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Cashback Settings
            [
                'group' => 'loyalty',
                'key' => 'cashback_enabled',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Enable Cashback Points',
                'description' => 'Allow users to earn and redeem points on orders.',
            ],
            [
                'group' => 'loyalty',
                'key' => 'cashback_min_order_amount',
                'value' => '500',
                'type' => 'number',
                'label' => 'Min Order for Cashback',
                'description' => 'Minimum order amount to earn points.',
            ],
            [
                'group' => 'loyalty',
                'key' => 'cashback_percentage',
                'value' => '10',
                'type' => 'number',
                'label' => 'Cashback Percentage',
                'description' => 'Percentage of order total awarded as points.',
            ],
            [
                'group' => 'loyalty',
                'key' => 'cashback_points_per_currency',
                'value' => '100',
                'type' => 'number',
                'label' => 'Points Per Currency Unit',
                'description' => 'How many points equal 1 unit of currency (e.g., 100 points = ₹1).',
            ],

            // Referral Settings
            [
                'group' => 'loyalty',
                'key' => 'referral_enabled',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Enable Referral System',
                'description' => 'Allow users to invite friends and earn rewards.',
            ],
            [
                'group' => 'loyalty',
                'key' => 'referral_referrer_reward',
                'value' => '50',
                'type' => 'number',
                'label' => 'Referrer Reward Amount',
                'description' => 'Flat money credited to referrer wallet after successful referral.',
            ],
            [
                'group' => 'loyalty',
                'key' => 'referral_referee_reward',
                'value' => '25',
                'type' => 'number',
                'label' => 'Referee Welcome Reward',
                'description' => 'Flat money credited to referee wallet after first order.',
            ],
            [
                'group' => 'loyalty',
                'key' => 'referral_free_deliveries',
                'value' => '3',
                'type' => 'number',
                'label' => 'Referee Free Deliveries',
                'description' => 'Number of free deliveries awarded to referee.',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                $setting
            );
        }
    }
}

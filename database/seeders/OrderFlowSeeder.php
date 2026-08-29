<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class OrderFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds order flow and timeout settings for the order management system.
     */
    public function run(): void
    {
        // Order Confirmation Flow
        Setting::set('order_confirm_by', 'store', 'order');
        $setting = Setting::where('key', 'order_confirm_by')->first();
        if ($setting) {
            $setting->update([
                'label' => 'Order Confirmation By',
                'description' => 'Determines who confirms the order first. store: Store confirms then driver assigned. delivery_partner: Driver accepts then store confirms.',
                'type' => 'select',
                'options' => json_encode([
                    'store' => 'Store First',
                    'delivery_partner' => 'Delivery Partner First'
                ]),
            ]);
        }

        // Order Timeout Minutes
        Setting::set('order_timeout_minutes', '3', 'order');
        $setting = Setting::where('key', 'order_timeout_minutes')->first();
        if ($setting) {
            $setting->update([
                'label' => 'Order Timeout (Minutes)',
                'description' => 'Time limit for store/delivery partner to accept order before timeout action is triggered.',
                'type' => 'select',
                'options' => json_encode([
                    '1' => '1 Minute',
                    '2' => '2 Minutes',
                    '3' => '3 Minutes',
                    '5' => '5 Minutes',
                    '10' => '10 Minutes',
                    '15' => '15 Minutes',
                    '30' => '30 Minutes'
                ]),
            ]);
        }

        // Order Timeout Action
        Setting::set('order_timeout_action', 'auto_cancel', 'order');
        $setting = Setting::where('key', 'order_timeout_action')->first();
        if ($setting) {
            $setting->update([
                'label' => 'Order Timeout Action',
                'description' => 'Action to take when order times out. auto_cancel: Cancel the order. auto_accept: Automatically accept the order.',
                'type' => 'select',
                'options' => json_encode([
                    'auto_cancel' => 'Auto Cancel',
                    'auto_accept' => 'Auto Accept'
                ]),
            ]);
        }
    }
}

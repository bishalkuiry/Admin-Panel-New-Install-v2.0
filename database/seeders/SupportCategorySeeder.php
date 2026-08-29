<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupportCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Order Issue',        'icon' => 'shopping_bag',       'description' => 'Problems with your order',              'is_active' => true, 'sort_order' => 1],
            ['name' => 'Payment Problem',    'icon' => 'payment',            'description' => 'Payment failures or refund requests',   'is_active' => true, 'sort_order' => 2],
            ['name' => 'Delivery Issue',     'icon' => 'local_shipping',     'description' => 'Late or missing deliveries',            'is_active' => true, 'sort_order' => 3],
            ['name' => 'Account & Login',    'icon' => 'manage_accounts',    'description' => 'Login, profile or account issues',      'is_active' => true, 'sort_order' => 4],
            ['name' => 'App Bug / Feedback', 'icon' => 'bug_report',         'description' => 'Report a bug or share feedback',        'is_active' => true, 'sort_order' => 5],
            ['name' => 'Other',              'icon' => 'help_outline',       'description' => 'Any other query',                       'is_active' => true, 'sort_order' => 6],
        ];

        foreach ($categories as $cat) {
            DB::table('support_categories')->updateOrInsert(
                ['name' => $cat['name']],
                array_merge($cat, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}

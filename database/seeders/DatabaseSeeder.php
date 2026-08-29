<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Run seeders in order
        $this->call([
            // Core settings
            ConsolidatedSettingsSeeder::class,
            AuthSettingsSeeder::class,
            LoyaltySettingsSeeder::class,
            OrderFlowSeeder::class,
            
            // System data
            RoleSeeder::class,
            UnitsSeeder::class,
            EmailTemplateSeeder::class,
            SchedulerJobsSeeder::class,
            ZoneSeeder::class,
            
            // Demo content (users, stores, categories, brands, products, home header, app contents)
            HomeContentCatalogSeeder::class,

            // Support system
            SupportCategorySeeder::class,
        ]);
    }
}

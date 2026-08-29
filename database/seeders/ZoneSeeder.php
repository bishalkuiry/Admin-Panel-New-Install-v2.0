<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('zones')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $zones = [
            // 0. All Over World (Global coverage) — USD
            [
                'name'              => 'All Over World',
                'slug'              => 'all-over-world',
                'city'              => 'Global',
                'state'             => null,
                'country'           => 'Global',
                'currency'          => 'USD',
                'base_delivery_fee' => 5.00,
                'per_km_fee'        => 1.00,
                'min_order_amount'  => 10.00,
                'is_active'         => true,
                'sort_order'        => 0,
                // Full world bounding polygon covering lat -90 to +90 and lng -180 to +180
                // Inset by a tiny epsilon: MySQL's ST_GeomFromGeoJSON rejects exact +/-180/+/-90 boundary values
                'geojson' => '{"type":"Polygon","coordinates":[[[-179.999999,-89.999999],[179.999999,-89.999999],[179.999999,89.999999],[-179.999999,89.999999],[-179.999999,-89.999999]]]}',
            ],

            // 1. India (entire country) — INR
            [
                'name'              => 'India',
                'slug'              => 'india',
                'city'              => 'New Delhi',
                'state'             => null,
                'country'           => 'India',
                'currency'          => 'INR',
                'base_delivery_fee' => 40.00,
                'per_km_fee'        => 5.00,
                'min_order_amount'  => 100.00,
                'is_active'         => true,
                'sort_order'        => 1,
                // Rough bounding polygon for India
                'geojson' => '{"type":"Polygon","coordinates":[[[68.1,8.0],[97.4,8.0],[97.4,37.1],[68.1,37.1],[68.1,8.0]]]}',
            ],

            // 2. UAE — AED
            [
                'name'              => 'UAE',
                'slug'              => 'uae',
                'city'              => 'Dubai',
                'state'             => null,
                'country'           => 'UAE',
                'currency'          => 'AED',
                'base_delivery_fee' => 10.00,
                'per_km_fee'        => 2.00,
                'min_order_amount'  => 30.00,
                'is_active'         => true,
                'sort_order'        => 2,
                'geojson' => '{"type":"Polygon","coordinates":[[[51.5,22.6],[56.4,22.6],[56.4,26.1],[51.5,26.1],[51.5,22.6]]]}',
            ],

            // 3. USA — USD
            [
                'name'              => 'USA',
                'slug'              => 'usa',
                'city'              => 'New York',
                'state'             => null,
                'country'           => 'USA',
                'currency'          => 'USD',
                'base_delivery_fee' => 5.00,
                'per_km_fee'        => 1.50,
                'min_order_amount'  => 20.00,
                'is_active'         => true,
                'sort_order'        => 3,
                // Contiguous US bounding box
                'geojson' => '{"type":"Polygon","coordinates":[[[-125.0,24.0],[-66.0,24.0],[-66.0,49.5],[-125.0,49.5],[-125.0,24.0]]]}',
            ],

            // 4. UK — GBP
            [
                'name'              => 'United Kingdom',
                'slug'              => 'united-kingdom',
                'city'              => 'London',
                'state'             => null,
                'country'           => 'UK',
                'currency'          => 'GBP',
                'base_delivery_fee' => 3.00,
                'per_km_fee'        => 1.00,
                'min_order_amount'  => 15.00,
                'is_active'         => true,
                'sort_order'        => 4,
                'geojson' => '{"type":"Polygon","coordinates":[[[-8.0,49.8],[2.0,49.8],[2.0,60.9],[-8.0,60.9],[-8.0,49.8]]]}',
            ],

            // 5. Europe (EU) — EUR
            [
                'name'              => 'Europe',
                'slug'              => 'europe',
                'city'              => 'Berlin',
                'state'             => null,
                'country'           => 'EU',
                'currency'          => 'EUR',
                'base_delivery_fee' => 4.00,
                'per_km_fee'        => 1.20,
                'min_order_amount'  => 25.00,
                'is_active'         => true,
                'sort_order'        => 5,
                'geojson' => '{"type":"Polygon","coordinates":[[[-10.0,35.0],[40.0,35.0],[40.0,71.0],[-10.0,71.0],[-10.0,35.0]]]}',
            ],
        ];

        foreach ($zones as $zone) {
            $geojson = $zone['geojson'];
            unset($zone['geojson']);

            $zone['created_at'] = now();
            $zone['updated_at'] = now();

            // Insert with area set inline (area is NOT NULL, no default)
            DB::statement(
                "INSERT INTO zones (name, slug, city, state, country, currency,
                    base_delivery_fee, per_km_fee, min_order_amount,
                    is_active, sort_order, created_at, updated_at, area)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ST_GeomFromGeoJSON(?))",
                [
                    $zone['name'], $zone['slug'], $zone['city'], $zone['state'],
                    $zone['country'], $zone['currency'],
                    $zone['base_delivery_fee'], $zone['per_km_fee'], $zone['min_order_amount'],
                    $zone['is_active'], $zone['sort_order'],
                    $zone['created_at'], $zone['updated_at'],
                    $geojson,
                ]
            );

            $id = DB::getPdo()->lastInsertId();

            // Store coordinates as JSON for admin UI
            DB::table('zones')->where('id', $id)->update([
                'coordinates' => $geojson,
            ]);
        }

        $this->command->info('✓ 5 zones seeded: India (INR), UAE (AED), USA (USD), UK (GBP), Europe (EUR)');
    }
}

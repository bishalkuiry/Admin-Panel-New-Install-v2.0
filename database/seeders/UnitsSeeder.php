<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitsSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Gram', 'short_name' => 'g', 'type' => 'weight', 'conversion_factor' => 1, 'base_unit' => null, 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Kilogram', 'short_name' => 'kg', 'type' => 'weight', 'conversion_factor' => 1000, 'base_unit' => 'g', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Milliliter', 'short_name' => 'ml', 'type' => 'volume', 'conversion_factor' => 1, 'base_unit' => null, 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Liter', 'short_name' => 'L', 'type' => 'volume', 'conversion_factor' => 1000, 'base_unit' => 'ml', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Piece', 'short_name' => 'pcs', 'type' => 'count', 'conversion_factor' => 1, 'base_unit' => null, 'is_active' => true, 'sort_order' => 5],
            ['name' => 'Pack', 'short_name' => 'pack', 'type' => 'count', 'conversion_factor' => 1, 'base_unit' => null, 'is_active' => true, 'sort_order' => 6],
            ['name' => 'Box', 'short_name' => 'box', 'type' => 'count', 'conversion_factor' => 1, 'base_unit' => null, 'is_active' => true, 'sort_order' => 7],
            ['name' => 'Dozen', 'short_name' => 'dz', 'type' => 'count', 'conversion_factor' => 12, 'base_unit' => 'pcs', 'is_active' => true, 'sort_order' => 8],
        ];

        foreach ($units as $unit) {
            DB::table('units')->insert(array_merge($unit, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}

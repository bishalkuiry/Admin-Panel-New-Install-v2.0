<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Enums\UserRole;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(
                ['slug' => $role->value],
                [
                    'name' => $role->label(),
                    'permissions' => $role->permissions(),
                    'is_system' => true,
                ]
            );
        }
    }
}

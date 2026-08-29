<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'group' => 'mobile_app',
                'key' => 'driver_assignment_mode',
                'value' => 'manual',
                'type' => 'select',
                'options' => json_encode(['manual' => 'Manual Assignment', 'auto' => 'Auto Broadcast']),
                'label' => 'Driver Assignment Mode',
                'description' => 'Manual: Store/Admin assigns driver. Auto: Order broadcast to nearby drivers.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'mobile_app',
                'key' => 'auto_assign_radius',
                'value' => '5',
                'type' => 'number',
                'label' => 'Auto Assign Radius (km)',
                'description' => 'Radius to search for drivers in Auto mode',
                'options' => null, // Added to match column count
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => $setting['group'], 'key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['driver_assignment_mode', 'auto_assign_radius'])->delete();
    }
};

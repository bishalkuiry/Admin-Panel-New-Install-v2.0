<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add soft deletes to users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // 2. Add grace period setting to settings table
        if (Schema::hasTable('settings')) {
            // Check if it already exists to avoid unique constraint issues
            $existing = DB::table('settings')->where('key', 'auth_account_deletion_grace_period')->first();
            
            if ($existing) {
                DB::table('settings')->where('id', $existing->id)->update([
                    'group' => 'auth',
                    'value' => '7',
                    'type' => 'number',
                    'label' => 'Account Deletion Grace Period (Days)',
                    'description' => 'Number of days users can restore their account after deletion.',
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('settings')->insert([
                    'group' => 'auth',
                    'key' => 'auth_account_deletion_grace_period',
                    'value' => '7',
                    'type' => 'number',
                    'label' => 'Account Deletion Grace Period (Days)',
                    'description' => 'Number of days users can restore their account after deletion.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('key', 'auth_account_deletion_grace_period')->delete();
        }
    }
};

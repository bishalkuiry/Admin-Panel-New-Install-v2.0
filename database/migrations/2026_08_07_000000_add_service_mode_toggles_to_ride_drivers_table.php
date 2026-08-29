<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ride_drivers')) {
            Schema::table('ride_drivers', function (Blueprint $table) {
                if (!Schema::hasColumn('ride_drivers', 'is_delivery_enabled')) {
                    $table->boolean('is_delivery_enabled')->default(true)->after('status');
                }
                if (!Schema::hasColumn('ride_drivers', 'is_ride_enabled')) {
                    $table->boolean('is_ride_enabled')->default(true)->after('is_delivery_enabled');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ride_drivers')) {
            Schema::table('ride_drivers', function (Blueprint $table) {
                if (Schema::hasColumn('ride_drivers', 'is_delivery_enabled')) {
                    $table->dropColumn('is_delivery_enabled');
                }
                if (Schema::hasColumn('ride_drivers', 'is_ride_enabled')) {
                    $table->dropColumn('is_ride_enabled');
                }
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Complete zones table migration.
 * Drop the zones table in phpMyAdmin, then run: php artisan migrate
 *
 * Final schema includes:
 *  - spatial area column (GEOMETRY NOT NULL, MariaDB compatible, no SRID clause)
 *  - surge pricing columns
 *  - currency column
 *  - state/country nullable
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('zones')) {
            return;
        }

        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->nullable()->default('India');
            $table->string('currency', 10)->nullable();
            $table->json('coordinates')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('radius_km', 8, 2)->nullable();
            $table->decimal('base_delivery_fee', 10, 2)->default(0);
            $table->decimal('per_km_fee', 10, 2)->default(0);
            $table->integer('base_delivery_time_minutes')->default(30);
            $table->integer('per_km_time_minutes')->default(5);
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->boolean('surge_status')->default(false);
            $table->string('surge_type')->default('percent');
            $table->decimal('surge_value', 10, 2)->default(0);
            $table->string('surge_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['city', 'is_active']);
        });

        // Add spatial column separately — MariaDB requires this outside Blueprint
        // (no SRID clause, MariaDB compatible)
        if (!Schema::hasColumn('zones', 'area')) {
            DB::statement('ALTER TABLE zones ADD COLUMN area GEOMETRY NOT NULL AFTER `coordinates`');
            DB::statement('ALTER TABLE zones ADD SPATIAL INDEX zones_area_spatialindex (area)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};

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
        Schema::table('stores', function (Blueprint $table) {
            // Add missing column
            if (!Schema::hasColumn('stores', 'alternate_phone')) {
                $table->string('alternate_phone', 20)->nullable()->after('phone');
            }

            // Rename columns to match Model
            if (Schema::hasColumn('stores', 'packing_time') && !Schema::hasColumn('stores', 'preparation_time')) {
                $table->renameColumn('packing_time', 'preparation_time');
            }
            
            if (Schema::hasColumn('stores', 'packing_charges') && !Schema::hasColumn('stores', 'packing_charge')) {
                $table->renameColumn('packing_charges', 'packing_charge');
            }
            
            if (Schema::hasColumn('stores', 'delivery_radius') && !Schema::hasColumn('stores', 'delivery_radius_km')) {
                $table->renameColumn('delivery_radius', 'delivery_radius_km');
            }
            
            if (Schema::hasColumn('stores', 'fssai_number') && !Schema::hasColumn('stores', 'fssai_license')) {
                $table->renameColumn('fssai_number', 'fssai_license');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('alternate_phone');
            
            $table->renameColumn('preparation_time', 'packing_time');
            $table->renameColumn('packing_charge', 'packing_charges');
            $table->renameColumn('delivery_radius_km', 'delivery_radius');
            $table->renameColumn('fssai_license', 'fssai_number');
        });
    }
};

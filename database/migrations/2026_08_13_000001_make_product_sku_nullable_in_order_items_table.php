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
        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'product_sku')) {
            try {
                DB::statement('ALTER TABLE order_items MODIFY product_sku VARCHAR(255) NULL;');
            } catch (\Exception $e) {
                // Fallback for drivers that don't support raw MODIFY
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

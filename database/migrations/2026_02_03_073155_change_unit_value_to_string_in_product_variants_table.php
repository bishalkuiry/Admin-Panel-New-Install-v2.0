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
        if (!Schema::hasColumn('product_variants', 'unit_value')) {
            Schema::table('product_variants', function (Blueprint $table) {
            $table->string('unit_value')->nullable()->change();
        });
        }}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('unit_value', 10, 3)->default(1)->change();
        });
    }
};

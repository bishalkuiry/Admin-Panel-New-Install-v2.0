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
        if (!Schema::hasColumn('products', 'commission')) {
            Schema::table('products', function (Blueprint $table) {
            $table->decimal('commission', 10, 2)->nullable()->after('price')->comment('Product specific commission percentage or flat value');
        });
        }}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('commission');
        });
    }
};

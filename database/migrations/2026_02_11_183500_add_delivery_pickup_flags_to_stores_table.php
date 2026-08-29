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
            if (!Schema::hasColumn('stores', 'pickup_enabled')) {
                $table->boolean('pickup_enabled')->default(true);
            }
            if (!Schema::hasColumn('stores', 'delivery_enabled')) {
                $table->boolean('delivery_enabled')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('stores', 'pickup_enabled')) $columns[] = 'pickup_enabled';
            if (Schema::hasColumn('stores', 'delivery_enabled')) $columns[] = 'delivery_enabled';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

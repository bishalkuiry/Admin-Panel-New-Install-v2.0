<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add indexes to support high-performance prefix search (LIKE 'query%')
     * and efficient filtering on products table.
     */
    public function up(): void
    {
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['is_active', 'status', 'name']);
                $table->index(['is_active', 'status', 'sku']);
                $table->index('name');
                $table->index('sku');
                $table->index('slug');
            });
        } catch (\Throwable $e) {}

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->index(['is_active', 'name']);
            });
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'status', 'name']);
            $table->dropIndex(['is_active', 'status', 'sku']);
            $table->dropIndex(['name']);
            $table->dropIndex(['sku']);
            $table->dropIndex(['slug']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'name']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Performance optimization for ETag-based caching.
     * These indexes ensure MAX(updated_at) queries are O(1) even with millions of rows.
     */
    public function up(): void
    {
        $tables = [
            'products', 'stores', 'categories', 'app_contents',
            'category_screen_contents', 'home_header_settings',
            'home_header_tabs', 'home_header_cards'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                try {
                    Schema::table($table, function (Blueprint $t) {
                        $t->index('updated_at');
                    });
                } catch (\Throwable $e) {
                    // Index already exists or duplicate key - ignore
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop Catalog & Store Indexes
        Schema::table('products', function (Blueprint $table) { $table->dropIndex(['updated_at']); });
        Schema::table('stores', function (Blueprint $table) { $table->dropIndex(['updated_at']); });
        Schema::table('categories', function (Blueprint $table) { $table->dropIndex(['updated_at']); });

        // Drop Content Indexes
        Schema::table('app_contents', function (Blueprint $table) { $table->dropIndex(['updated_at']); });
        Schema::table('category_screen_contents', function (Blueprint $table) { $table->dropIndex(['updated_at']); });

        // Drop Header Indexes
        Schema::table('home_header_settings', function (Blueprint $table) { $table->dropIndex(['updated_at']); });
        Schema::table('home_header_tabs', function (Blueprint $table) { $table->dropIndex(['updated_at']); });
        Schema::table('home_header_cards', function (Blueprint $table) { $table->dropIndex(['updated_at']); });
    }
};

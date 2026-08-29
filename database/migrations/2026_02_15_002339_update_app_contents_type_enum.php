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
        // Modify 'type' enum to include 'store'
        // Using raw SQL is often necessary for ENUM modifications in Laravel/MySQL
        // app_contents table
        DB::statement("ALTER TABLE app_contents MODIFY COLUMN type ENUM('product', 'category', 'brand', 'media', 'store') DEFAULT 'product'");
        
        // Also update 'link_type' enum in case it doesn't have 'store' (it should, but good to be safe)
        // Schema definition showed: enum('link_type', ['none', 'product', 'category', 'brand', 'store', 'url'])
        // So link_type is likely fine, but we will ensure it.
        DB::statement("ALTER TABLE app_contents MODIFY COLUMN link_type ENUM('none', 'product', 'category', 'brand', 'store', 'url') DEFAULT 'none'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'type' enum - WARNING: This will fail if there are 'store' rows.
        // We generally don't revert enum extensions in production without data cleanup.
        DB::statement("ALTER TABLE app_contents MODIFY COLUMN type ENUM('product', 'category', 'brand', 'media') DEFAULT 'product'");
    }
};

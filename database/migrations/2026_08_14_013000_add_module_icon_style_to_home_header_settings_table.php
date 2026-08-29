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
        if (Schema::hasTable('home_header_settings')) {
            Schema::table('home_header_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('home_header_settings', 'module_icon_style')) {
                    $table->string('module_icon_style')->default('image_and_name')->after('tabs_horizontal_style');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('home_header_settings')) {
            Schema::table('home_header_settings', function (Blueprint $table) {
                if (Schema::hasColumn('home_header_settings', 'module_icon_style')) {
                    $table->dropColumn('module_icon_style');
                }
            });
        }
    }
};

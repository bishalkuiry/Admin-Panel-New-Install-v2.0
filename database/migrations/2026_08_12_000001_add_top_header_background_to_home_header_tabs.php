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
        Schema::table('home_header_tabs', function (Blueprint $table) {
            if (!Schema::hasColumn('home_header_tabs', 'header_bg_type')) {
                $table->string('header_bg_type', 20)->default('gradient')->after('use_header_name');
            }
            if (!Schema::hasColumn('home_header_tabs', 'header_gradient_color1')) {
                $table->string('header_gradient_color1', 10)->default('#FF5722')->after('header_bg_type');
            }
            if (!Schema::hasColumn('home_header_tabs', 'header_gradient_color2')) {
                $table->string('header_gradient_color2', 10)->default('#FF9800')->after('header_gradient_color1');
            }
            if (!Schema::hasColumn('home_header_tabs', 'header_gradient_style')) {
                $table->string('header_gradient_style', 30)->default('top_to_bottom')->after('header_gradient_color2');
            }
            if (!Schema::hasColumn('home_header_tabs', 'header_bg_image_url')) {
                $table->string('header_bg_image_url', 500)->nullable()->after('header_gradient_style');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_header_tabs', function (Blueprint $table) {
            $table->dropColumn([
                'header_bg_type',
                'header_gradient_color1',
                'header_gradient_color2',
                'header_gradient_style',
                'header_bg_image_url',
            ]);
        });
    }
};

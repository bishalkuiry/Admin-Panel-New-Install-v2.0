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
        if (!Schema::hasColumn('home_header_tabs', 'sticky_header_color')) {
            Schema::table('home_header_tabs', function (Blueprint $table) {
            $table->string('sticky_header_color')->default('#FFFFFF')->nullable()->after('background_url');
        });
        }}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_header_tabs', function (Blueprint $table) {
            $table->dropColumn('sticky_header_color');
        });
    }
};

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
        if (!Schema::hasColumn('home_header_settings', 'tabs_horizontal_style')) {
            Schema::table('home_header_settings', function (Blueprint $table) {
            $table->boolean('tabs_horizontal_style')->default(false)->after('cards_horizontal');
        });
        }}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_header_settings', function (Blueprint $table) {
            $table->dropColumn('tabs_horizontal_style');
        });
    }
};

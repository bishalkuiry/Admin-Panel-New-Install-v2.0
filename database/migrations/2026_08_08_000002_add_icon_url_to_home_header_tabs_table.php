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
            if (!Schema::hasColumn('home_header_tabs', 'icon_url')) {
                $table->string('icon_url', 500)->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_header_tabs', function (Blueprint $table) {
            if (Schema::hasColumn('home_header_tabs', 'icon_url')) {
                $table->dropColumn('icon_url');
            }
        });
    }
};

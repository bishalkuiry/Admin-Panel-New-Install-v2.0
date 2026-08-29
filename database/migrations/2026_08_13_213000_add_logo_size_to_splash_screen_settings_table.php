<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('splash_screen_settings')) {
            Schema::table('splash_screen_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('splash_screen_settings', 'logo_size')) {
                    $table->string('logo_size')->default('medium')->after('logo_animation');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('splash_screen_settings')) {
            Schema::table('splash_screen_settings', function (Blueprint $table) {
                if (Schema::hasColumn('splash_screen_settings', 'logo_size')) {
                    $table->dropColumn('logo_size');
                }
            });
        }
    }
};

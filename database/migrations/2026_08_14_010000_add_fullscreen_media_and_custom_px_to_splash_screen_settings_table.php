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
                if (!Schema::hasColumn('splash_screen_settings', 'fullscreen_media_url')) {
                    $table->string('fullscreen_media_url', 500)->nullable()->after('logo_url');
                }
                if (!Schema::hasColumn('splash_screen_settings', 'logo_size_px')) {
                    $table->integer('logo_size_px')->nullable()->after('logo_size');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('splash_screen_settings')) {
            Schema::table('splash_screen_settings', function (Blueprint $table) {
                if (Schema::hasColumn('splash_screen_settings', 'fullscreen_media_url')) {
                    $table->dropColumn('fullscreen_media_url');
                }
                if (Schema::hasColumn('splash_screen_settings', 'logo_size_px')) {
                    $table->dropColumn('logo_size_px');
                }
            });
        }
    }
};

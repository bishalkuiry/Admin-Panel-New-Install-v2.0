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
                $table->string('title_text')->nullable()->change();
                $table->string('subtitle_text')->nullable()->change();
                $table->string('tagline_text')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('splash_screen_settings')) {
            Schema::table('splash_screen_settings', function (Blueprint $table) {
                $table->string('title_text')->nullable(false)->change();
                $table->string('subtitle_text')->nullable(false)->change();
                $table->string('tagline_text')->nullable(false)->change();
            });
        }
    }
};

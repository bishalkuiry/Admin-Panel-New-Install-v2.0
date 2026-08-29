<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('splash_screen_settings')) {
            Schema::create('splash_screen_settings', function (Blueprint $table) {
                $table->id();
                $table->integer('active_screen_style')->default(1);
                $table->string('logo_url')->nullable();
                $table->string('logo_animation')->default('pulse');
                $table->string('background_style')->default('gradient_vibrant');
                $table->string('primary_color')->default('#F97316');
                $table->string('secondary_color')->default('#EA580C');
                $table->string('background_color')->default('#0F172A');
                $table->string('title_text')->nullable()->default('InAllCart');
                $table->string('subtitle_text')->nullable()->default('Everything Delivered to Your Doorstep');
                $table->string('tagline_text')->nullable()->default('Fast · Reliable · Premium');
                $table->string('text_color')->default('#FFFFFF');
                $table->boolean('show_tagline')->default(true);
                $table->boolean('show_loading_bar')->default(true);
                $table->text('custom_css')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('splash_screen_settings');
    }
};

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
        if (!Schema::hasTable('category_screen_contents')) {
            Schema::create('category_screen_contents', function (Blueprint $table) {
                $table->id();
                
                // Widget type: category, banner, media
                $table->string('type', 50)->default('category');
                
                // Style variant for categories (style_1, style_2, style_3, style_4)
                $table->string('style', 50)->default('style_1');
                
                // Title/Header
                $table->string('title', 100)->nullable();
                $table->boolean('show_title')->default(true);
                $table->string('subtitle', 200)->nullable();
                $table->boolean('show_subtitle')->default(false);
                
                // Data source (custom, featured, all)
                $table->string('source', 50)->default('featured');
                
                // Background styling
                $table->boolean('enable_background')->default(false);
                $table->string('background_type', 20)->nullable(); // color, image, gif, video
                $table->string('background_color', 20)->nullable();
                $table->string('background_media_url', 500)->nullable();
                
                // Grid configuration  
                $table->integer('grid_columns')->nullable()->default(2);
                $table->integer('grid_rows')->nullable()->default(2);
                
                // Animation
                $table->boolean('enable_horizontal_animation')->default(false);
                
                // Media widget fields
                $table->json('media_items')->nullable();
                $table->string('media_url', 500)->nullable();
                $table->string('media_type', 20)->nullable(); // image, gif, video
                $table->integer('media_height')->nullable()->default(200);
                $table->integer('media_width')->nullable();
                
                // Link configuration for media
                $table->string('link_type', 50)->nullable(); // none, product, category, brand, store, url
                $table->unsignedBigInteger('link_id')->nullable();
                $table->string('link_url', 500)->nullable();
                
                // Custom selection of items
                $table->json('custom_items')->nullable();
                
                // Ordering and status
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_screen_contents');
    }
};

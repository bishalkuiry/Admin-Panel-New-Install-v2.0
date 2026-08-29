<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * App Content & Home Header Tables
 * 
 * Mobile app content management including home header tabs, cards,
 * and dynamic content widgets for the mobile application.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // HOME HEADER SYSTEM
        // ============================================
        
        Schema::create('home_header_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('tabs_active')->default(false);
            $table->boolean('background_active')->default(false);
            $table->boolean('cards_active')->default(false);
            $table->boolean('cards_horizontal')->default(false);
            $table->boolean('tabs_horizontal_style')->default(false);
            $table->timestamps();
            $table->index('updated_at');
        });

        Schema::create('home_header_tabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->boolean('use_header_name')->default(false);
            $table->enum('background_type', ['image', 'video', 'gif'])->default('image');
            $table->string('background_url')->nullable();
            $table->string('sticky_header_color')->default('#FFFFFF')->nullable();
            $table->boolean('cards_horizontal')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('updated_at');
        });

        Schema::create('home_header_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tab_id')->constrained('home_header_tabs')->cascadeOnDelete();
            $table->string('image_url')->nullable();
            $table->enum('link_type', ['category', 'product', 'store', 'url'])->default('category');
            $table->unsignedBigInteger('link_id')->nullable();
            $table->string('link_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tab_id', 'is_active', 'sort_order']);
            $table->index('updated_at');
        });

        // ============================================
        // APP CONTENT WIDGETS
        // ============================================
        
        Schema::create('app_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('header_tab_id')->nullable()->constrained('home_header_tabs')->nullOnDelete();
            $table->enum('type', ['product', 'category', 'brand', 'media', 'store'])->default('product');
            $table->enum('style', ['style_1', 'style_2', 'style_3', 'style_4'])->default('style_1');
            $table->string('title')->nullable();
            $table->boolean('show_title')->default(true);
            $table->string('subtitle')->nullable();
            $table->boolean('show_subtitle')->default(false);
            $table->boolean('show_view_all')->default(false);
            $table->enum('source', ['custom', 'recent', 'featured'])->nullable();
            $table->boolean('enable_background')->default(false);
            $table->enum('background_type', ['color', 'image', 'gif', 'video'])->nullable();
            $table->string('background_color')->nullable();
            $table->string('background_media_url')->nullable();
            $table->integer('grid_columns')->default(2);
            $table->integer('grid_rows')->default(2);
            $table->boolean('enable_horizontal_animation')->default(false);
            $table->boolean('show_on_category_screen')->default(false);
            $table->boolean('show_on_tracking')->default(false);
            $table->text('media_items')->nullable();
            $table->string('media_url')->nullable();
            $table->enum('media_type', ['image', 'gif', 'video'])->nullable();
            $table->integer('media_height')->default(200);
            $table->integer('media_width')->nullable();
            $table->enum('link_type', ['none', 'product', 'category', 'brand', 'store', 'url'])->default('none');
            $table->unsignedBigInteger('link_id')->nullable();
            $table->string('link_url')->nullable();
            $table->json('custom_items')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['header_tab_id', 'is_active', 'sort_order']);
            $table->index(['type', 'is_active']);
            $table->index('updated_at');
        });

        Schema::create('category_screen_contents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->default('category');
            $table->string('style', 50)->default('style_1');
            $table->string('title', 100)->nullable();
            $table->boolean('show_title')->default(true);
            $table->string('subtitle', 200)->nullable();
            $table->boolean('show_subtitle')->default(false);
            $table->string('source', 50)->default('featured');
            $table->boolean('enable_background')->default(false);
            $table->string('background_type', 20)->nullable();
            $table->string('background_color', 20)->nullable();
            $table->string('background_media_url', 500)->nullable();
            $table->integer('grid_columns')->nullable()->default(2);
            $table->integer('grid_rows')->nullable()->default(2);
            $table->boolean('enable_horizontal_animation')->default(false);
            $table->json('media_items')->nullable();
            $table->string('media_url', 500)->nullable();
            $table->string('media_type', 20)->nullable();
            $table->integer('media_height')->nullable()->default(200);
            $table->integer('media_width')->nullable();
            $table->string('link_type', 50)->nullable();
            $table->unsignedBigInteger('link_id')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->json('custom_items')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('updated_at');
        });


        // Insert default home header settings
        \DB::table('home_header_settings')->insert([
            'tabs_active' => false,
            'background_active' => false,
            'cards_active' => false,
            'cards_horizontal' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('category_screen_contents');
        Schema::dropIfExists('app_contents');
        Schema::dropIfExists('home_header_cards');
        Schema::dropIfExists('home_header_tabs');
        Schema::dropIfExists('home_header_settings');
    }
};

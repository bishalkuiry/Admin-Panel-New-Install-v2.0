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
        if (!Schema::hasTable('popups')) {
            Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('media_url');
            $table->enum('media_type', ['image', 'gif'])->default('image');
            $table->enum('status', ['draft', 'scheduled', 'active', 'expired', 'inactive'])->default('active');
            $table->enum('position', ['center', 'top', 'bottom', 'floating', 'full_screen'])->default('center');
            $table->boolean('show_close_button')->default(true);
            $table->enum('click_action', ['none', 'url', 'page', 'product', 'category', 'store', 'coupon', 'membership'])->default('none');
            $table->string('click_action_target')->nullable();
            $table->enum('display_trigger', [
                'first_app_open',
                'second_app_open',
                'every_app_open',
                'once_per_day',
                'once_per_session',
                'once_per_user',
                'after_x_seconds',
                'after_x_opens',
                'on_app_exit',
                'after_order_completion',
                'after_login',
                'before_checkout',
                'after_checkout',
                'specific_product_in_cart',
                'cart_amount_reached'
            ])->default('every_app_open');
            $table->string('trigger_value')->nullable(); // seconds, cart amount, product ID, open count
            $table->enum('audience_type', ['all', 'new', 'existing', 'vip', 'non_vip'])->default('all');
            $table->json('zone_ids')->nullable();
            $table->json('country_ids')->nullable();
            $table->json('language_codes')->nullable();
            $table->json('store_ids')->nullable();
            $table->json('category_ids')->nullable();
            $table->json('product_ids')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};

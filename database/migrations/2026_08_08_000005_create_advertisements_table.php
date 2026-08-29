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
        // 1. Advertisements Table
        if (!Schema::hasTable('advertisements')) {
            Schema::create('advertisements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id');
                $table->enum('ad_type', ['banner', 'featured_store', 'sponsored_product'])->default('banner');
                $table->string('title');
                $table->string('image_url')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->decimal('price', 10, 2)->default(0.00);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->enum('status', ['pending', 'active', 'expired', 'rejected'])->default('pending');
                $table->timestamps();

                $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            });
        }

        // 2. User Membership Plans Table
        if (!Schema::hasTable('membership_plans')) {
            Schema::create('membership_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g., "Gold VIP", "Platinum VIP"
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->default(0.00);
                $table->integer('duration_days')->default(30);
                $table->decimal('cashback_percentage', 5, 2)->default(0.00);
                $table->boolean('free_delivery')->default(false);
                $table->boolean('priority_support')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. User Active Memberships Table
        if (!Schema::hasTable('user_memberships')) {
            Schema::create('user_memberships', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('membership_plan_id');
                $table->timestamp('starts_at')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('membership_plan_id')->references('id')->on('membership_plans')->onDelete('cascade');
            });
        }

        // 4. Store Subscription Plans Table
        if (!Schema::hasTable('store_subscription_plans')) {
            Schema::create('store_subscription_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g. "Basic Store", "Pro Store"
                $table->decimal('price', 10, 2)->default(0.00);
                $table->integer('duration_days')->default(30);
                $table->integer('max_products')->default(100);
                $table->decimal('commission_rate', 5, 2)->default(10.00);
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
        Schema::dropIfExists('store_subscription_plans');
        Schema::dropIfExists('user_memberships');
        Schema::dropIfExists('membership_plans');
        Schema::dropIfExists('advertisements');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seller_ad_plans')) {
            Schema::create('seller_ad_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('ad_type', ['banner_ad', 'featured_store', 'sponsored_product'])->default('banner_ad');
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->default(0.00);
                $table->enum('billing_type', ['one_time', 'daily', 'weekly', 'monthly'])->default('one_time');
                $table->integer('duration_days')->default(7);
                $table->string('placement')->default('home_top');
                $table->integer('impression_limit')->nullable();
                $table->integer('click_limit')->nullable();
                $table->integer('priority_level')->default(1);
                $table->json('targeting_options')->nullable();
                $table->string('banner_size')->nullable();
                $table->integer('max_items')->default(1);
                $table->boolean('auto_renew')->default(false);
                $table->boolean('status')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('seller_ad_purchases')) {
            Schema::create('seller_ad_purchases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plan_id')->constrained('seller_ad_plans')->onDelete('cascade');
                $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
                $table->string('title')->nullable();
                $table->string('image')->nullable();
                $table->string('target_url')->nullable();
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
                $table->enum('status', ['pending', 'active', 'rejected', 'expired'])->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->integer('impressions_count')->default(0);
                $table->integer('clicks_count')->default(0);
                $table->decimal('amount_paid', 10, 2)->default(0.00);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_ad_purchases');
        Schema::dropIfExists('seller_ad_plans');
    }
};

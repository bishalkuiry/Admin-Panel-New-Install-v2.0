<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * E-Commerce Core Tables
 * 
 * Core e-commerce entities: zones, stores, categories, brands, attributes, units.
 * These are the foundational tables for the marketplace structure.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // DELIVERY ZONES
        // ============================================
        
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->nullable()->default('India');
            $table->string('currency', 10)->nullable();
            $table->json('coordinates')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('radius_km', 8, 2)->nullable();
            $table->decimal('base_delivery_fee', 10, 2)->default(0);
            $table->decimal('per_km_fee', 10, 2)->default(0);
            $table->integer('base_delivery_time_minutes')->default(30);
            $table->integer('per_km_time_minutes')->default(5);
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->boolean('surge_status')->default(false);
            $table->string('surge_type')->default('percent');
            $table->decimal('surge_value', 10, 2)->default(0);
            $table->string('surge_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['city', 'is_active']);
            $table->index('updated_at');
        });

        // Add spatial column separately — MariaDB/MySQL compatibility
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE zones ADD COLUMN area GEOMETRY NOT NULL AFTER `coordinates`');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE zones ADD SPATIAL INDEX zones_area_spatialindex (area)');

        // ============================================
        // CATEGORIES
        // ============================================
        
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('banner')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->decimal('commission_percent', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'is_active']);
            $table->index('sort_order');
            $table->index('updated_at');
            $table->index(['is_active', 'name']);
        });

        // ============================================
        // BRANDS
        // ============================================
        
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_featured']);
        });

        // ============================================
        // STORES & SELLERS
        // ============================================
        
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('store_id')->unique()->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('alternate_phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country')->default('India');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('fssai_license')->nullable();
            $table->string('business_type')->nullable();
            $table->json('opening_hours')->nullable();
            $table->integer('preparation_time')->default(15);
            $table->decimal('packing_charge', 10, 2)->default(0);
            $table->decimal('delivery_radius_km', 8, 2)->nullable();
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->enum('delivery_type', ['global', 'custom', 'self'])->default('global');
            $table->enum('delivery_method', ['flat', 'percentage', 'per_km'])->nullable();
            $table->decimal('delivery_flat_rate', 10, 2)->nullable();
            $table->decimal('delivery_percentage', 5, 2)->nullable();
            $table->decimal('delivery_per_km_rate', 10, 2)->nullable();
            $table->boolean('store_free_delivery')->default(false);
            $table->boolean('pickup_enabled')->default(true);
            $table->boolean('delivery_enabled')->default(true);
            $table->string('status')->default('pending');
            $table->string('kyc_status')->default('pending');
            $table->boolean('is_online')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->string('discount_text')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->integer('order_count')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->string('commission_type')->default('percentage');
            $table->decimal('payout_balance', 12, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->json('settings')->nullable();
            $table->json('payment_methods')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_id', 'status']);
            $table->index(['city', 'status']);
            $table->index(['status', 'kyc_status']);
            $table->index(['is_online', 'is_featured']);
            $table->index('updated_at');
        });

        Schema::create('store_zone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->decimal('delivery_fee_override', 10, 2)->nullable();
            $table->integer('delivery_time_override')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['store_id', 'zone_id']);
        });

        Schema::create('store_brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['store_id', 'brand_id']);
        });

        Schema::create('store_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('staff');
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['store_id', 'user_id']);
            $table->index(['user_id', 'is_active']);
        });

        Schema::create('store_kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('document_number')->nullable();
            $table->string('file_path');
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'document_type']);
            $table->index('status');
        });

        Schema::create('store_commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('percentage');
            $table->decimal('value', 10, 2);
            $table->decimal('min_amount', 10, 2)->nullable();
            $table->decimal('max_amount', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['store_id', 'category_id']);
        });

        Schema::create('store_payouts', function (Blueprint $table) {
            $table->id();
            $table->string('payout_id')->unique();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('commission_deducted', 12, 2)->default(0);
            $table->decimal('tax_deducted', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);
        });

        Schema::create('store_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'action']);
            $table->index(['entity_type', 'entity_id']);
        });

        // ============================================
        // PRODUCT ATTRIBUTES & UNITS
        // ============================================
        
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('select');
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->string('slug');
            $table->string('color_code')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['attribute_id', 'sort_order']);
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name');
            $table->string('type')->default('weight');
            $table->decimal('conversion_factor', 10, 6)->default(1);
            $table->string('base_unit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('type');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('product');
            $table->timestamps();

            $table->index('type');
        });

        // Add active store reference to users (after stores table is created)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('active_store_id')->nullable()->constrained('stores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
        Schema::dropIfExists('units');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('store_activity_logs');
        Schema::dropIfExists('store_payouts');
        Schema::dropIfExists('store_commission_rules');
        Schema::dropIfExists('store_kyc_documents');
        Schema::dropIfExists('store_staff');
        Schema::dropIfExists('store_brands');
        Schema::dropIfExists('store_zone');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('zones');
    }
};

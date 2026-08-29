<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Order Management Tables
 * 
 * All order-related tables including orders, addresses, coupons, and order tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // CUSTOMER ADDRESSES
        // ============================================
        
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('home');
            $table->string('name');
            $table->string('phone', 20);
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('landmark')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country')->default('India');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_default']);
        });

        // ============================================
        // COUPONS & DISCOUNTS
        // ============================================
        
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->string('type');
            $table->decimal('value', 10, 2);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_limit_per_user')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_first_order_only')->default(false);
            $table->json('applicable_categories')->nullable();
            $table->json('applicable_products')->nullable();
            $table->json('applicable_stores')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'is_active']);
            $table->index(['starts_at', 'expires_at']);
        });

        // ============================================
        // ORDERS
        // ============================================
        
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('packing_charges', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('tip', 10, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('payment_id')->nullable();
            $table->decimal('wallet_amount', 12, 2)->default(0);
            $table->string('transaction_id')->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->foreignId('delivery_partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // Real-time tracking fields
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->timestamp('last_location_update')->nullable();
            
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('packed_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('out_for_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['store_id', 'status']);
            $table->index('order_number');
            $table->index(['status', 'payment_status']);
            $table->index('delivery_partner_id');
            $table->index('wallet_amount');
            $table->index('last_location_update');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->string('variant_name')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('mrp', 10, 2)->nullable();
            $table->integer('quantity');
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('commission', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->json('options')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'product_id']);
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        Schema::create('coupon_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();

            $table->index(['coupon_id', 'user_id']);
        });

        // ============================================
        // DELIVERY CALCULATION LOGS (Enterprise Feature)
        // ============================================
        
        Schema::create('delivery_calculation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('cart_id')->nullable(); // No FK constraint - carts created in later migration
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            
            // Strategy used
            $table->enum('strategy_used', ['zone', 'store', 'global'])->index();
            $table->string('calculation_method')->nullable(); // 'flat', 'percentage', 'per_km', 'override'
            
            // Input data
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            
            // Calculation breakdown
            $table->json('calculation_steps')->nullable();
            $table->json('metadata')->nullable(); // Additional context
            
            // Results
            $table->decimal('base_fee', 10, 2)->default(0);
            $table->decimal('distance_fee', 10, 2)->default(0);
            $table->decimal('zone_fee', 10, 2)->default(0);
            $table->decimal('final_charge', 10, 2);
            
            // Flags
            $table->boolean('was_free_delivery')->default(false);
            $table->boolean('was_cached')->default(false);
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['strategy_used', 'created_at']);
            $table->index('cart_id'); // Index for cart lookups even without FK
            $table->index(['order_id', 'created_at']);
            $table->index(['store_id', 'created_at']);
            $table->index(['zone_id', 'created_at']);
        });
        
        // ============================================
        // DELIVERY TRACKING (Real-time Location History)
        // ============================================
        
        Schema::create('delivery_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_partner_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('speed', 5, 2)->nullable(); // km/h
            $table->decimal('accuracy', 8, 2)->nullable(); // meters
            $table->string('status')->default('moving'); // moving, stopped, arrived
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->index(['order_id', 'recorded_at']);
            $table->index('delivery_partner_id');
            $table->index('recorded_at');
        });

        // ============================================
        // REVIEWS
        // ============================================
        
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('rating');
            $table->tinyInteger('delivery_rating')->nullable();
            $table->text('comment')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'is_approved']);
            $table->index(['user_id', 'product_id']);
        });

        // ============================================
        // ORDER CHATS (Firebase Realtime DB system)
        // ============================================
        
        Schema::create('order_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('firebase_chat_id')->unique(); // Firebase Realtime DB chat ID (format: {orderId}_{chatType}_{timestamp})
            $table->enum('chat_type', ['customer_delivery', 'customer_seller']); // Type of chat
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('participant_id')->nullable()->constrained('users')->onDelete('set null'); // Delivery partner or seller
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null'); // Admin who joined the chat
            $table->timestamp('admin_joined_at')->nullable(); // When admin joined the chat
            $table->timestamp('last_message_at')->nullable();
            $table->text('last_message')->nullable();
            $table->integer('unread_count_customer')->default(0);
            $table->integer('unread_count_participant')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['order_id', 'chat_type']);
            $table->index('customer_id');
            $table->index('participant_id');
            $table->index('admin_id');
        });


        // ============================================
        // INVOICES
        // ============================================

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('business_name')->nullable();
            $table->text('business_address')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('business_email')->nullable();
            $table->string('business_gstin')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'issued', 'paid', 'cancelled'])->default('issued');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
        });

        // ============================================
        // TAXES
        // ============================================

        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('applies_to', ['customer', 'store', 'delivery_partner'])->default('customer');
            $table->enum('calculation_type', ['fixed', 'percentage'])->default('percentage');
            $table->decimal('value', 10, 4);
            $table->decimal('min_order_value', 10, 2)->nullable();
            $table->decimal('max_order_value', 10, 2)->nullable();
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('store_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_inclusive')->default(false);
            $table->timestamps();
        });

        // Data cleanup: Update legacy status
        \Illuminate\Support\Facades\DB::table('orders')
            ->where('status', 'processing')
            ->update(['status' => 'confirmed']);
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('order_chats');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('delivery_tracking');
        Schema::dropIfExists('delivery_calculation_logs');
        Schema::dropIfExists('coupon_usage');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('addresses');
    }
};

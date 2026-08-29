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
        // 1. Food Variation Groups
        if (!Schema::hasTable('food_variation_groups')) {
            Schema::create('food_variation_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('name'); // e.g. "Select Crust", "Extra Toppings"
                $table->boolean('is_required')->default(false);
                $table->enum('selection_type', ['single', 'multiple'])->default('single');
                $table->integer('min_selection')->default(0);
                $table->integer('max_selection')->default(1);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }

        // 2. Food Variation Options
        if (!Schema::hasTable('food_variation_options')) {
            Schema::create('food_variation_options', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('food_variation_group_id');
                $table->string('option_name'); // e.g. "Cheese Burst", "Thin Crust"
                $table->decimal('price', 10, 2)->default(0.00);
                $table->boolean('is_default')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('food_variation_group_id')->references('id')->on('food_variation_groups')->onDelete('cascade');
            });
        }

        // 3. Product Returns & Replacements
        if (!Schema::hasTable('product_returns')) {
            Schema::create('product_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_number')->unique();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('order_item_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id')->nullable();
                $table->enum('type', ['return', 'replacement'])->default('return');
                $table->string('reason');
                $table->text('comments')->nullable();
                $table->json('attachments')->nullable(); // Photo/Video URLs
                $table->enum('status', ['pending', 'approved', 'rejected', 'pickup_scheduled', 'picked_up', 'refunded', 'replaced'])->default('pending');
                $table->decimal('refund_amount', 10, 2)->default(0.00);
                $table->text('admin_notes')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 4. Dynamic eKYC Form Fields
        if (!Schema::hasTable('kyc_form_fields')) {
            Schema::create('kyc_form_fields', function (Blueprint $table) {
                $table->id();
                $table->enum('target_role', ['vendor', 'rider'])->default('vendor');
                $table->string('field_name'); // e.g., "aadhaar_number", "driving_license_photo"
                $table->string('field_label'); // e.g., "Aadhaar Card Number"
                $table->enum('field_type', ['text', 'number', 'file', 'date', 'select'])->default('text');
                $table->boolean('is_required')->default(true);
                $table->json('options')->nullable(); // For select type
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 5. Dynamic eKYC Submissions
        if (!Schema::hasTable('kyc_submissions')) {
            Schema::create('kyc_submissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->enum('role', ['vendor', 'rider'])->default('vendor');
                $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->json('data')->nullable(); // Key-value pairs of field_name => input/file_url
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 6. Customer Complaints
        if (!Schema::hasTable('customer_complaints')) {
            Schema::create('customer_complaints', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number')->unique();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('driver_id')->nullable();
                $table->string('category'); // e.g. "Damaged Item", "Wrong Quantity", "Bad Quality"
                $table->text('description');
                $table->json('attachments')->nullable(); // Photos/Videos
                $table->enum('status', ['open', 'under_review', 'approved', 'rejected', 'resolved'])->default('open');
                $table->enum('action_taken', ['none', 'refund_customer', 'penalize_vendor', 'penalize_driver', 'suspend_account'])->default('none');
                $table->decimal('penalty_amount', 10, 2)->default(0.00);
                $table->text('admin_response')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 7. Product extensions for Video, Return/Replacement Policies, Warranty
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'video_type')) {
                $table->enum('video_type', ['upload', 'youtube', 'vimeo', 'url'])->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'video_url')) {
                $table->string('video_url', 500)->nullable()->after('video_type');
            }
            if (!Schema::hasColumn('products', 'return_period_days')) {
                $table->integer('return_period_days')->default(7)->after('video_url');
            }
            if (!Schema::hasColumn('products', 'replacement_period_days')) {
                $table->integer('replacement_period_days')->default(7)->after('return_period_days');
            }
            if (!Schema::hasColumn('products', 'warranty_summary')) {
                $table->string('warranty_summary')->nullable()->after('replacement_period_days');
            }
            if (!Schema::hasColumn('products', 'guarantee_summary')) {
                $table->string('guarantee_summary')->nullable()->after('warranty_summary');
            }
            if (!Schema::hasColumn('products', 'delivered_by_lead_hours')) {
                $table->integer('delivered_by_lead_hours')->default(24)->after('guarantee_summary');
            }
        });

        // 8. Order extensions for Order Notes, Delivery OTP, Scheduled Orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_notes')) {
                $table->text('order_notes')->nullable();
            }
            if (!Schema::hasColumn('orders', 'delivery_otp')) {
                $table->string('delivery_otp', 6)->nullable();
            }
            if (!Schema::hasColumn('orders', 'otp_verified_at')) {
                $table->timestamp('otp_verified_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'module_type')) {
                $table->string('module_type', 50)->default('grocery');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_complaints');
        Schema::dropIfExists('kyc_submissions');
        Schema::dropIfExists('kyc_form_fields');
        Schema::dropIfExists('product_returns');
        Schema::dropIfExists('food_variation_options');
        Schema::dropIfExists('food_variation_groups');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'video_type',
                'video_url',
                'return_period_days',
                'replacement_period_days',
                'warranty_summary',
                'guarantee_summary',
                'delivered_by_lead_hours',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_notes',
                'delivery_otp',
                'otp_verified_at',
                'scheduled_at',
                'module_type',
            ]);
        });
    }
};

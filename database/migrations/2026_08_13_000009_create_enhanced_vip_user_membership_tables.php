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
        // 1. Enhance membership_plans table
        if (Schema::hasTable('membership_plans')) {
            Schema::table('membership_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('membership_plans', 'short_description')) {
                    $table->string('short_description')->nullable()->after('description');
                }
                if (!Schema::hasColumn('membership_plans', 'logo')) {
                    $table->string('logo')->nullable()->after('short_description');
                }
                if (!Schema::hasColumn('membership_plans', 'badge_icon')) {
                    $table->string('badge_icon')->nullable()->after('logo');
                }
                if (!Schema::hasColumn('membership_plans', 'banner_image')) {
                    $table->string('banner_image')->nullable()->after('badge_icon');
                }
                if (!Schema::hasColumn('membership_plans', 'monthly_price')) {
                    $table->decimal('monthly_price', 10, 2)->nullable()->after('price');
                }
                if (!Schema::hasColumn('membership_plans', 'yearly_price')) {
                    $table->decimal('yearly_price', 10, 2)->nullable()->after('monthly_price');
                }
                if (!Schema::hasColumn('membership_plans', 'trial_period_days')) {
                    $table->integer('trial_period_days')->default(0)->after('duration_days');
                }
                if (!Schema::hasColumn('membership_plans', 'auto_renewal')) {
                    $table->boolean('auto_renewal')->default(true)->after('trial_period_days');
                }
                if (!Schema::hasColumn('membership_plans', 'max_free_deliveries_per_month')) {
                    $table->integer('max_free_deliveries_per_month')->default(10)->after('free_delivery');
                }
                if (!Schema::hasColumn('membership_plans', 'min_order_for_free_delivery')) {
                    $table->decimal('min_order_for_free_delivery', 10, 2)->default(0.00)->after('max_free_deliveries_per_month');
                }
                if (!Schema::hasColumn('membership_plans', 'max_delivery_distance_km')) {
                    $table->decimal('max_delivery_distance_km', 8, 2)->default(15.00)->after('min_order_for_free_delivery');
                }
                if (!Schema::hasColumn('membership_plans', 'extra_discount_percentage')) {
                    $table->decimal('extra_discount_percentage', 5, 2)->default(0.00)->after('max_delivery_distance_km');
                }
                if (!Schema::hasColumn('membership_plans', 'max_discount_per_order')) {
                    $table->decimal('max_discount_per_order', 10, 2)->default(100.00)->after('extra_discount_percentage');
                }
                if (!Schema::hasColumn('membership_plans', 'max_cashback_per_month')) {
                    $table->decimal('max_cashback_per_month', 10, 2)->default(500.00)->after('cashback_percentage');
                }
                if (!Schema::hasColumn('membership_plans', 'eligible_stores')) {
                    $table->json('eligible_stores')->nullable()->after('priority_support');
                }
                if (!Schema::hasColumn('membership_plans', 'eligible_categories')) {
                    $table->json('eligible_categories')->nullable()->after('eligible_stores');
                }
                if (!Schema::hasColumn('membership_plans', 'eligible_zones')) {
                    $table->json('eligible_zones')->nullable()->after('eligible_categories');
                }
                if (!Schema::hasColumn('membership_plans', 'benefits_config')) {
                    $table->json('benefits_config')->nullable()->after('eligible_zones');
                }
                if (!Schema::hasColumn('membership_plans', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('is_active');
                }
            });
        }

        // 2. Enhance user_memberships table
        if (Schema::hasTable('user_memberships')) {
            Schema::table('user_memberships', function (Blueprint $table) {
                if (!Schema::hasColumn('user_memberships', 'payment_method')) {
                    $table->string('payment_method')->default('wallet')->after('status');
                }
                if (!Schema::hasColumn('user_memberships', 'payment_status')) {
                    $table->string('payment_status')->default('paid')->after('payment_method');
                }
                if (!Schema::hasColumn('user_memberships', 'payment_id')) {
                    $table->string('payment_id')->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('user_memberships', 'amount_paid')) {
                    $table->decimal('amount_paid', 10, 2)->default(0.00)->after('payment_id');
                }
                if (!Schema::hasColumn('user_memberships', 'free_deliveries_used_this_month')) {
                    $table->integer('free_deliveries_used_this_month')->default(0)->after('amount_paid');
                }
                if (!Schema::hasColumn('user_memberships', 'cashback_earned_this_month')) {
                    $table->decimal('cashback_earned_this_month', 10, 2)->default(0.00)->after('free_deliveries_used_this_month');
                }
                if (!Schema::hasColumn('user_memberships', 'total_discount_saved')) {
                    $table->decimal('total_discount_saved', 10, 2)->default(0.00)->after('cashback_earned_this_month');
                }
                if (!Schema::hasColumn('user_memberships', 'auto_renew')) {
                    $table->boolean('auto_renew')->default(true)->after('total_discount_saved');
                }
                if (!Schema::hasColumn('user_memberships', 'renewal_count')) {
                    $table->integer('renewal_count')->default(0)->after('auto_renew');
                }
                if (!Schema::hasColumn('user_memberships', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('renewal_count');
                }
            });
        }

        // 3. Create membership_pages table for Page Builder
        if (!Schema::hasTable('membership_pages')) {
            Schema::create('membership_pages', function (Blueprint $table) {
                $table->id();
                $table->string('title')->default('VIP User Membership Page');
                $table->string('slug')->unique();
                $table->json('sections_data')->nullable();
                $table->boolean('is_published')->default(true);
                $table->timestamps();
            });
        }

        // 4. Create user_membership_histories table
        if (!Schema::hasTable('user_membership_histories')) {
            Schema::create('user_membership_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('membership_plan_id')->nullable()->constrained('membership_plans')->onDelete('set null');
                $table->string('action'); // PURCHASE, RENEWAL, EXPIRY, CANCEL, ADMIN_MANUAL
                $table->decimal('amount', 10, 2)->default(0.00);
                $table->string('payment_method')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_membership_histories');
        Schema::dropIfExists('membership_pages');
    }
};

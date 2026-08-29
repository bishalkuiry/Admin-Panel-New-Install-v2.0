<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('store_subscription_plans')) {
            Schema::table('store_subscription_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('store_subscription_plans', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }
                if (!Schema::hasColumn('store_subscription_plans', 'billing_cycle')) {
                    $table->string('billing_cycle', 20)->default('monthly')->after('price');
                }
                if (!Schema::hasColumn('store_subscription_plans', 'features')) {
                    $table->json('features')->nullable()->after('commission_rate');
                }
                if (!Schema::hasColumn('store_subscription_plans', 'trial_period_days')) {
                    $table->integer('trial_period_days')->default(0)->after('features');
                }
                if (!Schema::hasColumn('store_subscription_plans', 'auto_renew')) {
                    $table->boolean('auto_renew')->default(true)->after('trial_period_days');
                }
            });
        }

        if (Schema::hasTable('store_subscriptions')) {
            Schema::table('store_subscriptions', function (Blueprint $table) {
                if (!Schema::hasColumn('store_subscriptions', 'payment_method')) {
                    $table->string('payment_method', 50)->nullable()->after('is_trial');
                }
                if (!Schema::hasColumn('store_subscriptions', 'payment_id')) {
                    $table->string('payment_id', 100)->nullable()->after('payment_method');
                }
                if (!Schema::hasColumn('store_subscriptions', 'price_paid')) {
                    $table->decimal('price_paid', 10, 2)->default(0.00)->after('payment_id');
                }
                if (!Schema::hasColumn('store_subscriptions', 'auto_renew')) {
                    $table->boolean('auto_renew')->default(true)->after('price_paid');
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};

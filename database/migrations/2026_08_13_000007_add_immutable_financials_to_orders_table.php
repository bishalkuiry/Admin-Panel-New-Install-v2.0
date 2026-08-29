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
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'store_earning')) {
                    $table->decimal('store_earning', 12, 2)->default(0.00)->after('wallet_amount');
                }
                if (!Schema::hasColumn('orders', 'admin_commission')) {
                    $table->decimal('admin_commission', 12, 2)->default(0.00)->after('store_earning');
                }
                if (!Schema::hasColumn('orders', 'driver_earning')) {
                    $table->decimal('driver_earning', 12, 2)->default(0.00)->after('admin_commission');
                }
                if (!Schema::hasColumn('orders', 'coupon_funded_by')) {
                    $table->enum('coupon_funded_by', ['admin', 'seller', 'shared'])->default('admin')->after('driver_earning');
                }
            });
        }

        if (Schema::hasTable('coupons') && !Schema::hasColumn('coupons', 'funded_by')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->enum('funded_by', ['admin', 'seller', 'shared'])->default('admin')->after('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn(['store_earning', 'admin_commission', 'driver_earning', 'coupon_funded_by']);
            });
        }

        if (Schema::hasTable('coupons') && Schema::hasColumn('coupons', 'funded_by')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropColumn('funded_by');
            });
        }
    }
};

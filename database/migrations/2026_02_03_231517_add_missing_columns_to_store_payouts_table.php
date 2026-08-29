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
        Schema::table('store_payouts', function (Blueprint $table) {
            if (!Schema::hasColumn('store_payouts', 'payout_id')) {
                $table->string('payout_id')->unique()->after('id');
            }
            if (!Schema::hasColumn('store_payouts', 'commission_deducted')) {
                $table->decimal('commission_deducted', 12, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('store_payouts', 'tax_deducted')) {
                $table->decimal('tax_deducted', 12, 2)->default(0)->after('commission_deducted');
            }
            if (!Schema::hasColumn('store_payouts', 'net_amount')) {
                $table->decimal('net_amount', 12, 2)->default(0)->after('tax_deducted');
            }
            if (!Schema::hasColumn('store_payouts', 'period_start')) {
                $table->date('period_start')->nullable()->after('net_amount');
            }
            if (!Schema::hasColumn('store_payouts', 'period_end')) {
                $table->date('period_end')->nullable()->after('period_start');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_payouts', function (Blueprint $table) {
            $table->dropColumn([
                'payout_id',
                'commission_deducted',
                'tax_deducted',
                'net_amount',
                'period_start',
                'period_end'
            ]);
        });
    }
};

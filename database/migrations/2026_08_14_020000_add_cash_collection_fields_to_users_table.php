<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'cash_in_hand')) {
                $table->decimal('cash_in_hand', 12, 2)->default(0.00)->after('payout_balance');
            }
            if (!Schema::hasColumn('users', 'pending_cash_deposit')) {
                $table->decimal('pending_cash_deposit', 12, 2)->default(0.00)->after('cash_in_hand');
            }
            if (!Schema::hasColumn('users', 'blocked_amount')) {
                $table->decimal('blocked_amount', 12, 2)->default(0.00)->after('pending_cash_deposit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cash_in_hand', 'pending_cash_deposit', 'blocked_amount']);
        });
    }
};

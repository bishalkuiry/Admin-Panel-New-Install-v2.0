<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Simplify order status columns:
 * - Remove: processing_at, shipped_at, returned_at
 * - Add: picked_up_at
 * (packed_at and out_for_delivery_at already exist)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add picked_up_at column (new status timestamp)
            if (!Schema::hasColumn('orders', 'picked_up_at')) {
                $table->timestamp('picked_up_at')->nullable()->after('packed_at');
            }

            // Drop deprecated columns
            $dropColumns = [];
            if (Schema::hasColumn('orders', 'processing_at')) {
                $dropColumns[] = 'processing_at';
            }
            if (Schema::hasColumn('orders', 'shipped_at')) {
                $dropColumns[] = 'shipped_at';
            }
            if (Schema::hasColumn('orders', 'returned_at')) {
                $dropColumns[] = 'returned_at';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Re-add deprecated columns
            $table->timestamp('processing_at')->nullable()->after('confirmed_at');
            $table->timestamp('shipped_at')->nullable()->after('packed_at');
            $table->timestamp('returned_at')->nullable()->after('cancelled_at');

            // Drop the new column
            if (Schema::hasColumn('orders', 'picked_up_at')) {
                $table->dropColumn('picked_up_at');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add idempotency_key to orders table.
 *
 * Prevents duplicate orders caused by network retries or double-taps.
 * The client generates a UUID v4 per checkout attempt and sends it as
 * `idempotency_key` in the POST /api/v1/orders request body.
 * The controller caches the response for 5 minutes under this key so
 * retries receive the original response without re-running order logic.
 *
 * The column is nullable (existing orders have no key) and unique per user
 * to allow different users to coincidentally use the same key string.
 * A composite unique index on (user_id, idempotency_key) enforces this.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'idempotency_key')) {
            Schema::table('orders', function (Blueprint $table) {
                // Nullable so existing rows are unaffected.
                $table->string('idempotency_key', 64)->nullable()->after('order_number');

                // Unique per user — two different users may use the same key string.
                $table->unique(['user_id', 'idempotency_key'], 'orders_user_idempotency_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'idempotency_key')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropUnique('orders_user_idempotency_unique');
                $table->dropColumn('idempotency_key');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fix AUTO_INCREMENT missing on ALL id columns across every table.
 *
 * On some shared-hosting MariaDB/MySQL setups the BIGINT UNSIGNED id column
 * is created without AUTO_INCREMENT, causing inserts to fail with:
 *   "Field 'id' doesn't have a default value"
 *
 * SAFE TO RUN MULTIPLE TIMES:
 *   Each table is checked via information_schema first.
 *   If AUTO_INCREMENT is already present the ALTER is skipped entirely.
 *   If the table doesn't exist it is silently skipped.
 */
return new class extends Migration
{
    /**
     * Every table in the application that uses $table->id()
     * Includes core app tables + ride-sharing plugin tables.
     */
    protected array $tables = [
        // ── System ──────────────────────────────────────────────
        'users',
        'personal_access_tokens',
        'migrations',
        'jobs',
        'failed_jobs',
        'settings',
        'static_pages',
        'email_templates',
        'scheduler_jobs',
        'delivery_partner_payouts',
        'user_points',
        'point_transactions',
        'referrals',

        // ── E-commerce core ──────────────────────────────────────
        'zones',
        'categories',
        'brands',
        'stores',
        'store_zone',
        'store_brands',
        'store_staff',
        'store_kyc_documents',
        'store_commission_rules',
        'store_payouts',
        'store_activity_logs',
        'attributes',
        'attribute_values',
        'units',
        'tags',

        // ── Product catalog ──────────────────────────────────────
        'products',
        'product_images',
        'product_variants',
        'product_variant_attributes',
        'product_attributes',
        'product_tag',
        'product_attachments',
        'product_imports',
        'store_products',

        // ── Order management ─────────────────────────────────────
        'addresses',
        'coupons',
        'orders',
        'order_items',
        'order_status_history',
        'coupon_usage',
        'delivery_calculation_logs',
        'delivery_tracking',
        'reviews',
        'order_chats',
        'invoices',
        'taxes',

        // ── Cart & wishlist ──────────────────────────────────────
        'carts',
        'cart_items',
        'wishlists',

        // ── Wallet ───────────────────────────────────────────────
        'wallets',
        'wallet_transactions',
        'wallet_withdrawals',

        // ── App content ──────────────────────────────────────────
        'home_header_settings',
        'home_header_tabs',
        'home_header_cards',
        'app_contents',
        'category_screen_contents',

        // ── OTP ──────────────────────────────────────────────────
        'otp_codes',

        // ── Roles ────────────────────────────────────────────────
        'roles',

        // ── Plugin system ────────────────────────────────────────
        'plugins',
        'plugin_hooks',
        'plugin_settings',
        'plugin_metrics',

        // ── Ride-sharing plugin ──────────────────────────────────
        'ride_vehicle_types',
        'ride_drivers',
        'ride_driver_documents',
        'rides',
        'ride_driver_locations',
        'ride_surge_pricing_rules',
        'ride_ratings',
        'ride_driver_earnings',
        'ride_promo_codes',
        'ride_promo_code_usage',
        'ride_sos_alerts',
        'ride_settings',
        'ride_riders',
    ];

    public function up(): void
    {
        $fixed   = [];
        $skipped = [];

        foreach ($this->tables as $table) {
            // Skip tables that don't exist yet (plugin not installed, etc.)
            if (!Schema::hasTable($table)) {
                $skipped[] = "{$table} (table not found)";
                continue;
            }

            // Check current column definition via information_schema
            $row = DB::selectOne("
                SELECT EXTRA
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME   = ?
                  AND COLUMN_NAME  = 'id'
            ", [$table]);

            // Table has no id column — nothing to do
            if (!$row) {
                $skipped[] = "{$table} (no id column)";
                continue;
            }

            // AUTO_INCREMENT already present — skip
            if (str_contains(strtolower($row->EXTRA ?? ''), 'auto_increment')) {
                $skipped[] = "{$table} (already ok)";
                continue;
            }

            // Drop PRIMARY KEY first if one exists — required when id has no PK
            // (AUTO_INCREMENT column must be defined as a key)
            $pk = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA    = DATABASE()
                  AND TABLE_NAME      = ?
                  AND CONSTRAINT_TYPE = 'PRIMARY KEY'
            ", [$table]);

            if ($pk) {
                DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY");
            }

            // Apply AUTO_INCREMENT + PRIMARY KEY together
            DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
            $fixed[] = $table;
        }

        if (!empty($fixed)) {
            \Illuminate\Support\Facades\Log::info(
                '[fix_auto_increment] Applied AUTO_INCREMENT to: ' . implode(', ', $fixed)
            );
        }
    }

    public function down(): void
    {
        // Intentionally empty — removing AUTO_INCREMENT would break the tables.
    }
};

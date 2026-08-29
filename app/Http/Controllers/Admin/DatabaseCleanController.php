<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseCleanController extends Controller
{
    /**
     * Whitelist of tables grouped by category that can be cleaned.
     */
    private function getCleanableGroups(): array
    {
        return [
            'Users & Authentication' => [
                'users_customers' => ['label' => 'Customers', 'description' => 'Customer accounts', 'icon' => '👤', 'real_table' => 'users', 'filter' => "role = 'customer'"],
                'users_sellers' => ['label' => 'Sellers', 'description' => 'Store owners, managers & staff', 'icon' => '🏪', 'real_table' => 'users', 'filter' => "role IN ('store_owner','store_manager','store_staff')"],
                'users_delivery' => ['label' => 'Delivery Partners', 'description' => 'Delivery partner accounts', 'icon' => '�', 'real_table' => 'users', 'filter' => "role = 'delivery_partner'"],
                'addresses' => ['label' => 'Addresses', 'description' => 'User delivery addresses', 'icon' => '📍'],
                'otp_codes' => ['label' => 'OTP Codes', 'description' => 'One-time password codes', 'icon' => '🔑'],
            ],
            'Sellers & Stores' => [
                'stores' => ['label' => 'Stores', 'description' => 'Seller storefronts', 'icon' => '🏪'],
                'store_staff' => ['label' => 'Store Staff', 'description' => 'Staff assignments', 'icon' => '👥'],
                'store_kyc_documents' => ['label' => 'KYC Documents', 'description' => 'Verification documents', 'icon' => '📄'],
                'store_activity_logs' => ['label' => 'Activity Logs', 'description' => 'Store activity history', 'icon' => '📋'],
                'store_commission_rules' => ['label' => 'Commission Rules', 'description' => 'Commission configurations', 'icon' => '💰'],
                'store_payouts' => ['label' => 'Store Payouts', 'description' => 'Payout records', 'icon' => '💳'],
            ],
            'Products & Catalog' => [
                'products' => ['label' => 'Products', 'description' => 'All product listings', 'icon' => '📦'],
                'product_images' => ['label' => 'Product Images', 'description' => 'Product image records', 'icon' => '🖼️'],
                'product_variants' => ['label' => 'Product Variants', 'description' => 'Size, color variants', 'icon' => '🔄'],
                'product_attachments' => ['label' => 'Product Attachments', 'description' => 'Downloadable files', 'icon' => '📎'],
                'categories' => ['label' => 'Categories', 'description' => 'Product categories', 'icon' => '📂'],
                'brands' => ['label' => 'Brands', 'description' => 'Product brands', 'icon' => '🏷️'],
                'attributes' => ['label' => 'Attributes', 'description' => 'Product attributes', 'icon' => '🏷️'],
                'attribute_values' => ['label' => 'Attribute Values', 'description' => 'Attribute value options', 'icon' => '📝'],
                'tags' => ['label' => 'Tags', 'description' => 'Product tags', 'icon' => '🔖'],
                'units' => ['label' => 'Units', 'description' => 'Measurement units', 'icon' => '📏'],
            ],
            'Orders & Transactions' => [
                'orders' => ['label' => 'Orders', 'description' => 'All customer orders', 'icon' => '🛒'],
                'order_chats' => ['label' => 'Order Chats', 'description' => 'Order chat messages', 'icon' => '💬'],
                'carts' => ['label' => 'Carts', 'description' => 'Shopping carts', 'icon' => '🛒'],
                'cart_items' => ['label' => 'Cart Items', 'description' => 'Items in carts', 'icon' => '📦'],
                'wishlists' => ['label' => 'Wishlists', 'description' => 'User wishlists', 'icon' => '❤️'],
                'coupons' => ['label' => 'Coupons', 'description' => 'Discount coupons', 'icon' => '🎟️'],
                'coupon_usages' => ['label' => 'Coupon Usages', 'description' => 'Coupon usage records', 'icon' => '📊'],
            ],
            'Wallets & Payments' => [
                'wallets' => ['label' => 'Wallets', 'description' => 'User wallets', 'icon' => '👛'],
                'wallet_transactions' => ['label' => 'Wallet Transactions', 'description' => 'Transaction history', 'icon' => '💸'],
                'wallet_withdrawals' => ['label' => 'Wallet Withdrawals', 'description' => 'Withdrawal requests', 'icon' => '🏧'],
                'delivery_partner_payouts' => ['label' => 'Delivery Payouts', 'description' => 'Driver payout records', 'icon' => '💳'],
            ],
            'Content & Media' => [
                'app_contents' => ['label' => 'App Contents', 'description' => 'Home screen widgets', 'icon' => '📱'],
                'category_screen_contents' => ['label' => 'Category Screen', 'description' => 'Category page widgets', 'icon' => '📱'],
                'home_header_tabs' => ['label' => 'Home Header Tabs', 'description' => 'Header tab configs', 'icon' => '🗂️'],
                'home_header_cards' => ['label' => 'Home Header Cards', 'description' => 'Header card configs', 'icon' => '🃏'],
                'reviews' => ['label' => 'Reviews', 'description' => 'Product reviews', 'icon' => '⭐'],
                'static_pages' => ['label' => 'Static Pages', 'description' => 'CMS pages', 'icon' => '📄'],
                'point_transactions' => ['label' => 'Point Transactions', 'description' => 'Loyalty point history', 'icon' => '🏅'],
                'user_points' => ['label' => 'User Points', 'description' => 'User point balances', 'icon' => '🎯'],
                'referrals' => ['label' => 'Referrals', 'description' => 'Referral records', 'icon' => '🤝'],
            ],
            'Delivery' => [
                'delivery_tracking' => ['label' => 'Delivery Tracking', 'description' => 'Tracking records', 'icon' => '🚚'],
                'delivery_calculation_logs' => ['label' => 'Delivery Calc Logs', 'description' => 'Fee calculation logs', 'icon' => '🧮'],
            ],
        ];
    }

    /**
     * Get whitelist of all allowed table names.
     */
    private function getWhitelist(): array
    {
        $whitelist = [];
        foreach ($this->getCleanableGroups() as $tables) {
            $whitelist = array_merge($whitelist, array_keys($tables));
        }
        return $whitelist;
    }

    /**
     * Display the database clean page.
     */
    public function index()
    {
        $groups = $this->getCleanableGroups();
        $tableData = [];

        foreach ($groups as $groupName => $tables) {
            $tableData[$groupName] = [];
            foreach ($tables as $tableName => $info) {
                $count = 0;
                $realTable = $info['real_table'] ?? $tableName;
                if (Schema::hasTable($realTable)) {
                    if (isset($info['filter'])) {
                        $count = DB::table($realTable)->whereRaw($info['filter'])->count();
                    } else {
                        $count = DB::table($realTable)->count();
                    }
                }
                $tableData[$groupName][$tableName] = array_merge($info, [
                    'count' => $count,
                    'exists' => Schema::hasTable($realTable),
                ]);
            }
        }

        return view('admin.database-clean.index', compact('tableData'));
    }

    /**
     * Clean the selected database tables.
     */
    public function clean(Request $request)
    {
        if (\App\Helpers\DemoHelper::isDemoMode()) {
            return back()->with('error', 'Action Restricted: Database Clean action is disabled in Demo Mode.');
        }

        $request->validate([
            'tables' => 'required|array|min:1',
            'tables.*' => 'string',
        ]);

        $whitelist = $this->getWhitelist();
        $selectedTables = array_intersect($request->tables, $whitelist);

        if (empty($selectedTables)) {
            return back()->withErrors(['tables' => 'No valid tables selected.']);
        }

        // Tables that must be cleaned in dependency order when selected together.
        // If a parent table is being truncated, its child tables must be
        // truncated first so foreign key constraints are not violated.
        // This map defines: if table X is selected, also clean table Y first.
        $dependencyOrder = [
            'orders' => ['order_items'],
        ];

        // Expand selection to include required dependents
        $expandedTables = $selectedTables;
        foreach ($selectedTables as $table) {
            if (isset($dependencyOrder[$table])) {
                foreach ($dependencyOrder[$table] as $dep) {
                    if (!in_array($dep, $expandedTables)) {
                        $expandedTables[] = $dep;
                    }
                }
            }
        }

        // Re-order so dependents come before parents
        $orderedTables = [];
        foreach ($dependencyOrder as $parent => $children) {
            foreach ($children as $child) {
                if (in_array($child, $expandedTables) && !in_array($child, $orderedTables)) {
                    $orderedTables[] = $child;
                }
            }
        }
        foreach ($expandedTables as $table) {
            if (!in_array($table, $orderedTables)) {
                $orderedTables[] = $table;
            }
        }

        $cleaned = [];
        $groups = $this->getCleanableGroups();

        // Build a flat lookup for filter info
        $flatInfo = [];
        foreach ($groups as $tables) {
            foreach ($tables as $tableName => $info) {
                $flatInfo[$tableName] = $info;
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($orderedTables as $table) {
            $info = $flatInfo[$table] ?? [];
            $realTable = $info['real_table'] ?? $table;

            if (Schema::hasTable($realTable)) {
                if (isset($info['filter'])) {
                    // Use DELETE with filter (e.g., delete only customers or only sellers)
                    DB::table($realTable)->whereRaw($info['filter'])->delete();
                } else {
                    DB::table($realTable)->truncate();
                }
                // Only report tables the user explicitly selected
                if (in_array($table, $selectedTables)) {
                    $cleaned[] = $info['label'] ?? $table;
                }
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $count = count($cleaned);
        return back()->with('success', "Successfully cleaned {$count} table(s): " . implode(', ', $cleaned));
    }
}

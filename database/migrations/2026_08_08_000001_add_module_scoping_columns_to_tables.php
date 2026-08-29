<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add module_type to home_header_tabs
        if (Schema::hasTable('home_header_tabs')) {
            Schema::table('home_header_tabs', function (Blueprint $table) {
                if (!Schema::hasColumn('home_header_tabs', 'module_type')) {
                    $table->string('module_type', 30)->default('grocery')->after('name');
                }
            });
        }

        // 2. Add module_id to categories
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'module_id')) {
                    $table->foreignId('module_id')->nullable()->after('id')->constrained('home_header_tabs')->nullOnDelete();
                }
            });
        }

        // 3. Add module_id to stores
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
                if (!Schema::hasColumn('stores', 'module_id')) {
                    $table->foreignId('module_id')->nullable()->after('owner_id')->constrained('home_header_tabs')->nullOnDelete();
                }
            });
        }

        // 4. Add module_id and vertical fields to products
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'module_id')) {
                    $table->foreignId('module_id')->nullable()->after('store_id')->constrained('home_header_tabs')->nullOnDelete();
                }
                if (!Schema::hasColumn('products', 'module_type')) {
                    $table->string('module_type', 30)->default('grocery')->after('module_id');
                }
                if (!Schema::hasColumn('products', 'is_veg')) {
                    $table->boolean('is_veg')->nullable()->after('is_featured');
                }
                if (!Schema::hasColumn('products', 'is_prescription_required')) {
                    $table->boolean('is_prescription_required')->default(false)->after('is_veg');
                }
                if (!Schema::hasColumn('products', 'generic_name')) {
                    $table->string('generic_name', 255)->nullable()->after('is_prescription_required');
                }
                if (!Schema::hasColumn('products', 'custom_attributes')) {
                    $table->json('custom_attributes')->nullable()->after('generic_name');
                }
            });
        }

        // 5. Add module_id to app_contents (banners)
        if (Schema::hasTable('app_contents')) {
            Schema::table('app_contents', function (Blueprint $table) {
                if (!Schema::hasColumn('app_contents', 'module_id')) {
                    $table->foreignId('module_id')->nullable()->after('id')->constrained('home_header_tabs')->nullOnDelete();
                }
            });
        }

        // 6. Add module_id to brands
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (!Schema::hasColumn('brands', 'module_id')) {
                    $table->foreignId('module_id')->nullable()->after('id')->constrained('home_header_tabs')->nullOnDelete();
                }
            });
        }

        // 7. Add module_id to units
        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {
                if (!Schema::hasColumn('units', 'module_id')) {
                    $table->foreignId('module_id')->nullable()->after('id')->constrained('home_header_tabs')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        // Rollback columns if needed
        if (Schema::hasTable('units') && Schema::hasColumn('units', 'module_id')) {
            Schema::table('units', function (Blueprint $table) { $table->dropForeign(['module_id']); $table->dropColumn('module_id'); });
        }
        if (Schema::hasTable('brands') && Schema::hasColumn('brands', 'module_id')) {
            Schema::table('brands', function (Blueprint $table) { $table->dropForeign(['module_id']); $table->dropColumn('module_id'); });
        }
        if (Schema::hasTable('app_contents') && Schema::hasColumn('app_contents', 'module_id')) {
            Schema::table('app_contents', function (Blueprint $table) { $table->dropForeign(['module_id']); $table->dropColumn('module_id'); });
        }
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'module_id')) { $table->dropForeign(['module_id']); $table->dropColumn('module_id'); }
                $cols = ['module_type', 'is_veg', 'is_prescription_required', 'generic_name', 'custom_attributes'];
                foreach ($cols as $col) { if (Schema::hasColumn('products', $col)) $table->dropColumn($col); }
            });
        }
        if (Schema::hasTable('stores') && Schema::hasColumn('stores', 'module_id')) {
            Schema::table('stores', function (Blueprint $table) { $table->dropForeign(['module_id']); $table->dropColumn('module_id'); });
        }
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'module_id')) {
            Schema::table('categories', function (Blueprint $table) { $table->dropForeign(['module_id']); $table->dropColumn('module_id'); });
        }
        if (Schema::hasTable('home_header_tabs') && Schema::hasColumn('home_header_tabs', 'module_type')) {
            Schema::table('home_header_tabs', function (Blueprint $table) { $table->dropColumn('module_type'); });
        }
    }
};

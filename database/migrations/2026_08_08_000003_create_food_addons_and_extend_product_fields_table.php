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
        // 1. Create food_addons table
        if (!Schema::hasTable('food_addons')) {
            Schema::create('food_addons', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('module_id')->nullable();
                $table->string('name');
                $table->decimal('price', 10, 2)->default(0.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('module_id')->references('id')->on('home_header_tabs')->onDelete('cascade');
            });
        }

        // 2. Create food_addon_product pivot table
        if (!Schema::hasTable('food_addon_product')) {
            Schema::create('food_addon_product', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('food_addon_id');
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('food_addon_id')->references('id')->on('food_addons')->onDelete('cascade');
            });
        }

        // 3. Extend products table with food delivery fields
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'prep_time_min')) {
                $table->integer('prep_time_min')->nullable()->after('is_veg');
            }
            if (!Schema::hasColumn('products', 'prep_time_max')) {
                $table->integer('prep_time_max')->nullable()->after('prep_time_min');
            }
            if (!Schema::hasColumn('products', 'prep_time_unit')) {
                $table->string('prep_time_unit', 20)->default('minutes')->after('prep_time_max');
            }
            if (!Schema::hasColumn('products', 'available_time_starts')) {
                $table->time('available_time_starts')->nullable()->after('prep_time_unit');
            }
            if (!Schema::hasColumn('products', 'available_time_ends')) {
                $table->time('available_time_ends')->nullable()->after('available_time_starts');
            }
            if (!Schema::hasColumn('products', 'is_halal')) {
                $table->boolean('is_halal')->default(false)->after('available_time_ends');
            }
            if (!Schema::hasColumn('products', 'nutrition_info')) {
                $table->text('nutrition_info')->nullable()->after('is_halal');
            }
            if (!Schema::hasColumn('products', 'search_tags')) {
                $table->text('search_tags')->nullable()->after('nutrition_info');
            }
            if (!Schema::hasColumn('products', 'food_variations')) {
                $table->json('food_variations')->nullable()->after('search_tags');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_addon_product');
        Schema::dropIfExists('food_addons');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'prep_time_min',
                'prep_time_max',
                'prep_time_unit',
                'available_time_starts',
                'available_time_ends',
                'is_halal',
                'nutrition_info',
                'search_tags',
                'food_variations',
            ]);
        });
    }
};

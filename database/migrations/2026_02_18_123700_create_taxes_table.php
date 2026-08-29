<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('taxes')) {
            Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('applies_to', ['customer', 'store', 'delivery_partner'])->default('customer');
            $table->enum('calculation_type', ['fixed', 'percentage'])->default('percentage');
            $table->decimal('value', 10, 4); //for 5% or 10.0000 for ₹10 fixed
            // Customer-specific: order value thresholds
            $table->decimal('min_order_value', 10, 2)->nullable();
            $table->decimal('max_order_value', 10, 2)->nullable();
            // Store-specific: apply only to stores in this category
            $table->foreignId('store_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_inclusive')->default(false); // true = tax included in price
            $table->timestamps();
        });
        }}

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};

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
        if (Schema::hasTable('order_items') && !Schema::hasColumn('order_items', 'prescription_image')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('prescription_image')->nullable();
            });
        }

        if (!Schema::hasTable('product_notify_subscribers')) {
            Schema::create('product_notify_subscribers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->enum('status', ['pending', 'notified'])->default('pending');
                $table->timestamps();

                $table->unique(['user_id', 'product_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'prescription_image')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('prescription_image');
            });
        }

        Schema::dropIfExists('product_notify_subscribers');
    }
};

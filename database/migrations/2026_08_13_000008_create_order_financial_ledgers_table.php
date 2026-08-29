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
        if (!Schema::hasTable('order_financial_ledgers')) {
            Schema::create('order_financial_ledgers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->string('type'); // ORDER_PAYMENT, STORE_EARNING, ADMIN_COMMISSION, DRIVER_EARNING, CUSTOMER_TIP, REFUND, RETURN_REFUND, CANCELLATION
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('set null');
                $table->foreignId('delivery_partner_id')->nullable()->constrained('users')->onDelete('set null');
                $table->decimal('amount', 12, 2)->default(0.00);
                $table->enum('entry_type', ['credit', 'debit']);
                $table->string('description')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['order_id', 'type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_financial_ledgers');
    }
};

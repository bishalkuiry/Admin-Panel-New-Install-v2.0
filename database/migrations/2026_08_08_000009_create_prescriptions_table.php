<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prescriptions')) {
            Schema::create('prescriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('set null');
                $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
                $table->string('prescription_file');
                $table->enum('status', ['pending_review', 'medicines_added', 'customer_approved', 'rejected', 'ordered'])->default('pending_review');
                $table->json('prescribed_medicines')->nullable(); // List of added medicines with qty & price
                $table->decimal('estimated_price', 10, 2)->default(0.00);
                $table->text('pharmacist_notes')->nullable();
                $table->timestamps();

                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wallet System Tables
 * 
 * Digital wallet functionality for users including balance management,
 * transactions, and withdrawal requests.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // WALLET SYSTEM
        // ============================================
        
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('INR');
            $table->timestamps();
            
            $table->unique('user_id', 'unique_user_wallet');
            $table->index('balance', 'idx_balance');
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->text('description');
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['wallet_id', 'created_at'], 'idx_wallet_created');
            $table->index('type', 'idx_type');
            $table->index(['reference_type', 'reference_id'], 'idx_reference');
        });

        Schema::create('wallet_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('bank_name', 255);
            $table->string('account_number', 50);
            $table->string('account_holder_name', 255);
            $table->string('ifsc_code', 20)->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index('status', 'idx_status');
            $table->index(['wallet_id', 'status'], 'idx_wallet_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_withdrawals');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};

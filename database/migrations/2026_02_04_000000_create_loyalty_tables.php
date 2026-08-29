<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Loyalty System Tables
 * 
 * Cashback points and referral system for user retention and growth.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // CASHBACK POINTS SYSTEM
        // ============================================
        
        // User points balance
        if (!Schema::hasTable('user_points')) {
            Schema::create('user_points', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('total_points')->default(0);
                $table->unsignedInteger('available_points')->default(0);
                $table->unsignedInteger('redeemed_points')->default(0);
                $table->timestamps();
                
                $table->unique('user_id', 'unique_user_points');
                $table->index('available_points', 'idx_available_points');
            });
        }

        // Points transaction history
        if (!Schema::hasTable('point_transactions')) {
            Schema::create('point_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_points_id')->constrained('user_points')->cascadeOnDelete();
                $table->enum('type', ['earned', 'redeemed', 'expired', 'admin_credit', 'admin_debit']);
                $table->integer('points'); // Can be negative for deductions
                $table->unsignedInteger('points_before');
                $table->unsignedInteger('points_after');
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->text('description');
                $table->json('metadata')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                
                $table->index(['user_points_id', 'created_at'], 'idx_user_points_created');
                $table->index('type', 'idx_point_txn_type');
                $table->index('order_id', 'idx_point_txn_order');
            });
        }

        // ============================================
        // REFERRAL SYSTEM
        // ============================================
        
        if (!Schema::hasTable('referrals')) {
            Schema::create('referrals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('referee_id')->constrained('users')->cascadeOnDelete();
                $table->string('referral_code', 20);
                $table->enum('status', ['pending', 'completed', 'rewarded'])->default('pending');
                
                // Referrer rewards
                $table->decimal('referrer_reward_amount', 10, 2)->default(0);
                $table->boolean('referrer_reward_paid')->default(false);
                $table->timestamp('referrer_rewarded_at')->nullable();
                
                // Referee rewards
                $table->decimal('referee_reward_amount', 10, 2)->default(0);
                $table->boolean('referee_reward_paid')->default(false);
                $table->timestamp('referee_rewarded_at')->nullable();
                
                // Free delivery tracking for referee
                $table->unsignedInteger('referee_free_deliveries')->default(0);
                $table->unsignedInteger('referee_free_deliveries_used')->default(0);
                
                $table->timestamps();
                
                $table->unique('referee_id', 'unique_referee'); // Each user can only be referred once
                $table->index('referrer_id', 'idx_referrer');
                $table->index('referral_code', 'idx_referral_code');
                $table->index('status', 'idx_referral_status');
            });
        }

        // Add referral_code to users table if missing
        if (!Schema::hasColumn('users', 'referral_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('referral_code', 20)->nullable()->unique()->after('remember_token');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'referral_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('referral_code');
            });
        }
        
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('user_points');
    }
};

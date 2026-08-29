<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * System Infrastructure Tables
 * 
 * Core Laravel system tables for authentication, caching, queuing, and sessions.
 * These tables are foundational and required for the application to function.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // AUTHENTICATION & USER MANAGEMENT
        // ============================================
        
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('customer'); // customer, seller, delivery_partner, admin
            $table->string('phone', 20)->nullable();
            $table->string('avatar')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->string('fcm_token')->nullable(); // Firebase Cloud Messaging for push notifications
            $table->string('referral_code', 20)->nullable()->unique();
            $table->decimal('payout_balance', 12, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->timestamps();

            $table->index(['role', 'is_active']);
            $table->index('fcm_token');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        // ============================================
        // CACHE SYSTEM
        // ============================================
        
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // ============================================
        // QUEUE SYSTEM
        // ============================================
        
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // ============================================
        // SETTINGS & CONFIGURATION
        // ============================================
        
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general')->index();
            $table->string('key');
            $table->longText('value')->nullable();
            $table->string('type')->default('text');
            $table->json('options')->nullable();
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key']);
        });

        Schema::create('static_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ============================================
        // COMMUNICATION & AUTOMATION
        // ============================================
        
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->string('name');
            $table->string('subject');
            $table->longText('body');
            $table->json('placeholders')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('scheduler_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('url');
            $table->string('target');
            $table->string('frequency');
            $table->json('parameters')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_run_status')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        // ============================================
        // FINANCIAL SYSTEM
        // ============================================

        Schema::create('delivery_partner_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // ============================================
        // LOYALTY & REFERRAL SYSTEM
        // ============================================
        
        Schema::create('user_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedInteger('available_points')->default(0);
            $table->unsignedInteger('redeemed_points')->default(0);
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_points_id')->constrained('user_points')->cascadeOnDelete();
            $table->enum('type', ['earned', 'redeemed', 'expired', 'admin_credit', 'admin_debit']);
            $table->integer('points');
            $table->unsignedInteger('points_before');
            $table->unsignedInteger('points_after');
            $table->unsignedBigInteger('order_id')->nullable(); // nullable as orders created later
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referee_id')->constrained('users')->cascadeOnDelete();
            $table->string('referral_code', 20);
            $table->enum('status', ['pending', 'completed', 'rewarded'])->default('pending');
            $table->decimal('referrer_reward_amount', 10, 2)->default(0);
            $table->boolean('referrer_reward_paid')->default(false);
            $table->timestamp('referrer_rewarded_at')->nullable();
            $table->decimal('referee_reward_amount', 10, 2)->default(0);
            $table->boolean('referee_reward_paid')->default(false);
            $table->timestamp('referee_rewarded_at')->nullable();
            $table->unsignedInteger('referee_free_deliveries')->default(0);
            $table->unsignedInteger('referee_free_deliveries_used')->default(0);
            $table->timestamps();
            $table->unique('referee_id');
        });

        // ============================================
        // DEFAULT SETTINGS
        // ============================================
        
        \Illuminate\Support\Facades\DB::table('settings')->insert([
            [
                'group' => 'mobile_app',
                'key' => 'driver_assignment_mode',
                'value' => 'manual',
                'type' => 'select',
                'options' => json_encode(['manual' => 'Manual Assignment', 'auto' => 'Auto Broadcast']),
                'label' => 'Driver Assignment Mode',
                'description' => 'Manual: Store/Admin assigns driver. Auto: Order broadcast to nearby drivers.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'mobile_app',
                'key' => 'auto_assign_radius',
                'value' => '5',
                'type' => 'number',
                'label' => 'Auto Assign Radius (km)',
                'description' => 'Radius to search for drivers in Auto mode',
                'options' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_partner_payouts');
        Schema::dropIfExists('scheduler_jobs');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('user_points');
        Schema::dropIfExists('static_pages');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};

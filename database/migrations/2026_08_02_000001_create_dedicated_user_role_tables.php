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
        // 1. Create admin_staff table for Admin Panel Employees & Staff
        if (!Schema::hasTable('admin_staff')) {
            Schema::create('admin_staff', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('role')->default('staff');
                $table->string('phone')->nullable();
                $table->string('avatar')->nullable();
                $table->json('permissions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login_at')->nullable();
                $table->string('last_login_ip')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Create seller_staff table for Store Owners, Managers & Seller Staff
        if (!Schema::hasTable('seller_staff')) {
            Schema::create('seller_staff', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('role')->default('store_owner');
                $table->string('phone')->nullable();
                $table->string('avatar')->nullable();
                $table->json('permissions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->decimal('payout_balance', 12, 2)->default(0.00);
                $table->string('bank_name')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('bank_ifsc')->nullable();
                $table->string('bank_account_holder')->nullable();
                $table->string('kyc_status')->default('not_submitted');
                $table->text('kyc_rejection_reason')->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. Create delivery_partners table for Delivery Partners & Drivers
        if (!Schema::hasTable('delivery_partners')) {
            Schema::create('delivery_partners', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('role')->default('delivery_partner');
                $table->string('phone')->nullable();
                $table->string('avatar')->nullable();
                $table->string('vehicle_type')->nullable();
                $table->string('license_number')->nullable();
                $table->boolean('is_active')->default(true);
                $table->decimal('payout_balance', 12, 2)->default(0.00);
                $table->string('fcm_token')->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_partners');
        Schema::dropIfExists('seller_staff');
        Schema::dropIfExists('admin_staff');
    }
};

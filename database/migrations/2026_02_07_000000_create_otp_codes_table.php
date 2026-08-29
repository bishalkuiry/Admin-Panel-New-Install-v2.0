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
        // Create otp_codes table for server-side OTP verification
        if (!Schema::hasTable('otp_codes')) {
            Schema::create('otp_codes', function (Blueprint $table) {
                $table->id();
                $table->string('identifier'); // phone number or email
                $table->string('code'); // hashed OTP code
                $table->enum('type', ['phone', 'email'])->default('phone');
                $table->enum('purpose', ['login', 'register', 'reset_password', 'verify'])->default('login');
                $table->timestamp('expires_at');
                $table->timestamp('verified_at')->nullable();
                $table->unsignedTinyInteger('attempts')->default(0); // track failed attempts
                $table->timestamps();
                
                $table->index(['identifier', 'type']);
                $table->index('expires_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};

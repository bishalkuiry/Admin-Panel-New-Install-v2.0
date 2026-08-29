<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Store Subscriptions Table
        if (!Schema::hasTable('store_subscriptions')) {
            Schema::create('store_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
                $table->foreignId('plan_id')->constrained('store_subscription_plans')->onDelete('cascade');
                $table->timestamp('starts_at')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_trial')->default(false);
                $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
                $table->timestamps();
            });
        }

        // 2. Support Chat Threads Table (Admin <-> Customer / Seller / Rider)
        if (!Schema::hasTable('support_chats')) {
            Schema::create('support_chats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('subject')->default('Customer Support Ticket');
                $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Support Chat Messages Table
        if (!Schema::hasTable('support_chat_messages')) {
            Schema::create('support_chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_id')->constrained('support_chats')->onDelete('cascade');
                $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
                $table->text('message')->nullable();
                $table->string('attachment_url')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_chat_messages');
        Schema::dropIfExists('support_chats');
        Schema::dropIfExists('store_subscriptions');
    }
};

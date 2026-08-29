<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates order_chats table for Firebase Realtime Database chat system
     * Supports customer-delivery partner and customer-seller chats
     */
    public function up(): void
    {
        if (!Schema::hasTable('order_chats')) {
            Schema::create('order_chats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->string('firebase_chat_id')->unique(); // Firebase Realtime DB chat ID (format: {orderId}_{chatType}_{timestamp})
                $table->enum('chat_type', ['customer_delivery', 'customer_seller']); // Type of chat
                $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('participant_id')->nullable()->constrained('users')->onDelete('set null'); // Delivery partner or seller
                $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null'); // Admin who joined the chat
                $table->timestamp('admin_joined_at')->nullable(); // When admin joined the chat
                $table->timestamp('last_message_at')->nullable();
                $table->text('last_message')->nullable();
                $table->integer('unread_count_customer')->default(0);
                $table->integer('unread_count_participant')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['order_id', 'chat_type']);
                $table->index('customer_id');
                $table->index('participant_id');
                $table->index('admin_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_chats');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Ticket categories ────────────────────────────────────────────────
        if (!Schema::hasTable('support_categories')) {
            Schema::create('support_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('icon')->default('help_outline');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // ── Support tickets ──────────────────────────────────────────────────
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number')->unique();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('support_categories');
                $table->string('subject');
                $table->text('description');
                $table->enum('status', ['open', 'in_progress', 'waiting_user', 'resolved', 'closed'])
                      ->default('open');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->string('firebase_chat_id')->nullable()->unique();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('status');
                $table->index('assigned_to');
                $table->index('created_at');
            });
        }

        // ── Ticket messages (REST fallback + media) ──────────────────────────
        if (!Schema::hasTable('support_messages')) {
            Schema::create('support_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('message');
                $table->json('attachments')->nullable(); // array of file paths
                $table->boolean('is_admin')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index('ticket_id');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('support_categories');
    }
};

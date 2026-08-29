<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plugin System Foundations
 * 
 * This migration consolidates all plugin-related infrastructure:
 * 1. Plugins Registry: Core plugin metadata and status
 * 2. Plugin Hooks: Event-based callback registration
 * 3. Plugin Settings: Key-value configuration storage
 * 4. Plugin Metrics: Performance and reliability tracking
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // 1. PLUGINS REGISTRY
        // ============================================
        
        if (!Schema::hasTable('plugins')) {
            Schema::create('plugins', function (Blueprint $table) {
                $table->id();
                
                // Basic Information
                $table->string('name')->unique()->comment('Unique identifier (slug)');
                $table->string('display_name')->comment('Human-readable name');
                $table->text('description')->nullable();
                $table->string('version', 50);
                $table->string('author')->nullable();
                $table->string('author_url')->nullable();
                $table->string('plugin_url')->nullable();
                
                // Status & Visibility
                $table->boolean('is_active')->default(false)->index();
                $table->boolean('is_installed')->default(true)->index();
                
                // Paths & Namespaces
                $table->string('path')->comment('plugins/plugin-name');
                $table->string('namespace')->nullable()->comment('PHP namespace');
                
                // Requirements
                $table->string('requires_php', 50)->nullable()->comment('Minimum PHP version');
                $table->string('requires_laravel', 50)->nullable()->comment('Minimum Laravel version');
                $table->string('requires_quixko', 50)->nullable()->comment('Minimum Quixko version');
                $table->json('dependencies')->nullable()->comment('Other plugin dependencies');
                
                // Licensing
                $table->string('license_key')->nullable();
                $table->enum('license_status', ['valid', 'invalid', 'expired', 'none'])->default('none');
                $table->timestamp('license_verified_at')->nullable();
                
                // Metadata & Config
                $table->json('settings')->nullable()->comment('Plugin-specific settings');
                $table->json('metadata')->nullable()->comment('Additional metadata');
                
                // Hook Execution & Storage
                $table->json('hooks')->nullable()->comment('Registered hooks summary');
                $table->json('migrations_run')->nullable()->comment('History of plugin migrations');
                
                // Reliability & Error Tracking (Enterprise)
                $table->string('backup_path')->nullable();
                $table->integer('hook_failure_count')->default(0);
                $table->timestamp('last_error_at')->nullable();
                $table->text('last_error_message')->nullable();
                
                // Installation Audit
                $table->timestamp('installed_at')->nullable();
                $table->timestamp('activated_at')->nullable();
                $table->timestamps();
                
                $table->index('updated_at');
            });
        }

        // ============================================
        // 2. PLUGIN HOOKS
        // ============================================
        
        if (!Schema::hasTable('plugin_hooks')) {
            Schema::create('plugin_hooks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plugin_id')->constrained('plugins')->onDelete('cascade');
                $table->string('hook_name')->index()->comment('e.g., order.created');
                $table->string('callback')->comment('Class@method');
                $table->integer('priority')->default(10)->index();
                $table->boolean('is_active')->default(true);
                $table->timestamp('created_at')->nullable();
                
                $table->index(['hook_name', 'priority']);
            });
        }

        // ============================================
        // 3. PLUGIN SETTINGS
        // ============================================
        
        if (!Schema::hasTable('plugin_settings')) {
            Schema::create('plugin_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plugin_id')->constrained('plugins')->onDelete('cascade');
                $table->string('key')->index();
                $table->text('value')->nullable();
                $table->enum('type', ['string', 'integer', 'boolean', 'json', 'encrypted'])->default('string');
                $table->timestamps();
                
                $table->unique(['plugin_id', 'key'], 'unique_plugin_key');
            });
        }

        // ============================================
        // 4. PLUGIN METRICS (Enterprise Features)
        // ============================================
        
        if (!Schema::hasTable('plugin_metrics')) {
            Schema::create('plugin_metrics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plugin_id')->constrained('plugins')->onDelete('cascade');
                $table->string('hook_name', 100);
                $table->integer('execution_count')->default(0);
                $table->decimal('total_execution_time', 10, 2)->default(0); // milliseconds
                $table->decimal('avg_execution_time', 10, 2)->default(0); // milliseconds
                $table->decimal('max_execution_time', 10, 2)->default(0); // milliseconds
                $table->integer('failure_count')->default(0);
                $table->timestamp('last_executed_at')->nullable();
                $table->timestamps();
                
                $table->unique(['plugin_id', 'hook_name']);
                $table->index('avg_execution_time');
                $table->index('updated_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_metrics');
        Schema::dropIfExists('plugin_settings');
        Schema::dropIfExists('plugin_hooks');
        Schema::dropIfExists('plugins');
    }
};

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
        if (!Schema::hasTable('scheduler_jobs')) {
            Schema::create('scheduler_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('url'); // 'command' or 'url'
            $table->string('target'); // The artisan command OR the URL
            $table->string('frequency'); // e.g. 'everyMinute', 'hourly', 'daily', or cron expression
            $table->json('parameters')->nullable(); // Optional params for command
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_run_status')->nullable(); // 'success', 'failed'
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
        }}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduler_jobs');
    }
};

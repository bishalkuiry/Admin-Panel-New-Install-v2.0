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
        if (!Schema::hasColumn('store_activity_logs', 'notes')) {
            Schema::table('store_activity_logs', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('user_agent');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('store_activity_logs', 'notes')) {
            Schema::table('store_activity_logs', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};

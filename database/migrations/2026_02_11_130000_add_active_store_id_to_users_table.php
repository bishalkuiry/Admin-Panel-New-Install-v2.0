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
        if (!Schema::hasColumn('users', 'active_store_id')) {
            Schema::table('users', function (Blueprint $table) {
            $table->foreignId('active_store_id')->nullable()->constrained('stores')->nullOnDelete()->after('permissions');
        });
        }}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['active_store_id']);
            $table->dropColumn('active_store_id');
        });
    }
};

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
        if (!Schema::hasColumn('app_contents', 'target')) {
            Schema::table('app_contents', function (Blueprint $table) {
                $table->enum('target', ['all', 'app', 'website'])->default('all')->after('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('app_contents', 'target')) {
            Schema::table('app_contents', function (Blueprint $table) {
                $table->dropColumn('target');
            });
        }
    }
};

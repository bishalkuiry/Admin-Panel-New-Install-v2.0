<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'prescription')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('prescription', 1000)->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'prescription')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('prescription');
            });
        }
    }
};

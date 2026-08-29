<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('app_contents', 'show_on_tracking')) {
            Schema::table('app_contents', function (Blueprint $table) {
                $table->boolean('show_on_tracking')->default(false)->after('show_on_category_screen');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('app_contents', 'show_on_tracking')) {
            Schema::table('app_contents', function (Blueprint $table) {
                $table->dropColumn('show_on_tracking');
            });
        }
    }
};

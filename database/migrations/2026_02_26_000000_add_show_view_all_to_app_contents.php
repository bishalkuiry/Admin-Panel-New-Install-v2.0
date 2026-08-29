<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('app_contents', 'show_view_all')) {
            Schema::table('app_contents', function (Blueprint $table) {
                $table->boolean('show_view_all')->default(false)->after('show_subtitle');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('app_contents', 'show_view_all')) {
            Schema::table('app_contents', function (Blueprint $table) {
                $table->dropColumn('show_view_all');
            });
        }
    }
};

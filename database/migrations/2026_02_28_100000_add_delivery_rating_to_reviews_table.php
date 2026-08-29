<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('reviews', 'delivery_rating')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->tinyInteger('delivery_rating')->nullable()->after('rating');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('reviews', 'delivery_rating')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropColumn('delivery_rating');
            });
        }
    }
};

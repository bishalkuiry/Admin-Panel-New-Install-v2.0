<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ad_plans')) {
            Schema::create('ad_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('ad_type', ['banner', 'featured_store', 'sponsored_product'])->default('banner');
                $table->decimal('price', 10, 2)->default(500.00);
                $table->integer('duration_days')->default(30);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_plans');
    }
};

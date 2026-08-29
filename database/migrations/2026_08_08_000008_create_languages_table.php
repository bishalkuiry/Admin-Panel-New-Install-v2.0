<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('languages')) {
            Schema::create('languages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique(); // e.g. en, ar, hi, es, fr
                $table->boolean('is_rtl')->default(false);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed initial languages
            DB::table('languages')->insert([
                ['name' => 'English', 'code' => 'en', 'is_rtl' => false, 'is_default' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Arabic (العربية)', 'code' => 'ar', 'is_rtl' => true, 'is_default' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Hindi (हिंदी)', 'code' => 'hi', 'is_rtl' => false, 'is_default' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Spanish (Español)', 'code' => 'es', 'is_rtl' => false, 'is_default' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};

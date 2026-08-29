<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('driver_order_declines')) {
            Schema::create('driver_order_declines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('driver_id');
                $table->string('reason')->nullable();
                $table->timestamps();

                $table->unique(['order_id', 'driver_id']);
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_order_declines');
    }
};

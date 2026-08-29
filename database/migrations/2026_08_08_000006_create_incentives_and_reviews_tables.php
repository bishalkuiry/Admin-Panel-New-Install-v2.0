<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rider Incentive Rules Table
        if (!Schema::hasTable('rider_incentive_rules')) {
            Schema::create('rider_incentive_rules', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('description')->nullable();
                $table->integer('target_deliveries')->default(10);
                $table->decimal('bonus_amount', 10, 2)->default(200.00);
                $table->string('period_type')->default('daily'); // daily, weekly, peak_hours
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Customer Reviews & Ratings Table (Product, Store, Delivery Partner)
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
                $table->string('reviewable_type'); // App\Models\Product, App\Models\Store, App\Models\User (Driver)
                $table->unsignedBigInteger('reviewable_id');
                $table->integer('rating')->default(5);
                $table->text('comment')->nullable();
                $table->json('images')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->timestamps();

                $table->index(['reviewable_type', 'reviewable_id']);
                $table->index('status');
            });
        }

        // Add driver_tip to orders table if missing
        if (!Schema::hasColumn('orders', 'driver_tip')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('driver_tip', 10, 2)->default(0)->after('delivery_fee');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('rider_incentive_rules');
    }
};

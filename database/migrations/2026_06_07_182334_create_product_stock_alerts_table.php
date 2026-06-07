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
        Schema::create('product_stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users_buyers')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'buyer_id', 'status'], 'stock_alert_product_buyer_status_unique');
            $table->index(['buyer_id', 'status', 'created_at']);
            $table->index(['product_id', 'status', 'notified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_alerts');
    }
};

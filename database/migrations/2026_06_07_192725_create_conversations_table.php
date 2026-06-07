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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users_buyers')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('seller_id')->constrained('users_sellers')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnUpdate()->nullOnDelete();
            $table->string('status', 32)->index();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamp('buyer_archived_at')->nullable();
            $table->timestamp('seller_archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('buyer_id');
            $table->index('seller_id');
            $table->index('product_id');
            $table->index('order_id');
            $table->index(['buyer_id', 'last_message_at']);
            $table->index(['seller_id', 'last_message_at']);
            $table->unique(['buyer_id', 'seller_id', 'product_id'], 'conversations_buyer_seller_product_unique');
            $table->unique(['buyer_id', 'seller_id', 'order_id'], 'conversations_buyer_seller_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};

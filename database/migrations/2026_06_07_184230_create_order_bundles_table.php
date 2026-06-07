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
        Schema::create('order_bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_bundle_id')->nullable()->constrained('product_bundles')->nullOnDelete();
            $table->foreignId('seller_id')->constrained('users_sellers');
            $table->string('bundle_name_snapshot');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('base_price', 10, 2);
            $table->string('discount_type')->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2);
            $table->json('products_snapshot');
            $table->timestamps();

            $table->index(['order_id', 'seller_id']);
            $table->index(['product_bundle_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_bundles');
    }
};

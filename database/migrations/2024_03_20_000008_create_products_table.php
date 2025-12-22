<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('category_id')->constrained();
            $table->foreignId('seller_id')->constrained('users_sellers');
            $table->decimal('price', 8, 2);
            $table->decimal('min_order_price', 8, 2)->nullable();
            $table->integer('min_order_count')->nullable();
            $table->integer('stock')->required();
            $table->json('description')->nullable();
            $table->string('unit');
            $table->decimal('package_weight', 10, 3)->nullable();
            $table->decimal('price_per_liter', 10, 2)->nullable();
            $table->boolean('is_organic')->default(false);
            $table->foreignId('country_of_origin')->constrained('countries');
            $table->string('product_image');
            $table->string('product_additional_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};

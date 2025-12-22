<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('selected_value_id')->constrained('attribute_values');
            $table->unique(['attribute_id', 'product_id', 'selected_value_id'], 'attr_prod_value_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_product');
    }
};

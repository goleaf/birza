<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attribute_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->foreignId('attribute_id')->nullable()->constrained('attributes');
            $table->foreignId('attribute_value_id')->nullable()->constrained('attribute_values');
            $table->timestamps();

            // $table->unique(['product_id', 'attribute_value_id', 'attribute_id'])->name('unique_product_attribute_value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_value');
    }
};

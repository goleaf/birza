<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('price', 'products_price_idx');
            $table->index('stock', 'products_stock_idx');
            $table->index(['is_active', 'is_organic'], 'products_active_organic_idx');
            $table->index(['is_active', 'created_at'], 'products_active_created_at_idx');
            $table->index(['deleted_at', 'created_at'], 'products_deleted_created_at_idx');
        });

        Schema::table('product_attribute_value', function (Blueprint $table) {
            $table->dropIndex('product_attribute_value_product_attribute_idx');
            $table->index(
                ['product_id', 'attribute_id', 'attribute_value_id'],
                'product_attribute_value_product_attribute_value_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('product_attribute_value', function (Blueprint $table) {
            $table->dropIndex('product_attribute_value_product_attribute_value_idx');
            $table->index(
                ['product_id', 'attribute_id'],
                'product_attribute_value_product_attribute_idx',
            );
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_price_idx');
            $table->dropIndex('products_stock_idx');
            $table->dropIndex('products_active_organic_idx');
            $table->dropIndex('products_active_created_at_idx');
            $table->dropIndex('products_deleted_created_at_idx');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['parent_category_id', 'is_active'], 'categories_parent_active_idx');
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->index(['is_active', 'is_filterable'], 'attributes_active_filterable_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['category_id', 'is_active'], 'products_category_active_idx');
            $table->index(['seller_id', 'is_active'], 'products_seller_active_idx');
            $table->index(['country_of_origin', 'is_active'], 'products_country_active_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['buyer_id', 'status'], 'orders_buyer_status_idx');
            $table->index(['buyer_id', 'payment_status'], 'orders_buyer_payment_status_idx');
            $table->index(['status', 'created_at'], 'orders_status_created_at_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_id', 'seller_id'], 'order_items_order_seller_idx');
            $table->index(['seller_id', 'created_at'], 'order_items_seller_created_at_idx');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'carts_user_created_at_idx');
        });

        Schema::table('buyer_credit_history', function (Blueprint $table) {
            $table->index(['buyer_id', 'created_at'], 'buyer_credit_history_buyer_created_at_idx');
            $table->index(['admin_id'], 'buyer_credit_history_admin_idx');
        });

        Schema::table('seller_transactions', function (Blueprint $table) {
            $table->index(['seller_id', 'created_at'], 'seller_transactions_seller_created_at_idx');
            $table->index(['order_id', 'created_at'], 'seller_transactions_order_created_at_idx');
        });

        Schema::table('product_attribute', function (Blueprint $table) {
            $table->unique(['product_id', 'attribute_id'], 'product_attribute_product_attribute_unique');
        });

        Schema::table('product_attribute_value', function (Blueprint $table) {
            $table->index(['product_id', 'attribute_id'], 'product_attribute_value_product_attribute_idx');
        });
    }

    public function down(): void
    {
        Schema::table('product_attribute_value', function (Blueprint $table) {
            $table->dropIndex('product_attribute_value_product_attribute_idx');
        });

        Schema::table('product_attribute', function (Blueprint $table) {
            $table->dropUnique('product_attribute_product_attribute_unique');
        });

        Schema::table('seller_transactions', function (Blueprint $table) {
            $table->dropIndex('seller_transactions_seller_created_at_idx');
            $table->dropIndex('seller_transactions_order_created_at_idx');
        });

        Schema::table('buyer_credit_history', function (Blueprint $table) {
            $table->dropIndex('buyer_credit_history_buyer_created_at_idx');
            $table->dropIndex('buyer_credit_history_admin_idx');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('carts_user_created_at_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_order_seller_idx');
            $table->dropIndex('order_items_seller_created_at_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_buyer_status_idx');
            $table->dropIndex('orders_buyer_payment_status_idx');
            $table->dropIndex('orders_status_created_at_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_category_active_idx');
            $table->dropIndex('products_seller_active_idx');
            $table->dropIndex('products_country_active_idx');
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropIndex('attributes_active_filterable_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_parent_active_idx');
        });
    }
};

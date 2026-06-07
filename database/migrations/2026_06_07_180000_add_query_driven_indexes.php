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
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['buyer_id', 'created_at'], 'orders_buyer_created_at_idx');
            $table->index(['buyer_id', 'payment_status', 'created_at'], 'orders_buyer_payment_created_at_idx');
            $table->index(['payment_status', 'created_at'], 'orders_payment_created_at_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['seller_id', 'order_id'], 'order_items_seller_order_idx');
            $table->index(['order_id', 'product_id'], 'order_items_order_product_idx');
        });

        Schema::table('buyer_credit_history', function (Blueprint $table) {
            $table->index(['buyer_id', 'type', 'created_at'], 'buyer_credit_history_buyer_type_created_idx');
        });

        Schema::table('seller_transactions', function (Blueprint $table) {
            $table->index(['seller_id', 'type', 'created_at'], 'seller_transactions_seller_type_created_idx');
        });

        Schema::table('users_buyers', function (Blueprint $table) {
            $table->index(['is_active', 'created_at'], 'users_buyers_active_created_at_idx');
            $table->index(['is_verified', 'created_at'], 'users_buyers_verified_created_at_idx');
            $table->index(['credit_balance'], 'users_buyers_credit_balance_idx');
        });

        Schema::table('users_sellers', function (Blueprint $table) {
            $table->index(['is_active', 'created_at'], 'users_sellers_active_created_at_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['parent_category_id', 'order', 'id'], 'categories_parent_order_idx');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->index(['region', 'is_active', 'alpha2'], 'countries_region_active_alpha2_idx');
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->index(['attribute_id', 'is_active'], 'attribute_values_attribute_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropIndex('attribute_values_attribute_active_idx');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex('countries_region_active_alpha2_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_parent_order_idx');
        });

        Schema::table('users_sellers', function (Blueprint $table) {
            $table->dropIndex('users_sellers_active_created_at_idx');
        });

        Schema::table('users_buyers', function (Blueprint $table) {
            $table->dropIndex('users_buyers_credit_balance_idx');
            $table->dropIndex('users_buyers_verified_created_at_idx');
            $table->dropIndex('users_buyers_active_created_at_idx');
        });

        Schema::table('seller_transactions', function (Blueprint $table) {
            $table->dropIndex('seller_transactions_seller_type_created_idx');
        });

        Schema::table('buyer_credit_history', function (Blueprint $table) {
            $table->dropIndex('buyer_credit_history_buyer_type_created_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_order_product_idx');
            $table->dropIndex('order_items_seller_order_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_payment_created_at_idx');
            $table->dropIndex('orders_buyer_payment_created_at_idx');
            $table->dropIndex('orders_buyer_created_at_idx');
        });
    }
};

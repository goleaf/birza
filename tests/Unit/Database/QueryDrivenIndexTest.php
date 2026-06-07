<?php

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QueryDrivenIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_query_driven_indexes_exist_with_expected_columns(): void
    {
        $expectedIndexes = [
            'orders' => [
                'orders_buyer_created_at_idx' => ['buyer_id', 'created_at'],
                'orders_buyer_payment_created_at_idx' => ['buyer_id', 'payment_status', 'created_at'],
                'orders_payment_created_at_idx' => ['payment_status', 'created_at'],
            ],
            'order_items' => [
                'order_items_seller_order_idx' => ['seller_id', 'order_id'],
                'order_items_order_product_idx' => ['order_id', 'product_id'],
            ],
            'buyer_credit_history' => [
                'buyer_credit_history_buyer_type_created_idx' => ['buyer_id', 'type', 'created_at'],
            ],
            'seller_transactions' => [
                'seller_transactions_seller_type_created_idx' => ['seller_id', 'type', 'created_at'],
            ],
            'users_buyers' => [
                'users_buyers_active_created_at_idx' => ['is_active', 'created_at'],
                'users_buyers_verified_created_at_idx' => ['is_verified', 'created_at'],
                'users_buyers_credit_balance_idx' => ['credit_balance'],
            ],
            'users_sellers' => [
                'users_sellers_active_created_at_idx' => ['is_active', 'created_at'],
            ],
            'categories' => [
                'categories_parent_order_idx' => ['parent_category_id', 'order', 'id'],
            ],
            'countries' => [
                'countries_region_active_alpha2_idx' => ['region', 'is_active', 'alpha2'],
            ],
            'attribute_values' => [
                'attribute_values_attribute_active_idx' => ['attribute_id', 'is_active'],
            ],
        ];

        foreach ($expectedIndexes as $table => $indexes) {
            $actualIndexes = collect(Schema::getIndexes($table))->keyBy('name');

            foreach ($indexes as $name => $columns) {
                $this->assertTrue($actualIndexes->has($name), "Missing index [{$name}] on [{$table}].");
                $this->assertSame($columns, $actualIndexes->get($name)['columns']);
            }
        }
    }
}

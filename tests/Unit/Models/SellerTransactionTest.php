<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\SellerTransaction;
use App\Models\Users\Seller;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_belongs_to_seller(): void
    {
        $seller = Seller::factory()->create();
        $transaction = SellerTransaction::factory()->create(['seller_id' => $seller->id]);

        $this->assertInstanceOf(Seller::class, $transaction->seller);
        $this->assertEquals($seller->id, $transaction->seller->id);
    }

    public function test_transaction_belongs_to_order(): void
    {
        $order = Order::factory()->create();
        $transaction = SellerTransaction::factory()->create(['order_id' => $order->id]);

        $this->assertInstanceOf(Order::class, $transaction->order);
        $this->assertEquals($order->id, $transaction->order->id);
    }

    public function test_transaction_casts(): void
    {
        $transaction = SellerTransaction::factory()->create([
            'amount' => '100.50',
        ]);

        $this->assertIsFloat($transaction->amount);
    }
}


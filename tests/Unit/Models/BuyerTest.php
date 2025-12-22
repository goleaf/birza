<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Users\Buyer;
use App\Models\Order;
use App\Models\BuyerCreditHistory;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuyerTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_has_many_orders(): void
    {
        $buyer = Buyer::factory()->create();
        Order::factory()->count(3)->create(['buyer_id' => $buyer->id]);

        $this->assertCount(3, $buyer->orders);
    }

    public function test_buyer_has_many_credit_history(): void
    {
        $buyer = Buyer::factory()->create();
        BuyerCreditHistory::factory()->count(3)->create(['buyer_id' => $buyer->id]);

        $this->assertCount(3, $buyer->creditHistory);
    }

    public function test_buyer_add_credit(): void
    {
        $buyer = Buyer::factory()->create(['credit_balance' => 100]);
        $admin = Admin::factory()->create();

        $buyer->addCredit(50, $admin->id, 'Test credit');

        $buyer->refresh();
        $this->assertEquals(150, $buyer->credit_balance);
        $this->assertDatabaseHas('buyer_credit_history', [
            'buyer_id' => $buyer->id,
            'amount' => 50,
            'type' => 'add',
        ]);
    }

    public function test_buyer_deduct_credit_success(): void
    {
        $buyer = Buyer::factory()->create(['credit_balance' => 100]);
        $admin = Admin::factory()->create();

        $result = $buyer->deductCredit(50, $admin->id, 'Test deduction');

        $buyer->refresh();
        $this->assertTrue($result);
        $this->assertEquals(50, $buyer->credit_balance);
    }

    public function test_buyer_deduct_credit_insufficient_balance(): void
    {
        $buyer = Buyer::factory()->create(['credit_balance' => 30]);
        $admin = Admin::factory()->create();

        $result = $buyer->deductCredit(50, $admin->id, 'Test deduction');

        $buyer->refresh();
        $this->assertFalse($result);
        $this->assertEquals(30, $buyer->credit_balance);
    }

    public function test_buyer_soft_deletes(): void
    {
        $buyer = Buyer::factory()->create();
        $buyerId = $buyer->id;

        $buyer->delete();

        $this->assertSoftDeleted('users_buyers', ['id' => $buyerId]);
    }

    public function test_buyer_password_is_hashed(): void
    {
        $buyer = Buyer::factory()->create(['password' => 'plaintext']);

        $this->assertNotEquals('plaintext', $buyer->password);
        $this->assertTrue(\Hash::check('plaintext', $buyer->password));
    }
}


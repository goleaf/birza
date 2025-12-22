<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\BuyerCreditHistory;
use App\Models\Users\Buyer;
use App\Models\Users\Admin;
use App\Models\CreditAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuyerCreditHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_history_belongs_to_buyer(): void
    {
        $buyer = Buyer::factory()->create();
        $history = BuyerCreditHistory::factory()->create(['buyer_id' => $buyer->id]);

        $this->assertInstanceOf(Buyer::class, $history->buyer);
        $this->assertEquals($buyer->id, $history->buyer->id);
    }

    public function test_credit_history_belongs_to_admin(): void
    {
        $admin = Admin::factory()->create();
        $history = BuyerCreditHistory::factory()->create(['admin_id' => $admin->id]);

        $this->assertInstanceOf(Admin::class, $history->admin);
        $this->assertEquals($admin->id, $history->admin->id);
    }

    public function test_credit_history_has_many_attachments(): void
    {
        $history = BuyerCreditHistory::factory()->create();
        CreditAttachment::factory()->count(3)->create(['credit_history_id' => $history->id]);

        $this->assertCount(3, $history->attachments);
    }

    public function test_credit_history_casts(): void
    {
        $history = BuyerCreditHistory::factory()->create([
            'amount' => '100.50',
            'balance_after' => '500.75',
        ]);

        $this->assertIsFloat($history->amount);
        $this->assertIsFloat($history->balance_after);
    }
}


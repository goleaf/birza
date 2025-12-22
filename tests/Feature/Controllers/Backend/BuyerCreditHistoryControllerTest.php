<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\BuyerCreditHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuyerCreditHistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_history_index_requires_authentication(): void
    {
        $buyer = Buyer::factory()->create();
        $response = $this->get(route('backend.buyers.credit_history', $buyer));

        $response->assertRedirect(route('home'));
    }

    public function test_credit_history_index_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $buyer = Buyer::factory()->create();
        BuyerCreditHistory::factory()->count(3)->create(['buyer_id' => $buyer->id]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.buyers.credit_history', $buyer));

        $response->assertStatus(200);
    }
}


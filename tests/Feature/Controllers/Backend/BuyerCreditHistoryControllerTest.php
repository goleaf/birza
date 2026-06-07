<?php

namespace Tests\Feature\Controllers\Backend;

use App\Livewire\Backend\Buyers\CreditHistory as BuyerCreditHistoryPage;
use App\Models\BuyerCreditHistory;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

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
        BuyerCreditHistory::factory()->create([
            'buyer_id' => $buyer->id,
            'admin_id' => $admin->id,
            'amount' => 123.45,
            'type' => 'add',
            'balance_after' => 456.78,
            'note' => 'Credit adjustment',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.buyers.credit_history', $buyer));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerCreditHistoryPage::class)
            ->assertSee(__('common_filter'))
            ->assertSee(__('backend_buyers_credit_history_filter_apply'))
            ->assertSee(__('backend_buyers_credit_history_filter_date_from'))
            ->assertSee(__('backend_buyers_credit_history_filter_date_to'))
            ->assertSee('Credit adjustment')
            ->assertSee('123.45');
    }

    public function test_credit_history_can_be_filtered_by_type(): void
    {
        $admin = Admin::factory()->create();
        $buyer = Buyer::factory()->create();

        BuyerCreditHistory::factory()->create([
            'buyer_id' => $buyer->id,
            'admin_id' => $admin->id,
            'amount' => 90,
            'type' => 'add',
            'balance_after' => 190,
            'note' => 'Credit only',
        ]);

        BuyerCreditHistory::factory()->create([
            'buyer_id' => $buyer->id,
            'admin_id' => $admin->id,
            'amount' => 25,
            'type' => 'deduct',
            'balance_after' => 165,
            'note' => 'Debit only',
        ]);

        $this->actingAs($admin, 'admin');

        Livewire::test(BuyerCreditHistoryPage::class, ['buyer' => $buyer])
            ->set('typeFilter', 'add')
            ->call('applyFilters')
            ->assertSee('Credit only')
            ->assertDontSee('Debit only');
    }
}

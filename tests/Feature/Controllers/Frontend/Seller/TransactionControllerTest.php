<?php

namespace Tests\Feature\Controllers\Frontend\Seller;

use App\Models\SellerTransaction;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_index_requires_authentication(): void
    {
        $response = $this->get(route('seller.transactions.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_transaction_index_displays_for_authenticated_seller(): void
    {
        $seller = Seller::factory()->create();
        SellerTransaction::factory()->count(3)->create([
            'seller_id' => $seller->id,
            'type' => 'deduction',
        ]);

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.transactions.index'));

        $response->assertStatus(200)
            ->assertSee(__('common_dashboard'))
            ->assertSee(__('common_transactions'))
            ->assertSee(__('common_back_to_dashboard'))
            ->assertSee(__('transactions_total_refunds'))
            ->assertSee(__('transactions_total_deductions'))
            ->assertSee('flatpickr.min.css')
            ->assertSee('flatpickr($refs.input', false)
            ->assertSee('badge-error')
            ->assertSee('font-black text-xl', false);
    }
}

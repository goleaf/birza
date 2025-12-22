<?php

namespace Tests\Feature\Controllers\Frontend\Seller;

use Tests\TestCase;
use App\Models\Users\Seller;
use App\Models\SellerTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        SellerTransaction::factory()->count(3)->create(['seller_id' => $seller->id]);

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.transactions.index'));

        $response->assertStatus(200);
    }
}


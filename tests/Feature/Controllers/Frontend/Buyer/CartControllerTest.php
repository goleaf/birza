<?php

namespace Tests\Feature\Controllers\Frontend\Buyer;

use App\Livewire\Frontend\Buyer\Cart\Index as BuyerCartIndex;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_index_requires_authentication(): void
    {
        $response = $this->get(route('buyer.cart.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_cart_index_displays_for_authenticated_buyer(): void
    {
        $buyer = Buyer::factory()->create();

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.cart.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerCartIndex::class);
    }
}

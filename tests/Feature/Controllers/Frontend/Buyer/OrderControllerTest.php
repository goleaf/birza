<?php

namespace Tests\Feature\Controllers\Frontend\Buyer;

use Tests\TestCase;
use App\Models\Users\Buyer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_index_requires_authentication(): void
    {
        $response = $this->get(route('buyer.orders.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_order_index_displays_for_authenticated_buyer(): void
    {
        $buyer = Buyer::factory()->create();
        Order::factory()->count(3)->create(['buyer_id' => $buyer->id]);

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.orders.index'));

        $response->assertStatus(200);
    }
}


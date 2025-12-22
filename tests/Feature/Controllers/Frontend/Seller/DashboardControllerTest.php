<?php

namespace Tests\Feature\Controllers\Frontend\Seller;

use Tests\TestCase;
use App\Models\Users\Seller;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('seller.dashboard'));

        $response->assertRedirect(route('home'));
    }

    public function test_dashboard_displays_for_authenticated_seller(): void
    {
        $seller = Seller::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'seller_id' => $seller->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.dashboard'));

        $response->assertStatus(200);
    }
}


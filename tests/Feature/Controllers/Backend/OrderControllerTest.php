<?php

namespace Tests\Feature\Controllers\Backend;

use App\Livewire\Backend\Orders\Index as OrderIndex;
use App\Livewire\Backend\Orders\Show as OrderShow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_index_requires_authentication(): void
    {
        $response = $this->get(route('admin.orders.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_order_index_displays_orders(): void
    {
        $admin = Admin::factory()->create();
        Order::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(OrderIndex::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_orders'))
            ->assertSee(__('orders_table_date'));
    }

    public function test_order_show_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $buyer = Buyer::factory()->create([
            'company_name' => 'Archived Buyer',
        ]);
        $seller = Seller::factory()->create([
            'company_name' => 'Baltic Farm',
        ]);
        $product = Product::factory()->for($seller, 'seller')->create([
            'name' => 'Aged Cheese',
        ]);
        $order = Order::factory()->for($buyer, 'buyer')->create();

        OrderItem::factory()
            ->for($order)
            ->for($product)
            ->for($seller, 'seller')
            ->create();

        $buyer->delete();
        $product->delete();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $order));

        $response->assertStatus(200)
            ->assertSeeLivewire(OrderShow::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_orders'))
            ->assertSee(__('orders_order_details').' #'.$order->id)
            ->assertSee('Aged Cheese')
            ->assertSee('Baltic Farm')
            ->assertSee(__('backend_orders_show_deleted_buyer_alert'))
            ->assertSee(__('backend_orders_show_deleted_products_alert', ['count' => 1]));
    }
}

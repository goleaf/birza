<?php

namespace Tests\Feature\Controllers\Frontend\Seller;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Livewire\Frontend\Seller\Orders\Show as SellerOrderShow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_index_requires_authentication(): void
    {
        $response = $this->get(route('seller.orders.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_order_index_displays_for_authenticated_seller(): void
    {
        $seller = Seller::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $order = Order::factory()->create([
            'payment_status' => OrderPaymentStatus::Pending,
        ]);
        OrderItem::factory()->create([
            'seller_id' => $seller->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.orders.index'));

        $response->assertStatus(200)
            ->assertSee(__('common_dashboard'))
            ->assertSee(__('common_orders'))
            ->assertSee(__('orders_order_list'))
            ->assertSee(__('common_back_to_dashboard'))
            ->assertSee(__('dashboard_total_revenue'))
            ->assertSee(__('dashboard_avg_order_value'))
            ->assertSee(__('orders_calendar_title'))
            ->assertSee(__('orders_calendar_subtitle'))
            ->assertSee('flatpickr.min.css')
            ->assertSee('flatpickr($refs.input', false)
            ->assertSee('order-calendar-event-pending', false)
            ->assertSee('VanillaCalendarPro.Calendar', false)
            ->assertSee('badge-warning')
            ->assertSee('font-black text-xl', false);
    }

    public function test_order_index_displays_seller_subtotals_instead_of_full_order_totals(): void
    {
        $seller = Seller::factory()->create();
        $otherSeller = Seller::factory()->create();
        $sellerProduct = Product::factory()->create(['seller_id' => $seller->id]);
        $otherProduct = Product::factory()->create(['seller_id' => $otherSeller->id]);
        $order = Order::factory()->create([
            'payment_status' => OrderPaymentStatus::Paid,
            'order_total' => 100,
        ]);

        OrderItem::factory()->create([
            'seller_id' => $seller->id,
            'order_id' => $order->id,
            'product_id' => $sellerProduct->id,
            'total_price' => 20,
        ]);
        OrderItem::factory()->create([
            'seller_id' => $otherSeller->id,
            'order_id' => $order->id,
            'product_id' => $otherProduct->id,
            'total_price' => 80,
        ]);

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.orders.index'));

        $response->assertOk()
            ->assertSee('€20.00')
            ->assertSee('20.00 €')
            ->assertDontSee('€100.00')
            ->assertDontSee('100.00 €');
    }

    public function test_order_index_keeps_aggregate_queries_bounded(): void
    {
        $seller = Seller::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $order = Order::factory()->create([
            'payment_status' => OrderPaymentStatus::Paid,
        ]);
        OrderItem::factory()->create([
            'seller_id' => $seller->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        DB::enableQueryLog();

        try {
            $this->actingAs($seller, 'seller')
                ->get(route('seller.orders.index'))
                ->assertOk();

            $aggregateQueries = collect(DB::getQueryLog())
                ->pluck('query')
                ->filter(fn (string $query): bool => str_contains(strtolower($query), 'sum('));

            $this->assertLessThanOrEqual(4, $aggregateQueries->count());
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_order_show_displays_for_authenticated_seller(): void
    {
        $seller = Seller::factory()->create();
        $buyer = Buyer::factory()->create([
            'name' => 'Buyer Jane',
        ]);
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'name' => 'Smoked Ham',
        ]);
        $order = Order::factory()->for($buyer, 'buyer')->create([
            'payment_status' => OrderPaymentStatus::Pending,
        ]);

        OrderItem::factory()->create([
            'seller_id' => $seller->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.orders.show', $order));

        $response->assertStatus(200)
            ->assertSee(__('common_dashboard'))
            ->assertSee(__('common_orders'))
            ->assertSee(__('orders_order_details').' #'.$order->id)
            ->assertSee(__('common_back_to_orders'))
            ->assertSee(__('orders_steps_title'))
            ->assertSee(__('orders_order_timeline'))
            ->assertSee(__('orders_timeline_order_placed_title'))
            ->assertSee(__('orders_timeline_waiting_confirmation_description'))
            ->assertSee(OrderStatus::Pending->label())
            ->assertSee(OrderStatus::Pending->description())
            ->assertSee('Smoked Ham')
            ->assertSee('badge-warning');
    }

    public function test_order_confirm_updates_seller_lifecycle_step(): void
    {
        $seller = Seller::factory()->create([
            'balance' => 0,
        ]);
        $buyer = Buyer::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
        ]);
        $order = Order::factory()->for($buyer, 'buyer')->create([
            'payment_status' => OrderPaymentStatus::Pending,
            'status' => OrderStatus::Pending,
        ]);

        OrderItem::factory()->create([
            'seller_id' => $seller->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 12.50,
            'total_price' => 25.00,
        ]);

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerOrderShow::class, ['order' => $order])
            ->assertSet('currentOrderStep', 1)
            ->call('updateStatus', OrderStatus::Accepted->value)
            ->assertSet('currentOrderStep', 2)
            ->assertSee(__('orders_timeline_processing_next_description'));

        $order->refresh();
        $seller->refresh();

        $this->assertSame(OrderPaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Accepted, $order->status);
        $this->assertSame(25.0, (float) $seller->balance);
    }

    public function test_order_cancel_confirmation_uses_modal_flow(): void
    {
        $seller = Seller::factory()->create([
            'balance' => 0,
        ]);
        $buyer = Buyer::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
        ]);
        $order = Order::factory()->for($buyer, 'buyer')->create([
            'payment_status' => OrderPaymentStatus::Pending,
            'status' => OrderStatus::Pending,
        ]);

        OrderItem::factory()->create([
            'seller_id' => $seller->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 12.50,
            'total_price' => 25.00,
        ]);

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerOrderShow::class, ['order' => $order])
            ->assertSet('currentOrderStep', 1)
            ->call('confirmCancelOrder')
            ->assertSet('confirmModal', true)
            ->assertSet('confirmModalMethod', 'updateStatus')
            ->call('runConfirmedAction')
            ->assertSet('confirmModal', false)
            ->assertSet('currentOrderStep', 1);

        $order->refresh();

        $this->assertSame(OrderPaymentStatus::Cancelled, $order->payment_status);
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame(0.0, (float) $seller->fresh()->balance);
    }
}

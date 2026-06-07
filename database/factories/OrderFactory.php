<?php

namespace Database\Factories;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderStatusActorRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $total = $this->faker->randomFloat(2, 10, 1000);
        $createdAt = $this->faker->dateTimeBetween('-9 months', 'now');

        return [
            'order_total' => $total,
            'subtotal' => $total,
            'buyer_id' => Buyer::factory(),
            'payment_method' => $this->faker->randomElement(['card', 'bank_transfer', 'cash']),
            'payment_status' => $this->faker->randomElement(OrderPaymentStatus::cases()),
            'status' => OrderStatus::Pending,
            'shipping_address_snapshot' => $this->faker->address(),
            'billing_address_snapshot' => $this->faker->address(),
            'delivery_method' => $this->faker->randomElement(['pickup', 'courier', 'seller_delivery']),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function status(OrderStatus $status, ?OrderPaymentStatus $paymentStatus = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => $status,
            'payment_status' => $paymentStatus ?? $this->paymentStatusFor($status),
        ]);
    }

    public function pending(): static
    {
        return $this->status(OrderStatus::Pending, OrderPaymentStatus::Pending);
    }

    public function accepted(): static
    {
        return $this->status(OrderStatus::Accepted, OrderPaymentStatus::Paid);
    }

    public function paid(): static
    {
        return $this->accepted();
    }

    public function rejected(): static
    {
        return $this->status(OrderStatus::Rejected, OrderPaymentStatus::Cancelled);
    }

    public function processing(): static
    {
        return $this->status(OrderStatus::Processing, OrderPaymentStatus::Paid);
    }

    public function shipped(): static
    {
        return $this->status(OrderStatus::Shipped, OrderPaymentStatus::Paid);
    }

    public function delivered(): static
    {
        return $this->status(OrderStatus::Delivered, OrderPaymentStatus::Paid);
    }

    public function completed(): static
    {
        return $this->status(OrderStatus::Completed, OrderPaymentStatus::Paid);
    }

    public function cancelled(): static
    {
        return $this->status(OrderStatus::Cancelled, OrderPaymentStatus::Cancelled);
    }

    public function refunded(): static
    {
        return $this->status(OrderStatus::Refunded, OrderPaymentStatus::Refunded);
    }

    public function disputed(): static
    {
        return $this->status(OrderStatus::Disputed, OrderPaymentStatus::Paid);
    }

    public function failedPayment(): static
    {
        return $this->state(fn (array $attributes): array => [
            'payment_status' => OrderPaymentStatus::Failed,
        ]);
    }

    public function paymentPending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'payment_status' => OrderPaymentStatus::Pending,
        ]);
    }

    public function withItems(int $count = 2, ?Seller $seller = null): static
    {
        return $this->afterCreating(function (Order $order) use ($count, $seller): void {
            $total = 0.0;

            for ($index = 0; $index < max(1, $count); $index++) {
                $product = Product::factory()
                    ->for($seller ?? Seller::factory(), 'seller')
                    ->create();

                $item = OrderItem::factory()
                    ->for($order)
                    ->forProduct($product)
                    ->create();

                $total += (float) $item->total_price;
            }

            $order->forceFill([
                'subtotal' => $total,
                'order_total' => $total,
            ])->save();
        });
    }

    public function withStatusHistory(): static
    {
        return $this->afterCreating(function (Order $order): void {
            if (! Schema::hasTable('order_status_histories')) {
                return;
            }

            OrderStatusHistory::factory()
                ->for($order)
                ->create([
                    'old_status' => OrderStatus::Pending,
                    'new_status' => $order->lifecycleStatus(),
                    'changed_by_role' => OrderStatusActorRole::System,
                    'created_at' => $order->created_at,
                ]);
        });
    }

    public function softDeleted(): static
    {
        return $this->afterCreating(function (Order $order): void {
            $order->delete();
        });
    }

    private function paymentStatusFor(OrderStatus $status): OrderPaymentStatus
    {
        return match ($status) {
            OrderStatus::Pending => OrderPaymentStatus::Pending,
            OrderStatus::Rejected, OrderStatus::Cancelled => OrderPaymentStatus::Cancelled,
            OrderStatus::Refunded => OrderPaymentStatus::Refunded,
            default => OrderPaymentStatus::Paid,
        };
    }
}

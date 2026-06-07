<?php

namespace Database\Seeders\Demo;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderStatusActorRole;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\SellerTransaction;
use App\Models\Users\Buyer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoOrderSeeder extends Seeder
{
    public function run(): void
    {
        $buyer = Buyer::query()->where('email', 'buyer@example.com')->firstOrFail();
        $ordersBuyer = Buyer::query()->where('email', 'demo-orders-buyer@example.com')->firstOrFail();

        $products = collect([
            'Demo Active Apples',
            'Demo Published Product',
            'Demo Changed Price Cheese',
            'Demo Seller Two Bread',
            'Demo Low Stock Yogurt',
        ])
            ->map(fn (string $name): Product => Product::withTrashed()->where('name', $name)->firstOrFail())
            ->values();

        foreach (OrderStatus::cases() as $index => $status) {
            $this->statusOrder(
                buyer: $index % 2 === 0 ? $buyer : $ordersBuyer,
                product: $products->get($index % $products->count()),
                status: $status,
                quantity: 1 + ($index % 4),
                daysAgo: 7 + ($index * 11),
            );
        }

        $this->deletedProductSnapshotOrder($buyer);
    }

    private function statusOrder(
        Buyer $buyer,
        Product $product,
        OrderStatus $status,
        int $quantity,
        int $daysAgo,
    ): Order {
        $subtotal = round((float) $product->price * $quantity, 2);
        $createdAt = now()->subDays($daysAgo);

        $order = Order::query()->firstOrNew([
            'buyer_id' => $buyer->id,
            'payment_method' => 'demo_status_'.$status->value,
        ]);

        $order->forceFill([
            'subtotal' => $subtotal,
            'order_total' => $subtotal,
            'payment_status' => $this->paymentStatusFor($status),
            'status' => $status,
            'shipping_address_snapshot' => $buyer->address ?? 'Demo shipping address',
            'billing_address_snapshot' => $buyer->address ?? 'Demo billing address',
            'delivery_method' => 'courier',
        ]);
        $order->created_at = $createdAt;
        $order->updated_at = $createdAt->copy()->addHours(4);

        Order::allowStatusMutation(fn (): bool => $order->save());

        $this->item($order, $product, $quantity);
        $this->history($order, $status, $createdAt);
        $this->sellerTransaction($order, $product, $status, $subtotal);

        return $order;
    }

    private function deletedProductSnapshotOrder(Buyer $buyer): void
    {
        $product = Product::withTrashed()
            ->where('name', 'Demo Soft Deleted Product')
            ->firstOrFail();

        $subtotal = round((float) $product->price * 2, 2);
        $createdAt = now()->subDays(180);

        $order = Order::query()->firstOrNew([
            'buyer_id' => $buyer->id,
            'payment_method' => 'demo_deleted_product_snapshot',
        ]);

        $order->forceFill([
            'subtotal' => $subtotal,
            'order_total' => $subtotal,
            'payment_status' => OrderPaymentStatus::Paid,
            'status' => OrderStatus::Completed,
            'delivery_method' => 'courier',
        ]);
        $order->created_at = $createdAt;
        $order->updated_at = $createdAt->copy()->addHours(4);
        $order->shipping_address_snapshot = 'Archived Buyer Address 1, Vilnius';
        $order->billing_address_snapshot = 'Archived Billing Address 1, Vilnius';

        Order::allowStatusMutation(fn (): bool => $order->save());

        $this->item($order, $product, 2);
        $this->history($order, OrderStatus::Completed, $createdAt);
        $this->sellerTransaction($order, $product, OrderStatus::Completed, $subtotal);
    }

    private function item(Order $order, Product $product, int $quantity): void
    {
        $unitPrice = (float) $product->price;

        $order->items()->updateOrCreate([
            'product_id' => $product->id,
        ], [
            'seller_id' => $product->seller_id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => round($unitPrice * $quantity, 2),
            'product_title_snapshot' => $product->name ?? 'Deleted product',
            'product_price_snapshot' => $unitPrice,
            'seller_name_snapshot' => $product->seller?->company_name,
        ]);
    }

    private function history(Order $order, OrderStatus $status, mixed $createdAt): void
    {
        if (! Schema::hasTable('order_status_histories')) {
            return;
        }

        OrderStatusHistory::query()->updateOrCreate([
            'order_id' => $order->id,
            'new_status' => $status->value,
        ], [
            'old_status' => OrderStatus::Pending,
            'changed_by_user_id' => null,
            'changed_by_role' => OrderStatusActorRole::System,
            'reason' => 'Demo lifecycle coverage',
            'note' => 'Seeded order status for dashboard and filter testing.',
            'created_at' => $createdAt,
        ]);
    }

    private function sellerTransaction(Order $order, Product $product, OrderStatus $status, float $amount): void
    {
        if (! Schema::hasTable('seller_transactions')) {
            return;
        }

        $type = match ($status) {
            OrderStatus::Refunded => 'refund',
            OrderStatus::Cancelled, OrderStatus::Rejected => 'deduction',
            default => 'addition',
        };

        SellerTransaction::query()->updateOrCreate([
            'seller_id' => $product->seller_id,
            'order_id' => $order->id,
            'type' => $type,
        ], [
            'amount' => $amount,
            'description' => 'Demo '.$type.' for order #'.$order->id,
        ]);
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

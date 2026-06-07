<?php

namespace Database\Seeders\Demo;

use App\Models\Conversation;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoNotificationSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        $buyer = Buyer::query()->where('email', 'buyer@example.com')->first();
        $seller = Seller::query()->where('email', 'seller@example.com')->first();
        $sellerOne = Seller::query()->where('email', 'demo-seller-one@example.com')->first();
        $admin = Admin::query()->where('email', 'admin@example.com')->first();

        $this->clearDemoNotifications(collect([$buyer, $seller, $sellerOne, $admin])->filter());

        if ($buyer instanceof Buyer) {
            $this->seedBuyerNotifications($buyer);
            $this->seedBuyerMessageNotification($buyer);
        }

        if ($seller instanceof Seller) {
            $this->seedSellerNotifications($seller);
            $this->seedSellerMessageNotification($seller);
        }

        if ($sellerOne instanceof Seller) {
            $this->seedRejectedProductNotification($sellerOne);
        }

        if ($admin instanceof Admin) {
            $this->seedAdminNotifications($admin);
        }
    }

    /**
     * @param  Collection<int, Model>  $notifiables
     */
    private function clearDemoNotifications(Collection $notifiables): void
    {
        $notifiables->each(function (Model $notifiable): void {
            $notifiable->notifications()
                ->where('data->source', 'demo_seeder')
                ->delete();
        });
    }

    private function seedBuyerNotifications(Buyer $buyer): void
    {
        $orders = Order::query()
            ->where('buyer_id', $buyer->id)
            ->orderByDesc('id')
            ->get(['id', 'buyer_id', 'status', 'created_at'])
            ->values();

        if ($orders->isEmpty()) {
            return;
        }

        for ($index = 0; $index < 12; $index++) {
            /** @var Order $order */
            $order = $orders->get($index % $orders->count());

            $this->notification(
                notifiable: $buyer,
                type: 'marketplace.order.status_changed',
                data: [
                    'title_key' => 'notifications.orders.status_changed.title',
                    'message_key' => 'notifications.orders.status_changed.message',
                    'title_params' => ['order' => $order->id],
                    'message_params' => [
                        'order' => $order->id,
                        'old' => __('orders.status.pending'),
                        'new' => $order->lifecycleStatus()->label(),
                    ],
                    'related_type' => 'order',
                    'related_id' => $order->id,
                    'url' => route('buyer.orders.show', $order, false),
                    'status' => $order->lifecycleStatus()->value,
                    'icon' => $order->lifecycleStatus()->icon(),
                ],
                daysAgo: $index + 1,
                read: $index > 3,
            );
        }
    }

    private function seedSellerNotifications(Seller $seller): void
    {
        $order = Order::query()
            ->whereHas('items', fn ($query) => $query->where('seller_id', $seller->id))
            ->orderByDesc('id')
            ->first(['id', 'status']);

        if ($order instanceof Order) {
            $this->notification(
                notifiable: $seller,
                type: 'marketplace.order.new_for_seller',
                data: [
                    'title_key' => 'notifications.orders.new_for_seller.title',
                    'message_key' => 'notifications.orders.new_for_seller.message',
                    'title_params' => ['order' => $order->id],
                    'message_params' => ['order' => $order->id],
                    'related_type' => 'order',
                    'related_id' => $order->id,
                    'url' => route('seller.orders.show', $order, false),
                    'status' => $order->lifecycleStatus()->value,
                    'icon' => 'shopping-bag',
                ],
                daysAgo: 1,
            );
        }

        $product = Product::query()
            ->where('name', 'Demo Low Stock Yogurt')
            ->where('seller_id', $seller->id)
            ->first(['id', 'seller_id', 'name', 'stock']);

        if ($product instanceof Product) {
            $this->notification(
                notifiable: $seller,
                type: 'marketplace.stock.low',
                data: [
                    'title_key' => 'notifications.stock.low.title',
                    'message_key' => 'notifications.stock.low.message',
                    'title_params' => ['product' => $product->name],
                    'message_params' => [
                        'product' => $product->name,
                        'stock' => (int) $product->stock,
                        'threshold' => (int) config('notifications.low_stock_threshold', 5),
                    ],
                    'related_type' => 'product',
                    'related_id' => $product->id,
                    'url' => route('seller.products.edit', $product, false),
                    'status' => 'low',
                    'icon' => 'exclamation-triangle',
                ],
                daysAgo: 2,
                read: true,
            );
        }
    }

    private function seedBuyerMessageNotification(Buyer $buyer): void
    {
        $conversation = Conversation::query()
            ->forBuyer($buyer)
            ->unreadFor($buyer)
            ->with(['latestMessage.senderSeller'])
            ->latestActivity()
            ->first();

        if (! $conversation instanceof Conversation || ! $conversation->latestMessage) {
            return;
        }

        $message = $conversation->latestMessage;

        $this->notification(
            notifiable: $buyer,
            type: 'marketplace.message.new',
            data: [
                'title_key' => 'notifications.messages.new.title',
                'message_key' => 'notifications.messages.new.message',
                'title_params' => ['sender' => $message->senderLabel()],
                'message_params' => [
                    'sender' => $message->senderLabel(),
                    'preview' => $message->preview(),
                ],
                'related_type' => 'conversation',
                'related_id' => $conversation->id,
                'url' => route('buyer.messages.show', $conversation, false),
                'status' => $conversation->status->value,
                'icon' => 'chat-bubble-left-right',
            ],
            daysAgo: 1,
        );
    }

    private function seedSellerMessageNotification(Seller $seller): void
    {
        $conversation = Conversation::query()
            ->forSeller($seller)
            ->unreadFor($seller)
            ->with(['latestMessage.senderBuyer'])
            ->latestActivity()
            ->first();

        if (! $conversation instanceof Conversation || ! $conversation->latestMessage) {
            return;
        }

        $message = $conversation->latestMessage;

        $this->notification(
            notifiable: $seller,
            type: 'marketplace.message.new',
            data: [
                'title_key' => 'notifications.messages.new.title',
                'message_key' => 'notifications.messages.new.message',
                'title_params' => ['sender' => $message->senderLabel()],
                'message_params' => [
                    'sender' => $message->senderLabel(),
                    'preview' => $message->preview(),
                ],
                'related_type' => 'conversation',
                'related_id' => $conversation->id,
                'url' => route('seller.messages.show', $conversation, false),
                'status' => $conversation->status->value,
                'icon' => 'chat-bubble-left-right',
            ],
            daysAgo: 1,
        );
    }

    private function seedRejectedProductNotification(Seller $seller): void
    {
        $product = Product::query()
            ->where('name', 'Demo Inactive Honey')
            ->where('seller_id', $seller->id)
            ->first(['id', 'seller_id', 'name']);

        if (! $product instanceof Product) {
            return;
        }

        $this->notification(
            notifiable: $seller,
            type: 'marketplace.product.rejected',
            data: [
                'title_key' => 'notifications.products.rejected.title',
                'message_key' => 'notifications.products.rejected.message_with_reason',
                'title_params' => ['product' => $product->name],
                'message_params' => [
                    'product' => $product->name,
                    'reason' => 'Demo moderation reason',
                ],
                'related_type' => 'product',
                'related_id' => $product->id,
                'url' => route('seller.products.edit', $product, false),
                'status' => 'rejected',
                'icon' => 'x-circle',
            ],
            daysAgo: 1,
        );
    }

    private function seedAdminNotifications(Admin $admin): void
    {
        Product::query()
            ->whereIn('name', ['Demo Product Without Image', 'Demo Inactive Category Product'])
            ->orderBy('id')
            ->get(['id', 'name'])
            ->each(function (Product $product, int $index) use ($admin): void {
                $this->notification(
                    notifiable: $admin,
                    type: 'marketplace.product.moderation_required',
                    data: [
                        'title_key' => 'notifications.products.moderation_required.title',
                        'message_key' => 'notifications.products.moderation_required.message',
                        'title_params' => ['product' => $product->name],
                        'message_params' => ['product' => $product->name],
                        'related_type' => 'product',
                        'related_id' => $product->id,
                        'url' => route('admin.products.show', $product, false),
                        'status' => 'pending',
                        'icon' => 'shield-exclamation',
                    ],
                    daysAgo: $index + 1,
                    read: $index > 0,
                );
            });

        $report = ProductReport::query()
            ->with(['product', 'reporter'])
            ->whereIn('status', ['pending', 'reviewing'])
            ->orderBy('id')
            ->first();

        if (! $report instanceof ProductReport) {
            return;
        }

        $this->notification(
            notifiable: $admin,
            type: 'marketplace.product_report.created',
            data: [
                'title_key' => 'notifications.reports.product_created.title',
                'message_key' => 'notifications.reports.product_created.message',
                'title_params' => ['product' => $report->product?->name],
                'message_params' => [
                    'product' => $report->product?->name,
                    'reason' => $report->reason->label(),
                    'reporter' => $report->reporterLabel(),
                ],
                'related_type' => 'product_report',
                'related_id' => $report->id,
                'url' => $report
                    ? route('admin.reports.show', $report, false)
                    : route('admin.notifications.index', absolute: false),
                'status' => $report->status->value,
                'icon' => 'shield-exclamation',
            ],
            daysAgo: 3,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function notification(
        Model $notifiable,
        string $type,
        array $data,
        int $daysAgo,
        bool $read = false,
    ): void {
        $createdAt = now()->subDays($daysAgo);

        $notifiable->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => array_merge($data, [
                'source' => 'demo_seeder',
            ]),
            'read_at' => $read ? $createdAt->copy()->addHours(4) : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}

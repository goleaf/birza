<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Admin;
use App\Models\Users\Seller;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::query()->first();
        $seller = Seller::query()->first();
        $product = Product::query()->first();
        $order = Order::query()->first();

        if (! $admin || ! $product) {
            return;
        }

        $this->updateLog($admin, 'product.created', $product, null, [
            'id' => $product->id,
            'price' => $product->price,
            'is_active' => $product->is_active,
        ], ['source' => 'demo_seeder']);

        $this->updateLog($seller ?? $admin, 'product.price_changed', $product, [
            'price' => '10.00',
        ], [
            'price' => (string) $product->price,
        ], ['source' => 'demo_seeder']);

        if ($order) {
            $this->updateLog($admin, 'order.created', $order, null, [
                'id' => $order->id,
                'buyer_id' => $order->buyer_id,
                'order_total' => $order->order_total,
            ], ['source' => 'demo_seeder']);

            $this->updateLog($admin, 'order.status_changed', $order, [
                'status' => 'pending',
            ], [
                'status' => $this->enumValue($order->status),
            ], ['source' => 'demo_seeder'], 'Demo status update');

            $this->updateLog($admin, 'order.dispute_opened', $order, [
                'status' => 'delivered',
            ], [
                'status' => 'disputed',
            ], ['source' => 'demo_seeder'], 'Demo dispute investigation');

            $this->updateLog($admin, 'order.refunded', $order, [
                'status' => 'disputed',
            ], [
                'status' => 'refunded',
            ], ['source' => 'demo_seeder'], 'Demo refund decision');
        }

        $this->updateLog($admin, 'user.blocked', $admin, [
            'is_active' => true,
        ], [
            'is_active' => false,
        ], ['source' => 'demo_seeder'], 'Demo account moderation');

        if ($seller) {
            $this->updateLog($admin, 'seller.approved', $seller, [
                'is_verified' => false,
            ], [
                'is_verified' => true,
            ], ['source' => 'demo_seeder'], 'Demo seller approval');
        }

        $remainingRows = max(0, 35 - AuditLog::query()->count());

        if ($remainingRows > 0) {
            AuditLog::factory()
                ->count($remainingRows)
                ->create([
                    'actor_id' => $admin->id,
                    'actor_type' => $admin::class,
                    'actor_role' => 'admin',
                    'auditable_id' => $product->id,
                    'auditable_type' => $product::class,
                ]);
        }
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $metadata
     */
    private function updateLog(
        mixed $actor,
        string $action,
        mixed $auditable,
        ?array $oldValues,
        ?array $newValues,
        ?array $metadata,
        ?string $reason = null,
    ): void {
        AuditLog::query()->updateOrCreate([
            'action' => $action,
            'auditable_type' => $auditable::class,
            'auditable_id' => $auditable->getKey(),
        ], [
            'actor_id' => $actor->getAuthIdentifier(),
            'actor_type' => $actor::class,
            'actor_role' => match (class_basename($actor)) {
                'Admin' => 'admin',
                'Buyer' => 'buyer',
                'Seller' => 'seller',
                default => 'system',
            },
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'reason' => $reason,
        ]);
    }

    private function enumValue(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }
}

<?php

namespace App\Actions\ProductBundles;

use App\Models\ProductBundle;
use App\Services\AuditLogService;
use Illuminate\Contracts\Auth\Authenticatable;

class RecordProductBundleAuditLogsAction
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function snapshot(ProductBundle $bundle): array
    {
        return $this->auditLogService->snapshot($bundle, $this->fields());
    }

    public function created(?Authenticatable $actor, ProductBundle $bundle, string $source): void
    {
        $this->log($actor, 'product_bundle.created', $bundle, null, $this->snapshot($bundle), $source);
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    public function updated(?Authenticatable $actor, ProductBundle $bundle, array $oldValues, string $source): void
    {
        $newValues = $this->snapshot($bundle);
        $changed = $this->auditLogService->changedValues($oldValues, $newValues);

        if ($changed['old'] === [] && $changed['new'] === []) {
            return;
        }

        $this->log($actor, 'product_bundle.updated', $bundle, $changed['old'], $changed['new'], $source);

        if (
            array_key_exists('discount_type', $changed['old'])
            || array_key_exists('discount_value', $changed['old'])
            || array_key_exists('discount_type', $changed['new'])
            || array_key_exists('discount_value', $changed['new'])
        ) {
            $this->log(
                $actor,
                'product_bundle.discount_changed',
                $bundle,
                collect($changed['old'])->only(['discount_type', 'discount_value'])->all(),
                collect($changed['new'])->only(['discount_type', 'discount_value'])->all(),
                $source,
            );
        }
    }

    public function statusChanged(?Authenticatable $actor, ProductBundle $bundle, string $oldStatus, string $source): void
    {
        $action = match ($bundle->status) {
            ProductBundle::STATUS_ACTIVE => 'product_bundle.published',
            ProductBundle::STATUS_ARCHIVED => 'product_bundle.archived',
            ProductBundle::STATUS_INACTIVE => 'product_bundle.unpublished',
            default => 'product_bundle.status_changed',
        };

        $this->log(
            $actor,
            $action,
            $bundle,
            ['status' => $oldStatus],
            ['status' => $bundle->status],
            $source,
        );
    }

    public function itemAdded(?Authenticatable $actor, ProductBundle $bundle, int $productId, int $quantity, string $source): void
    {
        $this->log(
            $actor,
            'product_bundle.product_added',
            $bundle,
            null,
            ['product_id' => $productId, 'quantity' => $quantity],
            $source,
        );
    }

    public function itemRemoved(?Authenticatable $actor, ProductBundle $bundle, int $productId, string $source): void
    {
        $this->log(
            $actor,
            'product_bundle.product_removed',
            $bundle,
            ['product_id' => $productId],
            null,
            $source,
        );
    }

    public function purchased(?Authenticatable $actor, ProductBundle $bundle, int $orderId, int $quantity): void
    {
        $this->log(
            $actor,
            'product_bundle.purchased',
            $bundle,
            null,
            ['order_id' => $orderId, 'quantity' => $quantity],
            'checkout',
        );
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function log(
        ?Authenticatable $actor,
        string $action,
        ProductBundle $bundle,
        ?array $oldValues,
        ?array $newValues,
        string $source,
    ): void {
        $this->auditLogService->log(
            actor: $actor,
            action: $action,
            auditable: $bundle,
            oldValues: $oldValues,
            newValues: $newValues,
            metadata: [
                'source' => $source,
                'seller_id' => $bundle->seller_id,
            ],
        );
    }

    /**
     * @return list<string>
     */
    private function fields(): array
    {
        return [
            'seller_id',
            'name',
            'slug',
            'description',
            'status',
            'discount_type',
            'discount_value',
            'starts_at',
            'ends_at',
            'published_at',
            'image_path',
            'deleted_at',
        ];
    }
}

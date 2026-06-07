<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Services\AuditLogService;
use Illuminate\Contracts\Auth\Authenticatable;

class RecordProductAuditLogsAction
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function snapshot(Product $product): array
    {
        return $this->auditLogService->snapshot($product, $this->fields());
    }

    /**
     * @return list<string>
     */
    public function imagePaths(Product $product): array
    {
        $freshProduct = $product->fresh(['images']);

        if (! $freshProduct) {
            return [];
        }

        return $freshProduct
            ->imageLibraryPreview()
            ->map(function (mixed $image): ?string {
                if (is_string($image)) {
                    return $image;
                }

                $path = data_get($image, 'path');

                return is_string($path) ? $path : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    public function created(
        ?Authenticatable $actor,
        Product $product,
        string $source,
        ?string $reason = null,
    ): void {
        $newValues = $this->snapshot($product);
        $newImages = $this->imagePaths($product);

        $this->auditLogService->log(
            actor: $actor,
            action: 'product.created',
            auditable: $product,
            oldValues: null,
            newValues: $newValues,
            metadata: [
                'source' => $source,
                'seller_id' => $product->seller_id,
                'image_count' => count($newImages),
            ],
            reason: $reason,
        );

        $this->logImageDelta($actor, $product, [], $newImages, $source, $reason);
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  list<string>  $oldImages
     */
    public function updated(
        ?Authenticatable $actor,
        Product $product,
        array $oldValues,
        array $oldImages,
        string $source,
        ?string $reason = null,
    ): void {
        $newValues = $this->snapshot($product);
        $newImages = $this->imagePaths($product);
        $changed = $this->auditLogService->changedValues($oldValues, $newValues);

        if ($changed['old'] !== [] || $changed['new'] !== []) {
            $this->auditLogService->log(
                actor: $actor,
                action: 'product.updated',
                auditable: $product,
                oldValues: $changed['old'],
                newValues: $changed['new'],
                metadata: [
                    'source' => $source,
                    'seller_id' => $product->seller_id,
                ],
                reason: $reason,
            );
        }

        if (($oldValues['price'] ?? null) !== ($newValues['price'] ?? null)) {
            $this->auditLogService->log(
                actor: $actor,
                action: 'product.price_changed',
                auditable: $product,
                oldValues: ['price' => $oldValues['price'] ?? null],
                newValues: ['price' => $newValues['price'] ?? null],
                metadata: [
                    'source' => $source,
                    'seller_id' => $product->seller_id,
                ],
                reason: $reason,
            );
        }

        if (($oldValues['is_active'] ?? null) !== ($newValues['is_active'] ?? null)) {
            $this->auditLogService->log(
                actor: $actor,
                action: $newValues['is_active'] ? 'product.published' : 'product.unpublished',
                auditable: $product,
                oldValues: ['is_active' => $oldValues['is_active'] ?? null],
                newValues: ['is_active' => $newValues['is_active'] ?? null],
                metadata: [
                    'source' => $source,
                    'seller_id' => $product->seller_id,
                ],
                reason: $reason,
            );
        }

        $this->logImageDelta($actor, $product, $oldImages, $newImages, $source, $reason);
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    public function deleted(
        ?Authenticatable $actor,
        Product $product,
        array $oldValues,
        string $source,
        ?string $reason = null,
        bool $force = false,
    ): void {
        $this->auditLogService->log(
            actor: $actor,
            action: $force ? 'product.force_deleted' : 'product.deleted',
            auditable: $product,
            oldValues: $oldValues,
            newValues: [
                'deleted_at' => $product->deleted_at?->toISOString(),
                'is_active' => $product->is_active,
            ],
            metadata: [
                'source' => $source,
                'seller_id' => $product->seller_id,
            ],
            reason: $reason,
        );
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    public function restored(
        ?Authenticatable $actor,
        Product $product,
        array $oldValues,
        string $source,
        ?string $reason = null,
    ): void {
        $this->auditLogService->log(
            actor: $actor,
            action: 'product.restored',
            auditable: $product,
            oldValues: $oldValues,
            newValues: $this->snapshot($product),
            metadata: [
                'source' => $source,
                'seller_id' => $product->seller_id,
            ],
            reason: $reason,
        );
    }

    /**
     * @param  list<string>  $oldImages
     * @param  list<string>  $newImages
     */
    private function logImageDelta(
        ?Authenticatable $actor,
        Product $product,
        array $oldImages,
        array $newImages,
        string $source,
        ?string $reason,
    ): void {
        $uploaded = collect($newImages)->diff($oldImages)->values()->all();
        $deleted = collect($oldImages)->diff($newImages)->values()->all();

        if ($uploaded !== []) {
            $this->auditLogService->log(
                actor: $actor,
                action: 'product.image_uploaded',
                auditable: $product,
                oldValues: null,
                newValues: ['paths' => $uploaded],
                metadata: [
                    'source' => $source,
                    'seller_id' => $product->seller_id,
                    'image_count' => count($uploaded),
                ],
                reason: $reason,
            );
        }

        if ($deleted !== []) {
            $this->auditLogService->log(
                actor: $actor,
                action: 'product.image_deleted',
                auditable: $product,
                oldValues: ['paths' => $deleted],
                newValues: null,
                metadata: [
                    'source' => $source,
                    'seller_id' => $product->seller_id,
                    'image_count' => count($deleted),
                ],
                reason: $reason,
            );
        }
    }

    /**
     * @return list<string>
     */
    private function fields(): array
    {
        return [
            'name',
            'category_id',
            'seller_id',
            'price',
            'pack_type',
            'unit',
            'country_of_origin',
            'is_organic',
            'is_active',
            'min_order_price',
            'min_order_count',
            'stock',
            'temperature_conditions_from',
            'temperature_conditions_to',
            'use_until',
            'total_shelf_life',
            'package_weight',
            'price_per_liter',
            'deleted_at',
        ];
    }
}

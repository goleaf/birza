<?php

namespace App\Actions\Wishlists;

use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RecordWishlistAuditLogAction
{
    public function created(Buyer $actor, Wishlist $wishlist): void
    {
        $this->log(
            actor: $actor,
            action: 'wishlist.created',
            auditable: $wishlist,
            newValues: $this->snapshot($wishlist),
            metadata: ['source' => 'wishlist_action'],
        );
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    public function updated(Buyer $actor, Wishlist $wishlist, array $oldValues): void
    {
        $newValues = $this->snapshot($wishlist);
        $changed = $this->changedValues($oldValues, $newValues);

        if ($changed['old'] === [] && $changed['new'] === []) {
            return;
        }

        $this->log(
            actor: $actor,
            action: 'wishlist.updated',
            auditable: $wishlist,
            oldValues: $changed['old'],
            newValues: $changed['new'],
            metadata: ['source' => 'wishlist_action'],
        );
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    public function deleted(Buyer $actor, Wishlist $wishlist, array $oldValues): void
    {
        $this->log(
            actor: $actor,
            action: 'wishlist.deleted',
            auditable: $wishlist,
            oldValues: $oldValues,
            metadata: ['source' => 'wishlist_action'],
        );
    }

    public function productAdded(Buyer $actor, Wishlist $wishlist, Product $product): void
    {
        $this->productEvent($actor, 'wishlist.product_added', $wishlist, $product);
    }

    public function productRemoved(Buyer $actor, Wishlist $wishlist, Product $product): void
    {
        $this->productEvent($actor, 'wishlist.product_removed', $wishlist, $product);
    }

    public function productMoved(Buyer $actor, Wishlist $fromWishlist, Wishlist $toWishlist, Product $product): void
    {
        $this->log(
            actor: $actor,
            action: 'wishlist.product_moved',
            auditable: $toWishlist,
            oldValues: ['wishlist_id' => $fromWishlist->id],
            newValues: ['wishlist_id' => $toWishlist->id, 'product_id' => $product->id],
            metadata: [
                'source' => 'wishlist_action',
                'from_wishlist_id' => $fromWishlist->id,
                'product_id' => $product->id,
            ],
        );
    }

    public function cleared(Buyer $actor, Wishlist $wishlist, int $itemsCount): void
    {
        $this->log(
            actor: $actor,
            action: 'wishlist.cleared',
            auditable: $wishlist,
            oldValues: ['items_count' => $itemsCount],
            metadata: ['source' => 'wishlist_action'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(Wishlist $wishlist): array
    {
        return collect($wishlist->getAttributes())
            ->only($this->snapshotFields())
            ->all();
    }

    private function productEvent(Buyer $actor, string $action, Wishlist $wishlist, Product $product): void
    {
        $this->log(
            actor: $actor,
            action: $action,
            auditable: $wishlist,
            newValues: [
                'product_id' => $product->id,
                'product_name' => $product->name,
            ],
            metadata: [
                'source' => 'wishlist_action',
                'product_id' => $product->id,
            ],
        );
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $metadata
     */
    private function log(
        Buyer $actor,
        string $action,
        Wishlist $auditable,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
    ): void {
        $auditLogService = $this->auditLogService();

        if (! $auditLogService || ! method_exists($auditLogService, 'log')) {
            return;
        }

        try {
            $auditLogService->log(
                actor: $actor,
                action: $action,
                auditable: $auditable,
                oldValues: $oldValues,
                newValues: $newValues,
                metadata: $metadata,
            );
        } catch (Throwable) {
            return;
        }
    }

    private function auditLogService(): ?object
    {
        try {
            $auditLogIsAvailable = class_exists('App\\Services\\AuditLogService') && Schema::hasTable('audit_logs');
        } catch (Throwable) {
            $auditLogIsAvailable = false;
        }

        if (! $auditLogIsAvailable) {
            return null;
        }

        return app('App\\Services\\AuditLogService');
    }

    /**
     * @return list<string>
     */
    private function snapshotFields(): array
    {
        return [
            'buyer_id',
            'name',
            'slug',
            'description',
            'is_default',
            'is_private',
        ];
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     * @return array{old: array<string, mixed>, new: array<string, mixed>}
     */
    private function changedValues(array $oldValues, array $newValues): array
    {
        $keys = collect(array_keys($oldValues))
            ->merge(array_keys($newValues))
            ->unique();

        $old = [];
        $new = [];

        foreach ($keys as $key) {
            $oldValue = $oldValues[$key] ?? null;
            $newValue = $newValues[$key] ?? null;

            if ($oldValue === $newValue) {
                continue;
            }

            $old[$key] = $oldValue;
            $new[$key] = $newValue;
        }

        return ['old' => $old, 'new' => $new];
    }
}

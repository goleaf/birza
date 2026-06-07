<?php

namespace App\Actions\ProductBundles;

use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\Users\Seller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveProductBundleAction
{
    public function __construct(
        private readonly ValidateBundleAvailabilityAction $validateBundleAvailabilityAction,
        private readonly RecordProductBundleAuditLogsAction $recordProductBundleAuditLogsAction,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     slug: string,
     *     description?: string|null,
     *     status: string,
     *     discount_type?: string|null,
     *     discount_value?: float|int|string|null,
     *     starts_at?: string|null,
     *     ends_at?: string|null,
     *     image_path?: string|null,
     *     product_ids?: array<int, int|string>,
     *     quantities?: array<int|string, int|string>,
     *     sort_orders?: array<int|string, int|string>
     * }  $data
     */
    public function handle(
        Seller $seller,
        array $data,
        ?ProductBundle $bundle = null,
        ?Authenticatable $actor = null,
        string $source = 'seller_product_bundle_form',
    ): ProductBundle {
        return DB::transaction(function () use ($seller, $data, $bundle, $actor, $source): ProductBundle {
            $bundle ??= new ProductBundle(['seller_id' => $seller->id]);

            if ($bundle->exists && (int) $bundle->seller_id !== (int) $seller->id) {
                $this->fail('bundle', 'bundles.messages.foreign_bundle_not_allowed');
            }

            $this->validateData($data);

            $isNew = ! $bundle->exists;
            $oldStatus = (string) ($bundle->status ?: ProductBundle::STATUS_DRAFT);
            $oldValues = $bundle->exists ? $this->recordProductBundleAuditLogsAction->snapshot($bundle) : [];
            $status = (string) $data['status'];
            $discountType = filled($data['discount_type'] ?? null) ? (string) $data['discount_type'] : null;

            $bundle->forceFill([
                'seller_id' => $seller->id,
                'name' => trim((string) $data['name']),
                'slug' => trim((string) $data['slug']),
                'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
                'status' => $status,
                'discount_type' => $discountType,
                'discount_value' => $discountType === null ? null : (float) $data['discount_value'],
                'starts_at' => $this->dateOrNull($data['starts_at'] ?? null),
                'ends_at' => $this->dateOrNull($data['ends_at'] ?? null),
                'published_at' => $status === ProductBundle::STATUS_ACTIVE
                    ? ($bundle->published_at ?: now())
                    : null,
                'image_path' => $data['image_path'] ?? $bundle->image_path,
            ])->save();

            $this->syncItems(
                seller: $seller,
                bundle: $bundle,
                productIds: $data['product_ids'] ?? [],
                quantities: $data['quantities'] ?? [],
                sortOrders: $data['sort_orders'] ?? [],
                actor: $actor,
                source: $source,
            );

            $bundle->refresh()->load('seller', 'items.product.seller');

            if ($bundle->status === ProductBundle::STATUS_ACTIVE) {
                $this->validateBundleAvailabilityAction->validateForPublication($bundle);
            }

            if ($isNew) {
                $this->recordProductBundleAuditLogsAction->created($actor, $bundle, $source);
            } else {
                $this->recordProductBundleAuditLogsAction->updated($actor, $bundle, $oldValues, $source);
            }

            if (! $isNew && $oldStatus !== $bundle->status) {
                $this->recordProductBundleAuditLogsAction->statusChanged($actor, $bundle, $oldStatus, $source);
            }

            return $bundle;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validateData(array $data): void
    {
        if (! in_array($data['status'] ?? null, ProductBundle::statuses(), true)) {
            $this->fail('status', 'bundles.messages.invalid_status');
        }

        $discountType = filled($data['discount_type'] ?? null) ? (string) $data['discount_type'] : null;

        if ($discountType !== null && ! in_array($discountType, ProductBundle::discountTypes(), true)) {
            $this->fail('discount_type', 'bundles.messages.invalid_discount');
        }

        if ($discountType !== null && ! is_numeric($data['discount_value'] ?? null)) {
            $this->fail('discount_value', 'bundles.messages.invalid_discount');
        }

        $startsAt = $this->dateOrNull($data['starts_at'] ?? null);
        $endsAt = $this->dateOrNull($data['ends_at'] ?? null);

        if ($startsAt instanceof Carbon && $endsAt instanceof Carbon && $startsAt->greaterThanOrEqualTo($endsAt)) {
            $this->fail('ends_at', 'bundles.messages.invalid_dates');
        }
    }

    /**
     * @param  array<int, int|string>  $productIds
     * @param  array<int|string, int|string>  $quantities
     * @param  array<int|string, int|string>  $sortOrders
     */
    private function syncItems(
        Seller $seller,
        ProductBundle $bundle,
        array $productIds,
        array $quantities,
        array $sortOrders,
        ?Authenticatable $actor,
        string $source,
    ): void {
        $rawProductIds = collect($productIds)
            ->filter(fn (mixed $productId): bool => filled($productId))
            ->map(fn (mixed $productId): int => (int) $productId)
            ->values();
        $uniqueProductIds = $rawProductIds->unique()->values();

        if ($rawProductIds->count() !== $uniqueProductIds->count()) {
            $this->fail('product_ids', 'bundles.messages.duplicate_product_not_allowed');
        }

        $products = Product::query()
            ->select(['id', 'seller_id'])
            ->where('seller_id', $seller->id)
            ->whereKey($uniqueProductIds)
            ->get()
            ->keyBy('id');

        if ($products->count() !== $uniqueProductIds->count()) {
            $this->fail('product_ids', 'bundles.messages.foreign_product_not_allowed');
        }

        $existingItems = $bundle->items()->get()->keyBy('product_id');
        $removedProductIds = $existingItems->keys()->diff($uniqueProductIds);

        $removedProductIds->each(function (int $productId) use ($bundle, $actor, $source): void {
            $bundle->items()
                ->where('product_id', $productId)
                ->delete();
            $this->recordProductBundleAuditLogsAction->itemRemoved($actor, $bundle, $productId, $source);
        });

        $uniqueProductIds->each(function (int $productId, int $index) use ($bundle, $existingItems, $quantities, $sortOrders, $actor, $source): void {
            $quantity = max(1, (int) ($quantities[$productId] ?? $quantities[(string) $productId] ?? 1));
            $sortOrder = max(0, (int) ($sortOrders[$productId] ?? $sortOrders[(string) $productId] ?? $index));
            $wasExisting = $existingItems->has($productId);

            $bundle->items()->updateOrCreate(
                ['product_id' => $productId],
                [
                    'quantity' => $quantity,
                    'sort_order' => $sortOrder,
                ],
            );

            if (! $wasExisting) {
                $this->recordProductBundleAuditLogsAction->itemAdded($actor, $bundle, $productId, $quantity, $source);
            }
        });
    }

    private function dateOrNull(mixed $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        return Carbon::parse($value);
    }

    private function fail(string $field, string $translationKey): never
    {
        throw ValidationException::withMessages([
            $field => __($translationKey),
        ]);
    }
}

<?php

namespace App\Actions\ProductBundles;

use App\Models\ProductBundle;
use App\Models\ProductBundleItem;
use Illuminate\Validation\ValidationException;

class ValidateBundleAvailabilityAction
{
    public function handle(ProductBundle $bundle, int $bundleQuantity = 1, bool $requireActive = true): ProductBundle
    {
        $bundle = $bundle->loadMissing('seller', 'items.product.seller');
        $bundleQuantity = max(1, $bundleQuantity);

        if ($bundle->trashed()) {
            $this->fail('bundle', 'bundles.messages.not_available');
        }

        if ($bundle->seller === null || $bundle->seller->trashed() || ! $bundle->seller->is_active) {
            $this->fail('bundle', 'cart_messages_seller_unavailable');
        }

        if ($requireActive && ! $bundle->isCurrentlyAvailable()) {
            $this->fail('bundle', 'bundles.messages.not_available');
        }

        if ($bundle->items->count() < ProductBundle::minimumProducts()) {
            $this->fail('items', 'bundles.messages.minimum_products_required');
        }

        if ($bundle->items->pluck('product_id')->duplicates()->isNotEmpty()) {
            $this->fail('items', 'bundles.messages.duplicate_product_not_allowed');
        }

        $bundle->items->each(function (ProductBundleItem $item) use ($bundle, $bundleQuantity): void {
            $product = $item->product;

            if ($product === null || $product->trashed()) {
                $this->fail('items', 'cart_messages_product_not_found');
            }

            if ((int) $product->seller_id !== (int) $bundle->seller_id) {
                $this->fail('items', 'bundles.messages.foreign_product_not_allowed');
            }

            if (! $product->is_active) {
                $this->fail('items', 'cart_messages_product_unavailable');
            }

            if ($product->seller === null || $product->seller->trashed() || ! $product->seller->is_active) {
                $this->fail('items', 'cart_messages_seller_unavailable');
            }

            if ((int) $item->quantity < 1) {
                $this->fail('items', 'bundles.messages.invalid_quantity');
            }

            if ((int) $product->stock < ((int) $item->quantity * $bundleQuantity)) {
                $this->fail('items', 'cart_messages_insufficient_stock');
            }
        });

        $this->validateDiscount($bundle);

        return $bundle;
    }

    public function validateForPublication(ProductBundle $bundle): ProductBundle
    {
        return $this->handle($bundle, requireActive: false);
    }

    private function validateDiscount(ProductBundle $bundle): void
    {
        if ($bundle->discount_type === null && $bundle->discount_value === null) {
            return;
        }

        if (! in_array($bundle->discount_type, ProductBundle::discountTypes(), true)) {
            $this->fail('discount_type', 'bundles.messages.invalid_discount');
        }

        $discountValue = (float) $bundle->discount_value;

        if ($bundle->discount_type === ProductBundle::DISCOUNT_TYPE_PERCENTAGE && ($discountValue <= 0 || $discountValue > 100)) {
            $this->fail('discount_value', 'bundles.messages.invalid_discount');
        }

        if ($bundle->discount_type === ProductBundle::DISCOUNT_TYPE_FIXED_AMOUNT && $discountValue <= 0) {
            $this->fail('discount_value', 'bundles.messages.invalid_discount');
        }

        if ($bundle->discount_type === ProductBundle::DISCOUNT_TYPE_FIXED_AMOUNT && $discountValue > $bundle->basePrice()) {
            $this->fail('discount_value', 'bundles.messages.discount_too_large');
        }
    }

    private function fail(string $field, string $translationKey): never
    {
        throw ValidationException::withMessages([
            $field => __($translationKey),
        ]);
    }
}

<?php

namespace App\Actions\Cart;

use App\Actions\ProductBundles\CalculateBundlePriceAction;
use App\Actions\Promotions\ValidatePromoCodeAction;
use App\Models\Cart;
use App\Models\CartBundleItem;
use App\Models\CartItem;
use App\Models\Discount;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Users\Buyer;
use Illuminate\Support\Collection;

class CalculateCartTotalsAction
{
    public function __construct(
        private readonly ValidatePromoCodeAction $validatePromoCodeAction,
        private readonly CalculateBundlePriceAction $calculateBundlePriceAction,
    ) {}

    /**
     * @return array{
     *     item_count: int,
     *     subtotal: string,
     *     total_before_discount: string,
     *     automatic_discount_total: string,
     *     promo_discount_amount: string,
     *     discount_total: string,
     *     total_after_discount: string,
     *     total: string,
     *     promo_code: string|null,
     *     promo_code_id: int|null,
     *     promo_seller_id: int|null,
     *     seller_totals: array<int, array{subtotal: float, automatic_discount_total: float, promo_discount_amount: float, total_before_promo: float, total: float}>,
     *     lines: array<int, array{product: Product, product_id: int, seller_id: int, quantity: int, unit_price: float, original_line_total: float, discount_id: int|null, discount_amount: float, final_unit_price: float, total_price: float, discount_source: string|null, previous_stock?: int}>
     * }
     */
    public function handle(Cart $cart, ?Buyer $buyer = null, ?string $promoCode = null): array
    {
        $items = $cart->items()
            ->with('product.seller')
            ->get();
        $bundleItems = $cart->bundleItems()
            ->with('productBundle.items.product.seller')
            ->get();

        $preparedItems = $items
            ->filter(fn (CartItem $item): bool => $item->product instanceof Product)
            ->map(fn (CartItem $item): array => [
                'product' => $item->product,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) ($item->product?->price ?? $item->unit_price),
            ])
            ->values();

        $totals = $this->handlePreparedItems($preparedItems, $buyer, $promoCode);
        $bundleLines = $this->bundleLines($bundleItems);
        $bundleSubtotal = round((float) $bundleLines->sum('base_price'), 2);
        $bundleDiscountTotal = round((float) $bundleLines->sum('discount_amount'), 2);
        $bundleTotal = round((float) $bundleLines->sum('total_price'), 2);
        $subtotal = round((float) $totals['subtotal'] + $bundleSubtotal, 2);
        $automaticDiscountTotal = round((float) $totals['automatic_discount_total'] + $bundleDiscountTotal, 2);
        $discountTotal = round((float) $totals['discount_total'] + $bundleDiscountTotal, 2);
        $total = round((float) $totals['total'] + $bundleTotal, 2);

        foreach ($bundleLines as $bundleLine) {
            $sellerId = (int) $bundleLine['seller_id'];
            $totals['seller_totals'][$sellerId] ??= [
                'subtotal' => 0.0,
                'automatic_discount_total' => 0.0,
                'promo_discount_amount' => 0.0,
                'total_before_promo' => 0.0,
                'total' => 0.0,
            ];

            $totals['seller_totals'][$sellerId]['subtotal'] = round($totals['seller_totals'][$sellerId]['subtotal'] + $bundleLine['base_price'], 2);
            $totals['seller_totals'][$sellerId]['automatic_discount_total'] = round($totals['seller_totals'][$sellerId]['automatic_discount_total'] + $bundleLine['discount_amount'], 2);
            $totals['seller_totals'][$sellerId]['total_before_promo'] = round($totals['seller_totals'][$sellerId]['total_before_promo'] + $bundleLine['total_price'], 2);
            $totals['seller_totals'][$sellerId]['total'] = round($totals['seller_totals'][$sellerId]['total'] + $bundleLine['total_price'], 2);
        }

        $totals['item_count'] += (int) $bundleLines->sum('quantity');
        $totals['subtotal'] = $this->money($subtotal);
        $totals['total_before_discount'] = $this->money($subtotal);
        $totals['automatic_discount_total'] = $this->money($automaticDiscountTotal);
        $totals['discount_total'] = $this->money($discountTotal);
        $totals['total_after_discount'] = $this->money($total);
        $totals['total'] = $this->money($total);
        $totals['bundle_lines'] = $bundleLines->all();

        return $totals;
    }

    /**
     * @param  Collection<int, array{product: Product, quantity: int, unit_price: float, previous_stock?: int}>  $preparedItems
     * @return array{
     *     item_count: int,
     *     subtotal: string,
     *     total_before_discount: string,
     *     automatic_discount_total: string,
     *     promo_discount_amount: string,
     *     discount_total: string,
     *     total_after_discount: string,
     *     total: string,
     *     promo_code: string|null,
     *     promo_code_id: int|null,
     *     promo_seller_id: int|null,
     *     seller_totals: array<int, array{subtotal: float, automatic_discount_total: float, promo_discount_amount: float, total_before_promo: float, total: float}>,
     *     lines: array<int, array{product: Product, product_id: int, seller_id: int, quantity: int, unit_price: float, original_line_total: float, discount_id: int|null, discount_amount: float, final_unit_price: float, total_price: float, discount_source: string|null, previous_stock?: int}>
     * }
     */
    public function handlePreparedItems(Collection $preparedItems, ?Buyer $buyer = null, ?string $promoCode = null): array
    {
        $preparedItems = $preparedItems->values();
        $sellerSubtotals = $this->sellerSubtotals($preparedItems);
        $discounts = $this->availableDiscounts($preparedItems);

        $lines = $preparedItems
            ->map(function (array $preparedItem) use ($sellerSubtotals, $discounts): array {
                /** @var Product $product */
                $product = $preparedItem['product'];
                $quantity = (int) $preparedItem['quantity'];
                $unitPrice = (float) $preparedItem['unit_price'];
                $originalLineTotal = round($unitPrice * $quantity, 2);
                $sellerSubtotal = (float) ($sellerSubtotals[(int) $product->seller_id] ?? 0);
                $discount = $this->bestDiscountFor($product, $unitPrice, $quantity, $sellerSubtotal, $discounts);
                $discountAmount = $discount ? $discount->discountAmount($unitPrice, $quantity) : 0.0;
                $totalPrice = round(max(0, $originalLineTotal - $discountAmount), 2);

                return [
                    'product' => $product,
                    'line_type' => 'product',
                    'product_id' => (int) $product->id,
                    'seller_id' => (int) $product->seller_id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'original_line_total' => $originalLineTotal,
                    'discount_id' => $discount?->id,
                    'discount_amount' => $discountAmount,
                    'final_unit_price' => $quantity > 0 ? round($totalPrice / $quantity, 2) : 0.0,
                    'total_price' => $totalPrice,
                    'discount_source' => $discount ? 'discount:'.$discount->id : null,
                    'previous_stock' => isset($preparedItem['previous_stock']) ? (int) $preparedItem['previous_stock'] : null,
                ];
            })
            ->values();

        $sellerTotals = $this->sellerTotals($lines);
        $promo = $this->validatedPromoCode($promoCode, $buyer, $sellerTotals);
        $promoDiscountAmount = 0.0;

        if ($promo instanceof PromoCode) {
            $sellerId = (int) $promo->seller_id;
            $sellerTotal = (float) ($sellerTotals[$sellerId]['total_before_promo'] ?? 0);
            $promoDiscountAmount = $promo->discountAmount($sellerTotal);

            $sellerTotals[$sellerId]['promo_discount_amount'] = $promoDiscountAmount;
            $sellerTotals[$sellerId]['total'] = round(max(0, $sellerTotal - $promoDiscountAmount), 2);
        }

        $subtotal = round($lines->sum('original_line_total'), 2);
        $automaticDiscountTotal = round($lines->sum('discount_amount'), 2);
        $discountTotal = round($automaticDiscountTotal + $promoDiscountAmount, 2);
        $total = round(max(0, $subtotal - $discountTotal), 2);

        return [
            'item_count' => (int) $lines->sum('quantity'),
            'subtotal' => $this->money($subtotal),
            'total_before_discount' => $this->money($subtotal),
            'automatic_discount_total' => $this->money($automaticDiscountTotal),
            'promo_discount_amount' => $this->money($promoDiscountAmount),
            'discount_total' => $this->money($discountTotal),
            'total_after_discount' => $this->money($total),
            'total' => $this->money($total),
            'promo_code' => $promo?->code,
            'promo_code_id' => $promo?->id,
            'promo_seller_id' => $promo?->seller_id,
            'seller_totals' => $sellerTotals,
            'lines' => $lines->all(),
        ];
    }

    /**
     * @param  Collection<int, array{product: Product, quantity: int, unit_price: float}>  $preparedItems
     * @return array<int, float>
     */
    private function sellerSubtotals(Collection $preparedItems): array
    {
        return $preparedItems
            ->groupBy(fn (array $preparedItem): int => (int) $preparedItem['product']->seller_id)
            ->map(fn (Collection $items): float => round(
                (float) $items->sum(fn (array $preparedItem): float => (float) $preparedItem['unit_price'] * (int) $preparedItem['quantity']),
                2,
            ))
            ->all();
    }

    /**
     * @param  Collection<int, array{product: Product, quantity: int, unit_price: float}>  $preparedItems
     * @return Collection<int, Discount>
     */
    private function availableDiscounts(Collection $preparedItems): Collection
    {
        $products = $preparedItems->pluck('product');
        $sellerIds = $products->pluck('seller_id')->filter()->unique()->values();
        $productIds = $products->pluck('id')->filter()->unique()->values();
        $categoryIds = $products->pluck('category_id')->filter()->unique()->values();

        if ($sellerIds->isEmpty()) {
            return collect();
        }

        return Discount::query()
            ->select([
                'id',
                'seller_id',
                'product_id',
                'category_id',
                'name',
                'type',
                'value',
                'starts_at',
                'ends_at',
                'status',
                'usage_limit',
                'used_count',
                'minimum_order_amount',
            ])
            ->whereIn('seller_id', $sellerIds)
            ->availableAt()
            ->where(function ($query) use ($productIds, $categoryIds): void {
                $query->whereNull('product_id')
                    ->orWhereIn('product_id', $productIds)
                    ->orWhereNull('category_id')
                    ->orWhereIn('category_id', $categoryIds);
            })
            ->get();
    }

    /**
     * @param  Collection<int, Discount>  $discounts
     */
    private function bestDiscountFor(Product $product, float $unitPrice, int $quantity, float $sellerSubtotal, Collection $discounts): ?Discount
    {
        return $discounts
            ->filter(fn (Discount $discount): bool => $discount->isActiveAt() && $discount->canApplyTo($product, $sellerSubtotal))
            ->sortByDesc(fn (Discount $discount): float => $discount->discountAmount($unitPrice, $quantity))
            ->first();
    }

    /**
     * @param  Collection<int, array{seller_id: int, original_line_total: float, discount_amount: float, total_price: float}>  $lines
     * @return array<int, array{subtotal: float, automatic_discount_total: float, promo_discount_amount: float, total_before_promo: float, total: float}>
     */
    private function sellerTotals(Collection $lines): array
    {
        return $lines
            ->groupBy('seller_id')
            ->map(fn (Collection $sellerLines): array => [
                'subtotal' => round((float) $sellerLines->sum('original_line_total'), 2),
                'automatic_discount_total' => round((float) $sellerLines->sum('discount_amount'), 2),
                'promo_discount_amount' => 0.0,
                'total_before_promo' => round((float) $sellerLines->sum('total_price'), 2),
                'total' => round((float) $sellerLines->sum('total_price'), 2),
            ])
            ->all();
    }

    /**
     * @param  array<int, array{subtotal: float, automatic_discount_total: float, promo_discount_amount: float, total_before_promo: float, total: float}>  $sellerTotals
     */
    private function validatedPromoCode(?string $promoCode, ?Buyer $buyer, array $sellerTotals): ?PromoCode
    {
        if (! filled($promoCode)) {
            return null;
        }

        $promo = PromoCode::query()
            ->code((string) $promoCode)
            ->first();

        if (! $promo) {
            $this->validatePromoCodeAction->fail('promo_code', 'promo_codes.invalid');
        }

        $this->validatePromoCodeAction->validateForSellerTotals($promo, $buyer, $sellerTotals);

        return $promo;
    }

    /**
     * @param  Collection<int, CartBundleItem>  $bundleItems
     * @return Collection<int, array{cart_bundle_item: CartBundleItem, product_bundle_id: int, seller_id: int, quantity: int, base_price: float, discount_amount: float, final_price: float, total_price: float}>
     */
    private function bundleLines(Collection $bundleItems): Collection
    {
        return $bundleItems
            ->filter(fn (CartBundleItem $item): bool => $item->productBundle !== null)
            ->map(function (CartBundleItem $item): array {
                $price = $this->calculateBundlePriceAction->handle($item->productBundle, (int) $item->quantity);

                return [
                    'cart_bundle_item' => $item,
                    'product_bundle_id' => (int) $item->product_bundle_id,
                    'seller_id' => (int) $item->productBundle->seller_id,
                    'quantity' => (int) $item->quantity,
                    'base_price' => $price['base_price'],
                    'discount_amount' => $price['discount_amount'],
                    'final_price' => $price['final_price'],
                    'total_price' => $price['final_price'],
                ];
            })
            ->values();
    }

    private function money(float $amount): string
    {
        return number_format(round($amount, 2), 2, '.', '');
    }
}

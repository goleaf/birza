<?php

namespace App\Actions\Cart;

use App\Actions\Notifications\SendMarketplaceNotificationAction;
use App\Actions\Orders\CreateOrderEventAction;
use App\Actions\Notifications\SendStockThresholdNotificationAction;
use App\Actions\ProductBundles\CalculateBundlePriceAction;
use App\Actions\ProductBundles\RecordProductBundleAuditLogsAction;
use App\Actions\ProductBundles\ValidateBundleAvailabilityAction;
use App\Actions\Promotions\RecordPromoCodeRedemptionAction;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderEventType;
use App\Enums\OrderStatus;
use App\Enums\OrderStatusActorRole;
use App\Models\Cart;
use App\Models\CartBundleItem;
use App\Models\CartItem;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderBundle;
use App\Models\Product;
use App\Models\ProductBundleItem;
use App\Models\PromoCode;
use App\Models\Users\Buyer;
use App\Notifications\Marketplace\NewOrderForSellerNotification;
use App\Notifications\Marketplace\OrderCreatedNotification;
use App\Services\AuditLogService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrdersFromCartAction
{
    public function __construct(
        private readonly ValidateCartAction $validateCartAction,
        private readonly SendStockThresholdNotificationAction $sendStockThresholdNotification,
        private readonly SendMarketplaceNotificationAction $sendNotification,
        private readonly AuditLogService $auditLogService,
        private readonly CalculateCartTotalsAction $calculateCartTotalsAction,
        private readonly RecordPromoCodeRedemptionAction $recordPromoCodeRedemptionAction,
        private readonly CalculateBundlePriceAction $calculateBundlePriceAction,
        private readonly ValidateBundleAvailabilityAction $validateBundleAvailabilityAction,
        private readonly RecordProductBundleAuditLogsAction $recordProductBundleAuditLogsAction,
        private readonly CreateOrderEventAction $createOrderEventAction,
    ) {}

    /**
     * @param  array{shipping_address?: string|null, billing_address?: string|null, delivery_method?: string|null, payment_method?: string|null, promo_code?: string|null}  $checkoutData
     * @return Collection<int, Order>
     */
    public function handle(Cart $cart, Buyer $buyer, array $checkoutData = []): Collection
    {
        $shippingAddress = trim((string) ($checkoutData['shipping_address'] ?? $buyer->address ?? ''));
        $paymentMethod = trim((string) ($checkoutData['payment_method'] ?? ''));

        if ($shippingAddress === '') {
            throw ValidationException::withMessages([
                'shipping_address' => __('cart_messages_shipping_address_required'),
            ]);
        }

        if ($paymentMethod === '') {
            throw ValidationException::withMessages([
                'payment_method' => __('cart_messages_payment_method_required'),
            ]);
        }

        $orders = DB::transaction(function () use ($cart, $buyer, $checkoutData, $shippingAddress, $paymentMethod): Collection {
            $lockedCart = Cart::query()
                ->with(['items.product.seller', 'bundleItems.productBundle.items.product.seller'])
                ->lockForUpdate()
                ->findOrFail($cart->id);

            $this->authorizeCart($lockedCart, $buyer);

            $items = $this->validateCartAction->handle($lockedCart, $buyer);
            $preparedItems = $this->prepareItems($items);
            $bundleLines = $this->prepareBundleLines($lockedCart->bundleItems);
            $this->validateCombinedStock($preparedItems, $bundleLines);
            $pricing = $this->calculateCartTotalsAction->handlePreparedItems(
                $preparedItems->map(fn (array $preparedItem): array => [
                    'product' => $preparedItem['product'],
                    'quantity' => $preparedItem['quantity'],
                    'unit_price' => $preparedItem['unit_price'],
                ]),
                $buyer,
                $checkoutData['promo_code'] ?? null,
            );
            $orders = collect();

            collect($pricing['lines'])
                ->map(fn (array $line): array => $line + ['line_type' => 'product'])
                ->concat($bundleLines)
                ->groupBy(fn (array $line): int => (int) $line['seller_id'])
                ->each(function (Collection $sellerAllLines) use ($orders, $buyer, $checkoutData, $lockedCart, $shippingAddress, $paymentMethod, $pricing): void {
                    $sellerId = (int) $sellerAllLines->first()['seller_id'];
                    $sellerLines = $sellerAllLines->where('line_type', 'product')->values();
                    $sellerBundleLines = $sellerAllLines->where('line_type', 'bundle')->values();
                    $sellerTotals = $pricing['seller_totals'][$sellerId] ?? [
                        'subtotal' => 0.0,
                        'automatic_discount_total' => 0.0,
                        'promo_discount_amount' => 0.0,
                        'total_before_promo' => 0.0,
                        'total' => 0.0,
                    ];
                    $bundleSubtotal = round((float) $sellerBundleLines->sum('base_price'), 2);
                    $promoDiscountAmount = (float) $sellerTotals['promo_discount_amount'];
                    $promoAppliesToSeller = (int) ($pricing['promo_seller_id'] ?? 0) === $sellerId && $promoDiscountAmount > 0;
                    $sellerLines = $this->allocatePromoDiscount($sellerLines, $promoDiscountAmount);
                    $orderTotal = round((float) $sellerLines->sum('total_price') + (float) $sellerBundleLines->sum('total_price'), 2);
                    $subtotal = round((float) $sellerTotals['subtotal'] + $bundleSubtotal, 2);
                    $discountTotal = round(max(0, $subtotal - $orderTotal), 2);

                    $order = new Order;
                    $order->forceFill([
                        'buyer_id' => $buyer->id,
                        'payment_method' => $paymentMethod,
                        'payment_status' => OrderPaymentStatus::Pending,
                        'status' => OrderStatus::Pending,
                        'subtotal' => $subtotal,
                        'discount_total' => $discountTotal,
                        'promo_code_id' => $promoAppliesToSeller ? $pricing['promo_code_id'] : null,
                        'promo_code' => $promoAppliesToSeller ? $pricing['promo_code'] : null,
                        'promo_discount_amount' => $promoDiscountAmount,
                        'order_total' => $orderTotal,
                        'shipping_address_snapshot' => $shippingAddress,
                        'billing_address_snapshot' => $checkoutData['billing_address'] ?? null,
                        'delivery_method' => $checkoutData['delivery_method'] ?? null,
                    ]);
                    $order->save();

                    $order->statusHistory()->create([
                        'old_status' => OrderStatus::Pending,
                        'new_status' => OrderStatus::Pending,
                        'changed_by_user_id' => null,
                        'changed_by_role' => OrderStatusActorRole::System,
                        'reason' => null,
                        'note' => __('orders.messages.created_successfully'),
                    ]);

                    $this->createOrderEventAction->handle(
                        order: $order,
                        eventType: OrderEventType::Created,
                        actor: $buyer,
                        newStatus: OrderStatus::Pending,
                        publicNote: __('orders.messages.created_successfully'),
                        metadata: [
                            'source' => 'checkout',
                            'seller_id' => $sellerId,
                        ],
                        createdAt: $order->created_at,
                    );

                    $sellerLines->each(function (array $preparedItem) use ($order, $pricing): void {
                        /** @var Product $product */
                        $product = $preparedItem['product'];
                        $this->incrementDiscountUsage($preparedItem['discount_id'] ?? null);

                        $order->items()->create([
                            'product_id' => $product->id,
                            'seller_id' => $product->seller_id,
                            'discount_id' => $preparedItem['discount_id'],
                            'quantity' => $preparedItem['quantity'],
                            'unit_price' => $preparedItem['unit_price'],
                            'original_unit_price' => $preparedItem['unit_price'],
                            'discount_amount' => $preparedItem['discount_amount'],
                            'final_unit_price' => $preparedItem['final_unit_price'],
                            'total_price' => $preparedItem['total_price'],
                            'product_title_snapshot' => $product->name,
                            'product_price_snapshot' => $preparedItem['unit_price'],
                            'seller_name_snapshot' => $product->seller?->company_name ?: $product->seller?->name,
                            'discount_source' => $this->discountSource($preparedItem, $pricing),
                        ]);

                        Product::query()
                            ->whereKey($product->id)
                            ->decrement('stock', $preparedItem['quantity']);
                        $product->forceFill([
                            'stock' => (int) $preparedItem['previous_stock'] - (int) $preparedItem['quantity'],
                        ]);
                        $this->sendStockThresholdNotification->handle($product, $preparedItem['previous_stock']);
                    });

                    $sellerBundleLines->each(fn (array $bundleLine): mixed => $this->createOrderBundle($order, $bundleLine, $buyer));

                    if ($promoAppliesToSeller && $pricing['promo_code_id']) {
                        $promoCode = PromoCode::query()->findOrFail($pricing['promo_code_id']);
                        $this->recordPromoCodeRedemptionAction->handle(
                            promoCode: $promoCode,
                            buyer: $buyer,
                            order: $order,
                            discountAmount: $promoDiscountAmount,
                            eligibleSubtotal: (float) $sellerTotals['total_before_promo'],
                        );
                    }

                    $freshOrder = $order->fresh(['buyer', 'items.seller', 'orderBundles.items.product', 'statusHistory']);

                    if ($freshOrder) {
                        $this->logCheckoutAudit($buyer, $lockedCart, $freshOrder, $sellerLines->concat($sellerBundleLines));
                        $orders->push($freshOrder);
                    }
                });

            $lockedCart->items()->delete();
            $lockedCart->bundleItems()->delete();
            $lockedCart->forceFill(['status' => Cart::STATUS_CONVERTED])->save();

            return $orders->values();
        });

        $orders->each(fn (Order $order): mixed => $this->sendOrderNotifications($order));

        return $orders;
    }

    private function authorizeCart(Cart $cart, Buyer $buyer): void
    {
        if ((int) $cart->user_id !== (int) $buyer->id) {
            throw ValidationException::withMessages([
                'cart' => __('cart_messages_unresolvable_cart'),
            ]);
        }
    }

    /**
     * @param  Collection<int, CartItem>  $items
     * @return Collection<int, array{product: Product, previous_stock: int, quantity: int, unit_price: float}>
     */
    private function prepareItems(Collection $items): Collection
    {
        $products = Product::query()
            ->withTrashed()
            ->select(['id', 'seller_id', 'category_id', 'name', 'price', 'min_order_count', 'stock', 'is_active', 'deleted_at'])
            ->with('seller:id,name,company_name,is_active,deleted_at')
            ->whereKey($items->pluck('product_id')->unique()->values())
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        return $items->map(function (CartItem $item) use ($products): array {
            $product = $products->get($item->product_id);
            if (! $product) {
                throw ValidationException::withMessages([
                    'cart' => __('cart_messages_product_not_found'),
                ]);
            }

            $item->setRelation('product', $product);
            $this->validateCartAction->validateItem($item);

            $quantity = (int) $item->quantity;
            $unitPrice = (float) $product->price;

            return [
                'product' => $product,
                'previous_stock' => (int) $product->stock,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        });
    }

    /**
     * @param  Collection<int, CartBundleItem>  $bundleItems
     * @return Collection<int, array{line_type: string, cart_bundle_item: CartBundleItem, product_bundle: mixed, product_bundle_id: int, seller_id: int, quantity: int, base_price: float, discount_type: string|null, discount_value: float|null, discount_amount: float, final_price: float, total_price: float, products_snapshot: array<int, array<string, mixed>>, product_rows: Collection<int, array<string, mixed>>}>
     */
    private function prepareBundleLines(Collection $bundleItems): Collection
    {
        if ($bundleItems->isEmpty()) {
            return collect();
        }

        $productIds = $bundleItems
            ->flatMap(fn (CartBundleItem $item): Collection => $item->productBundle?->items?->pluck('product_id') ?? collect())
            ->filter()
            ->unique()
            ->values();

        $products = Product::query()
            ->withTrashed()
            ->select(['id', 'seller_id', 'category_id', 'name', 'price', 'min_order_count', 'stock', 'is_active', 'deleted_at'])
            ->with('seller:id,name,company_name,is_active,deleted_at')
            ->whereKey($productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        return $bundleItems
            ->filter(fn (CartBundleItem $item): bool => $item->productBundle !== null)
            ->map(function (CartBundleItem $item) use ($products): array {
                $bundle = $item->productBundle;

                $bundle->items->each(function (ProductBundleItem $bundleItem) use ($products): void {
                    $product = $products->get($bundleItem->product_id);

                    if ($product instanceof Product) {
                        $bundleItem->setRelation('product', $product);
                    }
                });

                $quantity = (int) $item->quantity;
                $this->validateBundleAvailabilityAction->handle($bundle, $quantity);
                $price = $this->calculateBundlePriceAction->handle($bundle, $quantity);

                return [
                    'line_type' => 'bundle',
                    'cart_bundle_item' => $item,
                    'product_bundle' => $bundle,
                    'product_bundle_id' => (int) $item->product_bundle_id,
                    'seller_id' => (int) $bundle->seller_id,
                    'quantity' => $quantity,
                    'base_price' => (float) $price['base_price'],
                    'discount_type' => $bundle->discount_type,
                    'discount_value' => $bundle->discount_value !== null ? (float) $bundle->discount_value : null,
                    'discount_amount' => (float) $price['discount_amount'],
                    'final_price' => (float) $price['final_price'],
                    'total_price' => (float) $price['final_price'],
                    'products_snapshot' => $price['products'],
                    'product_rows' => $bundle->items
                        ->map(fn (ProductBundleItem $bundleItem): array => $this->bundleProductRow($bundleItem, $quantity))
                        ->values(),
                ];
            })
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function bundleProductRow(ProductBundleItem $bundleItem, int $bundleQuantity): array
    {
        /** @var Product $product */
        $product = $bundleItem->product;
        $quantity = (int) $bundleItem->quantity * $bundleQuantity;
        $unitPrice = (float) $product->price;
        $lineTotal = round($unitPrice * $quantity, 2);

        return [
            'product' => $product,
            'product_id' => (int) $product->id,
            'seller_id' => (int) $product->seller_id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'original_line_total' => $lineTotal,
            'discount_amount' => 0.0,
            'final_unit_price' => $unitPrice,
            'total_price' => $lineTotal,
            'previous_stock' => (int) $product->stock,
        ];
    }

    /**
     * @param  Collection<int, array{product: Product, previous_stock: int, quantity: int, unit_price: float}>  $preparedItems
     * @param  Collection<int, array<string, mixed>>  $bundleLines
     */
    private function validateCombinedStock(Collection $preparedItems, Collection $bundleLines): void
    {
        $requirements = collect();

        $preparedItems->each(function (array $preparedItem) use ($requirements): void {
            /** @var Product $product */
            $product = $preparedItem['product'];

            $requirements->push([
                'product_id' => (int) $product->id,
                'required_quantity' => (int) $preparedItem['quantity'],
                'available_stock' => (int) $preparedItem['previous_stock'],
            ]);
        });

        $bundleLines->each(function (array $bundleLine) use ($requirements): void {
            collect($bundleLine['product_rows'])
                ->each(fn (array $row): mixed => $requirements->push([
                    'product_id' => (int) $row['product_id'],
                    'required_quantity' => (int) $row['quantity'],
                    'available_stock' => (int) $row['previous_stock'],
                ]));
        });

        $requirements
            ->groupBy('product_id')
            ->each(function (Collection $productRequirements): void {
                $requiredQuantity = (int) $productRequirements->sum('required_quantity');
                $availableStock = (int) $productRequirements->first()['available_stock'];

                if ($requiredQuantity > $availableStock) {
                    throw ValidationException::withMessages([
                        'cart' => __('cart_messages_insufficient_stock'),
                    ]);
                }
            });
    }

    /**
     * @param  array<string, mixed>  $bundleLine
     */
    private function createOrderBundle(Order $order, array $bundleLine, Buyer $buyer): OrderBundle
    {
        $bundle = $bundleLine['product_bundle'];

        $orderBundle = $order->orderBundles()->create([
            'product_bundle_id' => $bundleLine['product_bundle_id'],
            'seller_id' => $bundleLine['seller_id'],
            'bundle_name_snapshot' => $bundle->name,
            'quantity' => $bundleLine['quantity'],
            'base_price' => $bundleLine['base_price'],
            'discount_type' => $bundleLine['discount_type'],
            'discount_value' => $bundleLine['discount_value'],
            'discount_amount' => $bundleLine['discount_amount'],
            'final_price' => $bundleLine['final_price'],
            'products_snapshot' => $bundleLine['products_snapshot'],
        ]);

        $this->allocateBundleDiscount(collect($bundleLine['product_rows']), (float) $bundleLine['discount_amount'])
            ->each(function (array $row) use ($order, $orderBundle, $bundleLine): void {
                /** @var Product $product */
                $product = $row['product'];
                $previousStock = (int) $row['previous_stock'];

                $order->items()->create([
                    'order_bundle_id' => $orderBundle->id,
                    'product_id' => $product->id,
                    'seller_id' => $product->seller_id,
                    'discount_id' => null,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'original_unit_price' => $row['unit_price'],
                    'discount_amount' => $row['discount_amount'],
                    'final_unit_price' => $row['final_unit_price'],
                    'total_price' => $row['total_price'],
                    'product_title_snapshot' => $product->name,
                    'product_price_snapshot' => $row['unit_price'],
                    'seller_name_snapshot' => $product->seller?->company_name ?: $product->seller?->name,
                    'discount_source' => 'product_bundle:'.$bundleLine['product_bundle_id'],
                ]);

                Product::query()
                    ->whereKey($product->id)
                    ->decrement('stock', $row['quantity']);

                $product->forceFill([
                    'stock' => $previousStock - (int) $row['quantity'],
                ]);

                $this->sendStockThresholdNotification->handle($product, $previousStock);
            });

        $this->recordProductBundleAuditLogsAction->purchased($buyer, $bundle, $order->id, (int) $bundleLine['quantity']);

        return $orderBundle;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $productRows
     * @return Collection<int, array<string, mixed>>
     */
    private function allocateBundleDiscount(Collection $productRows, float $discountAmount): Collection
    {
        if ($discountAmount <= 0) {
            return $productRows->map(function (array $row): array {
                $row['discount_amount'] = 0.0;
                $row['final_unit_price'] = (float) $row['unit_price'];
                $row['total_price'] = (float) $row['original_line_total'];

                return $row;
            });
        }

        $remainingDiscount = round($discountAmount, 2);
        $remainingTotal = round((float) $productRows->sum('original_line_total'), 2);
        $rowCount = $productRows->count();

        return $productRows
            ->values()
            ->map(function (array $row, int $index) use (&$remainingDiscount, &$remainingTotal, $rowCount): array {
                $lineTotal = (float) $row['original_line_total'];
                $discountShare = $index === $rowCount - 1
                    ? $remainingDiscount
                    : round(min($remainingDiscount, $remainingTotal > 0 ? $remainingDiscount * ($lineTotal / $remainingTotal) : 0), 2);

                $row['discount_amount'] = $discountShare;
                $row['total_price'] = round(max(0, $lineTotal - $discountShare), 2);
                $row['final_unit_price'] = (int) $row['quantity'] > 0
                    ? round($row['total_price'] / (int) $row['quantity'], 2)
                    : 0.0;

                $remainingDiscount = round(max(0, $remainingDiscount - $discountShare), 2);
                $remainingTotal = round(max(0, $remainingTotal - $lineTotal), 2);

                return $row;
            });
    }

    private function sendOrderNotifications(Order $order): void
    {
        $this->sendNotification->handle($order->buyer, new OrderCreatedNotification($order));

        $order->items
            ->pluck('seller')
            ->filter()
            ->unique('id')
            ->each(fn ($seller): mixed => $this->sendNotification->handle(
                $seller,
                new NewOrderForSellerNotification($order),
            ));
    }

    /**
     * @param  Collection<int, array{product: Product, quantity: int, unit_price: float, total_price: float}>  $sellerItems
     */
    private function logCheckoutAudit(Buyer $buyer, Cart $cart, Order $order, Collection $sellerItems): void
    {
        $sellerIds = $sellerItems
            ->pluck('seller_id')
            ->unique()
            ->values()
            ->all();

        $metadata = [
            'source' => 'checkout',
            'cart_id' => $cart->id,
            'item_count' => $sellerItems->sum('quantity'),
            'seller_ids' => $sellerIds,
        ];

        $this->auditLogService->log(
            actor: $buyer,
            action: 'order.created',
            auditable: $order,
            oldValues: null,
            newValues: [
                'id' => $order->id,
                'buyer_id' => $order->buyer_id,
                'order_total' => $order->order_total,
                'subtotal' => $order->subtotal,
                'discount_total' => $order->discount_total,
                'promo_code' => $order->promo_code,
                'promo_discount_amount' => $order->promo_discount_amount,
                'status' => $order->lifecycleStatus()->value,
                'payment_status' => $order->payment_status?->value ?? OrderPaymentStatus::Pending->value,
            ],
            metadata: $metadata,
        );

        $this->auditLogService->log(
            actor: $buyer,
            action: 'cart.checked_out',
            auditable: $order,
            oldValues: [
                'cart_id' => $cart->id,
                'status' => Cart::STATUS_ACTIVE,
            ],
            newValues: [
                'order_id' => $order->id,
                'buyer_id' => $buyer->id,
                'total' => $order->order_total,
                'discount_total' => $order->discount_total,
                'item_count' => $metadata['item_count'],
                'seller_ids' => $sellerIds,
            ],
            metadata: $metadata,
        );
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $sellerLines
     * @return Collection<int, array<string, mixed>>
     */
    private function allocatePromoDiscount(Collection $sellerLines, float $promoDiscountAmount): Collection
    {
        if ($promoDiscountAmount <= 0) {
            return $sellerLines;
        }

        $remainingDiscount = round($promoDiscountAmount, 2);
        $remainingTotal = round((float) $sellerLines->sum('total_price'), 2);
        $lineCount = $sellerLines->count();

        return $sellerLines
            ->values()
            ->map(function (array $line, int $index) use (&$remainingDiscount, &$remainingTotal, $lineCount): array {
                $lineTotal = (float) $line['total_price'];
                $promoShare = $index === $lineCount - 1
                    ? $remainingDiscount
                    : round(min($remainingDiscount, $remainingTotal > 0 ? $remainingDiscount * ($lineTotal / $remainingTotal) : 0), 2);

                $line['discount_amount'] = round((float) $line['discount_amount'] + $promoShare, 2);
                $line['total_price'] = round(max(0, $lineTotal - $promoShare), 2);
                $line['final_unit_price'] = (int) $line['quantity'] > 0
                    ? round($line['total_price'] / (int) $line['quantity'], 2)
                    : 0.0;

                $remainingDiscount = round(max(0, $remainingDiscount - $promoShare), 2);
                $remainingTotal = round(max(0, $remainingTotal - $lineTotal), 2);

                return $line;
            });
    }

    private function incrementDiscountUsage(?int $discountId): void
    {
        if ($discountId === null) {
            return;
        }

        $discount = Discount::query()
            ->lockForUpdate()
            ->findOrFail($discountId);

        if (! $discount->isActiveAt()) {
            throw ValidationException::withMessages([
                'cart' => __('discounts.unavailable'),
            ]);
        }

        $discount->increment('used_count');
    }

    /**
     * @param  array<string, mixed>  $preparedItem
     * @param  array<string, mixed>  $pricing
     */
    private function discountSource(array $preparedItem, array $pricing): ?string
    {
        $sources = collect([$preparedItem['discount_source'] ?? null]);

        if ((int) ($pricing['promo_seller_id'] ?? 0) === (int) $preparedItem['seller_id'] && $pricing['promo_code_id']) {
            $sources->push('promo_code:'.$pricing['promo_code_id']);
        }

        return $sources->filter()->join(',');
    }
}

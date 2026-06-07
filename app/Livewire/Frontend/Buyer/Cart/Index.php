<?php

namespace App\Livewire\Frontend\Buyer\Cart;

use App\Models\Country;
use App\Models\GlobalSettings;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use LukePOLO\LaraCart\Facades\LaraCart;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    public array $quantities = [];

    public function mount(): void
    {
        $this->syncQuantitiesWithCart();
    }

    public function updateQuantity(string $itemHash): void
    {
        $cartItem = $this->findCartItem($itemHash);

        if (! $cartItem) {
            session()->flash('error', __('cart_messages_item_not_found'));

            return;
        }

        $qty = (int) ($this->quantities[$itemHash] ?? 1);

        if ($qty < 1) {
            $this->addError("quantities.$itemHash", __('validation_min_numeric', ['min' => 1]));

            return;
        }

        $product = Product::find($cartItem->id);

        if (! $product) {
            LaraCart::removeItem($itemHash);
            LaraCart::update();
            session()->flash('error', __('cart_messages_product_not_found'));

            return;
        }

        if ($qty < (int) $product->min_order_count) {
            session()->flash('error', __('cart_messages_minimum_quantity', ['min' => $product->min_order_count]));

            return;
        }

        if ($qty > (int) $product->stock) {
            session()->flash('error', __('cart_messages_insufficient_stock'));

            return;
        }

        LaraCart::updateItem($itemHash, 'qty', $qty);
        LaraCart::update();

        session()->flash('success', __('cart_messages_quantity_updated'));
    }

    public function removeItem(string $itemHash): void
    {
        $cartItem = $this->findCartItem($itemHash);

        if (! $cartItem) {
            session()->flash('error', __('cart_messages_item_not_found'));

            return;
        }

        LaraCart::removeItem($itemHash);
        LaraCart::update();

        unset($this->quantities[$itemHash]);

        session()->flash('success', __('cart_messages_product_removed'));
    }

    public function checkout(): void
    {
        if (LaraCart::count() === 0) {
            session()->flash('error', __('cart_messages_empty_cart'));

            return;
        }

        $buyerId = Auth::guard('buyer')->id();

        if (! $buyerId) {
            session()->flash('error', __('common_unauthorized'));

            return;
        }

        $cartTotals = $this->calculateCartTotals();
        $itemsTotal = (float) collect(LaraCart::getItems())->sum(fn ($item) => $item->qty * $item->price);
        $orderTotal = (float) ($cartTotals['totalWithVatAndPortal'] ?? $itemsTotal);

        try {
            DB::transaction(function () use ($buyerId, $orderTotal) {
                $order = Order::create([
                    'buyer_id' => $buyerId,
                    'order_total' => $orderTotal,
                    'payment_status' => Order::STATUS['PENDING'],
                    'status' => Order::STATUS['PENDING'],
                ]);

                foreach (LaraCart::getItems() as $item) {
                    $product = Product::lockForUpdate()->find($item->id);

                    if (! $product || $product->stock < $item->qty) {
                        throw new \RuntimeException(__('cart_messages_stock_changed'));
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item->qty,
                        'unit_price' => $item->price,
                        'total_price' => $item->qty * $item->price,
                        'seller_id' => $item->options['seller_id'] ?? $product->seller_id,
                    ]);

                    $product->decrement('stock', $item->qty);
                }

                // Payment simulation: always succeed for now.
                $order->update([
                    'payment_status' => Order::STATUS['PAID'],
                    'status' => Order::STATUS['PAID'],
                ]);
            });
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        LaraCart::destroyCart();

        $this->redirectRoute('buyer.orders.index', navigate: true);
    }

    public function render(): View
    {
        $this->syncQuantitiesWithCart();

        $cartItems = collect(LaraCart::getItems())->values();

        return view('frontend.buyer.cart.index', [
            'cartItems' => $cartItems,
            'hasCartItems' => $cartItems->isNotEmpty(),
            'cartTotals' => $this->calculateCartTotals(),
            'countries' => Country::pluck('country_name', 'alpha2')->toArray(),
        ]);
    }

    private function findCartItem(string $itemHash)
    {
        foreach (LaraCart::getItems() as $item) {
            if ($item->getHash() === $itemHash) {
                return $item;
            }
        }

        return null;
    }

    private function syncQuantitiesWithCart(): void
    {
        foreach (LaraCart::getItems() as $item) {
            $hash = $item->getHash();
            $this->quantities[$hash] = (int) ($this->quantities[$hash] ?? $item->qty ?? 1);
        }
    }

    protected function calculateCartTotals(): array
    {
        $cartTotal = LaraCart::total(2);
        $cartTotal = preg_replace('/[^0-9,.]/', '', $cartTotal);
        $cartTotal = number_format((float) str_replace(',', '.', $cartTotal), 2, '.', '');

        $vatRate = config('app.vat_rate');
        $vatAmount = round($cartTotal * $vatRate, 2);
        $portalPrice = $this->getPortalPrice();
        $totalWithVatAndPortal = round($cartTotal + $vatAmount + (int) $portalPrice, 2);

        return [
            'cartTotal' => $cartTotal,
            'vatAmount' => $vatAmount,
            'portalPrice' => $portalPrice,
            'totalWithVatAndPortal' => $totalWithVatAndPortal,
        ];
    }

    private function getPortalPrice()
    {
        return GlobalSettings::first()->portal_additional_price ?? 0;
    }
}

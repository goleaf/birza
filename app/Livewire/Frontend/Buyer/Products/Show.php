<?php

namespace App\Livewire\Frontend\Buyer\Products;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;
use LukePOLO\LaraCart\Facades\LaraCart;

#[Layout('layouts.frontend.app')]
class Show extends Component
{
    public Product $product;
    public int $quantity = 1;

    public function mount(Product $product): void
    {
        if ($product->trashed() || $product->is_active === false) {
            abort(404);
        }

        $this->product = $product;
        $this->quantity = (int) ($product->min_order_count ?? 1);

        if ($product->stock <= 0) {
            session()->flash('message', __('product.messages.out_of_stock'));
        }
    }

    public function addToCart(): void
    {
        $product = $this->product->fresh();

        if (! $product || $product->trashed() || $product->is_active === false) {
            session()->flash('message', __('cart.messages.product_not_found'));
            return;
        }

        if ((int) $product->stock === 0) {
            session()->flash('message', __('cart.messages.out_of_stock'));
            return;
        }

        $quantity = max(1, (int) $this->quantity);

        if ($quantity < (int) $product->min_order_count) {
            session()->flash('message', __('cart.messages.minimum_quantity', [
                'min' => $product->min_order_count,
                'product' => $product->name,
            ]));
            return;
        }

        if ($quantity > (int) $product->stock) {
            session()->flash('message', __('cart.messages.maximum_quantity', [
                'max' => $product->stock,
                'product' => $product->name,
            ]));
            return;
        }

        $cartItem = null;
        foreach (LaraCart::getItems() as $item) {
            if ((int) $item->id === (int) $product->id) {
                $cartItem = $item;
                break;
            }
        }

        if ($cartItem) {
            $newQty = (int) $cartItem->qty + $quantity;

            if ($newQty > (int) $product->stock) {
                session()->flash('message', __('cart.messages.maximum_total_quantity', [
                    'max' => $product->stock,
                    'product' => $product->name,
                ]));
                return;
            }

            LaraCart::updateItem($cartItem->getHash(), 'qty', $newQty);
        } else {
            LaraCart::add(
                $product->id,
                $product->name,
                $quantity,
                $product->price,
                [
                    'image' => $product->product_image,
                    'unit' => $product->unit,
                    'seller_id' => $product->seller_id,
                    'category_id' => $product->category_id,
                    'min_order_price' => $product->min_order_price,
                    'min_order_count' => $product->min_order_count,
                    'is_organic' => $product->is_organic,
                    'country_of_origin' => $product->country_of_origin,
                    'package_weight' => $product->package_weight,
                    'price_per_liter' => $product->price_per_liter,
                    'stock' => $product->stock,
                ]
            );
        }

        session()->flash('success', __('cart.messages.product_added'));
    }

    public function render()
    {
        return view('frontend.buyer.products.show', [
            'product' => $this->product,
            'message' => session('message'),
        ]);
    }
}



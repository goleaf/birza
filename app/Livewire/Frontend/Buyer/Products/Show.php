<?php

namespace App\Livewire\Frontend\Buyer\Products;

use App\Actions\StockAlerts\CancelStockAlertAction;
use App\Actions\StockAlerts\CreateStockAlertAction;
use App\Models\Product;
use App\Models\ProductStockAlert;
use App\Models\Users\Buyer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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

        $product->loadMissing([
            'seller:id,name,email,company_name',
            'country:id,country_name',
            'category:id,category_name,parent_category_id',
            'category.parent:id,category_name',
            'category.attributes' => function ($query) {
                $query
                    ->select(['attributes.id', 'attributes.name'])
                    ->where('is_active', true);
            },
            'attributeValues:id,attribute_id,value',
            'images',
        ]);

        $this->product = $product;
        $this->quantity = (int) ($product->min_order_count ?? 1);

        if ($product->stock <= 0) {
            session()->flash('message', __('product_messages_out_of_stock'));
        }
    }

    public function addToCart(): void
    {
        $product = $this->product->fresh();

        if (! $product || $product->trashed() || $product->is_active === false) {
            session()->flash('message', __('cart_messages_product_not_found'));

            return;
        }

        if ((int) $product->stock === 0) {
            session()->flash('message', __('cart_messages_out_of_stock'));

            return;
        }

        $quantity = max(1, (int) $this->quantity);

        if ($quantity < (int) $product->min_order_count) {
            session()->flash('message', __('cart_messages_minimum_quantity', [
                'min' => $product->min_order_count,
                'product' => $product->name,
            ]));

            return;
        }

        if ($quantity > (int) $product->stock) {
            session()->flash('message', __('cart_messages_maximum_quantity', [
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
                session()->flash('message', __('cart_messages_maximum_total_quantity', [
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
                    'image_url' => $product->imageUrl('thumb'),
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

        session()->flash('success', __('cart_messages_product_added'));
    }

    public function subscribeToStockAlert(CreateStockAlertAction $action): void
    {
        $buyer = $this->buyer();
        abort_if(! $buyer, 403);

        $product = $this->product->fresh(['seller']) ?? $this->product;

        try {
            $alert = $action->handle($product, $buyer);
        } catch (ValidationException $exception) {
            session()->flash('message', collect($exception->errors())->flatten()->first());

            return;
        }

        session()->flash(
            $alert->wasRecentlyCreated ? 'success' : 'message',
            $alert->wasRecentlyCreated
                ? __('stock_alerts.created_successfully')
                : __('stock_alerts.already_subscribed'),
        );
    }

    public function cancelStockAlert(int $alertId, CancelStockAlertAction $action): void
    {
        $buyer = $this->buyer();
        abort_if(! $buyer, 403);

        $alert = ProductStockAlert::query()
            ->where('product_id', $this->product->id)
            ->findOrFail($alertId);

        $action->handle($alert, $buyer);

        session()->flash('success', __('stock_alerts.cancelled_successfully'));
    }

    public function render(): View
    {
        return view('frontend.buyer.products.show', [
            'product' => $this->product,
            'productSlides' => $this->getProductSlides($this->product),
            'productGalleryImages' => $this->product->imageGalleryUrls('small'),
            'attributeValuesByAttribute' => $this->product->attributeValues->groupBy('attribute_id'),
            'activeStockAlert' => $this->activeStockAlert(),
            'stockAlertBuyer' => $this->buyer() !== null,
            'message' => session('message'),
        ]);
    }

    private function buyer(): ?Buyer
    {
        $buyer = Auth::guard('buyer')->user();

        return $buyer instanceof Buyer ? $buyer : null;
    }

    private function activeStockAlert(): ?ProductStockAlert
    {
        $buyer = $this->buyer();

        if (! $buyer) {
            return null;
        }

        return ProductStockAlert::query()
            ->select(['id', 'product_id', 'buyer_id', 'status', 'created_at'])
            ->active()
            ->where('product_id', $this->product->id)
            ->where('buyer_id', $buyer->id)
            ->first();
    }

    protected function getProductSlides(Product $product): array
    {
        return collect($product->imageGalleryUrls('large'))
            ->map(fn (string $url, int $index): array => [
                'image' => $url,
                'alt' => trim($product->name.' '.($index + 1)),
                'lazy' => $index > 0,
            ])
            ->values()
            ->all();
    }
}

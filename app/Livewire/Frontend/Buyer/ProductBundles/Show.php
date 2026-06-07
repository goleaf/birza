<?php

namespace App\Livewire\Frontend\Buyer\ProductBundles;

use App\Actions\ProductBundles\AddBundleToCartAction;
use App\Actions\ProductBundles\CalculateBundlePriceAction;
use App\Models\ProductBundle;
use App\Models\Users\Buyer;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Show extends Component
{
    use AuthorizesRequests;

    public ProductBundle $productBundle;

    public int $quantity = 1;

    public function mount(ProductBundle $productBundle): void
    {
        $this->productBundle = $productBundle->loadMissing([
            'seller',
            'items.product.primaryImage',
            'items.product.seller',
        ]);

        abort_unless($this->productBundle->isVisibleToBuyers(), 404);
        $this->authorize('view', $this->productBundle);
    }

    public function addToCart(AddBundleToCartAction $action): void
    {
        $this->authorize('addToCart', $this->productBundle);

        $validated = $this->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $action->handle(
                bundle: $this->productBundle,
                quantity: (int) $validated['quantity'],
                buyer: $this->buyer(),
                guestToken: $this->buyer() ? null : $this->guestToken(),
            );
        } catch (ValidationException $exception) {
            $this->addError('quantity', (string) collect($exception->errors())->flatten()->first());

            return;
        }

        session()->flash('success', __('cart.bundle_added'));
    }

    public function render(CalculateBundlePriceAction $priceAction): View
    {
        return view('livewire.frontend.buyer.product-bundles.show', [
            'bundle' => $this->productBundle,
            'price' => $priceAction->handle($this->productBundle, $this->quantity),
        ]);
    }

    private function buyer(): ?Buyer
    {
        $buyer = Auth::guard('buyer')->user();

        return $buyer instanceof Buyer ? $buyer : null;
    }

    private function guestToken(): string
    {
        if (! session()->has('cart_guest_token')) {
            session()->put('cart_guest_token', (string) Str::uuid());
        }

        return (string) session('cart_guest_token');
    }
}

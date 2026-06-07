<?php

namespace App\Livewire\Frontend\Buyer\Wishlists;

use App\Actions\Wishlists\AddWishlistItemToCartAction;
use App\Actions\Wishlists\ClearWishlistAction;
use App\Actions\Wishlists\MoveProductBetweenWishlistsAction;
use App\Actions\Wishlists\RemoveProductFromWishlistAction;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Show extends Component
{
    use AuthorizesRequests;

    public Wishlist $wishlist;

    /**
     * @var array<int, int|string|null>
     */
    public array $moveTargetWishlistIds = [];

    public function mount(Wishlist $wishlist): void
    {
        $this->authorize('view', $wishlist);
        $this->wishlist = $wishlist;
    }

    public function removeProduct(int $productId, RemoveProductFromWishlistAction $action): void
    {
        $this->authorize('update', $this->wishlist);

        $product = Product::query()
            ->withTrashed()
            ->findOrFail($productId);

        $action->handle($this->buyer(), $this->wishlist, $product);

        session()->flash('success', __('wishlists.messages.product_removed'));
    }

    public function moveProduct(int $productId, MoveProductBetweenWishlistsAction $action): void
    {
        $this->authorize('update', $this->wishlist);

        $targetWishlistId = (int) ($this->moveTargetWishlistIds[$productId] ?? 0);
        $targetWishlist = Wishlist::query()
            ->where('buyer_id', $this->buyer()->id)
            ->findOrFail($targetWishlistId);

        $product = Product::query()
            ->withTrashed()
            ->findOrFail($productId);

        try {
            $action->handle($this->buyer(), $this->wishlist, $targetWishlist, $product);
        } catch (ValidationException $exception) {
            $this->addError("moveTargetWishlistIds.$productId", collect($exception->errors())->flatten()->first());

            return;
        }

        unset($this->moveTargetWishlistIds[$productId]);

        session()->flash('success', __('wishlists.messages.product_moved'));
    }

    public function addItemToCart(int $wishlistItemId, AddWishlistItemToCartAction $action): void
    {
        $this->authorize('update', $this->wishlist);

        $item = WishlistItem::query()
            ->where('wishlist_id', $this->wishlist->id)
            ->with('product.seller', 'wishlist')
            ->findOrFail($wishlistItemId);

        try {
            $action->handle($this->buyer(), $item);
        } catch (ValidationException $exception) {
            $this->addError("cart.$wishlistItemId", collect($exception->errors())->flatten()->first());

            return;
        }

        session()->flash('success', __('cart_messages_product_added'));
    }

    public function clearWishlist(ClearWishlistAction $action): void
    {
        $this->authorize('update', $this->wishlist);

        $action->handle($this->buyer(), $this->wishlist);

        session()->flash('success', __('wishlists.messages.cleared'));
    }

    public function render(): View
    {
        $wishlist = Wishlist::query()
            ->with([
                'items.product.primaryImage',
                'items.product.seller:id,company_name,name,is_active,deleted_at',
                'items.product.category:id,category_name,parent_category_id',
                'items.product.category.parent:id,category_name',
            ])
            ->withCount('items')
            ->findOrFail($this->wishlist->id);

        $this->authorize('view', $wishlist);
        $this->wishlist = $wishlist;

        return view('livewire.frontend.buyer.wishlists.show', [
            'wishlist' => $wishlist,
            'otherWishlists' => Wishlist::query()
                ->forBuyer($this->buyer())
                ->where('id', '!=', $wishlist->id)
                ->orderBy('name')
                ->get(),
            'wishlistItems' => $this->wishlistItemRows($wishlist),
            'canManage' => $wishlist->isOwnedBy($this->buyer()),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function wishlistItemRows(Wishlist $wishlist): Collection
    {
        return $wishlist->items
            ->map(function (WishlistItem $item): array {
                $product = $item->product;
                $seller = $product?->seller;
                $category = $product?->category;
                $parentCategory = $category?->parent;
                $isAvailable = $product !== null
                    && ! $product->trashed()
                    && (bool) $product->is_active
                    && $seller !== null
                    && ! $seller->trashed()
                    && (bool) $seller->is_active;

                return [
                    'id' => $item->id,
                    'product_id' => $product?->id,
                    'name' => $product?->name ?? __('common_unnamed_product'),
                    'image_url' => $product?->imageUrl('thumb') ?? asset((string) config('images.fallbacks.product')),
                    'price' => $product ? number_format((float) $product->price, 2) : null,
                    'unit' => $product ? __('units_unit_'.strtolower((string) $product->unit)) : null,
                    'seller' => $seller?->company_name ?: $seller?->name ?: __('common_not_specified'),
                    'category' => $category?->getTranslation('category_name', app()->getLocale()) ?? __('common_not_specified'),
                    'parent_category' => $parentCategory?->getTranslation('category_name', app()->getLocale()),
                    'stock' => (int) ($product?->stock ?? 0),
                    'is_available' => $isAvailable,
                    'has_stock_warning' => $isAvailable && (int) $product->stock <= max(1, (int) $product->min_order_count),
                    'url' => $product && $isAvailable ? route('buyer.products.show', $product) : null,
                ];
            });
    }

    private function buyer(): Buyer
    {
        $buyer = Auth::guard('buyer')->user();

        abort_unless($buyer instanceof Buyer, 403);

        return $buyer;
    }
}

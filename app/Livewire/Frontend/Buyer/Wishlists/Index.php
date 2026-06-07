<?php

namespace App\Livewire\Frontend\Buyer\Wishlists;

use App\Actions\Wishlists\ClearWishlistAction;
use App\Actions\Wishlists\CreateWishlistAction;
use App\Actions\Wishlists\DeleteWishlistAction;
use App\Actions\Wishlists\UpdateWishlistAction;
use App\Models\Users\Buyer;
use App\Models\Wishlist;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public ?string $description = null;

    public bool $isPrivate = true;

    public ?int $editingWishlistId = null;

    public string $editName = '';

    public ?string $editDescription = null;

    public bool $editIsPrivate = true;

    public bool $editIsDefault = false;

    public function createWishlist(CreateWishlistAction $action): void
    {
        $this->authorize('create', Wishlist::class);

        $action->handle($this->buyer(), [
            'name' => $this->name,
            'description' => $this->description,
            'is_private' => $this->isPrivate,
        ]);

        $this->reset(['name', 'description']);
        $this->isPrivate = true;

        session()->flash('success', __('wishlists.messages.created'));
    }

    public function startEditing(int $wishlistId): void
    {
        $wishlist = $this->wishlist($wishlistId);
        $this->authorize('update', $wishlist);

        $this->editingWishlistId = $wishlist->id;
        $this->editName = $wishlist->name;
        $this->editDescription = $wishlist->description;
        $this->editIsPrivate = (bool) $wishlist->is_private;
        $this->editIsDefault = (bool) $wishlist->is_default;
    }

    public function cancelEditing(): void
    {
        $this->reset(['editingWishlistId', 'editName', 'editDescription']);
        $this->editIsPrivate = true;
        $this->editIsDefault = false;
    }

    public function updateWishlist(UpdateWishlistAction $action): void
    {
        $wishlist = $this->wishlist((int) $this->editingWishlistId);
        $this->authorize('update', $wishlist);

        $action->handle($this->buyer(), $wishlist, [
            'name' => $this->editName,
            'description' => $this->editDescription,
            'is_private' => $this->editIsPrivate,
            'is_default' => $this->editIsDefault,
        ]);

        $this->cancelEditing();

        session()->flash('success', __('wishlists.messages.updated'));
    }

    public function deleteWishlist(int $wishlistId, DeleteWishlistAction $action): void
    {
        $wishlist = $this->wishlist($wishlistId);
        $this->authorize('delete', $wishlist);

        $action->handle($this->buyer(), $wishlist);

        session()->flash('success', __('wishlists.messages.deleted'));
    }

    public function clearWishlist(int $wishlistId, ClearWishlistAction $action): void
    {
        $wishlist = $this->wishlist($wishlistId);
        $this->authorize('update', $wishlist);

        $action->handle($this->buyer(), $wishlist);

        session()->flash('success', __('wishlists.messages.cleared'));
    }

    public function render(): View
    {
        $this->authorize('viewAny', Wishlist::class);

        return view('livewire.frontend.buyer.wishlists.index', [
            'wishlists' => Wishlist::query()
                ->forBuyer($this->buyer())
                ->withCount('items')
                ->latest()
                ->get(),
        ]);
    }

    private function buyer(): Buyer
    {
        $buyer = Auth::guard('buyer')->user();

        abort_unless($buyer instanceof Buyer, 403);

        return $buyer;
    }

    private function wishlist(int $wishlistId): Wishlist
    {
        return Wishlist::query()
            ->where('buyer_id', $this->buyer()->id)
            ->findOrFail($wishlistId);
    }
}

<?php

namespace App\Policies;

use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Wishlist;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class WishlistPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor) || $this->isBuyer($actor);
    }

    public function view(Authenticatable $actor, Wishlist $wishlist): bool
    {
        if ($this->isAdmin($actor) || $this->buyerOwnsId($actor, $wishlist->buyer_id)) {
            return true;
        }

        return $actor instanceof Buyer && ! $wishlist->is_private;
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isBuyer($actor) && $this->isActive($actor) && $this->isVerified($actor);
    }

    public function update(Authenticatable $actor, Wishlist $wishlist): bool
    {
        return $this->buyerOwnsId($actor, $wishlist->buyer_id) && $this->isActive($actor) && $this->isVerified($actor);
    }

    public function delete(Authenticatable $actor, Wishlist $wishlist): bool
    {
        return $this->update($actor, $wishlist);
    }

    public function restore(Authenticatable $actor, Wishlist $wishlist): bool
    {
        return false;
    }

    public function forceDelete(Authenticatable $actor, Wishlist $wishlist): bool
    {
        return $actor instanceof Admin;
    }

    public function manage(Authenticatable $actor, Wishlist $wishlist): bool
    {
        return $this->update($actor, $wishlist);
    }
}

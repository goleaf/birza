<?php

namespace App\Policies;

use App\Models\Cart;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class CartPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor) || $this->isBuyer($actor);
    }

    public function view(Authenticatable $actor, Cart $cart): bool
    {
        return $this->isAdmin($actor) || $this->buyerOwnsId($actor, $cart->user_id);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isBuyer($actor) && $this->isActive($actor);
    }

    public function update(Authenticatable $actor, Cart $cart): bool
    {
        return $this->buyerOwnsId($actor, $cart->user_id);
    }

    public function delete(Authenticatable $actor, Cart $cart): bool
    {
        return $this->update($actor, $cart);
    }

    public function checkout(Authenticatable $actor, Cart $cart): bool
    {
        return $this->update($actor, $cart);
    }

    public function manage(Authenticatable $actor, Cart $cart): bool
    {
        return $this->update($actor, $cart);
    }
}

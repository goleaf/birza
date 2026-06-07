<?php

namespace App\Policies;

use App\Models\CartItem;
use Illuminate\Contracts\Auth\Authenticatable;

class CartItemPolicy
{
    public function view(Authenticatable $actor, CartItem $cartItem): bool
    {
        return $cartItem->cart !== null && $actor->can('view', $cartItem->cart);
    }

    public function update(Authenticatable $actor, CartItem $cartItem): bool
    {
        return $cartItem->cart !== null && $actor->can('update', $cartItem->cart);
    }

    public function delete(Authenticatable $actor, CartItem $cartItem): bool
    {
        return $this->update($actor, $cartItem);
    }
}

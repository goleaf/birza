<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class OrderItemPolicy
{
    use ResolvesPolicyActors;

    public function view(Authenticatable $actor, OrderItem $orderItem): bool
    {
        return $this->isAdmin($actor)
            || $this->sellerOwnsId($actor, $orderItem->seller_id)
            || ($orderItem->order !== null && $actor->can('view', $orderItem->order));
    }

    public function update(Authenticatable $actor, OrderItem $orderItem): bool
    {
        return false;
    }

    public function delete(Authenticatable $actor, OrderItem $orderItem): bool
    {
        return $this->isAdmin($actor);
    }
}

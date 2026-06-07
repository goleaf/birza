<?php

namespace App\Policies;

use App\Models\OrderStatusHistory;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class OrderStatusHistoryPolicy
{
    use ResolvesPolicyActors;

    public function view(Authenticatable $actor, OrderStatusHistory $orderStatusHistory): bool
    {
        return $orderStatusHistory->order !== null && $actor->can('view', $orderStatusHistory->order);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor) || $this->isBuyer($actor) || $this->isSeller($actor);
    }

    public function update(Authenticatable $actor, OrderStatusHistory $orderStatusHistory): bool
    {
        return false;
    }

    public function delete(Authenticatable $actor, OrderStatusHistory $orderStatusHistory): bool
    {
        return false;
    }
}

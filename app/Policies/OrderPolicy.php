<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Enums\OrderStatusActorRole;
use App\Models\Order;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

class OrderPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor) || $this->isBuyer($actor) || $this->isSeller($actor);
    }

    public function view(Authenticatable $actor, Order $order): bool
    {
        return $order->isManageableBy($actor);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isBuyer($actor) && $this->isActive($actor);
    }

    public function update(Authenticatable $actor, Order $order): bool
    {
        return false;
    }

    public function delete(Authenticatable $actor, Order $order): bool
    {
        return $this->isAdmin($actor);
    }

    public function restore(Authenticatable $actor, Order $order): bool
    {
        return $this->isAdmin($actor);
    }

    public function forceDelete(Authenticatable $actor, Order $order): bool
    {
        return $this->isAdmin($actor);
    }

    public function cancel(Authenticatable $actor, Order $order): bool
    {
        return $this->changeStatus($actor, $order, OrderStatus::Cancelled);
    }

    public function changeStatus(Authenticatable $actor, Order $order, OrderStatus $nextStatus): bool
    {
        try {
            $role = OrderStatusActorRole::fromActor($actor);
        } catch (InvalidArgumentException) {
            return false;
        }

        return $order->isManageableBy($actor, $role)
            && $order->lifecycleStatus()->canTransitionTo($nextStatus)
            && $nextStatus->canBeChangedBy($role);
    }

    public function manage(Authenticatable $actor, Order $order): bool
    {
        return $this->isAdmin($actor);
    }

    public function viewTimeline(Authenticatable $actor, Order $order): bool
    {
        return $this->view($actor, $order);
    }

    public function viewInternalTimeline(Authenticatable $actor, Order $order): bool
    {
        return $this->isAdmin($actor);
    }

    public function addShippingUpdate(Authenticatable $actor, Order $order): bool
    {
        return $this->isSeller($actor)
            && $this->isActive($actor)
            && $order->isManageableBy($actor, OrderStatusActorRole::Seller)
            && $order->canReceiveShippingUpdate();
    }

    public function addTrackingNumber(Authenticatable $actor, Order $order): bool
    {
        return $this->addShippingUpdate($actor, $order);
    }

    public function markAsShipped(Authenticatable $actor, Order $order): bool
    {
        return $this->changeStatus($actor, $order, OrderStatus::Shipped);
    }

    public function markAsDelivered(Authenticatable $actor, Order $order): bool
    {
        return $this->changeStatus($actor, $order, OrderStatus::Delivered);
    }

    public function addInternalNote(Authenticatable $actor, Order $order): bool
    {
        return $this->isAdmin($actor);
    }
}

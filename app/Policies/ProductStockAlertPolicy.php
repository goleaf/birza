<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\ProductStockAlert;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use Illuminate\Contracts\Auth\Authenticatable;

class ProductStockAlertPolicy
{
    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isBuyer($actor) || $this->isAdmin($actor);
    }

    public function view(Authenticatable $actor, ProductStockAlert $productStockAlert): bool
    {
        return $this->isAdmin($actor)
            || $this->buyerOwnsId($actor, $productStockAlert->buyer_id);
    }

    public function create(Authenticatable $actor, Product $product): bool
    {
        $product->loadMissing('seller:id,is_active,deleted_at');

        return $this->isBuyer($actor)
            && $this->isActive($actor)
            && $this->isVerified($actor)
            && $product->isVisibleToBuyers();
    }

    public function update(Authenticatable $actor, ProductStockAlert $productStockAlert): bool
    {
        return $this->buyerOwnsId($actor, $productStockAlert->buyer_id);
    }

    public function cancel(Authenticatable $actor, ProductStockAlert $productStockAlert): bool
    {
        return $this->update($actor, $productStockAlert);
    }

    public function delete(Authenticatable $actor, ProductStockAlert $productStockAlert): bool
    {
        return false;
    }

    public function restore(Authenticatable $actor, ProductStockAlert $productStockAlert): bool
    {
        return false;
    }

    public function forceDelete(Authenticatable $actor, ProductStockAlert $productStockAlert): bool
    {
        return false;
    }

    private function isAdmin(Authenticatable $actor): bool
    {
        return $actor instanceof Admin;
    }

    private function isBuyer(Authenticatable $actor): bool
    {
        return $actor instanceof Buyer;
    }

    private function isActive(Authenticatable $actor): bool
    {
        return (bool) ($actor->getAttribute('is_active') ?? true);
    }

    private function isVerified(Authenticatable $actor): bool
    {
        return (bool) ($actor->getAttribute('is_verified') ?? true);
    }

    private function buyerOwnsId(Authenticatable $actor, ?int $buyerId): bool
    {
        return $actor instanceof Buyer
            && $buyerId !== null
            && (int) $buyerId === (int) $actor->getAuthIdentifier();
    }
}

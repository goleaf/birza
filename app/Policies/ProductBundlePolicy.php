<?php

namespace App\Policies;

use App\Models\ProductBundle;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class ProductBundlePolicy
{
    use ResolvesPolicyActors;

    public function viewAny(?Authenticatable $actor): bool
    {
        return true;
    }

    public function view(?Authenticatable $actor, ProductBundle $productBundle): bool
    {
        if ($this->isAdmin($actor) || $this->sellerOwnsId($actor, $productBundle->seller_id)) {
            return true;
        }

        return $productBundle->isVisibleToBuyers();
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor) || $this->isApprovedSeller($actor);
    }

    public function update(Authenticatable $actor, ProductBundle $productBundle): bool
    {
        return $this->isAdmin($actor)
            || ($this->isApprovedSeller($actor) && $this->sellerOwnsId($actor, $productBundle->seller_id));
    }

    public function delete(Authenticatable $actor, ProductBundle $productBundle): bool
    {
        return $this->update($actor, $productBundle);
    }

    public function restore(Authenticatable $actor, ProductBundle $productBundle): bool
    {
        return $this->update($actor, $productBundle);
    }

    public function forceDelete(Authenticatable $actor, ProductBundle $productBundle): bool
    {
        return $this->isAdmin($actor);
    }

    public function publish(Authenticatable $actor, ProductBundle $productBundle): bool
    {
        return $this->update($actor, $productBundle);
    }

    public function unpublish(Authenticatable $actor, ProductBundle $productBundle): bool
    {
        return $this->update($actor, $productBundle);
    }

    public function archive(Authenticatable $actor, ProductBundle $productBundle): bool
    {
        return $this->update($actor, $productBundle);
    }

    public function manageItems(Authenticatable $actor, ProductBundle $productBundle): bool
    {
        return $this->update($actor, $productBundle);
    }

    public function addToCart(?Authenticatable $actor, ProductBundle $productBundle): bool
    {
        return $productBundle->isVisibleToBuyers();
    }
}

<?php

namespace App\Policies;

use App\Models\Product;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class ProductPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(?Authenticatable $actor): bool
    {
        return true;
    }

    public function view(?Authenticatable $actor, Product $product): bool
    {
        if ($this->isAdmin($actor) || $this->sellerOwnsProduct($actor, $product)) {
            return true;
        }

        return ! $product->trashed() && (bool) $product->is_active;
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor) || $this->isApprovedSeller($actor);
    }

    public function update(Authenticatable $actor, Product $product): bool
    {
        return $this->isAdmin($actor)
            || ($this->sellerOwnsProduct($actor, $product) && $this->isApprovedSeller($actor));
    }

    public function delete(Authenticatable $actor, Product $product): bool
    {
        return $this->isAdmin($actor)
            || ($this->sellerOwnsProduct($actor, $product) && $this->isApprovedSeller($actor));
    }

    public function restore(Authenticatable $actor, Product $product): bool
    {
        return $this->isAdmin($actor)
            || ($this->sellerOwnsProduct($actor, $product) && $this->isApprovedSeller($actor));
    }

    public function forceDelete(Authenticatable $actor, Product $product): bool
    {
        return $this->isAdmin($actor);
    }

    public function publish(Authenticatable $actor, Product $product): bool
    {
        return $this->update($actor, $product);
    }

    public function unpublish(Authenticatable $actor, Product $product): bool
    {
        return $this->update($actor, $product);
    }

    public function archive(Authenticatable $actor, Product $product): bool
    {
        return $this->delete($actor, $product);
    }

    public function changeStatus(Authenticatable $actor, Product $product): bool
    {
        return $this->isAdmin($actor);
    }

    public function approve(Authenticatable $actor, Product $product): bool
    {
        return $this->isAdmin($actor);
    }

    public function reject(Authenticatable $actor, Product $product): bool
    {
        return $this->isAdmin($actor);
    }

    public function moderate(Authenticatable $actor, Product $product): bool
    {
        return $this->isAdmin($actor);
    }

    public function uploadImage(Authenticatable $actor, Product $product): bool
    {
        return $this->update($actor, $product);
    }

    public function manageGallery(Authenticatable $actor, Product $product): bool
    {
        return $this->update($actor, $product);
    }

    public function manage(Authenticatable $actor, Product $product): bool
    {
        return $this->update($actor, $product);
    }
}

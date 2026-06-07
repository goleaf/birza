<?php

namespace App\Policies;

use App\Models\ProductImage;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class ProductImagePolicy
{
    use ResolvesPolicyActors;

    public function view(Authenticatable $actor, ProductImage $productImage): bool
    {
        return $productImage->product !== null && $actor->can('view', $productImage->product);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor) || $this->isApprovedSeller($actor);
    }

    public function update(Authenticatable $actor, ProductImage $productImage): bool
    {
        return $productImage->product !== null && $actor->can('manageGallery', $productImage->product);
    }

    public function delete(Authenticatable $actor, ProductImage $productImage): bool
    {
        return $this->update($actor, $productImage);
    }

    public function manage(Authenticatable $actor, ProductImage $productImage): bool
    {
        return $this->update($actor, $productImage);
    }
}

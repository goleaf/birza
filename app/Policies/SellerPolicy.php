<?php

namespace App\Policies;

use App\Models\Users\Seller;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class SellerPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function view(Authenticatable $actor, Seller $seller): bool
    {
        return $this->isAdmin($actor) || $this->sellerOwnsId($actor, $seller->id);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function update(Authenticatable $actor, Seller $seller): bool
    {
        return $this->isAdmin($actor) || $this->sellerOwnsId($actor, $seller->id);
    }

    public function delete(Authenticatable $actor, Seller $seller): bool
    {
        return $this->isAdmin($actor);
    }

    public function restore(Authenticatable $actor, Seller $seller): bool
    {
        return $this->isAdmin($actor);
    }

    public function forceDelete(Authenticatable $actor, Seller $seller): bool
    {
        return $this->isAdmin($actor);
    }

    public function approve(Authenticatable $actor, Seller $seller): bool
    {
        return $this->isAdmin($actor);
    }

    public function reject(Authenticatable $actor, Seller $seller): bool
    {
        return $this->isAdmin($actor);
    }

    public function block(Authenticatable $actor, Seller $seller): bool
    {
        return $this->isAdmin($actor);
    }

    public function unblock(Authenticatable $actor, Seller $seller): bool
    {
        return $this->isAdmin($actor);
    }

    public function changeStatus(Authenticatable $actor, Seller $seller): bool
    {
        return $this->isAdmin($actor);
    }

    public function manage(Authenticatable $actor, Seller $seller): bool
    {
        return $this->isAdmin($actor);
    }
}

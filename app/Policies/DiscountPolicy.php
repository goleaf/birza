<?php

namespace App\Policies;

use App\Models\Discount;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class DiscountPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor) || $this->isApprovedSeller($actor);
    }

    public function view(Authenticatable $actor, Discount $discount): bool
    {
        return $this->isAdmin($actor) || $this->sellerOwnsId($actor, $discount->seller_id);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isApprovedSeller($actor);
    }

    public function update(Authenticatable $actor, Discount $discount): bool
    {
        return $this->isAdmin($actor)
            || ($this->isApprovedSeller($actor) && $this->sellerOwnsId($actor, $discount->seller_id));
    }

    public function delete(Authenticatable $actor, Discount $discount): bool
    {
        return $this->update($actor, $discount);
    }

    public function restore(Authenticatable $actor, Discount $discount): bool
    {
        return $this->update($actor, $discount);
    }

    public function forceDelete(Authenticatable $actor, Discount $discount): bool
    {
        return $this->isAdmin($actor);
    }

    public function activate(Authenticatable $actor, Discount $discount): bool
    {
        return $this->update($actor, $discount);
    }

    public function deactivate(Authenticatable $actor, Discount $discount): bool
    {
        return $this->update($actor, $discount);
    }
}

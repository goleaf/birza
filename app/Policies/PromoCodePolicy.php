<?php

namespace App\Policies;

use App\Models\PromoCode;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class PromoCodePolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor) || $this->isApprovedSeller($actor);
    }

    public function view(Authenticatable $actor, PromoCode $promoCode): bool
    {
        return $this->isAdmin($actor) || $this->sellerOwnsId($actor, $promoCode->seller_id);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isApprovedSeller($actor);
    }

    public function update(Authenticatable $actor, PromoCode $promoCode): bool
    {
        return $this->isAdmin($actor)
            || ($this->isApprovedSeller($actor) && $this->sellerOwnsId($actor, $promoCode->seller_id));
    }

    public function delete(Authenticatable $actor, PromoCode $promoCode): bool
    {
        return $this->update($actor, $promoCode);
    }

    public function restore(Authenticatable $actor, PromoCode $promoCode): bool
    {
        return $this->update($actor, $promoCode);
    }

    public function forceDelete(Authenticatable $actor, PromoCode $promoCode): bool
    {
        return $this->isAdmin($actor);
    }

    public function activate(Authenticatable $actor, PromoCode $promoCode): bool
    {
        return $this->update($actor, $promoCode);
    }

    public function deactivate(Authenticatable $actor, PromoCode $promoCode): bool
    {
        return $this->update($actor, $promoCode);
    }

    public function apply(Authenticatable $actor, PromoCode $promoCode): bool
    {
        return $this->isBuyer($actor) && $this->isActive($actor);
    }
}

<?php

namespace App\Policies;

use App\Models\PromoCodeRedemption;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class PromoCodeRedemptionPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function view(Authenticatable $actor, PromoCodeRedemption $promoCodeRedemption): bool
    {
        return $this->isAdmin($actor)
            || $this->buyerOwnsId($actor, $promoCodeRedemption->user_id)
            || $this->sellerOwnsId($actor, $promoCodeRedemption->promoCode?->seller_id);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isBuyer($actor) && $this->isActive($actor);
    }

    public function update(Authenticatable $actor, PromoCodeRedemption $promoCodeRedemption): bool
    {
        return false;
    }

    public function delete(Authenticatable $actor, PromoCodeRedemption $promoCodeRedemption): bool
    {
        return $this->isAdmin($actor);
    }

    public function restore(Authenticatable $actor, PromoCodeRedemption $promoCodeRedemption): bool
    {
        return false;
    }

    public function forceDelete(Authenticatable $actor, PromoCodeRedemption $promoCodeRedemption): bool
    {
        return $this->isAdmin($actor);
    }
}

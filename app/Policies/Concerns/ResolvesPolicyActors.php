<?php

namespace App\Policies\Concerns;

use App\Models\Product;
use App\Models\User;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Contracts\Auth\Authenticatable;

trait ResolvesPolicyActors
{
    protected function isAdmin(?Authenticatable $actor): bool
    {
        return $actor instanceof Admin;
    }

    protected function isBuyer(?Authenticatable $actor): bool
    {
        return $actor instanceof Buyer;
    }

    protected function isSeller(?Authenticatable $actor): bool
    {
        return $actor instanceof Seller;
    }

    protected function isActive(?Authenticatable $actor): bool
    {
        return $actor !== null && (bool) ($actor->getAttribute('is_active') ?? true);
    }

    protected function isVerified(?Authenticatable $actor): bool
    {
        return $actor !== null && (bool) ($actor->getAttribute('is_verified') ?? true);
    }

    protected function isApprovedSeller(?Authenticatable $actor): bool
    {
        return $this->isSeller($actor) && $this->isActive($actor) && $this->isVerified($actor);
    }

    protected function sellerOwnsProduct(?Authenticatable $actor, Product $product): bool
    {
        return $this->isSeller($actor)
            && (int) $product->seller_id === (int) $actor->getAuthIdentifier();
    }

    protected function buyerOwnsId(?Authenticatable $actor, ?int $buyerId): bool
    {
        return $this->isBuyer($actor)
            && $buyerId !== null
            && (int) $buyerId === (int) $actor->getAuthIdentifier();
    }

    protected function sellerOwnsId(?Authenticatable $actor, ?int $sellerId): bool
    {
        return $this->isSeller($actor)
            && $sellerId !== null
            && (int) $sellerId === (int) $actor->getAuthIdentifier();
    }

    protected function baseUserOwnsId(?Authenticatable $actor, ?int $userId): bool
    {
        return $actor instanceof User
            && $userId !== null
            && (int) $userId === (int) $actor->getAuthIdentifier();
    }
}

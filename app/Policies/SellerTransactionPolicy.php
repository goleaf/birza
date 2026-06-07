<?php

namespace App\Policies;

use App\Models\SellerTransaction;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class SellerTransactionPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor) || $this->isSeller($actor);
    }

    public function view(Authenticatable $actor, SellerTransaction $sellerTransaction): bool
    {
        return $this->isAdmin($actor) || $this->sellerOwnsId($actor, $sellerTransaction->seller_id);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function update(Authenticatable $actor, SellerTransaction $sellerTransaction): bool
    {
        return false;
    }

    public function delete(Authenticatable $actor, SellerTransaction $sellerTransaction): bool
    {
        return false;
    }
}

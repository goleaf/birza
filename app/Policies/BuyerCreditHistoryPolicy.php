<?php

namespace App\Policies;

use App\Models\BuyerCreditHistory;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class BuyerCreditHistoryPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor) || $this->isBuyer($actor);
    }

    public function view(Authenticatable $actor, BuyerCreditHistory $buyerCreditHistory): bool
    {
        return $this->isAdmin($actor) || $this->buyerOwnsId($actor, $buyerCreditHistory->buyer_id);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function update(Authenticatable $actor, BuyerCreditHistory $buyerCreditHistory): bool
    {
        return false;
    }

    public function delete(Authenticatable $actor, BuyerCreditHistory $buyerCreditHistory): bool
    {
        return false;
    }
}

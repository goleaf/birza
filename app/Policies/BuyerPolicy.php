<?php

namespace App\Policies;

use App\Models\Users\Buyer;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class BuyerPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function view(Authenticatable $actor, Buyer $buyer): bool
    {
        return $this->isAdmin($actor) || $this->buyerOwnsId($actor, $buyer->id);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function update(Authenticatable $actor, Buyer $buyer): bool
    {
        return $this->isAdmin($actor) || $this->buyerOwnsId($actor, $buyer->id);
    }

    public function delete(Authenticatable $actor, Buyer $buyer): bool
    {
        return $this->isAdmin($actor);
    }

    public function restore(Authenticatable $actor, Buyer $buyer): bool
    {
        return $this->isAdmin($actor);
    }

    public function forceDelete(Authenticatable $actor, Buyer $buyer): bool
    {
        return $this->isAdmin($actor);
    }

    public function manageCredit(Authenticatable $actor, Buyer $buyer): bool
    {
        return $this->isAdmin($actor);
    }

    public function changeStatus(Authenticatable $actor, Buyer $buyer): bool
    {
        return $this->isAdmin($actor);
    }

    public function block(Authenticatable $actor, Buyer $buyer): bool
    {
        return $this->isAdmin($actor);
    }

    public function unblock(Authenticatable $actor, Buyer $buyer): bool
    {
        return $this->isAdmin($actor);
    }

    public function manage(Authenticatable $actor, Buyer $buyer): bool
    {
        return $this->isAdmin($actor);
    }
}

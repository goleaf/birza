<?php

namespace App\Policies;

use App\Models\Address;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class AddressPolicy
{
    use ResolvesPolicyActors;

    public function view(Authenticatable $actor, Address $address): bool
    {
        return $this->isAdmin($actor) || $this->baseUserOwnsId($actor, $address->user_id);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isActive($actor);
    }

    public function update(Authenticatable $actor, Address $address): bool
    {
        return $this->view($actor, $address);
    }

    public function delete(Authenticatable $actor, Address $address): bool
    {
        return $this->view($actor, $address);
    }

    public function manage(Authenticatable $actor, Address $address): bool
    {
        return $this->update($actor, $address);
    }
}

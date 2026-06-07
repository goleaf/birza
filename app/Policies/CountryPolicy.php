<?php

namespace App\Policies;

use App\Models\Country;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class CountryPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(?Authenticatable $actor): bool
    {
        return true;
    }

    public function view(?Authenticatable $actor, Country $country): bool
    {
        return $this->isAdmin($actor) || (bool) $country->is_active;
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function update(Authenticatable $actor, Country $country): bool
    {
        return $this->isAdmin($actor);
    }

    public function delete(Authenticatable $actor, Country $country): bool
    {
        return $this->isAdmin($actor);
    }

    public function manage(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }
}

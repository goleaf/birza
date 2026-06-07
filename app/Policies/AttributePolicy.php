<?php

namespace App\Policies;

use App\Models\Attribute;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class AttributePolicy
{
    use ResolvesPolicyActors;

    public function viewAny(?Authenticatable $actor): bool
    {
        return true;
    }

    public function view(?Authenticatable $actor, Attribute $attribute): bool
    {
        return $this->isAdmin($actor) || (bool) $attribute->is_active;
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function update(Authenticatable $actor, Attribute $attribute): bool
    {
        return $this->isAdmin($actor);
    }

    public function delete(Authenticatable $actor, Attribute $attribute): bool
    {
        return $this->isAdmin($actor);
    }

    public function manage(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }
}

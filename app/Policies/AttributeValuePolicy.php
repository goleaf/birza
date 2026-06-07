<?php

namespace App\Policies;

use App\Models\AttributeValue;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class AttributeValuePolicy
{
    use ResolvesPolicyActors;

    public function viewAny(?Authenticatable $actor): bool
    {
        return true;
    }

    public function view(?Authenticatable $actor, AttributeValue $attributeValue): bool
    {
        return $this->isAdmin($actor) || (bool) $attributeValue->is_active;
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function update(Authenticatable $actor, AttributeValue $attributeValue): bool
    {
        return $this->isAdmin($actor);
    }

    public function delete(Authenticatable $actor, AttributeValue $attributeValue): bool
    {
        return $this->isAdmin($actor);
    }

    public function manage(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }
}

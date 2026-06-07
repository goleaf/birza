<?php

namespace App\Policies;

use App\Models\Category;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class CategoryPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(?Authenticatable $actor): bool
    {
        return true;
    }

    public function view(?Authenticatable $actor, Category $category): bool
    {
        return $this->isAdmin($actor) || (bool) $category->is_active;
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function update(Authenticatable $actor, Category $category): bool
    {
        return $this->isAdmin($actor);
    }

    public function delete(Authenticatable $actor, Category $category): bool
    {
        return $this->isAdmin($actor);
    }

    public function restore(Authenticatable $actor, Category $category): bool
    {
        return $this->isAdmin($actor);
    }

    public function forceDelete(Authenticatable $actor, Category $category): bool
    {
        return $this->isAdmin($actor);
    }

    public function manage(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }
}

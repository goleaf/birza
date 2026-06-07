<?php

namespace App\Policies;

use App\Models\Users\Admin;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class AdminPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function view(Authenticatable $actor, Admin $admin): bool
    {
        return $this->isAdmin($actor);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function update(Authenticatable $actor, Admin $admin): bool
    {
        return $this->isAdmin($actor);
    }

    public function delete(Authenticatable $actor, Admin $admin): bool
    {
        return $this->isAdmin($actor) && (int) $actor->getAuthIdentifier() !== (int) $admin->id;
    }

    public function manage(Authenticatable $actor, Admin $admin): bool
    {
        return $this->isAdmin($actor);
    }
}

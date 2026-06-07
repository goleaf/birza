<?php

namespace App\Policies;

use App\Models\AdminAction;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class AdminActionPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function view(Authenticatable $actor, AdminAction $adminAction): bool
    {
        return $this->isAdmin($actor);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function update(Authenticatable $actor, AdminAction $adminAction): bool
    {
        return false;
    }

    public function delete(Authenticatable $actor, AdminAction $adminAction): bool
    {
        return false;
    }

    public function manage(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }
}

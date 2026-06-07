<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class UserPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function view(Authenticatable $actor, User $user): bool
    {
        return $this->isAdmin($actor) || $this->baseUserOwnsId($actor, $user->id);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function update(Authenticatable $actor, User $user): bool
    {
        return $this->isAdmin($actor) || $this->baseUserOwnsId($actor, $user->id);
    }

    public function delete(Authenticatable $actor, User $user): bool
    {
        return $this->isAdmin($actor);
    }

    public function restore(Authenticatable $actor, User $user): bool
    {
        return $this->isAdmin($actor);
    }

    public function forceDelete(Authenticatable $actor, User $user): bool
    {
        return $this->isAdmin($actor);
    }

    public function block(Authenticatable $actor, User $user): bool
    {
        return $this->isAdmin($actor);
    }

    public function unblock(Authenticatable $actor, User $user): bool
    {
        return $this->isAdmin($actor);
    }

    public function manage(Authenticatable $actor, User $user): bool
    {
        return $this->isAdmin($actor);
    }
}

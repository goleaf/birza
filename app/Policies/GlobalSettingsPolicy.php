<?php

namespace App\Policies;

use App\Models\GlobalSettings;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class GlobalSettingsPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function view(Authenticatable $actor, GlobalSettings $globalSettings): bool
    {
        return $this->isAdmin($actor);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function update(Authenticatable $actor, GlobalSettings $globalSettings): bool
    {
        return $this->isAdmin($actor);
    }

    public function delete(Authenticatable $actor, GlobalSettings $globalSettings): bool
    {
        return $this->isAdmin($actor);
    }

    public function manage(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }
}

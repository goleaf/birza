<?php

namespace App\Policies;

use App\Models\CreditAttachment;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class CreditAttachmentPolicy
{
    use ResolvesPolicyActors;

    public function view(Authenticatable $actor, CreditAttachment $creditAttachment): bool
    {
        $creditAttachment->loadMissing('creditHistory');

        return $creditAttachment->creditHistory !== null
            && $actor->can('view', $creditAttachment->creditHistory);
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function delete(Authenticatable $actor, CreditAttachment $creditAttachment): bool
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\Review;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class ReviewPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(?Authenticatable $actor): bool
    {
        return true;
    }

    public function view(?Authenticatable $actor, Review $review): bool
    {
        return (bool) $review->is_approved
            || $this->isAdmin($actor)
            || $this->baseUserOwnsId($actor, $review->user_id);
    }

    public function create(Authenticatable $actor): bool
    {
        return ($this->isBuyer($actor) || $this->baseUserOwnsId($actor, $actor->getAuthIdentifier()))
            && $this->isActive($actor);
    }

    public function update(Authenticatable $actor, Review $review): bool
    {
        return $this->isAdmin($actor) || $this->baseUserOwnsId($actor, $review->user_id);
    }

    public function delete(Authenticatable $actor, Review $review): bool
    {
        return $this->update($actor, $review);
    }

    public function restore(Authenticatable $actor, Review $review): bool
    {
        return $this->isAdmin($actor);
    }

    public function forceDelete(Authenticatable $actor, Review $review): bool
    {
        return $this->isAdmin($actor);
    }

    public function approve(Authenticatable $actor, Review $review): bool
    {
        return $this->isAdmin($actor);
    }

    public function reject(Authenticatable $actor, Review $review): bool
    {
        return $this->isAdmin($actor);
    }

    public function moderate(Authenticatable $actor, Review $review): bool
    {
        return $this->isAdmin($actor);
    }
}

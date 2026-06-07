<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\Message;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class MessagePolicy
{
    use ResolvesPolicyActors;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor) || $this->isBuyer($actor) || $this->isSeller($actor);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Authenticatable $actor, Message $message): bool
    {
        return app(ConversationPolicy::class)->view($actor, $message->conversation);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authenticatable $actor, ?Conversation $conversation = null): bool
    {
        return $conversation !== null
            && app(ConversationPolicy::class)->sendMessage($actor, $conversation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authenticatable $actor, Message $message): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authenticatable $actor, Message $message): bool
    {
        return $this->moderate($actor, $message);
    }

    public function moderate(Authenticatable $actor, Message $message): bool
    {
        return $this->isAdmin($actor) && $this->isActive($actor);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Authenticatable $actor, Message $message): bool
    {
        return $this->moderate($actor, $message);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Authenticatable $actor, Message $message): bool
    {
        return false;
    }
}

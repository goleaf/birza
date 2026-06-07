<?php

namespace App\Policies;

use App\Enums\ConversationStatus;
use App\Models\Conversation;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class ConversationPolicy
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
    public function view(Authenticatable $actor, Conversation $conversation): bool
    {
        return $this->isAdmin($actor) || $conversation->isParticipant($actor);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authenticatable $actor): bool
    {
        return $this->isBuyer($actor) && $this->isActive($actor) && $this->isVerified($actor);
    }

    public function createFromProduct(Authenticatable $actor, Product $product): bool
    {
        return $this->create($actor)
            && ! $product->trashed()
            && (bool) $product->is_active
            && $product->seller instanceof Seller
            && (bool) $product->seller->is_active
            && (bool) $product->seller->is_verified
            && ! $this->buyerOwnsSellerProfile($actor, $product->seller);
    }

    public function createFromOrder(Authenticatable $actor, Order $order, Seller $seller): bool
    {
        if ($this->isBuyer($actor)) {
            return (int) $order->buyer_id === (int) $actor->getAuthIdentifier()
                && $order->hasSellerItems((int) $seller->getKey());
        }

        if ($this->isSeller($actor)) {
            return (int) $seller->getKey() === (int) $actor->getAuthIdentifier()
                && $order->hasSellerItems((int) $seller->getKey());
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authenticatable $actor, Conversation $conversation): bool
    {
        return $this->archive($actor, $conversation) || $this->close($actor, $conversation);
    }

    public function sendMessage(Authenticatable $actor, Conversation $conversation): bool
    {
        return $conversation->isParticipant($actor)
            && $this->isActive($actor)
            && $this->isVerified($actor)
            && ($conversation->status ?? ConversationStatus::Active)->canReceiveMessages();
    }

    public function archive(Authenticatable $actor, Conversation $conversation): bool
    {
        return $conversation->isParticipant($actor);
    }

    public function close(Authenticatable $actor, Conversation $conversation): bool
    {
        return $this->isAdmin($actor) || $conversation->isParticipant($actor);
    }

    public function moderate(Authenticatable $actor, Conversation $conversation): bool
    {
        return $this->isAdmin($actor) && $this->isActive($actor);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authenticatable $actor, Conversation $conversation): bool
    {
        return $this->moderate($actor, $conversation);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Authenticatable $actor, Conversation $conversation): bool
    {
        return $this->moderate($actor, $conversation);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Authenticatable $actor, Conversation $conversation): bool
    {
        return false;
    }

    private function buyerOwnsSellerProfile(Authenticatable $actor, Seller $seller): bool
    {
        return $actor instanceof Buyer
            && (
                ($actor->user_id !== null && $seller->user_id !== null && (int) $actor->user_id === (int) $seller->user_id)
                || strtolower((string) $actor->email) === strtolower((string) $seller->email)
            );
    }
}

<?php

namespace App\Policies;

use App\Enums\ProductQuestionStatus;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class ProductQuestionPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(?Authenticatable $actor): bool
    {
        return true;
    }

    public function view(?Authenticatable $actor, ProductQuestion $productQuestion): bool
    {
        return $this->isAdmin($actor)
            || $this->sellerOwnsId($actor, $productQuestion->seller_id)
            || $this->buyerOwnsId($actor, $productQuestion->buyer_id)
            || (
                ProductQuestionStatus::fromValue($productQuestion->status) === ProductQuestionStatus::Answered
                && (bool) $productQuestion->is_public
            );
    }

    public function create(?Authenticatable $actor, ?Product $product = null): bool
    {
        if ($actor !== null && (! $this->isBuyer($actor) || ! $this->isActive($actor))) {
            return false;
        }

        if ($product === null) {
            return true;
        }

        return ! $product->trashed() && (bool) $product->is_active;
    }

    public function update(Authenticatable $actor, ProductQuestion $productQuestion): bool
    {
        return $this->answer($actor, $productQuestion) || $this->moderate($actor, $productQuestion);
    }

    public function delete(Authenticatable $actor, ProductQuestion $productQuestion): bool
    {
        return $this->isAdmin($actor);
    }

    public function restore(Authenticatable $actor, ProductQuestion $productQuestion): bool
    {
        return $this->isAdmin($actor);
    }

    public function forceDelete(Authenticatable $actor, ProductQuestion $productQuestion): bool
    {
        return false;
    }

    public function answer(Authenticatable $actor, ProductQuestion $productQuestion): bool
    {
        return $this->sellerOwnsId($actor, $productQuestion->seller_id)
            && $this->isApprovedSeller($actor)
            && $productQuestion->isAnswerable();
    }

    public function hide(Authenticatable $actor, ProductQuestion $productQuestion): bool
    {
        return $this->isAdmin($actor)
            || (
                $this->sellerOwnsId($actor, $productQuestion->seller_id)
                && ! in_array(ProductQuestionStatus::fromValue($productQuestion->status), [
                    ProductQuestionStatus::Rejected,
                    ProductQuestionStatus::Hidden,
                ], true)
            );
    }

    public function approve(Authenticatable $actor, ProductQuestion $productQuestion): bool
    {
        return $this->isAdmin($actor);
    }

    public function reject(Authenticatable $actor, ProductQuestion $productQuestion): bool
    {
        return $this->isAdmin($actor);
    }

    public function moderate(Authenticatable $actor, ProductQuestion $productQuestion): bool
    {
        return $this->isAdmin($actor);
    }
}

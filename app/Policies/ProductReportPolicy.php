<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\ProductReport;
use App\Policies\Concerns\ResolvesPolicyActors;
use Illuminate\Contracts\Auth\Authenticatable;

class ProductReportPolicy
{
    use ResolvesPolicyActors;

    public function viewAny(Authenticatable $actor): bool
    {
        return $this->isAdmin($actor);
    }

    public function view(Authenticatable $actor, ProductReport $productReport): bool
    {
        return $this->isAdmin($actor);
    }

    public function create(?Authenticatable $actor, Product $product): bool
    {
        if ($product->trashed() || ! (bool) $product->is_active) {
            return false;
        }

        if ($actor === null) {
            return (bool) config('marketplace.product_reports.allow_guest_reports', true);
        }

        return $this->isBuyer($actor) && $this->isActive($actor);
    }

    public function update(Authenticatable $actor, ProductReport $productReport): bool
    {
        return $this->isAdmin($actor);
    }

    public function delete(Authenticatable $actor, ProductReport $productReport): bool
    {
        return false;
    }

    public function restore(Authenticatable $actor, ProductReport $productReport): bool
    {
        return false;
    }

    public function forceDelete(Authenticatable $actor, ProductReport $productReport): bool
    {
        return false;
    }

    public function review(Authenticatable $actor, ProductReport $productReport): bool
    {
        return $this->isAdmin($actor);
    }

    public function resolve(Authenticatable $actor, ProductReport $productReport): bool
    {
        return $this->isAdmin($actor);
    }

    public function reject(Authenticatable $actor, ProductReport $productReport): bool
    {
        return $this->isAdmin($actor);
    }

    public function dismiss(Authenticatable $actor, ProductReport $productReport): bool
    {
        return $this->isAdmin($actor);
    }

    public function hideProduct(Authenticatable $actor, ProductReport $productReport): bool
    {
        return $this->isAdmin($actor);
    }
}

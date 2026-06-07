<?php

namespace App\Support\Products;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductComparison
{
    public const MAX_PRODUCTS = 4;

    private const SESSION_KEY = 'product_compare.ids';

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        return collect(session(self::SESSION_KEY, []))
            ->map(fn (mixed $productId): int => (int) $productId)
            ->filter(fn (int $productId): bool => $productId > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    public function add(int $productId): array
    {
        $ids = $this->ids();
        $ids[] = $productId;

        return $this->put($ids);
    }

    /**
     * @return list<int>
     */
    public function remove(int $productId): array
    {
        return $this->put(
            collect($this->ids())
                ->reject(fn (int $storedProductId): bool => $storedProductId === $productId)
                ->values()
                ->all()
        );
    }

    /**
     * @param  list<int>  $productIds
     * @return list<int>
     */
    public function put(array $productIds): array
    {
        $ids = collect($productIds)
            ->map(fn (mixed $productId): int => (int) $productId)
            ->filter(fn (int $productId): bool => $productId > 0)
            ->unique()
            ->take(self::MAX_PRODUCTS)
            ->values()
            ->all();

        session()->put(self::SESSION_KEY, $ids);

        return $ids;
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    public function count(): int
    {
        return count($this->ids());
    }

    /**
     * @return Collection<int, Product>
     */
    public function products(bool $pruneUnavailable = true): Collection
    {
        $ids = $this->ids();

        if ($ids === []) {
            return collect();
        }

        $positions = array_flip($ids);

        $products = $this->visibleProductQuery()
            ->whereKey($ids)
            ->get()
            ->sortBy(fn (Product $product): int => $positions[$product->getKey()] ?? PHP_INT_MAX)
            ->values();

        if ($pruneUnavailable) {
            $this->put($products->modelKeys());
        }

        return $products;
    }

    public function isProductComparable(Product $product): bool
    {
        return $this->visibleProductQuery()
            ->whereKey($product->getKey())
            ->exists();
    }

    public function visibleProductQuery(): Builder
    {
        return Product::query()
            ->select([
                'id',
                'name',
                'category_id',
                'seller_id',
                'price',
                'unit',
                'stock',
                'description',
                'is_active',
                'country_of_origin',
                'product_image',
                'product_additional_image',
                'image_library',
                'created_at',
            ])
            ->active()
            ->whereHas('seller', fn (Builder $query): Builder => $query
                ->where('is_active', true)
                ->where('is_verified', true))
            ->with([
                'seller:id,company_name,is_active,is_verified',
                'category:id,category_name,parent_category_id',
                'category.parent:id,category_name',
                'country:id,country_name',
            ])
            ->withCount([
                'reviews as approved_reviews_count' => fn (Builder $query): Builder => $query
                    ->where('is_approved', true),
            ])
            ->withAvg([
                'reviews as approved_reviews_avg_rating' => fn (Builder $query): Builder => $query
                    ->where('is_approved', true),
            ], 'rating');
    }
}

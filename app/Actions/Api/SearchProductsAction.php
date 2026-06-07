<?php

namespace App\Actions\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class SearchProductsAction
{
    private const RESULT_LIMIT = 5;

    /**
     * @return array{
     *     categories: array<int, array{id: int, category_name: string}>,
     *     products: array<int, array{id: int, name: string, price: string, product_image: ?string, image_url: string}>
     * }
     */
    public function handle(string $query, string $locale): array
    {
        $search = trim($query);

        if ($search === '') {
            return [
                'categories' => [],
                'products' => [],
            ];
        }

        $pattern = "%{$search}%";

        return [
            'categories' => $this->matchingSubcategories($pattern, $locale),
            'products' => $this->matchingProducts($pattern, $locale),
        ];
    }

    /**
     * @return array<int, array{id: int, category_name: string}>
     */
    private function matchingSubcategories(string $pattern, string $locale): array
    {
        return Category::query()
            ->select(['id', 'parent_category_id', 'category_name', 'order'])
            ->whereNotNull('parent_category_id')
            ->where(fn ($query) => $this->whereTranslatedLike($query, 'category_name', $pattern, $locale))
            ->with(['parent' => fn ($query) => $query->select(['id', 'category_name'])])
            ->orderBy('order')
            ->orderBy('id')
            ->limit(self::RESULT_LIMIT)
            ->get()
            ->map(fn (Category $subcategory): array => [
                'id' => (int) $subcategory->id,
                'category_name' => trim(sprintf(
                    '%s > %s',
                    (string) $subcategory->parent->getTranslation('category_name', $locale),
                    (string) $subcategory->getTranslation('category_name', $locale),
                )),
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, price: string, product_image: ?string, image_url: string}>
     */
    private function matchingProducts(string $pattern, string $locale): array
    {
        return Product::query()
            ->select(['id', 'name', 'price', 'product_image'])
            ->with('primaryImage:id,product_id,disk,path,variants,is_primary,sort_order')
            ->active()
            ->where(function ($query) use ($pattern, $locale): void {
                $query->whereLike('name', $pattern);

                foreach ($this->searchLocales($locale) as $searchLocale) {
                    $query->orWhereLike("description->{$searchLocale}", $pattern);
                }
            })
            ->orderBy('name')
            ->limit(self::RESULT_LIMIT)
            ->get()
            ->map(fn (Product $product): array => [
                'id' => (int) $product->id,
                'name' => (string) $product->name,
                'price' => number_format((float) $product->price, 2),
                'product_image' => $product->product_image,
                'image_url' => $product->imageUrl('thumb'),
            ])
            ->all();
    }

    private function whereTranslatedLike(Builder $query, string $column, string $pattern, string $locale): void
    {
        foreach ($this->searchLocales($locale) as $index => $searchLocale) {
            $method = $index === 0 ? 'whereLike' : 'orWhereLike';

            $query->{$method}("{$column}->{$searchLocale}", $pattern);
        }
    }

    /**
     * @return list<string>
     */
    private function searchLocales(string $locale): array
    {
        return collect([$locale, config('app.fallback_locale')])
            ->filter(fn (mixed $locale): bool => is_string($locale) && $locale !== '')
            ->unique()
            ->values()
            ->all();
    }
}

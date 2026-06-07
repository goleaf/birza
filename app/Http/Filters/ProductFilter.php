<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;

class ProductFilter extends Filterable
{
    /**
     * @var list<string>
     */
    protected $filters = [
        'search',
        'status',
        'category_id',
        'category_ids',
        'seller_id',
        'min_price',
        'max_price',
        'min_stock',
        'max_stock',
        'is_organic',
        'country_of_origin',
        'attribute_values',
    ];

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function fromArray(array $filters): static
    {
        $filters = array_filter(
            $filters,
            static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== [],
        );

        return new static(Request::create('/', 'GET', $filters));
    }

    public function search(Payload $payload): void
    {
        if (! $payload->isString() || blank($payload->value)) {
            return;
        }

        $search = '%'.trim($payload->value).'%';
        $locale = app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');

        $this->builder->where(function (Builder $query) use ($search, $locale, $fallbackLocale): void {
            $query->where('name', 'like', $search)
                ->orWhere('description', 'like', $search)
                ->orWhereHas('category', function (Builder $categoryQuery) use ($search, $locale, $fallbackLocale): void {
                    $categoryQuery->where("category_name->{$locale}", 'like', $search);

                    if ($fallbackLocale !== $locale) {
                        $categoryQuery->orWhere("category_name->{$fallbackLocale}", 'like', $search);
                    }
                })
                ->orWhereHas('seller', function (Builder $sellerQuery) use ($search): void {
                    $sellerQuery->where('name', 'like', $search)
                        ->orWhere('company_name', 'like', $search);
                });
        });
    }

    public function status(Payload $payload): void
    {
        if ($payload->value === 'trashed') {
            $this->builder->onlyTrashed();

            return;
        }

        if ($payload->value === 'active') {
            $this->builder->whereNull($this->builder->getModel()->getQualifiedDeletedAtColumn());
        }
    }

    public function categoryId(Payload $payload): void
    {
        $this->whereInteger('category_id', $payload);
    }

    public function categoryIds(Payload $payload): void
    {
        if (! $payload->isArray()) {
            return;
        }

        $categoryIds = collect($payload->value)
            ->filter(fn (mixed $categoryId): bool => is_numeric($categoryId))
            ->map(fn (mixed $categoryId): int => (int) $categoryId)
            ->unique()
            ->values()
            ->all();

        if ($categoryIds !== []) {
            $this->builder->whereIn('category_id', $categoryIds);
        }
    }

    public function sellerId(Payload $payload): void
    {
        $this->whereInteger('seller_id', $payload);
    }

    public function minPrice(Payload $payload): void
    {
        $this->whereNumeric('price', '>=', $payload);
    }

    public function maxPrice(Payload $payload): void
    {
        $this->whereNumeric('price', '<=', $payload);
    }

    public function minStock(Payload $payload): void
    {
        $this->whereNumeric('stock', '>=', $payload);
    }

    public function maxStock(Payload $payload): void
    {
        $this->whereNumeric('stock', '<=', $payload);
    }

    public function isOrganic(Payload $payload): void
    {
        if ($payload->isBoolean()) {
            $this->builder->where('is_organic', $payload->isTrue());
        }
    }

    public function countryOfOrigin(Payload $payload): void
    {
        $this->whereInteger('country_of_origin', $payload);
    }

    public function attributeValues(Payload $payload): void
    {
        if (! $payload->isArray()) {
            return;
        }

        foreach ($payload->value as $attributeId => $valueId) {
            if (! is_numeric($attributeId) || ! is_numeric($valueId)) {
                continue;
            }

            $this->builder->whereHas(
                'attributeValues',
                function (Builder $query) use ($attributeId, $valueId): void {
                    $query->whereKey((int) $valueId)
                        ->where('attribute_values.attribute_id', (int) $attributeId)
                        ->where('attribute_values.is_active', true);
                },
            );
        }
    }

    private function whereInteger(string $column, Payload $payload): void
    {
        if ($payload->isNumeric()) {
            $this->builder->where($column, (int) $payload->value);
        }
    }

    private function whereNumeric(string $column, string $operator, Payload $payload): void
    {
        if ($payload->isNumeric()) {
            $this->builder->where($column, $operator, $payload->value);
        }
    }
}

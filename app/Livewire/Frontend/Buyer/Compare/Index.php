<?php

namespace App\Livewire\Frontend\Buyer\Compare;

use App\Actions\Products\Comparison\ClearProductCompareAction;
use App\Actions\Products\Comparison\GetComparedProductsAction;
use App\Actions\Products\Comparison\RemoveProductFromCompareAction;
use App\Models\Product;
use App\Support\Products\ProductComparison;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app', ['fullWidth' => true])]
class Index extends Component
{
    public function removeProduct(int $productId, RemoveProductFromCompareAction $action): void
    {
        $action->handle($productId);

        session()->flash('success', __('compare.messages.removed'));
    }

    public function clearCompare(ClearProductCompareAction $action): void
    {
        $action->handle();

        session()->flash('success', __('compare.messages.cleared'));
    }

    public function render(GetComparedProductsAction $action): View
    {
        $products = $action->handle();
        $cards = $products
            ->map(fn (Product $product): array => $this->productCard($product))
            ->values()
            ->all();

        return view('frontend.buyer.compare.index', [
            'products' => $products,
            'cards' => $cards,
            'comparisonLimit' => ProductComparison::MAX_PRODUCTS,
            'comparisonRows' => $this->comparisonRows($cards),
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     title: string,
     *     url: string,
     *     image_url: string|null,
     *     price: string,
     *     seller: string,
     *     category: string,
     *     status: string,
     *     availability: string,
     *     stock: string,
     *     location: string,
     *     description: string,
     *     rating: string,
     *     review_count: string,
     *     created_at: string
     * }
     */
    private function productCard(Product $product): array
    {
        return [
            'id' => (int) $product->getKey(),
            'title' => (string) $product->name,
            'url' => route('buyer.products.show', $product),
            'image_url' => collect($product->imageGalleryUrls())->first(),
            'price' => Number::format((float) $product->price, precision: 2).' € / '.__('units_unit_'.strtolower($product->unit)),
            'seller' => (string) ($product->seller?->company_name ?: __('common_not_specified')),
            'category' => $this->categoryLabel($product),
            'status' => $product->is_active ? __('common_active') : __('common_inactive'),
            'availability' => $product->stock > 0
                ? __('compare.availability.in_stock')
                : __('compare.availability.out_of_stock'),
            'stock' => $product->stock.' '.__('units_unit_'.strtolower($product->unit)),
            'location' => (string) ($product->country?->getTranslation('country_name', app()->getLocale()) ?: __('common_not_specified')),
            'description' => $this->shortDescription($product),
            'rating' => $this->ratingLabel($product),
            'review_count' => trans_choice('compare.fields.review_count_value', (int) $product->approved_reviews_count, [
                'count' => (int) $product->approved_reviews_count,
            ]),
            'created_at' => $product->created_at?->format('Y-m-d') ?? __('common_not_specified'),
        ];
    }

    /**
     * @param  array<int, array<string, string|int>>  $cards
     * @return array<int, array{label: string, key: string, values: array<int, string>}>
     */
    private function comparisonRows(array $cards): array
    {
        return collect([
            'price',
            'seller',
            'category',
            'status',
            'availability',
            'stock',
            'location',
            'description',
            'rating',
            'review_count',
            'created_at',
        ])->map(fn (string $key): array => [
            'label' => __("compare.fields.{$key}"),
            'key' => $key,
            'values' => collect($cards)
                ->mapWithKeys(fn (array $card): array => [
                    (int) $card['id'] => (string) $card[$key],
                ])
                ->all(),
        ])->values()->all();
    }

    private function categoryLabel(Product $product): string
    {
        $category = $product->category;

        if (! $category) {
            return __('common_not_specified');
        }

        $labels = collect([
            $category->parent?->exists ? $category->parent->getTranslation('category_name', app()->getLocale()) : null,
            $category->getTranslation('category_name', app()->getLocale()),
        ])->filter()->values();

        return $labels->isNotEmpty()
            ? $labels->join(' / ')
            : __('common_not_specified');
    }

    private function shortDescription(Product $product): string
    {
        $description = (string) $product->getTranslation('description', app()->getLocale());

        if ($description === '') {
            return __('common_not_specified');
        }

        return Str::of(Str::markdown($description, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]))
            ->stripTags()
            ->squish()
            ->limit(160)
            ->toString();
    }

    private function ratingLabel(Product $product): string
    {
        $reviewCount = (int) $product->approved_reviews_count;

        if ($reviewCount === 0) {
            return __('compare.fields.no_rating');
        }

        return __('compare.fields.rating_value', [
            'rating' => Number::format((float) $product->approved_reviews_avg_rating, precision: 1),
        ]);
    }
}

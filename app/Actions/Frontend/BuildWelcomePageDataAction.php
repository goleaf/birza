<?php

namespace App\Actions\Frontend;

use App\Models\Category;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Support\Number;

class BuildWelcomePageDataAction
{
    /**
     * @return array{
     *     locales: array<int, array{code: string, label: string, isCurrent: bool}>,
     *     featuredCategories: array<int, array{imageUrl: string, title: string, description: string}>,
     *     communityStats: array<string, array{title: string, value: string, icon: string}>
     * }
     */
    public function handle(): array
    {
        return [
            'locales' => $this->locales(),
            'featuredCategories' => $this->featuredCategories(),
            'communityStats' => $this->communityStats(),
        ];
    }

    /**
     * @return array<int, array{code: string, label: string, isCurrent: bool}>
     */
    private function locales(): array
    {
        return array_map(
            fn (string $locale): array => [
                'code' => $locale,
                'label' => strtoupper($locale),
                'isCurrent' => app()->currentLocale() === $locale,
            ],
            array_values((array) config('app.locales', [])),
        );
    }

    /**
     * @return array<int, array{imageUrl: string, title: string, description: string}>
     */
    private function featuredCategories(): array
    {
        return [
            [
                'imageUrl' => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?ixlib=rb-4.0.3',
                'title' => __('welcome_product_category_1_title'),
                'description' => __('welcome_product_category_1_description'),
            ],
            [
                'imageUrl' => 'https://images.unsplash.com/photo-1544025162-d76694265947?ixlib=rb-4.0.3',
                'title' => __('welcome_product_category_2_title'),
                'description' => __('welcome_product_category_2_description'),
            ],
            [
                'imageUrl' => 'https://images.unsplash.com/photo-1598182198871-d3f4ab4fd181?ixlib=rb-4.0.3',
                'title' => __('welcome_product_category_3_title'),
                'description' => __('welcome_product_category_3_description'),
            ],
            [
                'imageUrl' => 'https://images.unsplash.com/photo-1615141982883-c7ad0e69fd62?ixlib=rb-4.0.3',
                'title' => __('welcome_product_category_4_title'),
                'description' => __('welcome_product_category_4_description'),
            ],
        ];
    }

    /**
     * @return array<string, array{title: string, value: string, icon: string}>
     */
    private function communityStats(): array
    {
        return [
            'sellers' => [
                'title' => __('welcome_seller_count'),
                'value' => Number::format(Seller::query()->where('is_active', true)->count()),
                'icon' => 'users',
            ],
            'categories' => [
                'title' => __('welcome_product_categories_count_title'),
                'value' => Number::format(Category::query()->count()),
                'icon' => 'categories',
            ],
            'buyers' => [
                'title' => __('welcome_buyer_count'),
                'value' => Number::format(Buyer::query()->where('is_active', true)->count()),
                'icon' => 'users',
            ],
        ];
    }
}

<?php

namespace App\Support;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Country;
use App\Models\GlobalSettings;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class Spotlight
{
    public function search(Request $request): array
    {
        if (! Auth::guard('admin')->check()) {
            return [];
        }

        $search = trim((string) $request->input('search', ''));

        if ($search === '') {
            return [];
        }

        return $this->quickActions($search)
            ->concat($this->products($search))
            ->concat($this->sellers($search))
            ->concat($this->buyers($search))
            ->concat($this->categories($search))
            ->concat($this->countries($search))
            ->concat($this->attributes($search))
            ->concat($this->orders($search))
            ->take(14)
            ->values()
            ->all();
    }

    private function quickActions(string $search): Collection
    {
        $spotlightTags = GlobalSettings::cachedAdminSpotlightTags();

        $actions = collect([
            [
                'name' => __('navigation_products'),
                'description' => __('backend_spotlight_description_create_product'),
                'link' => route('backend.products.create', absolute: false),
                'icon' => $this->icon('o-cube'),
                'keywords' => $spotlightTags,
            ],
            [
                'name' => __('navigation_sellers'),
                'description' => __('backend_spotlight_description_create_seller'),
                'link' => route('backend.sellers.create', absolute: false),
                'icon' => $this->icon('o-building-storefront'),
                'keywords' => $spotlightTags,
            ],
            [
                'name' => __('navigation_buyers'),
                'description' => __('backend_spotlight_description_create_buyer'),
                'link' => route('backend.buyers.create', absolute: false),
                'icon' => $this->icon('o-users'),
                'keywords' => $spotlightTags,
            ],
            [
                'name' => __('navigation_categories'),
                'description' => __('backend_spotlight_description_create_category'),
                'link' => route('backend.categories.create', absolute: false),
                'icon' => $this->icon('o-squares-2x2'),
                'keywords' => $spotlightTags,
            ],
            [
                'name' => __('navigation_countries'),
                'description' => __('backend_spotlight_description_create_country'),
                'link' => route('backend.countries.create', absolute: false),
                'icon' => $this->icon('o-globe-alt'),
                'keywords' => $spotlightTags,
            ],
            [
                'name' => __('navigation_attributes'),
                'description' => __('backend_spotlight_description_create_attribute'),
                'link' => route('backend.attributes.create', absolute: false),
                'icon' => $this->icon('o-adjustments-horizontal'),
                'keywords' => $spotlightTags,
            ],
            [
                'name' => __('global_dashboard'),
                'description' => __('backend_spotlight_description_dashboard'),
                'link' => route('backend.dashboard', absolute: false),
                'icon' => $this->icon('o-home'),
                'keywords' => $spotlightTags,
            ],
            [
                'name' => __('profile'),
                'description' => __('backend_spotlight_description_profile'),
                'link' => route('backend.admin.profile', absolute: false),
                'icon' => $this->icon('o-user-circle'),
                'keywords' => $spotlightTags,
            ],
            [
                'name' => __('navigation_global_settings'),
                'description' => __('backend_spotlight_description_settings'),
                'link' => route('backend.settings.index', absolute: false),
                'icon' => $this->icon('o-cog-6-tooth'),
                'keywords' => $spotlightTags,
            ],
            [
                'name' => __('navigation_orders'),
                'description' => __('backend_spotlight_description_orders'),
                'link' => route('backend.orders.index', absolute: false),
                'icon' => $this->icon('o-shopping-bag'),
                'keywords' => $spotlightTags,
            ],
        ]);

        return $actions
            ->filter(fn (array $action): bool => $this->matches(
                $search,
                $action['name'],
                $action['description'],
                ...($action['keywords'] ?? [])
            ))
            ->take(6)
            ->map(fn (array $action): array => Arr::except($action, ['keywords']))
            ->values();
    }

    private function products(string $search): Collection
    {
        return Product::query()
            ->select(['id', 'name', 'seller_id', 'category_id', 'price'])
            ->with([
                'seller:id,name',
                'category:id,category_name',
            ])
            ->where('name', 'like', "%{$search}%")
            ->latest('id')
            ->limit(3)
            ->get()
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'description' => trim(
                    collect([
                        __('navigation_products'),
                        $product->seller?->name,
                        number_format((float) $product->price, 2).' €',
                    ])->filter()->implode(' · ')
                ),
                'link' => route('backend.products.edit', $product, false),
                'icon' => $this->icon('o-cube'),
            ]);
    }

    private function sellers(string $search): Collection
    {
        return Seller::query()
            ->select(['id', 'name', 'email', 'company_name'])
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            })
            ->latest('id')
            ->limit(3)
            ->get()
            ->map(fn (Seller $seller): array => [
                'name' => $seller->name,
                'description' => trim(
                    collect([
                        __('navigation_sellers'),
                        $seller->company_name,
                        $seller->email,
                    ])->filter()->implode(' · ')
                ),
                'link' => route('backend.sellers.edit', $seller, false),
                'icon' => $this->icon('o-building-storefront'),
            ]);
    }

    private function buyers(string $search): Collection
    {
        return Buyer::query()
            ->select(['id', 'name', 'email', 'company_name'])
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            })
            ->latest('id')
            ->limit(3)
            ->get()
            ->map(fn (Buyer $buyer): array => [
                'name' => $buyer->name,
                'description' => trim(
                    collect([
                        __('navigation_buyers'),
                        $buyer->company_name,
                        $buyer->email,
                    ])->filter()->implode(' · ')
                ),
                'link' => route('backend.buyers.edit', $buyer, false),
                'icon' => $this->icon('o-users'),
            ]);
    }

    private function categories(string $search): Collection
    {
        return Category::query()
            ->select(['id', 'parent_category_id', 'category_name'])
            ->where(fn ($query) => $this->applyTranslatedSearch($query, 'category_name', $search))
            ->latest('id')
            ->limit(3)
            ->get()
            ->map(fn (Category $category): array => [
                'name' => $category->getTranslation('category_name', app()->getLocale()),
                'description' => __('navigation_categories'),
                'link' => route('backend.categories.edit', $category, false),
                'icon' => $this->icon('o-squares-2x2'),
            ]);
    }

    private function countries(string $search): Collection
    {
        return Country::query()
            ->select(['id', 'alpha2', 'country_name', 'region'])
            ->where(function ($query) use ($search) {
                $query->where('alpha2', 'like', "%{$search}%");

                foreach ($this->searchLocales() as $locale) {
                    $query->orWhere("country_name->{$locale}", 'like', "%{$search}%");
                }
            })
            ->latest('id')
            ->limit(3)
            ->get()
            ->map(fn (Country $country): array => [
                'name' => $country->getTranslation('country_name', app()->getLocale()),
                'description' => collect([
                    __('navigation_countries'),
                    strtoupper($country->alpha2),
                    $country->getRegionLabel(),
                ])->implode(' · '),
                'link' => route('backend.countries.edit', $country, false),
                'icon' => $this->icon('o-globe-alt'),
            ]);
    }

    private function attributes(string $search): Collection
    {
        return Attribute::query()
            ->select(['id', 'name', 'type', 'is_active'])
            ->where(fn ($query) => $this->applyTranslatedSearch($query, 'name', $search))
            ->latest('id')
            ->limit(3)
            ->get()
            ->map(fn (Attribute $attribute): array => [
                'name' => $attribute->getTranslation('name', app()->getLocale()),
                'description' => collect([
                    __('navigation_attributes'),
                    Str::headline($attribute->type),
                ])->implode(' · '),
                'link' => route('backend.attributes.edit', $attribute, false),
                'icon' => $this->icon('o-adjustments-horizontal'),
            ]);
    }

    private function orders(string $search): Collection
    {
        $query = Order::query()
            ->select(['id', 'buyer_id', 'order_total', 'status'])
            ->with(['buyer:id,name']);

        if (ctype_digit($search)) {
            $query->where('id', (int) $search);
        } else {
            $query->where(function ($builder) use ($search) {
                $builder->where('status', 'like', "%{$search}%")
                    ->orWhereHas('buyer', function ($buyerQuery) use ($search) {
                        $buyerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query
            ->latest('id')
            ->limit(3)
            ->get()
            ->map(fn (Order $order): array => [
                'name' => '#'.$order->id,
                'description' => trim(
                    collect([
                        __('navigation_orders'),
                        $order->buyer?->name,
                        number_format((float) $order->order_total, 2).' €',
                    ])->filter()->implode(' · ')
                ),
                'link' => route('backend.orders.show', $order, false),
                'icon' => $this->icon('o-shopping-bag'),
            ]);
    }

    private function searchLocales(): array
    {
        return array_values((array) config('app.locales', [config('app.locale')]));
    }

    private function matches(string $search, string ...$chunks): bool
    {
        return Str::contains(
            mb_strtolower(implode(' ', $chunks)),
            mb_strtolower($search)
        );
    }

    private function applyTranslatedSearch($query, string $column, string $search): void
    {
        $locales = $this->searchLocales();

        foreach ($locales as $index => $locale) {
            $method = $index === 0 ? 'where' : 'orWhere';
            $query->{$method}("{$column}->{$locale}", 'like', "%{$search}%");
        }
    }

    private function icon(string $name): string
    {
        return Blade::render("<x-mary-icon name=\"{$name}\" class=\"h-5 w-5 text-primary\" />");
    }
}

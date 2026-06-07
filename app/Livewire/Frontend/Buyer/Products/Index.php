<?php

namespace App\Livewire\Frontend\Buyer\Products;

use App\Http\Filters\ProductFilter;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app', ['fullWidth' => true])]
class Index extends Component
{
    public function render(): View
    {
        $request = request();

        $categories = Category::query()
            ->select(['id', 'category_name', 'parent_category_id'])
            ->with(['subcategories' => function ($query) {
                $query->select(['id', 'category_name', 'parent_category_id'])
                    ->with(['attributes' => function ($attributeQuery) {
                        $attributeQuery
                            ->select([
                                'attributes.id',
                                'attributes.name',
                                'attributes.is_required',
                            ])
                            ->where('is_active', true)
                            ->where('is_filterable', true)
                            ->with(['values' => function ($valueQuery) {
                                $valueQuery
                                    ->select(['id', 'attribute_id', 'value'])
                                    ->where('is_active', true)
                                    ->orderBy('value');
                            }]);
                    }]);
            }])
            ->whereNull('parent_category_id')
            ->get();

        $countries = Country::active()
            ->select(['id', 'country_name'])
            ->where('region', 'Europe')
            ->orderBy('country_name')
            ->get();

        $categoryIds = [];
        $attributeValueFilters = [];

        if ($categoryId = $request->integer('category')) {
            $category = $categories->firstWhere('id', $categoryId);
            $subcategory = null;

            if (! $category) {
                foreach ($categories as $rootCategory) {
                    $subcategory = $rootCategory->subcategories->firstWhere('id', $categoryId);
                    if ($subcategory) {
                        $category = $rootCategory;
                        break;
                    }
                }
            }

            if ($category && ! $subcategory) {
                $categoryIds = $category->subcategories
                    ->pluck('id')
                    ->push($categoryId)
                    ->all();
            } elseif ($subcategory) {
                $categoryIds = [$categoryId];
            }

            if (is_array($request->input('filters'))) {
                $attributeValueFilters = $request->input('filters');
            }
        }

        $filter = ProductFilter::fromArray([
            'category_ids' => $categoryIds,
            'attribute_values' => $attributeValueFilters,
            'min_price' => $request->filled('price_min') ? $request->float('price_min') : null,
            'max_price' => $request->filled('price_max') ? $request->float('price_max') : null,
            'min_stock' => $request->filled('stock_min') ? $request->integer('stock_min') : null,
            'max_stock' => $request->filled('stock_max') ? $request->integer('stock_max') : null,
            'is_organic' => $request->filled('is_organic') ? $request->boolean('is_organic') : null,
            'country_of_origin' => $request->filled('country_of_origin')
                ? $request->integer('country_of_origin')
                : null,
        ]);

        $query = Product::active()
            ->select([
                'id',
                'name',
                'category_id',
                'seller_id',
                'price',
                'unit',
                'stock',
                'product_image',
            ])
            ->with([
                'primaryImage:id,product_id,disk,path,variants,is_primary,sort_order',
                'seller:id,company_name',
                'category:id,category_name,parent_category_id',
                'category.parent:id,category_name',
            ])
            ->filter($filter)
            ->latest();

        $products = $query->paginate(12)->withQueryString();

        return view('frontend.buyer.products.index', [
            'products' => $products,
            'categories' => $categories,
            'countries' => $countries,
        ]);
    }
}

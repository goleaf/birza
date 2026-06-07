<?php

namespace App\Livewire\Frontend\Buyer\Products;

use App\Actions\Products\Comparison\AddProductToCompareAction;
use App\Actions\Wishlists\AddProductToWishlistAction;
use App\Http\Filters\ProductFilter;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\WishlistItem;
use App\Support\Products\ProductComparison;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app', ['fullWidth' => true])]
class Index extends Component
{
    public function addToWishlist(int $productId, AddProductToWishlistAction $action): void
    {
        $buyer = $this->buyer();

        if (! $buyer) {
            session()->flash('message', __('wishlists.messages.login_required'));
            $this->redirectRoute('buyer.login', navigate: true);

            return;
        }

        $product = Product::query()
            ->withTrashed()
            ->select(['id'])
            ->find($productId);

        if (! $product) {
            session()->flash('message', __('wishlists.messages.product_unavailable'));

            return;
        }

        try {
            $action->handle($buyer, $product);
        } catch (ValidationException $exception) {
            session()->flash('message', collect($exception->errors())->flatten()->first());

            return;
        }

        session()->flash('success', __('wishlists.messages.product_added'));
    }

    public function addToCompare(int $productId, AddProductToCompareAction $action): void
    {
        $product = Product::query()
            ->select(['id'])
            ->find($productId);

        if (! $product) {
            session()->flash('message', __('compare.messages.product_unavailable'));

            return;
        }

        try {
            $action->handle($product);
        } catch (ValidationException $exception) {
            session()->flash('message', collect($exception->errors())->flatten()->first());

            return;
        }

        session()->flash('success', __('compare.messages.added'));
    }

    public function render(): View
    {
        $request = request();
        $comparison = app(ProductComparison::class);

        $categories = Category::cachedFilterTree();
        $countries = Country::cachedActiveEuropeanOptions();

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
        $buyer = $this->buyer();

        return view('frontend.buyer.products.index', [
            'products' => $products,
            'categories' => $categories,
            'countries' => $countries,
            'wishlistedProductIds' => $this->wishlistedProductIds($buyer, $products->getCollection()->pluck('id')),
            'comparedProductIds' => $comparison->ids(),
            'comparisonCount' => $comparison->count(),
            'comparisonLimit' => ProductComparison::MAX_PRODUCTS,
        ]);
    }

    private function buyer(): ?Buyer
    {
        $buyer = Auth::guard('buyer')->user();

        return $buyer instanceof Buyer ? $buyer : null;
    }

    /**
     * @param  Collection<int, int>  $productIds
     * @return array<int, int>
     */
    private function wishlistedProductIds(?Buyer $buyer, Collection $productIds): array
    {
        if (! $buyer || $productIds->isEmpty()) {
            return [];
        }

        return WishlistItem::query()
            ->select(['product_id'])
            ->whereIn('product_id', $productIds->values())
            ->whereHas('wishlist', fn ($query) => $query->where('buyer_id', $buyer->id))
            ->pluck('product_id')
            ->map(fn ($productId): int => (int) $productId)
            ->all();
    }
}

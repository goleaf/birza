<?php

namespace App\Http\Controllers\Frontend\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::with(['subcategories:id,category_name,parent_category_id', 'attributes' => function($query) {
            $query->where('is_active', true)
                  ->where('is_filterable', true)
                  ->with(['values' => function($q) {
                      $q->where('is_active', true)
                         ->orderBy('value', 'asc');
                  }]);
        }])
        ->whereNull('parent_category_id')
        ->get();

        $countries = Country::active()->where('region', 'Europe')->orderBy('country_name')->get();

        $query = Product::active()->with(['seller', 'category', 'country'])->latest();

        if ($categoryId = $request->category) {
            $category = $categories->firstWhere('id', $categoryId);
            $subcategory = null;

            if (!$category) {
                foreach ($categories as $rootCategory) {
                    $subcategory = $rootCategory->subcategories->firstWhere('id', $categoryId);
                    if ($subcategory) {
                        $category = $rootCategory;
                        break;
                    }
                }
            }

            if ($category && !$subcategory) {
                // Main category selected
                $query->whereIn('category_id', $category->subcategories->pluck('id')->push($categoryId));
            } elseif ($subcategory) {
                // Subcategory selected
                $query->where('category_id', $categoryId);
            }

            // Apply category attribute filters
            if ($filters = $request->input('filters')) {
                foreach ($filters as $attributeId => $valueId) {
                    if ($subcategory) {
                        // For subcategory, check only its own attributes
                        $query->whereHas('category', function($q) use ($attributeId, $valueId) {
                            $q->whereHas('attributes', function($q) use ($attributeId, $valueId) {
                                $q->where('attributes.id', $attributeId)
                                  ->whereHas('values', function($q) use ($valueId) {
                                      $q->where('id', $valueId);
                                  });
                            });
                        });
                    } else {
                        // For main category, check attributes as before
                        $query->whereHas('category.attributes', function ($q) use ($attributeId, $valueId) {
                            $q->where('attributes.id', $attributeId)
                              ->whereHas('values', function($q) use ($valueId) {
                                  $q->where('id', $valueId);
                              });
                        });
                    }
                }
            }
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        if ($request->filled('is_organic')) {
            $query->where('is_organic', filter_var($request->is_organic, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('country_of_origin')) {
            $query->where('country_of_origin', $request->country_of_origin);
        }

        $products = $query->paginate(12)->withQueryString();

        return view('frontend.buyer.products.index', [
            'products' => $products,
            'categories' => $categories,
            'countries' => $countries,
        ]);
    }

    public function show(Product $product)
    {
        if ($product->trashed() || $product->is_active === false) {
            abort(404);
        }

        if ($product->stock <= 0) {
            session()->flash('message', __('product.messages.out_of_stock'));
        }

        return view('frontend.buyer.products.show', [
            'product' => $product,
            'message' => session('message')
        ]);
    }
}
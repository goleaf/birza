<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('query');
        $locale = $request->get('locale', app()->getLocale());

        $categories = Category::query()
            ->whereNull('parent_category_id')
            ->with(['subcategories' => function($q) use ($query, $locale) {
                $q->whereRaw("LOWER(JSON_EXTRACT(category_name, '$.\"{$locale}\"')) LIKE ?", ['%' . strtolower($query) . '%']);
            }])
            ->get()
            ->map(function ($mainCategory) use ($locale) {
                return [
                    'id' => $mainCategory->id,
                    'category_name' => $mainCategory->getTranslation('category_name', $locale),
                    'subcategories' => $mainCategory->subcategories->map(function ($subcategory) use ($locale, $mainCategory) {
                        return [
                            'id' => $subcategory->id,
                            'category_name' => $mainCategory->getTranslation('category_name', $locale) . ' > ' . 
                                            $subcategory->getTranslation('category_name', $locale)
                        ];
                    })
                ];
            })
            ->filter(function($category) {
                return $category['subcategories']->isNotEmpty();
            })
            ->values();

        $products = Product::query()
            ->active()
            ->where(function($q) use ($query, $locale) {
                $q->whereRaw("LOWER(name) LIKE ?", ['%' . strtolower($query) . '%'])
                  ->orWhereRaw("LOWER(JSON_EXTRACT(description, '$.\"{$locale}\"')) LIKE ?", ['%' . strtolower($query) . '%']);
            })
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => number_format($product->price, 2),
                    'product_image' => $product->product_image
                ];
            });

        return response()->json([
            'categories' => $categories->flatMap(function($category) {
                return $category['subcategories'];
            })->take(5),
            'products' => $products
        ]);
    }
}

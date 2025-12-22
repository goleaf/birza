<?php

namespace App\Livewire\Backend\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    public function deleteProduct(int $productId): void
    {
        Product::query()->findOrFail($productId)->delete();

        session()->flash('success', __('backend.common.delete_success'));
    }

    public function restoreProduct(int $productId): void
    {
        $product = Product::withTrashed()->findOrFail($productId);
        $product->restore();

        session()->flash('success', __('backend.common.restore_success'));
    }

    public function forceDeleteProduct(int $productId): void
    {
        $product = Product::withTrashed()->findOrFail($productId);

        if ($product->product_image) {
            Storage::disk('public')->delete('products/' . $product->product_image);
        }

        if ($product->product_additional_image) {
            Storage::disk('public')->delete('products/' . $product->product_additional_image);
        }

        $product->forceDelete();

        session()->flash('success', __('backend.common.force_delete_success'));
    }

    public function render()
    {
        $request = request();

        $query = Product::query()
            ->with(['category', 'seller'])
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->status === 'trashed') {
                    $q->onlyTrashed();
                } elseif ($request->status === 'active') {
                    $q->whereNull('deleted_at');
                }
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $search = '%'.$request->search.'%';
                    $query->where('name', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhereHas('category', function ($q) use ($search) {
                            $q->where('category_name->en', 'like', $search);
                        })
                        ->orWhereHas('seller', function ($q) use ($search) {
                            $q->where('name', 'like', $search)
                                ->orWhere('company_name', 'like', $search);
                        });
                });
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->where('category_id', $request->category);
            })
            ->when($request->filled('seller'), function ($q) use ($request) {
                $q->where('seller_id', $request->seller);
            })
            ->when($request->filled('min_price'), function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            })
            ->when($request->filled('max_price'), function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            })
            ->when($request->filled('sort'), function ($q) use ($request) {
                [$column, $direction] = explode(',', $request->sort);
                $q->orderBy($column, $direction);
            }, function ($q) {
                $q->latest();
            });

        $categories = Category::select('id', 'category_name', 'parent_category_id')
            ->whereNull('parent_category_id')
            ->with(['subcategories' => function ($q) {
                $q->select('id', 'category_name', 'parent_category_id')
                    ->orderBy('category_name->en');
            }])
            ->orderBy('category_name->en')
            ->get();

        $sellers = Seller::select('id', 'name', 'company_name')
            ->orderBy('name')
            ->get();

        return view('backend.products.index', [
            'products' => $query->paginate(15)->withQueryString(),
            'categories' => $categories,
            'sellers' => $sellers,
        ]);
    }
}



<?php

namespace App\Livewire\Frontend\Seller\Products;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    public function softDeleteProduct(int $productId): void
    {
        $sellerId = Auth::guard('seller')->id();

        $product = Product::query()
            ->where('seller_id', $sellerId)
            ->findOrFail($productId);

        $product->update(['is_active' => false]);
        $product->delete();

        session()->flash('success', __('backend.common.delete_success'));
    }

    public function restoreProduct(int $productId): void
    {
        $sellerId = Auth::guard('seller')->id();

        $product = Product::onlyTrashed()
            ->where('seller_id', $sellerId)
            ->findOrFail($productId);

        $product->restore();

        session()->flash('success', __('backend.common.restore_success'));
    }

    public function render()
    {
        $seller = Auth::guard('seller')->user();

        $categories = $this->getCategoriesWithProducts($seller);

        return view('frontend.seller.products.index', [
            'categories' => $categories,
        ]);
    }

    private function getCategoriesWithProducts($seller)
    {
        return Category::whereHas('sellers', function ($query) use ($seller) {
            $query->where('seller_id', $seller->id);
        })
            ->with(['products' => function ($query) use ($seller) {
                $query->withTrashed()->where('seller_id', $seller->id);
            }])
            ->whereNull('parent_category_id')
            ->orWhereHas('subcategories', function ($query) use ($seller) {
                $query->whereHas('sellers', function ($q) use ($seller) {
                    $q->where('seller_id', $seller->id);
                });
            })
            ->with(['subcategories.products' => function ($query) use ($seller) {
                $query->withTrashed()->where('seller_id', $seller->id);
            }])
            ->get();
    }
}



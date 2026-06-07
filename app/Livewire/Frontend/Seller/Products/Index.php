<?php

namespace App\Livewire\Frontend\Seller\Products;

use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    use InteractsWithWireUi;

    public function softDeleteProduct(int $productId): void
    {
        $sellerId = Auth::guard('seller')->id();

        $product = Product::query()
            ->where('seller_id', $sellerId)
            ->findOrFail($productId);

        $product->update(['is_active' => false]);
        $product->delete();

        $this->notifySuccess(__('backend_common_delete_success'));
    }

    public function restoreProduct(int $productId): void
    {
        $sellerId = Auth::guard('seller')->id();

        $product = Product::onlyTrashed()
            ->where('seller_id', $sellerId)
            ->findOrFail($productId);

        $product->restore();

        $this->notifySuccess(__('backend_common_restore_success'));
    }

    public function confirmSoftDeleteProduct(int $productId): void
    {
        $this->confirmAction(
            title: __('product_soft_delete_confirmation'),
            description: __('product_soft_delete_warning'),
            acceptLabel: __('product_soft_delete'),
            method: 'softDeleteProduct',
            params: $productId,
            icon: 'warning',
        );
    }

    public function confirmRestoreProduct(int $productId): void
    {
        $this->confirmAction(
            title: __('product_restore_confirmation'),
            description: __('product_restore_warning'),
            acceptLabel: __('product_restore'),
            method: 'restoreProduct',
            params: $productId,
            icon: 'question',
        );
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
                $query->withTrashed()
                    ->where('seller_id', $seller->id)
                    ->with('primaryImage:id,product_id,disk,path,variants,is_primary,sort_order');
            }])
            ->whereNull('parent_category_id')
            ->orWhereHas('subcategories', function ($query) use ($seller) {
                $query->whereHas('sellers', function ($q) use ($seller) {
                    $q->where('seller_id', $seller->id);
                });
            })
            ->with(['subcategories.products' => function ($query) use ($seller) {
                $query->withTrashed()
                    ->where('seller_id', $seller->id)
                    ->with('primaryImage:id,product_id,disk,path,variants,is_primary,sort_order');
            }])
            ->get();
    }
}

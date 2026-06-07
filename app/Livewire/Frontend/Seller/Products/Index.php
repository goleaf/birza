<?php

namespace App\Livewire\Frontend\Seller\Products;

use App\Actions\Products\RecordProductAuditLogsAction;
use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Category;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use InteractsWithWireUi;
    use WithPagination;

    public int $perPage = 15;

    public function softDeleteProduct(int $productId): void
    {
        $product = Product::query()
            ->findOrFail($productId);
        $this->authorize('delete', $product);

        $auditRecorder = app(RecordProductAuditLogsAction::class);
        $oldValues = $auditRecorder->snapshot($product);
        $oldImages = $auditRecorder->imagePaths($product);

        $product->forceFill(['is_active' => false])->save();
        $product->refresh();
        $auditRecorder->updated(
            actor: Auth::guard('seller')->user(),
            product: $product,
            oldValues: $oldValues,
            oldImages: $oldImages,
            source: 'seller_product_index',
        );

        $deleteOldValues = $auditRecorder->snapshot($product);
        $product->delete();
        $auditRecorder->deleted(
            actor: Auth::guard('seller')->user(),
            product: $product,
            oldValues: $deleteOldValues,
            source: 'seller_product_index',
        );

        $this->notifySuccess(__('backend_common_delete_success'));
    }

    public function restoreProduct(int $productId): void
    {
        $product = Product::onlyTrashed()
            ->findOrFail($productId);
        $this->authorize('restore', $product);

        $auditRecorder = app(RecordProductAuditLogsAction::class);
        $oldValues = $auditRecorder->snapshot($product);

        $product->restore();
        $product->refresh();
        $auditRecorder->restored(
            actor: Auth::guard('seller')->user(),
            product: $product,
            oldValues: $oldValues,
            source: 'seller_product_index',
        );

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

        $categories = $this->sellerCategoryTree($seller);
        $products = Product::query()
            ->withTrashed()
            ->select([
                'id',
                'name',
                'category_id',
                'seller_id',
                'price',
                'unit',
                'stock',
                'is_organic',
                'is_active',
                'product_image',
                'deleted_at',
                'created_at',
            ])
            ->where('seller_id', $seller->id)
            ->with([
                'primaryImage:id,product_id,disk,path,variants,is_primary,sort_order',
                'category:id,category_name,parent_category_id',
            ])
            ->latest()
            ->paginate($this->perPage)
            ->withQueryString();

        return view('frontend.seller.products.index', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * @return Collection<int, Category>
     */
    private function sellerCategoryTree(Seller $seller): Collection
    {
        $sellerCategoryIds = $seller->categories()
            ->pluck('categories.id')
            ->map(fn (mixed $categoryId): int => (int) $categoryId);

        return Category::cachedVisibleTree()
            ->filter(function (Category $category) use ($sellerCategoryIds): bool {
                return $sellerCategoryIds->contains((int) $category->id)
                    || $category->subcategories->contains(
                        fn (Category $subcategory): bool => $sellerCategoryIds->contains((int) $subcategory->id),
                    );
            })
            ->map(function (Category $category) use ($sellerCategoryIds): Category {
                if (! $sellerCategoryIds->contains((int) $category->id)) {
                    $category->setRelation(
                        'subcategories',
                        $category->subcategories
                            ->filter(fn (Category $subcategory): bool => $sellerCategoryIds->contains((int) $subcategory->id))
                            ->values(),
                    );
                }

                return $category;
            })
            ->values();
    }
}

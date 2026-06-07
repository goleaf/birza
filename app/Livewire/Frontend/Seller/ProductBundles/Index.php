<?php

namespace App\Livewire\Frontend\Seller\ProductBundles;

use App\Actions\ProductBundles\RecordProductBundleAuditLogsAction;
use App\Actions\ProductBundles\ValidateBundleAvailabilityAction;
use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\ProductBundle;
use App\Models\Users\Seller;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use InteractsWithWireUi;
    use WithPagination;

    public int $perPage = 12;

    public function mount(): void
    {
        $this->authorize('viewAny', ProductBundle::class);
    }

    public function publishBundle(int $bundleId, ValidateBundleAvailabilityAction $validator, RecordProductBundleAuditLogsAction $auditRecorder): void
    {
        $bundle = $this->bundleForSeller($bundleId);
        $this->authorize('publish', $bundle);

        try {
            $validator->validateForPublication($bundle->load('seller', 'items.product.seller'));
        } catch (ValidationException $exception) {
            $this->notifyError((string) collect($exception->errors())->flatten()->first());

            return;
        }

        $oldStatus = (string) $bundle->status;
        $bundle->forceFill([
            'status' => ProductBundle::STATUS_ACTIVE,
            'published_at' => $bundle->published_at ?: now(),
        ])->save();
        $auditRecorder->statusChanged(Auth::guard('seller')->user(), $bundle->refresh(), $oldStatus, 'seller_product_bundle_index');

        $this->notifySuccess(__('bundles.messages.published'));
    }

    public function unpublishBundle(int $bundleId, RecordProductBundleAuditLogsAction $auditRecorder): void
    {
        $bundle = $this->bundleForSeller($bundleId);
        $this->authorize('unpublish', $bundle);

        $oldStatus = (string) $bundle->status;
        $bundle->forceFill([
            'status' => ProductBundle::STATUS_INACTIVE,
            'published_at' => null,
        ])->save();
        $auditRecorder->statusChanged(Auth::guard('seller')->user(), $bundle->refresh(), $oldStatus, 'seller_product_bundle_index');

        $this->notifySuccess(__('bundles.messages.unpublished'));
    }

    public function archiveBundle(int $bundleId, RecordProductBundleAuditLogsAction $auditRecorder): void
    {
        $bundle = $this->bundleForSeller($bundleId);
        $this->authorize('archive', $bundle);

        $oldStatus = (string) $bundle->status;
        $bundle->forceFill([
            'status' => ProductBundle::STATUS_ARCHIVED,
            'published_at' => null,
        ])->save();
        $auditRecorder->statusChanged(Auth::guard('seller')->user(), $bundle->refresh(), $oldStatus, 'seller_product_bundle_index');

        $this->notifySuccess(__('bundles.messages.archived'));
    }

    public function deleteBundle(int $bundleId): void
    {
        $bundle = $this->bundleForSeller($bundleId);
        $this->authorize('delete', $bundle);

        $bundle->delete();

        $this->notifySuccess(__('bundles.messages.deleted'));
    }

    public function confirmDeleteBundle(int $bundleId): void
    {
        $this->confirmDelete('deleteBundle', $bundleId);
    }

    public function render(): View
    {
        $seller = $this->seller();
        $bundles = ProductBundle::query()
            ->forSeller($seller)
            ->with(['items.product.primaryImage'])
            ->withCount('items')
            ->latest()
            ->paginate($this->perPage)
            ->withQueryString();

        return view('livewire.frontend.seller.product-bundles.index', [
            'bundles' => $bundles,
        ]);
    }

    private function bundleForSeller(int $bundleId): ProductBundle
    {
        return ProductBundle::query()
            ->forSeller($this->seller())
            ->with(['seller', 'items.product.seller'])
            ->findOrFail($bundleId);
    }

    private function seller(): Seller
    {
        $seller = Auth::guard('seller')->user();

        abort_unless($seller instanceof Seller, 403);

        return $seller;
    }
}

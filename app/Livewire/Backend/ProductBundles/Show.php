<?php

namespace App\Livewire\Backend\ProductBundles;

use App\Actions\ProductBundles\RecordProductBundleAuditLogsAction;
use App\Actions\ProductBundles\ValidateBundleAvailabilityAction;
use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\ProductBundle;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Show extends Component
{
    use AuthorizesRequests;
    use InteractsWithWireUi;

    public ProductBundle $productBundle;

    public function mount(ProductBundle $productBundle): void
    {
        $this->authorize('view', $productBundle);
        $this->productBundle = $productBundle;
    }

    public function publishBundle(ValidateBundleAvailabilityAction $validator, RecordProductBundleAuditLogsAction $auditRecorder): void
    {
        $this->authorize('publish', $this->productBundle);
        $bundle = $this->productBundle->load('seller', 'items.product.seller');

        try {
            $validator->validateForPublication($bundle);
        } catch (ValidationException $exception) {
            $this->notifyError((string) collect($exception->errors())->flatten()->first());

            return;
        }

        $oldStatus = (string) $bundle->status;
        $bundle->forceFill([
            'status' => ProductBundle::STATUS_ACTIVE,
            'published_at' => $bundle->published_at ?: now(),
        ])->save();
        $auditRecorder->statusChanged(Auth::guard('admin')->user(), $bundle->refresh(), $oldStatus, 'admin_product_bundle_show');
        $this->productBundle = $bundle;

        $this->notifySuccess(__('bundles.messages.published'));
    }

    public function unpublishBundle(RecordProductBundleAuditLogsAction $auditRecorder): void
    {
        $this->authorize('unpublish', $this->productBundle);
        $oldStatus = (string) $this->productBundle->status;
        $this->productBundle->forceFill([
            'status' => ProductBundle::STATUS_INACTIVE,
            'published_at' => null,
        ])->save();
        $auditRecorder->statusChanged(Auth::guard('admin')->user(), $this->productBundle->refresh(), $oldStatus, 'admin_product_bundle_show');

        $this->notifySuccess(__('bundles.messages.unpublished'));
    }

    public function archiveBundle(RecordProductBundleAuditLogsAction $auditRecorder): void
    {
        $this->authorize('archive', $this->productBundle);
        $oldStatus = (string) $this->productBundle->status;
        $this->productBundle->forceFill([
            'status' => ProductBundle::STATUS_ARCHIVED,
            'published_at' => null,
        ])->save();
        $auditRecorder->statusChanged(Auth::guard('admin')->user(), $this->productBundle->refresh(), $oldStatus, 'admin_product_bundle_show');

        $this->notifySuccess(__('bundles.messages.archived'));
    }

    public function render(): View
    {
        return view('livewire.backend.product-bundles.show', [
            'bundle' => $this->productBundle->load([
                'seller',
                'items.product.primaryImage',
                'auditLogs.actor',
            ]),
        ]);
    }
}

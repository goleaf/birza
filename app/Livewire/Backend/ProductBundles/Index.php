<?php

namespace App\Livewire\Backend\ProductBundles;

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
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use InteractsWithWireUi;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public ?string $statusFilter = null;

    #[Url(as: 'seller', except: '')]
    public ?string $sellerFilter = null;

    public int $perPage = 15;

    public function mount(): void
    {
        $this->authorize('viewAny', ProductBundle::class);
    }

    public function publishBundle(int $bundleId, ValidateBundleAvailabilityAction $validator, RecordProductBundleAuditLogsAction $auditRecorder): void
    {
        $bundle = $this->bundleForAdmin($bundleId);
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
        $auditRecorder->statusChanged(Auth::guard('admin')->user(), $bundle->refresh(), $oldStatus, 'admin_product_bundle_index');

        $this->notifySuccess(__('bundles.messages.published'));
    }

    public function unpublishBundle(int $bundleId, RecordProductBundleAuditLogsAction $auditRecorder): void
    {
        $bundle = $this->bundleForAdmin($bundleId);
        $this->authorize('unpublish', $bundle);

        $oldStatus = (string) $bundle->status;
        $bundle->forceFill([
            'status' => ProductBundle::STATUS_INACTIVE,
            'published_at' => null,
        ])->save();
        $auditRecorder->statusChanged(Auth::guard('admin')->user(), $bundle->refresh(), $oldStatus, 'admin_product_bundle_index');

        $this->notifySuccess(__('bundles.messages.unpublished'));
    }

    public function archiveBundle(int $bundleId, RecordProductBundleAuditLogsAction $auditRecorder): void
    {
        $bundle = $this->bundleForAdmin($bundleId);
        $this->authorize('archive', $bundle);

        $oldStatus = (string) $bundle->status;
        $bundle->forceFill([
            'status' => ProductBundle::STATUS_ARCHIVED,
            'published_at' => null,
        ])->save();
        $auditRecorder->statusChanged(Auth::guard('admin')->user(), $bundle->refresh(), $oldStatus, 'admin_product_bundle_index');

        $this->notifySuccess(__('bundles.messages.archived'));
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'statusFilter', 'sellerFilter'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'statusFilter', 'sellerFilter');
        $this->resetPage();
    }

    public function render(): View
    {
        $bundles = ProductBundle::query()
            ->with(['seller:id,name,company_name,is_active', 'items.product:id,name'])
            ->withCount('items')
            ->when($this->search !== '', fn ($query) => $query->where(function ($query): void {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('slug', 'like', '%'.$this->search.'%');
            }))
            ->when(filled($this->statusFilter), fn ($query) => $query->where('status', $this->statusFilter))
            ->when(filled($this->sellerFilter), fn ($query) => $query->where('seller_id', (int) $this->sellerFilter))
            ->latest()
            ->paginate($this->perPage)
            ->withQueryString();

        return view('livewire.backend.product-bundles.index', [
            'bundles' => $bundles,
            'sellers' => Seller::query()->select(['id', 'name', 'company_name'])->orderBy('company_name')->get(),
            'statusOptions' => ProductBundle::statuses(),
        ]);
    }

    private function bundleForAdmin(int $bundleId): ProductBundle
    {
        return ProductBundle::query()
            ->with(['seller', 'items.product.seller'])
            ->findOrFail($bundleId);
    }
}

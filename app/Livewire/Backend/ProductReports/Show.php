<?php

namespace App\Livewire\Backend\ProductReports;

use App\Actions\ProductReports\DismissProductReportAction;
use App\Actions\ProductReports\HideReportedProductAction;
use App\Actions\ProductReports\RejectProductReportAction;
use App\Actions\ProductReports\ResolveProductReportAction;
use App\Actions\ProductReports\ReviewProductReportAction;
use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\ProductReport;
use App\Models\Users\Admin;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Show extends Component
{
    use AuthorizesRequests;
    use InteractsWithWireUi;

    public ProductReport $productReport;

    public ?string $adminNote = null;

    public function mount(ProductReport $productReport): void
    {
        $this->productReport = $productReport;
        $this->authorize('view', $this->productReport);
        $this->loadReport();
    }

    public function startReview(): void
    {
        $this->authorize('review', $this->productReport);
        $this->validateAdminNote(required: false);

        $this->productReport = app(ReviewProductReportAction::class)->handle($this->productReport, $this->admin(), $this->adminNote);
        $this->adminNote = null;
        $this->loadReport();

        $this->notifySuccess(__('admin.reports.messages.reviewing'));
    }

    public function confirmResolve(): void
    {
        $this->confirmAction(
            title: __('admin.reports.resolve'),
            description: __('admin.reports.confirm.resolve'),
            acceptLabel: __('admin.reports.resolve'),
            method: 'resolveReport',
        );
    }

    public function resolveReport(): void
    {
        $this->authorize('resolve', $this->productReport);
        $this->validateAdminNote(required: false);

        $this->productReport = app(ResolveProductReportAction::class)->handle($this->productReport, $this->admin(), $this->adminNote);
        $this->adminNote = null;
        $this->loadReport();

        $this->notifySuccess(__('admin.reports.messages.resolved'));
    }

    public function confirmReject(): void
    {
        $this->confirmAction(
            title: __('admin.reports.reject'),
            description: __('admin.reports.confirm.reject'),
            acceptLabel: __('admin.reports.reject'),
            method: 'rejectReport',
        );
    }

    public function rejectReport(): void
    {
        $this->authorize('reject', $this->productReport);
        $this->validateAdminNote(required: false);

        $this->productReport = app(RejectProductReportAction::class)->handle($this->productReport, $this->admin(), $this->adminNote);
        $this->adminNote = null;
        $this->loadReport();

        $this->notifySuccess(__('admin.reports.messages.rejected'));
    }

    public function confirmDismiss(): void
    {
        $this->confirmAction(
            title: __('admin.reports.dismiss'),
            description: __('admin.reports.confirm.dismiss'),
            acceptLabel: __('admin.reports.dismiss'),
            method: 'dismissReport',
        );
    }

    public function dismissReport(): void
    {
        $this->authorize('dismiss', $this->productReport);
        $this->validateAdminNote(required: false);

        $this->productReport = app(DismissProductReportAction::class)->handle($this->productReport, $this->admin(), $this->adminNote);
        $this->adminNote = null;
        $this->loadReport();

        $this->notifySuccess(__('admin.reports.messages.dismissed'));
    }

    public function confirmHideProduct(): void
    {
        $this->confirmAction(
            title: __('admin.reports.hide_product'),
            description: __('admin.reports.confirm.hide_product'),
            acceptLabel: __('admin.reports.hide_product'),
            method: 'hideProduct',
            icon: 'warning',
        );
    }

    public function hideProduct(): void
    {
        $this->authorize('hideProduct', $this->productReport);
        $this->validateAdminNote(required: true);

        $this->productReport = app(HideReportedProductAction::class)->handle($this->productReport, $this->admin(), $this->adminNote);
        $this->adminNote = null;
        $this->loadReport();

        $this->notifySuccess(__('admin.reports.messages.product_hidden'));
    }

    public function render()
    {
        return view('livewire.backend.product-reports.show', [
            'report' => $this->productReport,
        ]);
    }

    private function loadReport(): void
    {
        $this->productReport->load([
            'product.category:id,category_name',
            'product.seller:id,name,company_name,email',
            'reporter:id,name,email,is_active',
            'reviewedBy:id,name,email',
        ]);
    }

    private function admin(): Admin
    {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }

    private function validateAdminNote(bool $required): void
    {
        $rules = $required
            ? ['required', 'string', 'max:1000']
            : ['nullable', 'string', 'max:1000'];

        $this->validate([
            'adminNote' => $rules,
        ], attributes: [
            'adminNote' => __('reports.product.admin_note'),
        ]);
    }
}

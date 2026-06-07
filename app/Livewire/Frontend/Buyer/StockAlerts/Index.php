<?php

namespace App\Livewire\Frontend\Buyer\StockAlerts;

use App\Actions\StockAlerts\CancelStockAlertAction;
use App\Enums\ProductStockAlertStatus;
use App\Models\ProductStockAlert;
use App\Models\Users\Buyer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    use WithPagination;

    public string $filter = 'active';

    public int $perPage = 10;

    public function setFilter(string $filter): void
    {
        if (! in_array($filter, ['active', 'notified', 'cancelled', 'all'], true)) {
            return;
        }

        $this->filter = $filter;
        $this->resetPage();
    }

    public function cancelAlert(int $alertId, CancelStockAlertAction $action): void
    {
        $buyer = $this->buyer();
        $alert = ProductStockAlert::query()
            ->findOrFail($alertId);

        $action->handle($alert, $buyer);

        session()->flash('success', __('stock_alerts.cancelled_successfully'));
    }

    public function render(): View
    {
        $query = ProductStockAlert::query()
            ->forBuyer($this->buyer())
            ->select(['id', 'product_id', 'buyer_id', 'status', 'notified_at', 'created_at', 'updated_at'])
            ->with([
                'product:id,name,seller_id,price,stock,unit,is_active,deleted_at,product_image',
                'product.seller:id,name,company_name,is_active,deleted_at',
            ])
            ->latest();

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        return view('livewire.frontend.buyer.stock-alerts.index', [
            'alerts' => $query->paginate($this->perPage)->withQueryString(),
            'statusFilters' => $this->statusFilters(),
        ]);
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    private function statusFilters(): array
    {
        return collect([
            ProductStockAlertStatus::Active,
            ProductStockAlertStatus::Notified,
            ProductStockAlertStatus::Cancelled,
        ])
            ->map(fn (ProductStockAlertStatus $status): array => [
                'id' => $status->value,
                'label' => __($status->labelKey()),
            ])
            ->push([
                'id' => 'all',
                'label' => __('stock_alerts.status.all'),
            ])
            ->values()
            ->all();
    }

    private function buyer(): Buyer
    {
        $buyer = Auth::guard('buyer')->user();

        abort_if(! $buyer instanceof Buyer, 403);

        return $buyer;
    }
}

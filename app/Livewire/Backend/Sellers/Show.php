<?php

namespace App\Livewire\Backend\Sellers;

use App\Models\AuditLog;
use App\Models\Users\Seller;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Show extends Component
{
    use AuthorizesRequests;

    public Seller $seller;

    public function mount(Seller $seller): void
    {
        $this->authorize('view', $seller);

        $this->seller = $seller;
    }

    public function render(): View
    {
        $products = $this->seller->products()
            ->select('id', 'name', 'price', 'is_active', 'product_image', 'category_id')
            ->with(['category:id,category_name', 'primaryImage'])
            ->withCount('orderItems')
            ->latest()
            ->paginate(10);

        $orders = $this->seller->orders()
            ->select([
                'orders.id',
                'orders.buyer_id',
                'orders.payment_status',
                'orders.status',
                'orders.order_total',
                'orders.created_at',
            ])
            ->with('buyer:id,name,company_name')
            ->withCount('orderItems')
            ->distinct()
            ->orderBy('orders.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('backend.sellers.show', [
            'auditLogs' => AuditLog::query()
                ->entity($this->seller)
                ->with('actor')
                ->latest('created_at')
                ->limit(10)
                ->get(),
            'seller' => $this->seller,
            'sellerDetails' => $this->sellerDetails(),
            'products' => $products,
            'recentOrders' => $orders,
        ]);
    }

    /**
     * @return array<int, array{icon: string, value: string, label: string}>
     */
    protected function sellerDetails(): array
    {
        return [
            [
                'icon' => 'o-building-office-2',
                'value' => $this->seller->company_name ?: __('common_not_specified'),
                'label' => __('backend_sellers_fields_company_name'),
            ],
            [
                'icon' => 'o-user',
                'value' => $this->seller->name ?: __('common_not_specified'),
                'label' => __('sellers_contact_person'),
            ],
            [
                'icon' => 'o-envelope',
                'value' => $this->seller->email ?: __('common_not_specified'),
                'label' => __('sellers_email'),
            ],
            [
                'icon' => 'o-identification',
                'value' => $this->seller->vat_code ?: __('common_not_specified'),
                'label' => __('sellers_vat_code'),
            ],
            [
                'icon' => 'o-phone',
                'value' => $this->seller->phone ?: __('common_not_specified'),
                'label' => __('sellers_phone'),
            ],
            [
                'icon' => 'o-map-pin',
                'value' => $this->seller->address ?: __('common_not_specified'),
                'label' => __('sellers_address'),
            ],
        ];
    }
}

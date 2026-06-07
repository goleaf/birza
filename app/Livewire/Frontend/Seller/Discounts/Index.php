<?php

namespace App\Livewire\Frontend\Seller\Discounts;

use App\Actions\Promotions\ArchiveSellerDiscountAction;
use App\Actions\Promotions\CreateSellerDiscountAction;
use App\Actions\Promotions\UpdateSellerDiscountAction;
use App\Models\Discount;
use App\Models\Users\Seller;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public ?int $editingId = null;

    public ?int $product_id = null;

    public ?int $category_id = null;

    public string $name = '';

    public string $type = Discount::TYPE_PERCENTAGE;

    public ?float $value = null;

    public ?string $starts_at = null;

    public ?string $ends_at = null;

    public string $status = Discount::STATUS_ACTIVE;

    public ?int $usage_limit = null;

    public ?float $minimum_order_amount = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Discount::class);
    }

    public function save(CreateSellerDiscountAction $createAction, UpdateSellerDiscountAction $updateAction): void
    {
        $seller = $this->seller();
        $payload = $this->payload();

        if ($this->editingId) {
            $discount = $seller->discounts()->findOrFail($this->editingId);
            $updateAction->handle($seller, $discount, $payload);
            session()->flash('success', __('discounts.updated_successfully'));
        } else {
            $createAction->handle($seller, $payload);
            session()->flash('success', __('discounts.created_successfully'));
        }

        $this->resetForm();
    }

    public function edit(int $discountId): void
    {
        $discount = $this->seller()
            ->discounts()
            ->findOrFail($discountId);

        $this->authorize('update', $discount);

        $this->editingId = $discount->id;
        $this->product_id = $discount->product_id;
        $this->category_id = $discount->category_id;
        $this->name = (string) $discount->name;
        $this->type = (string) $discount->type;
        $this->value = (float) $discount->value;
        $this->starts_at = $discount->starts_at?->format('Y-m-d\TH:i');
        $this->ends_at = $discount->ends_at?->format('Y-m-d\TH:i');
        $this->status = (string) $discount->status;
        $this->usage_limit = $discount->usage_limit;
        $this->minimum_order_amount = $discount->minimum_order_amount !== null ? (float) $discount->minimum_order_amount : null;
    }

    public function toggleStatus(int $discountId, UpdateSellerDiscountAction $action): void
    {
        $seller = $this->seller();
        $discount = $seller->discounts()->findOrFail($discountId);
        $this->authorize('update', $discount);

        $payload = $this->payloadFromDiscount($discount);
        $payload['status'] = $discount->status === Discount::STATUS_ACTIVE
            ? Discount::STATUS_INACTIVE
            : Discount::STATUS_ACTIVE;

        $action->handle($seller, $discount, $payload);
        session()->flash('success', __('discounts.updated_successfully'));
    }

    public function archive(int $discountId, ArchiveSellerDiscountAction $action): void
    {
        $seller = $this->seller();
        $discount = $seller->discounts()->findOrFail($discountId);
        $action->handle($seller, $discount);

        if ($this->editingId === $discountId) {
            $this->resetForm();
        }

        session()->flash('success', __('discounts.deleted_successfully'));
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'product_id',
            'category_id',
            'name',
            'value',
            'starts_at',
            'ends_at',
            'usage_limit',
            'minimum_order_amount',
        ]);

        $this->type = Discount::TYPE_PERCENTAGE;
        $this->status = Discount::STATUS_ACTIVE;
        $this->resetValidation();
    }

    public function render(): View
    {
        $seller = $this->seller();

        return view('livewire.frontend.seller.discounts.index', [
            'discounts' => $seller->discounts()
                ->with(['product:id,name', 'category:id,category_name'])
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'products' => $seller->products()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get(),
            'categories' => $seller->categories()
                ->select(['categories.id', 'categories.category_name'])
                ->orderBy('categories.id')
                ->get(),
            'types' => Discount::types(),
            'statuses' => Discount::statuses(),
        ]);
    }

    private function seller(): Seller
    {
        $seller = Auth::guard('seller')->user();

        abort_unless($seller instanceof Seller, 403);

        return $seller;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'product_id' => $this->product_id ?: null,
            'category_id' => $this->category_id ?: null,
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'starts_at' => $this->starts_at ?: null,
            'ends_at' => $this->ends_at ?: null,
            'status' => $this->status,
            'usage_limit' => $this->usage_limit,
            'minimum_order_amount' => $this->minimum_order_amount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromDiscount(Discount $discount): array
    {
        return [
            'product_id' => $discount->product_id,
            'category_id' => $discount->category_id,
            'name' => $discount->name,
            'type' => $discount->type,
            'value' => $discount->value,
            'starts_at' => $discount->starts_at?->toDateTimeString(),
            'ends_at' => $discount->ends_at?->toDateTimeString(),
            'status' => $discount->status,
            'usage_limit' => $discount->usage_limit,
            'minimum_order_amount' => $discount->minimum_order_amount,
        ];
    }
}

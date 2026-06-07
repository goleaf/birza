<?php

namespace App\Livewire\Frontend\Seller\PromoCodes;

use App\Actions\Promotions\ArchivePromoCodeAction;
use App\Actions\Promotions\CreatePromoCodeAction;
use App\Actions\Promotions\UpdatePromoCodeAction;
use App\Models\PromoCode;
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

    public string $code = '';

    public string $type = PromoCode::TYPE_PERCENTAGE;

    public ?float $value = null;

    public ?string $starts_at = null;

    public ?string $ends_at = null;

    public string $status = PromoCode::STATUS_ACTIVE;

    public ?int $usage_limit = null;

    public ?int $per_user_limit = 1;

    public ?float $minimum_order_amount = null;

    public function mount(): void
    {
        $this->authorize('viewAny', PromoCode::class);
    }

    public function save(CreatePromoCodeAction $createAction, UpdatePromoCodeAction $updateAction): void
    {
        $seller = $this->seller();
        $payload = $this->payload();

        if ($this->editingId) {
            $promoCode = $seller->promoCodes()->findOrFail($this->editingId);
            $updateAction->handle($seller, $promoCode, $payload);
            session()->flash('success', __('promo_codes.updated_successfully'));
        } else {
            $createAction->handle($seller, $payload);
            session()->flash('success', __('promo_codes.created_successfully'));
        }

        $this->resetForm();
    }

    public function edit(int $promoCodeId): void
    {
        $promoCode = $this->seller()
            ->promoCodes()
            ->findOrFail($promoCodeId);

        $this->authorize('update', $promoCode);

        $this->editingId = $promoCode->id;
        $this->code = (string) $promoCode->code;
        $this->type = (string) $promoCode->type;
        $this->value = (float) $promoCode->value;
        $this->starts_at = $promoCode->starts_at?->format('Y-m-d\TH:i');
        $this->ends_at = $promoCode->ends_at?->format('Y-m-d\TH:i');
        $this->status = (string) $promoCode->status;
        $this->usage_limit = $promoCode->usage_limit;
        $this->per_user_limit = $promoCode->per_user_limit;
        $this->minimum_order_amount = $promoCode->minimum_order_amount !== null ? (float) $promoCode->minimum_order_amount : null;
    }

    public function toggleStatus(int $promoCodeId, UpdatePromoCodeAction $action): void
    {
        $seller = $this->seller();
        $promoCode = $seller->promoCodes()->findOrFail($promoCodeId);
        $this->authorize('update', $promoCode);

        $payload = $this->payloadFromPromoCode($promoCode);
        $payload['status'] = $promoCode->status === PromoCode::STATUS_ACTIVE
            ? PromoCode::STATUS_INACTIVE
            : PromoCode::STATUS_ACTIVE;

        $action->handle($seller, $promoCode, $payload);
        session()->flash('success', __('promo_codes.updated_successfully'));
    }

    public function archive(int $promoCodeId, ArchivePromoCodeAction $action): void
    {
        $seller = $this->seller();
        $promoCode = $seller->promoCodes()->findOrFail($promoCodeId);
        $action->handle($seller, $promoCode);

        if ($this->editingId === $promoCodeId) {
            $this->resetForm();
        }

        session()->flash('success', __('promo_codes.deleted_successfully'));
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'code',
            'value',
            'starts_at',
            'ends_at',
            'usage_limit',
            'minimum_order_amount',
        ]);

        $this->type = PromoCode::TYPE_PERCENTAGE;
        $this->status = PromoCode::STATUS_ACTIVE;
        $this->per_user_limit = 1;
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.frontend.seller.promo-codes.index', [
            'promoCodes' => $this->seller()
                ->promoCodes()
                ->withCount('redemptions')
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'types' => PromoCode::types(),
            'statuses' => PromoCode::statuses(),
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
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'starts_at' => $this->starts_at ?: null,
            'ends_at' => $this->ends_at ?: null,
            'status' => $this->status,
            'usage_limit' => $this->usage_limit,
            'per_user_limit' => $this->per_user_limit,
            'minimum_order_amount' => $this->minimum_order_amount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromPromoCode(PromoCode $promoCode): array
    {
        return [
            'code' => $promoCode->code,
            'type' => $promoCode->type,
            'value' => $promoCode->value,
            'starts_at' => $promoCode->starts_at?->toDateTimeString(),
            'ends_at' => $promoCode->ends_at?->toDateTimeString(),
            'status' => $promoCode->status,
            'usage_limit' => $promoCode->usage_limit,
            'per_user_limit' => $promoCode->per_user_limit,
            'minimum_order_amount' => $promoCode->minimum_order_amount,
        ];
    }
}

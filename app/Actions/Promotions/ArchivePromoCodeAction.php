<?php

namespace App\Actions\Promotions;

use App\Models\PromoCode;
use App\Models\Users\Seller;
use Illuminate\Support\Facades\Gate;

class ArchivePromoCodeAction
{
    public function __construct(
        private readonly RecordPromotionAuditLogsAction $auditLogsAction,
    ) {}

    public function handle(Seller $seller, PromoCode $promoCode): void
    {
        Gate::forUser($seller)->authorize('delete', $promoCode);

        $oldValues = $this->auditLogsAction->promoCodeSnapshot($promoCode);
        $promoCode->delete();
        $promoCode->refresh();

        $this->auditLogsAction->promoCodeDeleted($seller, $promoCode, $oldValues, 'seller_promo_code_archive');
    }
}

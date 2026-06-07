<?php

namespace App\Actions\Promotions;

use App\Models\Discount;
use App\Models\Users\Seller;
use Illuminate\Support\Facades\Gate;

class ArchiveSellerDiscountAction
{
    public function __construct(
        private readonly RecordPromotionAuditLogsAction $auditLogsAction,
    ) {}

    public function handle(Seller $seller, Discount $discount): void
    {
        Gate::forUser($seller)->authorize('delete', $discount);

        $oldValues = $this->auditLogsAction->discountSnapshot($discount);
        $discount->delete();
        $discount->refresh();

        $this->auditLogsAction->discountDeleted($seller, $discount, $oldValues, 'seller_discount_archive');
    }
}

<?php

namespace App\Actions\Notifications;

use App\Models\ProductReport;
use App\Models\Users\Admin;
use App\Notifications\Marketplace\ProductHiddenDueToReportNotification;
use App\Notifications\Marketplace\ProductReportCreatedNotification;

class SendProductReportNotificationAction
{
    public function __construct(
        private readonly SendMarketplaceNotificationAction $sendNotification,
    ) {}

    public function newReportCreated(ProductReport $report): void
    {
        $report->loadMissing(['product', 'reporter']);

        $admins = Admin::query()
            ->where('is_active', true)
            ->get(['id', 'name', 'email', 'password', 'is_active']);

        $this->sendNotification->handle($admins, new ProductReportCreatedNotification($report));
    }

    public function productHidden(ProductReport $report, ?string $adminNote = null): void
    {
        $report->loadMissing('product.seller');

        if (! $report->product?->seller) {
            return;
        }

        $this->sendNotification->handle(
            $report->product->seller,
            new ProductHiddenDueToReportNotification($report, $adminNote),
        );
    }
}

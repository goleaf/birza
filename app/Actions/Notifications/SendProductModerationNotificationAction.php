<?php

namespace App\Actions\Notifications;

use App\Models\Product;
use App\Models\Users\Admin;
use App\Notifications\Marketplace\ProductApprovedNotification;
use App\Notifications\Marketplace\ProductModerationRequiredNotification;
use App\Notifications\Marketplace\ProductRejectedNotification;

class SendProductModerationNotificationAction
{
    public function __construct(
        private readonly SendMarketplaceNotificationAction $sendNotification,
    ) {}

    public function moderationRequired(Product $product): void
    {
        $admins = Admin::query()
            ->where('is_active', true)
            ->get(['id', 'name', 'email', 'password', 'is_active']);

        $this->sendNotification->handle($admins, new ProductModerationRequiredNotification($product));
    }

    public function approved(Product $product): void
    {
        $product->loadMissing('seller');

        $this->sendNotification->handle($product->seller, new ProductApprovedNotification($product));
    }

    public function rejected(Product $product, ?string $reason = null): void
    {
        $product->loadMissing('seller');

        $this->sendNotification->handle($product->seller, new ProductRejectedNotification($product, $reason));
    }
}

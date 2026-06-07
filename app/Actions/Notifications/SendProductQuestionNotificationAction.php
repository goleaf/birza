<?php

namespace App\Actions\Notifications;

use App\Models\ProductQuestion;
use App\Notifications\Marketplace\ProductQuestionAnsweredNotification;
use App\Notifications\Marketplace\ProductQuestionCreatedNotification;
use App\Notifications\Marketplace\ProductQuestionRejectedNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class SendProductQuestionNotificationAction
{
    public function __construct(
        private readonly SendMarketplaceNotificationAction $sendNotification,
    ) {}

    public function created(ProductQuestion $productQuestion): void
    {
        $productQuestion->loadMissing(['product:id,name', 'seller:id,name,email,company_name']);

        $this->sendNotification->handle(
            $productQuestion->seller,
            new ProductQuestionCreatedNotification($productQuestion),
        );
    }

    public function answered(ProductQuestion $productQuestion): void
    {
        $productQuestion->loadMissing(['product:id,name', 'buyer:id,name,email,company_name']);
        $notification = new ProductQuestionAnsweredNotification($productQuestion);

        if ($productQuestion->buyer !== null) {
            $this->sendNotification->handle($productQuestion->buyer, $notification);

            return;
        }

        if (filled($productQuestion->guest_email)) {
            NotificationFacade::route('mail', $productQuestion->guest_email)
                ->notify($notification->afterCommit());
        }
    }

    public function rejected(ProductQuestion $productQuestion): void
    {
        $productQuestion->loadMissing(['product:id,name', 'buyer:id,name,email,company_name']);
        $notification = new ProductQuestionRejectedNotification($productQuestion);

        if ($productQuestion->buyer !== null) {
            $this->sendNotification->handle($productQuestion->buyer, $notification);

            return;
        }

        if (filled($productQuestion->guest_email)) {
            NotificationFacade::route('mail', $productQuestion->guest_email)
                ->notify($notification->afterCommit());
        }
    }
}

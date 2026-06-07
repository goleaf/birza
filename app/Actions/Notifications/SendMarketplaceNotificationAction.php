<?php

namespace App\Actions\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class SendMarketplaceNotificationAction
{
    public function handle(iterable|object|null $notifiables, Notification $notification): void
    {
        $recipients = $this->recipients($notifiables);

        if ($recipients->isEmpty()) {
            return;
        }

        NotificationFacade::send($recipients->all(), $notification->afterCommit());
    }

    /**
     * @return Collection<int, object>
     */
    private function recipients(iterable|object|null $notifiables): Collection
    {
        return collect(is_iterable($notifiables) ? $notifiables : [$notifiables])
            ->filter()
            ->unique(fn (object $recipient): string => $recipient::class.':'.(string) $recipient->getKey())
            ->values();
    }
}

<?php

namespace App\Actions\Notifications;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Notifications\DatabaseNotification;

class MarkNotificationReadAction
{
    public function handle(Authenticatable $notifiable, DatabaseNotification $notification): void
    {
        $ownedNotification = $notifiable->notifications()
            ->whereKey($notification->id)
            ->first();

        if (! $ownedNotification) {
            throw new AuthorizationException(__('notifications.messages.unauthorized'));
        }

        $ownedNotification->markAsRead();
    }
}

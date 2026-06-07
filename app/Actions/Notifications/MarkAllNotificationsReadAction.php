<?php

namespace App\Actions\Notifications;

use Illuminate\Contracts\Auth\Authenticatable;

class MarkAllNotificationsReadAction
{
    public function handle(Authenticatable $notifiable): void
    {
        $notifiable->unreadNotifications()->update(['read_at' => now()]);
    }
}

<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Contracts\Auth\Authenticatable;

class NotificationPolicy
{
    public function viewAny(Authenticatable $actor): bool
    {
        return $actor instanceof Admin
            || $actor instanceof Buyer
            || $actor instanceof Seller;
    }

    public function view(Authenticatable $actor, Notification $notification): bool
    {
        return $this->ownsNotification($actor, $notification);
    }

    public function update(Authenticatable $actor, Notification $notification): bool
    {
        return $this->ownsNotification($actor, $notification);
    }

    public function delete(Authenticatable $actor, Notification $notification): bool
    {
        return $this->ownsNotification($actor, $notification);
    }

    private function ownsNotification(Authenticatable $actor, Notification $notification): bool
    {
        return is_a($notification->notifiable_type, $actor::class, true)
            && (string) $notification->notifiable_id === (string) $actor->getAuthIdentifier();
    }
}

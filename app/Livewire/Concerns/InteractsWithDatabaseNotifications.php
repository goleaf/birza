<?php

namespace App\Livewire\Concerns;

use App\Actions\Notifications\MarkAllNotificationsReadAction;
use App\Actions\Notifications\MarkNotificationReadAction;
use App\Models\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

trait InteractsWithDatabaseNotifications
{
    public string $filter = 'all';

    public int $perPage = 10;

    abstract protected function notifiable(): Authenticatable;

    public function setFilter(string $filter): void
    {
        if (! in_array($filter, ['all', 'unread', 'read'], true)) {
            return;
        }

        $this->filter = $filter;
        $this->resetPage();
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = Notification::query()->findOrFail($notificationId);
        $notifiable = $this->notifiable();

        Gate::forUser($notifiable)->authorize('update', $notification);

        app(MarkNotificationReadAction::class)->handle($notifiable, $notification);
    }

    public function markAllAsRead(): void
    {
        $notifiable = $this->notifiable();

        Gate::forUser($notifiable)->authorize('viewAny', Notification::class);

        app(MarkAllNotificationsReadAction::class)->handle($notifiable);
    }

    protected function notificationRows(): LengthAwarePaginator
    {
        $notifiable = $this->notifiable();

        Gate::forUser($notifiable)->authorize('viewAny', Notification::class);

        $query = $notifiable
            ->notifications()
            ->select(['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at'])
            ->latest();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        if ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query->paginate($this->perPage)->withQueryString();
    }

    protected function unreadNotificationCount(): int
    {
        $notifiable = $this->notifiable();

        Gate::forUser($notifiable)->authorize('viewAny', Notification::class);

        return $notifiable->unreadNotifications()->count();
    }
}

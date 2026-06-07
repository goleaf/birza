<?php

namespace App\Livewire\Frontend\Notifications;

use App\Livewire\Concerns\InteractsWithDatabaseNotifications;
use App\Models\Notification;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    use InteractsWithDatabaseNotifications;
    use WithPagination;

    public function mount(): void
    {
        Gate::forUser($this->notifiable())->authorize('viewAny', Notification::class);
    }

    public function render(): View
    {
        return view('livewire.frontend.notifications.index', [
            'notifications' => $this->notificationRows(),
            'unreadCount' => $this->unreadNotificationCount(),
        ]);
    }

    protected function notifiable(): Authenticatable
    {
        $notifiable = Auth::guard('buyer')->user() ?? Auth::guard('seller')->user();

        abort_if(! $notifiable instanceof Buyer && ! $notifiable instanceof Seller, 403);

        return $notifiable;
    }
}

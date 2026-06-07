<?php

namespace App\Livewire\Backend\Notifications;

use App\Livewire\Concerns\InteractsWithDatabaseNotifications;
use App\Models\Users\Admin;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use InteractsWithDatabaseNotifications;
    use WithPagination;

    public function render(): View
    {
        return view('livewire.backend.notifications.index', [
            'notifications' => $this->notificationRows(),
            'unreadCount' => $this->unreadNotificationCount(),
        ]);
    }

    protected function notifiable(): Authenticatable
    {
        $notifiable = Auth::guard('admin')->user();

        abort_if(! $notifiable instanceof Admin, 403);

        return $notifiable;
    }
}

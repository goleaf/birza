<?php

namespace App\Http\Controllers\Notifications;

use App\Enums\MarketplaceRole;
use App\Actions\Notifications\MarkNotificationReadAction;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class MarkNotificationReadController extends Controller
{
    public function __invoke(
        Request $request,
        Notification $notification,
        MarkNotificationReadAction $markNotificationRead,
    ): RedirectResponse {
        $guard = (string) $request->route('guard');
        abort_if(! in_array($guard, MarketplaceRole::notificationGuards(), true), 404);

        $notifiable = Auth::guard($guard)->user();

        abort_if(! $notifiable, 403);

        Gate::forUser($notifiable)->authorize('update', $notification);

        $markNotificationRead->handle($notifiable, $notification);

        return back();
    }
}

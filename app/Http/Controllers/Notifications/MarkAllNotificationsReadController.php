<?php

namespace App\Http\Controllers\Notifications;

use App\Actions\Notifications\MarkAllNotificationsReadAction;
use App\Enums\MarketplaceRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarkAllNotificationsReadController extends Controller
{
    public function __invoke(Request $request, MarkAllNotificationsReadAction $markAllNotificationsRead): RedirectResponse
    {
        $guard = (string) $request->route('guard');
        abort_if(! in_array($guard, MarketplaceRole::notificationGuards(), true), 404);

        $notifiable = Auth::guard($guard)->user();

        abort_if(! $notifiable, 403);

        $markAllNotificationsRead->handle($notifiable);

        return back();
    }
}

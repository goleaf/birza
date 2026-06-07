<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\Conversation;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Models\WishlistItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewContract;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('layouts.frontend.header', function (ViewContract $view): void {
            $view->with([
                'cartItemsCount' => $this->buyerCartItemsCount(),
                'messageUnreadCount' => $this->frontendMessageUnreadCount(),
                'wishlistItemsCount' => $this->buyerWishlistItemsCount(),
                'notificationDropdown' => $this->frontendNotificationDropdown(),
            ]);
        });

        View::composer('layouts.backend.navigation', function (ViewContract $view): void {
            $view->with('notificationDropdown', $this->notificationDropdownForGuard(
                guard: 'admin',
                indexRouteName: 'admin.notifications.index',
                markReadRouteName: 'admin.notifications.read',
                markAllRouteName: 'admin.notifications.read_all',
            ));
        });
    }

    private function buyerCartItemsCount(): int
    {
        $cartQuery = Cart::query()->active();

        if (Auth::guard('buyer')->check()) {
            $cartQuery->where('user_id', Auth::guard('buyer')->id());
        } elseif (session()->has('cart_guest_token')) {
            $cartQuery->where('guest_token', session('cart_guest_token'));
        } else {
            return 0;
        }

        $cart = $cartQuery->with(['items', 'bundleItems'])->first();

        return (int) (($cart?->items->sum('quantity') ?? 0) + ($cart?->bundleItems->sum('quantity') ?? 0));
    }

    private function buyerWishlistItemsCount(): int
    {
        if (! Auth::guard('buyer')->check()) {
            return 0;
        }

        return WishlistItem::query()
            ->whereHas('wishlist', fn ($query) => $query->where('buyer_id', Auth::guard('buyer')->id()))
            ->count();
    }

    private function frontendMessageUnreadCount(): int
    {
        if (Auth::guard('buyer')->check()) {
            $buyer = Auth::guard('buyer')->user();

            if ($buyer instanceof Buyer) {
                return Conversation::query()
                    ->forBuyer($buyer)
                    ->whereNull('buyer_archived_at')
                    ->unreadFor($buyer)
                    ->count();
            }
        }

        if (Auth::guard('seller')->check()) {
            $seller = Auth::guard('seller')->user();

            if ($seller instanceof Seller) {
                return Conversation::query()
                    ->forSeller($seller)
                    ->whereNull('seller_archived_at')
                    ->unreadFor($seller)
                    ->count();
            }
        }

        return 0;
    }

    /**
     * @return array{notifications: Collection<int, mixed>, unreadCount: int, indexRoute: ?string, markReadRouteName: ?string, markAllRoute: ?string}
     */
    private function frontendNotificationDropdown(): array
    {
        if (Auth::guard('buyer')->check()) {
            return $this->notificationDropdownForGuard(
                guard: 'buyer',
                indexRouteName: 'buyer.notifications.index',
                markReadRouteName: 'buyer.notifications.read',
                markAllRouteName: 'buyer.notifications.read_all',
            );
        }

        if (Auth::guard('seller')->check()) {
            return $this->notificationDropdownForGuard(
                guard: 'seller',
                indexRouteName: 'seller.notifications.index',
                markReadRouteName: 'seller.notifications.read',
                markAllRouteName: 'seller.notifications.read_all',
            );
        }

        return $this->emptyNotificationDropdown();
    }

    /**
     * @return array{notifications: Collection<int, mixed>, unreadCount: int, indexRoute: ?string, markReadRouteName: ?string, markAllRoute: ?string}
     */
    private function notificationDropdownForGuard(
        string $guard,
        string $indexRouteName,
        string $markReadRouteName,
        string $markAllRouteName,
    ): array {
        $notifiable = Auth::guard($guard)->user();

        if (! $notifiable) {
            return $this->emptyNotificationDropdown();
        }

        return [
            'notifications' => $notifiable->notifications()
                ->select(['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at'])
                ->latest()
                ->limit(5)
                ->get(),
            'unreadCount' => $notifiable->unreadNotifications()->count(),
            'indexRoute' => route($indexRouteName),
            'markReadRouteName' => $markReadRouteName,
            'markAllRoute' => route($markAllRouteName),
        ];
    }

    /**
     * @return array{notifications: Collection<int, mixed>, unreadCount: int, indexRoute: ?string, markReadRouteName: ?string, markAllRoute: ?string}
     */
    private function emptyNotificationDropdown(): array
    {
        return [
            'notifications' => collect(),
            'unreadCount' => 0,
            'indexRoute' => null,
            'markReadRouteName' => null,
            'markAllRoute' => null,
        ];
    }
}

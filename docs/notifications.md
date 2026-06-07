# Marketplace Notifications

Birza uses Laravel's notification system for marketplace alerts across buyer, seller, and admin guards. Notifications are stored in the standard polymorphic `notifications` table so each guard-owned model receives only its own rows.

## Supported Events

Implemented notification flows:

- Buyer order created: buyer receives an in-app confirmation.
- New seller order: each seller with items in the order receives in-app and email notification.
- Order status changed: buyer and sellers receive updates unless they performed the change.
- Buyer order cancellation: sellers receive the status-change notification.
- Product requires moderation: active admins receive an in-app alert.
- Product approved or rejected: product seller receives an in-app decision; rejection also sends email.
- Product stock low or out: product seller receives one in-app alert per threshold state.
- Product reported: active admins receive an in-app product-report alert.
- Reported product hidden: product seller receives a report-specific product-hidden notification with the moderation reason and admin note when provided.
- Product back in stock: subscribed buyers receive one in-app alert when a visible product becomes purchasable again.
- New private message: the recipient receives an in-app alert with sender name, safe preview, related product/order context, and a conversation link.

Not implemented because the project has no active workflow yet:

- Disputes.
- Dedicated refund requests.
- Review-created notifications. Reviews exist in the data model, but there is no active review creation action to hook safely.

## Channels

The default channel is `database` for in-app notifications.

Important seller-facing events also use `mail`:

- `NewOrderForSellerNotification`
- `OrderStatusChangedNotification`
- `ProductRejectedNotification`
- `ProductHiddenDueToReportNotification`

Broadcast notifications are intentionally not enabled. The app has no realtime notification infrastructure yet, and the header/dropdown reads from the database.

## Queueing

Marketplace notification classes extend `MarketplaceNotification`, implement `ShouldQueue`, and use `afterCommit()` through `SendMarketplaceNotificationAction`. This keeps notifications from being processed before the related transaction is committed.

Local development uses:

```env
QUEUE_CONNECTION=sync
```

Production should use a real queue worker if mail volume matters. If the database queue driver is used, create and migrate the jobs table before deployment.

## Payload Shape

Database notifications store only render-safe metadata:

- `title_key`
- `message_key`
- `title_params`
- `message_params`
- `related_type`
- `related_id`
- `url`
- `status`
- `icon`

Do not store sensitive user input, private message bodies, raw secrets, or large relationship payloads in notification data.

Message notifications store a short escaped preview only. The full body remains in the `messages` table and is protected by conversation policies.

## Translation

Visible notification copy must use JSON translation keys in every supported locale. Current keys follow this pattern:

```text
notifications.orders.created.title
notifications.orders.status_changed.message
notifications.products.rejected.title
notifications.messages.new.message
notifications.stock_alert.back_in_stock.message
notifications.stock.low.message
```

Status values shown inside notification messages should be translated before they are stored in `message_params`.

## Triggering Notifications

Send notifications from actions or services, not Blade views or large Livewire methods.

Use `App\Actions\Notifications\SendMarketplaceNotificationAction`:

```php
$this->sendNotification->handle(
    $seller,
    new NewOrderForSellerNotification($order),
);
```

Current trigger points:

- `CreateOrdersFromCartAction`
- `ChangeOrderStatusAction`
- seller product create/edit Livewire actions
- backend product edit/index Livewire actions
- stock threshold action
- product observer for back-in-stock detection
- product report creation and hide-product moderation actions
- buyer-seller message send action

## UI

Notification UI exists on:

- frontend header dropdown for buyers and sellers
- backend navigation dropdown for admins
- buyer notification list page
- seller notification list page
- admin notification list page
- buyer dashboard recent notifications
- seller dashboard recent notifications
- admin dashboard alert panel

The full list pages support:

- unread count
- read/unread/all filters
- mark one notification as read
- mark all as read
- pagination
- empty state

## Permissions

Notification rows are always filtered through the authenticated notifiable model:

- buyers read only their buyer notifications
- sellers read only their seller notifications
- admins read only their admin notifications

`MarkNotificationReadAction` verifies ownership before marking a notification as read. Cross-user updates throw an authorization exception.

## Low Stock

The threshold is configured through:

```env
MARKETPLACE_LOW_STOCK_THRESHOLD=5
```

`SendStockThresholdNotificationAction` sends a seller alert when stock crosses from above the threshold into low stock or reaches zero. It checks existing notifications for the same product/type/status to avoid repeated low-stock spam.

## Cleanup

Notifications are kept by default. They are not audit logs, and should not replace order status history or admin audit records.

Future cleanup can delete old read notifications after the retention period in `config/notifications.php` once product requirements are confirmed.

## Testing

Focused tests live in:

```text
tests/Feature/Notifications/MarketplaceNotificationSystemTest.php
tests/Feature/Marketplace/MessagingFeatureTest.php
tests/Feature/Translations/TranslationFilesTest.php
```

Useful commands:

```bash
php artisan test --compact tests/Feature/Notifications/MarketplaceNotificationSystemTest.php
php artisan test --compact tests/Feature/Marketplace/MessagingFeatureTest.php
php artisan test --compact tests/Feature/Translations/TranslationFilesTest.php
```

Use `Notification::fake()` for recipient/channel assertions and real database notifications for payload/read-state tests.

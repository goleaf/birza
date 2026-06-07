# Marketplace Notification System Report

Date: 2026-06-07
Scope: existing notifications, notifiable models, migrations, controllers, Livewire pages, actions, events/listeners, policies, routes, Blade/layouts, database notification support, mail/queue config, order/product/message/review/dispute/refund/moderation/stock logic, tests, seeders, translations, and docs.

## Executive Summary

Birza has mail notifications and UI toast notifications, but it does not yet have a complete marketplace notification system. The live SQLite schema has no Laravel `notifications` table. The dirty worktree has a custom `user_notifications` model, migration, and factory, but that schema is not active in the live database and is not Laravel's polymorphic database notification format. Because buyer, seller, and admin users are separate notifiable models, the new system should use Laravel's standard `notifications` table with `notifiable_type` and `notifiable_id`.

The implementation should support real project features only:

- Orders and order status transitions are real and active.
- Product activation/moderation is represented by `products.is_active` and admin product edit/delete/restore flows, but there is no rich moderation status or rejection-reason table yet.
- Stock exists on products and checkout decrements it.
- Reviews exist in a pending dirty model/migration/factory, but no route/UI/action currently creates reviews.
- Messages, disputes, refunds, and reports do not have active marketplace tables or workflows. They should be documented as TODO instead of faked.
- Filament is not present; the admin is custom Livewire + Blade.

## Existing Notifications

| Notification | File | Channels | Trigger | Current gaps |
| --- | --- | --- | --- | --- |
| `ResetSellerPassword` | `app/Notifications/ResetSellerPassword.php` | mail | Seller password reset | Not queued. Uses translated Markdown mail view. |
| `OrderStatusChanged` | `app/Notifications/OrderStatusChanged.php` | mail | `ChangeOrderStatusAction` after transaction | Not queued. No database channel. Uses old flat keys in code while nested keys already exist in lang files. Action URL goes to `/`, not the related order. |
| WireUI toast notifications | `app/Livewire/Concerns/InteractsWithWireUi.php` and layouts | browser toast only | CRUD/order UI actions | Ephemeral only; not stored, not cross-page, not unread/read. |
| Dashboard placeholder notifications | `resources/views/frontend/buyer/dashboard/index.blade.php` | Blade placeholder text | none | Static commented/placeholder content, not connected to data. |

## Channels In Use Now

- Mail: yes, for seller password reset and order status changed.
- Database: no active Laravel database notifications in the live schema.
- Broadcast/realtime: no app-level broadcast notification support was found.
- UI toast: yes, WireUI toasts are mounted through `<x-notifications>` in frontend/backend layouts.

## Database Notifications Status

Live schema check: no `notifications` table and no `user_notifications` table currently exists.

Pending dirty files:

- `app/Models/Notification.php`
- `database/migrations/2026_06_07_171238_create_notifications_table.php`
- `database/factories/NotificationFactory.php`

These use a custom `user_notifications` table tied to `App\Models\User`. That does not fit the active multi-guard architecture because buyers, sellers, and admins authenticate from separate model classes and tables. The replacement should be Laravel's standard polymorphic `notifications` table.

## Email And Queue Status

- `config/mail.php` default mailer is `smtp`.
- `config/queue.php` default queue connection is `sync`.
- Queue connection `database` exists in config, but `jobs` table is not present in the live schema.
- Existing notifications do not implement `ShouldQueue`.
- Queue `after_commit` is `false`; notification dispatches that may happen near transactions should call `afterCommit()` or run after the transaction completes.

## Event And Listener Status

- `app/Events` and `app/Listeners` directories are not present.
- `EventServiceProvider` only maps `Registered` to email verification.
- Existing order notification flow is action-driven through `ChangeOrderStatusAction`, not event/listener-driven.

## Important Actions That Currently Do Not Notify Anyone

| Action | Current file/flow | Missing recipient(s) |
| --- | --- | --- |
| Buyer checkout creates order | `app/Livewire/Frontend/Buyer/Cart/Index::checkout()` | Buyer order-created confirmation and seller new-order notification. |
| Checkout auto-accepts order | same checkout method | Buyer/seller do not get a proper status-change/history notification because it bypasses `ChangeOrderStatusAction`. |
| Product stock crosses low/zero threshold | checkout decrement and product edits | Seller low-stock/out-of-stock notification. |
| Admin toggles product active/inactive | `app/Livewire/Backend/Products/Edit::save()` | Seller product approved/rejected notification. |
| Seller creates/updates product | `app/Livewire/Frontend/Seller/Products/Create/Edit` | Admin moderation-required notification when product is inactive or newly submitted for review. |
| Product delete/restore/force-delete | `app/Livewire/Backend/Products/Index` | Seller product moderation/admin-decision notification where useful. |
| Buyer/seller account activation or verification changes | admin buyer/seller forms | No notification currently. Scope optional; not needed for first marketplace notification architecture unless approval workflow is clarified. |
| Review created | no active UI route/action | Should notify seller only after a real review creation flow exists. |
| Message sent | no message model/table/route | Not supported yet. |
| Dispute/refund/report opened or updated | no dedicated model/table/route | Not supported yet. Seller transactions have `refund` text values, but no refund workflow. |

## Who Should Receive Each Real Notification

| Event | Buyer | Seller | Admin |
| --- | --- | --- | --- |
| Order created | Creator buyer | Sellers with items in the order | no |
| New order for seller | no | Sellers with items in the order | no |
| Order status changed | Buyer unless buyer caused the change | Sellers with items unless same seller caused the change | Admin only when admin/system alert is useful |
| Buyer cancelled order | no self-notification | Sellers with items | no |
| Buyer confirmed delivery/completed | no self-notification | Sellers with items | no |
| Product approved | no | Product seller | no |
| Product rejected/inactivated by admin | no | Product seller | no |
| Product requires moderation | no | no | Admins |
| Product low stock/out of stock | no | Product seller | no |
| Review created | no | Product seller | Admin only if review moderation is introduced |

## Files That Need Changes

Foundation:

- Replace `app/Models/Notification.php` and `database/factories/NotificationFactory.php` with Laravel database notification usage or remove the custom model from active app logic.
- Replace `database/migrations/2026_06_07_171238_create_notifications_table.php` with standard `notifications` table.
- Add marketplace notification classes under `app/Notifications/Marketplace`.
- Add a small dispatch action/service under `app/Actions/Notifications` to keep payloads and duplicate checks consistent.

Triggers:

- `app/Livewire/Frontend/Buyer/Cart/Index.php`
- `app/Actions/Orders/ChangeOrderStatusAction.php`
- `app/Livewire/Backend/Products/Edit.php`
- `app/Livewire/Backend/Products/Index.php`
- `app/Livewire/Frontend/Seller/Products/Create.php`
- `app/Livewire/Frontend/Seller/Products/Edit.php`

UI:

- `routes/buyer.php`, `routes/seller.php`, `routes/admin.php`
- New Livewire components for buyer/seller/admin notification list pages.
- New reusable Blade components for notification dropdown/recent notification list.
- `resources/views/layouts/frontend/header.blade.php`
- `resources/views/layouts/backend/navigation.blade.php`
- Buyer, seller, and admin dashboard components/views.

Translations/docs/seeders:

- `lang/en.json`, `lang/lt.json`
- `database/seeders/DatabaseSeeder.php`
- New demo notification seeder.
- `docs/notifications.md`, `README.md`, `CHANGELOG.md`

Tests:

- New notification feature tests under `tests/Feature/Marketplace` or `tests/Feature/Notifications`.
- Translation test update for new keys.

## Missing Tests

Required new coverage:

- Buyer receives database notification after order creation.
- Seller receives database/email notification after new order.
- Buyer receives notification after order status change by seller/admin.
- Seller receives notification after buyer cancellation/completion.
- Product approval and rejection notify seller.
- Product requiring moderation notifies admin.
- Low stock and out-of-stock notifications notify seller once per threshold.
- Notification payload contains title/message keys, related entity type/id, URL, and status when useful.
- Full notification list filters by current guard user.
- Mark single notification as read.
- Mark all notifications as read.
- Buyer cannot mark another buyer/seller/admin notification as read.
- Seller cannot mark another seller notification as read.
- Admin notification access is protected.
- Important notifications use mail channel and are queueable.
- Translation keys exist in all configured locales and raw keys are not rendered.

Conditional TODO coverage:

- Message notification tests after message/conversation workflow exists.
- Review notification tests after review creation UI/action exists.
- Dispute/refund/report tests after those modules exist.

## UI Pages That Need Notification Display

- Global frontend header for buyer/seller unread count and dropdown.
- Global backend/admin navigation unread count and dropdown.
- Buyer dashboard recent notifications.
- Seller dashboard recent notifications.
- Admin dashboard important admin alerts.
- Buyer full notification list page.
- Seller full notification list page.
- Admin full notification list page.

## Proposed Architecture

Use one Laravel notification architecture:

- Built-in `notifications` table with polymorphic `notifiable_type` and `notifiable_id`.
- Notification classes use `database` for in-app notifications and `mail` for important events.
- Notification classes implement `ShouldQueue` and use `Queueable`; production requires a real queue worker if mail volume grows.
- No broadcast channel for now because the app has no realtime notification infrastructure.
- Notification payloads store:
  - `title_key`
  - `message_key`
  - translated params that are safe to store
  - `related_type`
  - `related_id`
  - `url`
  - `status`
  - `icon`
- Notification list UI renders from the stored payload without extra relationship queries.
- Actions and domain flows trigger notifications only after successful persistence.
- Duplicate low-stock notifications are prevented by checking existing unread database notifications for the same product/type/status.

## Naming Standard

Use clear marketplace names under `App\Notifications\Marketplace`:

- `OrderCreatedNotification`
- `NewOrderForSellerNotification`
- `OrderStatusChangedNotification`
- `ProductModerationRequiredNotification`
- `ProductApprovedNotification`
- `ProductRejectedNotification`
- `LowStockNotification`

Unsupported until real workflows exist:

- `NewReviewNotification`
- `NewMessageNotification`
- `DisputeOpenedNotification`
- `RefundRequestedNotification`

## Cleanup Strategy

Keep notifications by default in this implementation. Do not delete old notifications automatically yet. Notifications are not audit logs, and audit-sensitive moderation/order history remains in domain tables such as order status history.

Future cleanup can delete read notifications after 90 days through a scheduled command once retention requirements are confirmed.

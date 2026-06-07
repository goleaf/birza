# Buyer Order Tracking And Delivery Timeline - Pre-Implementation Report

Date: 2026-06-07
Scope: buyer/seller/admin order tracking, delivery timeline, order events, shipping updates, notifications, audit logging, translations, tests, seeders, and docs.

## Foundation Gate

The requested feature is not allowed to ship until the project foundation is stable. Current verification shows the order/status-specific foundation is mostly healthy, but the full foundation gate is not green yet.

- `php artisan test --compact tests/Feature/Marketplace/OrderStatusWorkflowFeatureTest.php tests/Unit/Policies/OrderPolicyTest.php tests/Feature/Notifications/MarketplaceNotificationSystemTest.php tests/Feature/Translations/TranslationFilesTest.php tests/Feature/Marketplace/PerformanceQueryBudgetTest.php` passed: 27 tests, 893 assertions.
- `php artisan test --compact` failed: 535 passed, 2 failed.
- The failures are both caused by `database/seeders/Demo/DemoNotificationSeeder.php` using missing route `backend.products.show`; the real route prefix is `admin.products.show`.
- GSD project state still points to `$gsd-execute-phase 1` for the platform upgrade, and `.planning/PROJECT.md` currently says new marketplace business features are out of scope for the active UI modernization milestone.

Because the failing foundation issue is a small seeder route mismatch, the safe path is:

1. Fix the seeder route mismatch.
2. Re-run the full suite.
3. Implement order tracking only if the full suite is green.

## Current Order Tracking State

### Status History

Order status history already exists.

- Table: `order_status_histories`
- Model: `App\Models\OrderStatusHistory`
- Factory: `database/factories/OrderStatusHistoryFactory.php`
- Relationship: `Order::statusHistory()`
- Indexes: `order_id + created_at`, `new_status + created_at`, `changed_by_role + created_at`

The table stores:

- `order_id`
- `old_status`
- `new_status`
- `changed_by_user_id`
- `changed_by_role`
- `reason`
- `note`
- `created_at`

It does not store public/internal note separation, event type, title/description keys, metadata, tracking number, carrier, estimated delivery date, shipped timestamp, or delivered timestamp.

### Tracking Events

Tracking events do not yet exist as a dedicated table or model.

Buyer and seller detail pages currently render `Order::lifecycleTimeline()`, which is a synthetic status milestone timeline generated from the current order status and timestamps. It is not an actual event history.

Admin order detail currently renders the actual `statusHistory` rows and the latest audit logs.

### Status Storage

Order lifecycle status is stored on `orders.status` and cast to `App\Enums\OrderStatus`.

Payment status is stored separately on `orders.payment_status` and cast to `App\Enums\OrderPaymentStatus`.

Supported lifecycle statuses:

- `pending`
- `accepted`
- `rejected`
- `processing`
- `shipped`
- `delivered`
- `completed`
- `cancelled`
- `refunded`
- `disputed`

There is no separate refund request/approval workflow and no dispute resolution workflow table. The feature should not invent those.

### Seller Status Updates

Sellers update order status from `App\Livewire\Frontend\Seller\Orders\Show`.

The component uses `App\Actions\Orders\ChangeOrderStatusAction`, which:

- validates allowed transitions,
- checks actor/ownership through `Order::isManageableBy()` and `OrderPolicy::changeStatus`,
- creates `OrderStatusHistory`,
- writes `audit_logs`,
- sends `OrderStatusChangedNotification`,
- avoids notifying the actor.

### Buyer Status Visibility

Buyers see:

- order detail at `buyer.orders.show`,
- current lifecycle steps,
- a synthetic timeline,
- items/sellers,
- cancel action if the order can transition to cancelled.

Buyers do not currently see tracking number, carrier, estimated delivery date, shipped timestamp, delivered timestamp, or seller shipping notes.

### Seller Status Visibility

Sellers see:

- order detail at `seller.orders.show`,
- status transition buttons allowed for their role,
- a seller comment field used as status-change note,
- current lifecycle steps,
- a synthetic timeline,
- their own order items and bundles.

Sellers do not currently have a shipping/tracking update form.

### Admin Status Visibility

Admins see:

- order detail at `admin.orders.show`,
- buyer/payment details,
- status change form requiring reason,
- actual status history,
- recent audit logs.

Admins do not currently see a unified public/internal business timeline separate from status history and audit logs.

## Shipping And Delivery Fields

The `orders` table currently has:

- `shipping_address_snapshot`
- `billing_address_snapshot`
- `delivery_method`

The `orders` table does not have:

- tracking number
- carrier / delivery provider
- estimated delivery date
- shipped at
- delivered at
- public shipping note
- internal shipping note

## Existing Notifications

Notifications are implemented through Laravel's standard `notifications` table and `App\Notifications\Marketplace`.

Existing order notifications:

- `OrderCreatedNotification`
- `NewOrderForSellerNotification`
- `OrderStatusChangedNotification`

Status changes currently notify buyer/sellers through `ChangeOrderStatusAction`. There are no tracking-specific notification classes yet.

## Existing Audit Logging

Audit logging exists.

- Table: `audit_logs`
- Service: `App\Services\AuditLogService`
- Admin action table: `admin_actions`
- Admin audit UI: `admin.audit.index` and `admin.audit.show`

`ChangeOrderStatusAction` already logs `order.status_changed` and specific actions for cancelled/refunded/disputed status changes. It does not yet log specific shipped/delivered actions or tracking-number changes.

## Authorization State

Relevant policies are registered:

- `OrderPolicy`
- `OrderStatusHistoryPolicy`
- `AuditLogPolicy`
- `NotificationPolicy`

`OrderPolicy` currently has:

- `viewAny`
- `view`
- `create`
- `update`
- `delete`
- `restore`
- `forceDelete`
- `cancel`
- `changeStatus`
- `manage`

Missing tracking/timeline methods:

- `viewTimeline`
- `viewInternalTimeline`
- `addShippingUpdate`
- `addTrackingNumber`
- `markAsShipped`
- `markAsDelivered`
- `addInternalNote`

## Pages That Need Timeline UI

Required pages:

- Buyer detail: `resources/views/frontend/buyer/orders/show.blade.php`
- Seller detail: `resources/views/frontend/seller/orders/show.blade.php`
- Admin detail: `resources/views/backend/orders/show.blade.php`

Supporting Livewire components:

- `App\Livewire\Frontend\Buyer\Orders\Show`
- `App\Livewire\Frontend\Seller\Orders\Show`
- `App\Livewire\Backend\Orders\Show`

## Database Decision

Use both tables, with distinct responsibilities:

- `order_status_histories`: immutable status transition history used by the status workflow.
- `order_events`: business/user-visible timeline events used for buyer/seller/admin timeline rendering.

Reason: status history is enough for lifecycle status changes, but not enough for tracking-number updates, public/internal note separation, title/description translation keys, and metadata. Extending `order_status_histories` to store non-status events would make the name and semantics misleading.

Required new table:

- `order_events`

Required new order tracking fields:

- `orders.tracking_number`
- `orders.carrier_name`
- `orders.estimated_delivery_date`
- `orders.shipped_at`
- `orders.delivered_at`

## Files Needing Changes

Core:

- `app/Models/Order.php`
- `app/Models/OrderEvent.php`
- `app/Models/OrderStatusHistory.php` only if needed for relationships or mapping
- `app/Enums/OrderStatus.php`
- new order event type support

Actions:

- `app/Actions/Orders/CreateOrderEventAction.php`
- `app/Actions/Orders/GetOrderTimelineAction.php`
- `app/Actions/Orders/AddSellerShippingUpdateAction.php`
- existing `app/Actions/Orders/ChangeOrderStatusAction.php`
- existing `app/Actions/Cart/CreateOrdersFromCartAction.php`

UI:

- buyer/seller/admin order show components and views
- `resources/views/components/ui/timeline.blade.php`

Notifications:

- new tracking notification class
- existing order status notification key selection for shipped/delivered

Audit:

- `ChangeOrderStatusAction`
- shipping update action

Data:

- migrations for `order_events` and order tracking columns
- `database/factories/OrderEventFactory.php`
- `database/seeders/Demo/DemoOrderSeeder.php`
- `database/seeders/Demo/DemoNotificationSeeder.php` foundation route fix

Tests:

- new order tracking/timeline feature test
- update translation required keys
- possibly update performance query budget for order detail timeline rendering

Docs:

- `README.md`
- `CHANGELOG.md`
- `docs/`

## Privacy And Security Risks

- Buyer must not see `internal_note`, audit metadata, IP addresses, user agents, actor ids, or unrelated seller/buyer data.
- Seller must not see timelines for orders without their own items.
- Seller must not update tracking on another seller's order.
- Seller must not update tracking for terminal statuses such as completed, cancelled, refunded, or rejected.
- Tracking number, carrier, and notes must reject unsafe HTML.
- Metadata must be sanitized and must not store payment card data, private tokens, bank accounts, or raw request data.
- Admin status/tracking changes must remain audited.
- Livewire action parameters are untrusted and must be authorized server-side.

## Test Plan

Minimum required feature tests:

- checkout/order creation creates an `order_events` created event.
- status changes create `order_events` lifecycle events.
- buyer can view own public timeline.
- buyer cannot view another buyer's timeline.
- seller can view own order timeline.
- seller cannot view another seller's timeline.
- admin can view full/internal timeline.
- buyer/seller do not see internal notes.
- seller can add tracking to own allowed order.
- seller cannot add tracking to another seller's order.
- seller cannot add tracking to cancelled/completed/refunded/rejected orders.
- tracking number validation rejects HTML/unsafe values.
- carrier validation rejects HTML.
- estimated delivery date validation rejects past dates.
- tracking update notifies buyer once.
- shipped/delivered status updates notify buyer through status notification.
- tracking/status updates write audit logs.
- translation keys exist in English and Lithuanian.
- timeline events render oldest-first.
- timeline rendering does not expose private fields.
- order detail query count stays bounded.

## Recommendation

Do not implement the feature until the seeder route failure is fixed and the full suite passes. After that, implement the feature as a focused `order_events` timeline plus order tracking fields, integrated into the existing status action, notification system, policies, audit log, factories, seeders, translations, docs, and tests.

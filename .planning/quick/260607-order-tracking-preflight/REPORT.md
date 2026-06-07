# Buyer Order Tracking And Delivery Timeline Preflight

Date: 2026-06-07
Scope requested: exactly one new marketplace feature, buyer order tracking and delivery timeline.
Outcome: implementation is deferred until the project foundation is stable.

## Foundation Gate

The requested feature is explicitly gated behind a stable foundation:

- role architecture
- UI/design system
- database architecture
- factories and seeders
- feature tests
- security hardening
- performance audit
- documentation
- final cleanup

The gate is not currently satisfied.

Evidence:

- GSD state shows Phase 1, "Platform Upgrade", at 0% progress and still awaiting approved Tailwind/daisyUI dependency decisions.
- The worktree is heavily dirty, including many order/status/messaging/audit/docs/test files.
- The previous full suite run in this session failed: 524 tests passed and 1 failed. The failing test was `Tests\Feature\Marketplace\MessagingFeatureTest`, where `/` returned 500 because the in-memory test database did not have `users_sellers` while welcome-page data counted sellers.
- Several timeline-adjacent files are currently uncommitted, including `OrderStatus`, `OrderStatusHistory`, `ChangeOrderStatusAction`, order Livewire pages, routes, translations, factories, seeders, docs, and tests.

Decision: do not implement the new tracking feature yet. This report records the current state and the exact implementation surface to use when the foundation gate is closed.

## Current Order Tracking Surface

### Whether order status history already exists

Yes.

The current schema has `order_status_histories` with:

- `id`
- `order_id`
- `old_status`
- `new_status`
- `changed_by_user_id`
- `changed_by_role`
- `reason`
- `note`
- `created_at`

Indexes already present:

- `order_status_histories_order_id_created_at_index`
- `order_status_histories_new_status_created_at_index`
- `order_status_histories_changed_by_role_created_at_index`

Code already present:

- `app/Models/OrderStatusHistory.php`
- `database/migrations/2026_06_07_171818_create_order_status_histories_table.php`
- `database/factories/OrderStatusHistoryFactory.php`
- `app/Policies/OrderStatusHistoryPolicy.php`
- `Order::statusHistory()`
- `ChangeOrderStatusAction` creates history rows.
- `CreateOrdersFromCartAction` creates the initial pending history row.

### Whether tracking events already exist

Partial.

There is no `order_events` table and no generic tracking-event model. The persisted business timeline is currently `order_status_histories`.

Buyer and seller pages render a computed timeline from `Order::lifecycleTimeline()`, but that timeline is not backed by the full persisted history. It shows:

- order placed
- current status
- next milestone when applicable

Admin order detail shows the persisted `statusHistory` list.

### How order statuses are stored now

Orders store lifecycle status on `orders.status` and payment state on `orders.payment_status`.

Both are enum-cast:

- `App\Enums\OrderStatus`
- `App\Enums\OrderPaymentStatus`

Supported lifecycle statuses:

- pending
- accepted
- rejected
- processing
- shipped
- delivered
- completed
- cancelled
- refunded
- disputed

Direct status mutation is guarded in `Order::booted()`. Status changes are expected to go through `App\Actions\Orders\ChangeOrderStatusAction`.

### How sellers update order status now

Seller order detail page:

- Route: `seller.orders.show`
- Component: `App\Livewire\Frontend\Seller\Orders\Show`
- View: `resources/views/frontend/seller/orders/show.blade.php`

The seller can choose allowed next statuses from `Order::availableTransitionsFor(Auth::guard('seller')->user())`.

`updateStatus()` validates a nullable `comment`, authorizes `changeStatus`, and calls `ChangeOrderStatusAction`.

Seller comments are written to `order_status_histories.note`.

### How buyers see order status now

Buyer order detail page:

- Route: `buyer.orders.show`
- Component: `App\Livewire\Frontend\Buyer\Orders\Show`
- View: `resources/views/frontend/buyer/orders/show.blade.php`

The buyer sees:

- payment status badge
- lifecycle steps component
- computed `x-ui.timeline`
- order item table
- seller contact buttons
- cancel button when the order status allows cancellation

The buyer does not currently see the persisted `order_status_histories` rows.

### How admins see order status now

Admin order detail page:

- Route: `admin.orders.show`
- Component: `App\Livewire\Backend\Orders\Show`
- View: `resources/views/backend/orders/show.blade.php`

The admin sees:

- buyer/payment summary
- admin status-change form
- persisted status history
- latest 10 audit logs
- order bundles/items

Admin status changes require a reason and can include a note.

### Whether shipping/delivery fields exist

Partial.

`orders` currently has:

- `shipping_address_snapshot`
- `billing_address_snapshot`
- `delivery_method`

There are no dedicated tracking/shipment fields found in the current schema:

- no `tracking_number`
- no `carrier`
- no `estimated_delivery_date`
- no `shipped_at`
- no `delivered_at`

### Whether tracking number exists

No.

No schema column, model cast, Livewire property, form field, validation rule, notification payload, or translation key for tracking number was found.

### Whether carrier/delivery provider exists

No.

`delivery_method` exists, but carrier/provider does not.

### Checkout and order creation logic

`App\Actions\Cart\CreateOrdersFromCartAction`:

- validates buyer ownership, shipping address, payment method, stock, bundles, discounts, and promo codes
- groups cart lines by `seller_id`
- creates one `orders` row per seller
- snapshots buyer address fields and item prices
- decrements stock
- creates initial `order_status_histories` row
- logs checkout/order audit events
- sends buyer and seller marketplace notifications after commit

This one-order-per-seller design is helpful for tracking because seller shipping updates can be stored on the order without splitting a multi-seller fulfillment.

## Existing Business Rules

### Status transitions

`OrderStatus::allowedNextStatuses()` currently allows:

- pending -> accepted, rejected, cancelled
- accepted -> processing, cancelled, refunded, disputed
- processing -> shipped, cancelled, refunded, disputed
- shipped -> delivered, refunded, disputed
- delivered -> completed, refunded, disputed
- disputed -> completed, refunded, cancelled
- rejected/completed/cancelled/refunded -> no further transitions

### Actor permissions

`OrderStatus::allowedActorRoles()` currently allows:

- system/admin for pending
- seller/admin for accepted, rejected, processing, shipped, delivered
- buyer/admin for completed
- buyer/seller/admin for cancelled and disputed
- admin for refunded

`OrderPolicy::changeStatus()` also checks:

- actor role resolution
- order ownership/manageability
- valid transition
- next-status actor permission

### Revenue/payment side effects

`ChangeOrderStatusAction` currently maps payment status from lifecycle status. It also:

- credits sellers on pending -> accepted
- restores stock for rejected/cancelled from pending/accepted/processing
- debits sellers when a revenue-recognized order becomes cancelled/refunded

Any tracking implementation must not bypass this action for status-changing operations.

## Notification System

The project uses Laravel notifications through:

- `App\Actions\Notifications\SendMarketplaceNotificationAction`
- `App\Notifications\Marketplace\MarketplaceNotification`
- `OrderCreatedNotification`
- `NewOrderForSellerNotification`
- `OrderStatusChangedNotification`

`OrderStatusChangedNotification` already sends translated semantic payloads and role-aware URLs for buyer, seller, and admin.

Missing for this feature:

- tracking-added notification
- shipped/delivered-specific notification copy if generic status-changed copy is not enough
- duplicate prevention rules for tracking-specific notifications

The `notifications` table already has useful indexes:

- `notifiable_type, notifiable_id`
- `notifiable_type, notifiable_id, read_at`
- `notifiable_type, notifiable_id, created_at`

## Audit Log System

Audit logs already exist and are separate from business timeline data.

Current audited order actions include:

- `cart.checked_out`
- `order.created`
- `order.status_changed`
- `order.cancelled`
- `order.refunded`
- `order.dispute_opened`

Docs explicitly state:

- order status history is the business timeline
- audit logs are the security/dispute trace
- the two should not replace each other

Missing for tracking:

- `order.tracking_added`
- `order.tracking_changed`
- `order.marked_shipped`
- `order.marked_delivered`
- possibly `order.internal_note_added` if an admin-only note flow is added

`AuditLogService` already sanitizes sensitive keys including tokens, secrets, payment card data, bank accounts, and raw uploaded contents.

## UI Components To Reuse

Frontend buyer/seller:

- `x-ui.header`
- `x-ui.steps`
- `x-ui.timeline`
- `x-ui.card`
- `x-ui.button`
- `x-ui.badge`
- `x-backend.confirm-modal`

Admin:

- `x-mary-header`
- `x-mary-card`
- `x-mary-form`
- `x-mary-list-item`
- `x-mary-alert`
- `x-backend.audit-history`

`x-ui.timeline` already renders a Mary timeline item list and can be reused after moving timeline item construction to backend actions/view data.

## Database Decision

Preferred first move after foundation stability:

Reuse `order_status_histories` as the persisted business timeline for status events.

Reason:

- table already exists
- docs already define it as the business timeline
- checkout and status-change actions already write it
- admin UI already renders it
- indexes already support order timeline reads

Do not add `order_events` unless the product decision requires timeline events that are not status transitions, such as "tracking number added" without changing order status, public/internal notes independent of status, or richer metadata per event.

If tracking updates must be visible as distinct timeline events, two safe options exist:

1. Extend `order_status_histories` carefully with explicit public/internal fields.
2. Add `order_events` as a broader event table and document that `order_status_histories` remains the status-change source while `order_events` is the user-facing event stream.

Avoid having both tables represent the same status event without a single writer, because that would create duplicate or divergent timelines.

## Needed Database Changes After Gate

If shipping/tracking details are added to `orders`, likely fields:

- `tracking_number` nullable string, max 100
- `carrier_name` nullable string, max 120
- `estimated_delivery_date` nullable date
- `shipped_at` nullable timestamp
- `delivered_at` nullable timestamp

Needed indexes if filter/sort is introduced:

- `orders.status, created_at` already exists.
- `orders.buyer_id, status, created_at` already exists.
- `order_items.seller_id, order_id` already exists.
- `order_status_histories.order_id, created_at` already exists.
- Add tracking indexes only if a query needs them. Do not add random indexes for fields that are rendered only after loading one order by id.

If a separate event table is chosen, use:

- `order_id, created_at`
- `event_type`
- `actor_id`

## Expensive Query Risks

Current risks:

- Buyer and seller order show pages load order items/bundles/images and then build a computed timeline. They do not load full persisted history today, so adding history must eager-load it.
- Seller order show currently runs a separate `OrderItem` query scoped by seller and a separate bundles query. That is acceptable for one detail page but must not become a looped history lookup.
- Admin order show loads status history and then separately loads latest 10 audit logs. Keep audit logs admin-only and limited.
- `Order::hasSellerItems()` uses `exists()` when `items` is not loaded. This is fine in authorization, but high-volume list rendering should keep relationships eager-loaded.
- `ChangeOrderStatusAction::sellerTotals()` groups loaded items in PHP after loading only one locked order. This is acceptable for one order, not for dashboards or lists.

Implementation must avoid:

- querying actors inside timeline item loops
- loading all order histories globally
- rendering audit logs on buyer/seller pages
- resolving buyer/seller/admin actor names from only `changed_by_user_id` without considering role ambiguity

## Privacy And Security Risks

Buyer-safe timeline must never expose:

- admin reason/internal notes
- audit metadata
- IP address or user agent
- internal actor ids
- another buyer's order data
- another seller's private data
- payment/card/token/secret data

Seller-safe timeline must not expose:

- admin-only internal notes unless explicitly made seller-visible
- another seller's order rows
- another seller's tracking updates if multi-seller orders ever reappear

Admin can see full status history and audit data only through admin-authenticated routes.

Current actor storage risk:

- `order_status_histories.changed_by_user_id` is paired with `changed_by_role`, not `actor_type`.
- Because buyer, seller, and admin ids live in different tables, actor display must resolve by role or avoid showing names in public timeline.

Livewire risk:

- Livewire action parameters are mutable on the client. Existing status update methods authorize server-side. Tracking/shipping methods must follow the same pattern.

## Files That Need Changes After Gate

Likely backend/model/action files:

- `app/Models/Order.php`
- `app/Models/OrderStatusHistory.php`
- `app/Policies/OrderPolicy.php`
- `app/Policies/OrderStatusHistoryPolicy.php`
- `app/Actions/Orders/ChangeOrderStatusAction.php`
- new `app/Actions/Orders/GetOrderTimelineAction.php`
- new `app/Actions/Orders/AddOrderTrackingNumberAction.php` or `AddSellerShippingUpdateAction.php`
- maybe new notification classes under `app/Notifications/Marketplace`
- migrations for tracking columns or an event table

Likely Livewire files:

- `app/Livewire/Frontend/Buyer/Orders/Show.php`
- `app/Livewire/Frontend/Seller/Orders/Show.php`
- `app/Livewire/Backend/Orders/Show.php`

Likely Blade/component files:

- `resources/views/frontend/buyer/orders/show.blade.php`
- `resources/views/frontend/seller/orders/show.blade.php`
- `resources/views/backend/orders/show.blade.php`
- `resources/views/components/ui/timeline.blade.php`
- maybe a new `resources/views/components/order/tracking-summary.blade.php`

Likely support files:

- `database/factories/OrderFactory.php`
- `database/factories/OrderStatusHistoryFactory.php`
- `database/seeders/Demo/DemoOrderSeeder.php`
- `lang/en.json`
- `lang/lt.json`
- `README.md`
- `CHANGELOG.md`
- `docs/architecture.md`
- `docs/audit-log-system.md`
- `docs/notifications.md`
- `docs/testing.md`

## Tests Needed After Gate

Feature/action tests:

- order creation creates an initial timeline/history event
- status change creates a history/timeline event
- buyer can view own public timeline
- buyer cannot view another buyer's timeline
- seller can view own order timeline
- seller cannot view another seller's timeline
- admin can view internal timeline
- buyer does not see internal reason/note
- seller does not see admin-only internal note unless policy allows it
- seller can add tracking number to own order
- seller cannot add tracking number to another seller's order
- seller can mark own order as shipped when status allows it
- seller cannot mark cancelled/refunded/completed order as shipped
- tracking number validation rejects unsafe input and overlong values
- carrier validation rejects unsafe input and overlong values
- estimated delivery date validation handles invalid/past dates according to chosen rule
- buyer receives notification when tracking is added
- buyer receives notification when order is shipped
- audit log is created for sensitive tracking changes
- translations exist in all supported languages
- timeline renders persisted events in chronological order
- timeline does not expose private fields
- query count remains bounded on order detail pages with many history rows

Recommended existing test files to extend:

- `tests/Feature/Marketplace/OrderStatusWorkflowFeatureTest.php`
- `tests/Feature/Controllers/Frontend/Buyer/OrderControllerTest.php`
- `tests/Feature/Controllers/Frontend/Seller/OrderControllerTest.php`
- `tests/Feature/Controllers/Backend/OrderControllerTest.php`
- `tests/Feature/Notifications/MarketplaceNotificationSystemTest.php`
- `tests/Feature/Marketplace/AuditLoggingFeatureTest.php`
- `tests/Feature/Marketplace/PerformanceQueryBudgetTest.php`
- `tests/Unit/Policies/OrderPolicyTest.php`
- `tests/Unit/Models/OrderTest.php`
- `tests/Feature/Translations/TranslationFilesTest.php`

## Translation State

Supported translation files:

- `lang/en.json`
- `lang/lt.json`

Existing timeline/order status keys include:

- `orders_order_timeline`
- `orders_timeline_subtitle`
- `orders_timeline_order_placed_title`
- `orders_timeline_order_placed_description`
- `orders_timeline_waiting_confirmation_description`
- `orders_timeline_processing_next_description`
- `orders_timeline_shipped_next_description`
- `orders_timeline_delivered_next_description`
- `orders_timeline_completed_next_description`
- `orders.status.*`
- `notifications.orders.status_changed.*`

Missing tracking-specific keys:

- `orders.tracking.*`
- `orders.shipping.*`
- `orders.notes.public_note`
- `orders.notes.internal_note`
- `notifications.orders.tracking_added.*`
- shipped/delivered-specific notification keys if generic status-changed copy is not enough

## Documentation State

Existing docs already mention order status history and audit separation:

- `README.md`
- `docs/architecture.md`
- `docs/database.md`
- `docs/audit-log-system.md`
- `docs/notifications.md`
- `docs/testing.md`
- `docs/cart-checkout-workflow-2026-06-07.md`

Docs that would need updates after implementation:

- README feature list
- CHANGELOG Added section
- architecture order lifecycle section
- audit-log order action list
- notifications order notification list
- testing docs
- maybe release notes if current release workflow requires it

Note: `docs/database-structure-audit-2026-06-07.md` still contains older findings saying order address snapshots are missing. The current schema has `shipping_address_snapshot` and `billing_address_snapshot`, so update docs carefully instead of copying stale audit language.

## Recommended Implementation Shape After Gate

1. Keep `ChangeOrderStatusAction` as the only status-changing writer.
2. Create `GetOrderTimelineAction` to map persisted `OrderStatusHistory` rows into buyer/seller/admin-safe timeline DTO arrays.
3. Eager-load status history on buyer/seller/admin show components with selected columns.
4. Replace buyer/seller computed timeline data with persisted history-backed data plus next-milestone hint.
5. Add tracking columns to `orders` only if the accepted scope includes actual carrier/tracking fields.
6. Add `AddSellerShippingUpdateAction` and route all tracking updates through policy, validation, audit, notification, and timeline writing.
7. Keep audit logs admin-only and limited.
8. Extend demo seeders with no-history, full-history, shipped, delivered, cancelled, and tracking scenarios.
9. Add privacy and query-budget tests before wiring more UI.

## Do Not Do Yet

- Do not create a new `order_events` table while `order_status_histories` is still unsettled in the dirty tree.
- Do not add tracking fields to `orders` until the current order/status migration set is committed and tests are green.
- Do not show seller/admin notes to buyers.
- Do not query status history from Blade.
- Do not create fake delivery statuses beyond the current `OrderStatus` enum.
- Do not add shipping providers/carrier integrations as part of this feature.
- Do not log every timeline view.
- Do not make a commit for this feature while the implementation gate is blocked.

# Marketplace Messaging System Audit - 2026-06-07

This report was created before implementing buyer and seller private messaging.
It is based on the current working tree, Laravel Boost application/schema data,
route files, models, Livewire components, policies, seeders, tests, translations,
README, changelog, and docs.

## Existing Messaging State

| Item | Status | Evidence |
| --- | --- | --- |
| Private messaging feature | Missing | No `Conversation`, `Message`, inbox, chat, or thread models/components/routes exist. Only Laravel mail templates and flash-message components match message-related filenames. |
| Conversations table | Missing | Boost schema summary has no `conversations` table. |
| Messages table | Missing | Boost schema summary has no marketplace `messages` table. |
| Product questions | Exists | `product_questions` table, `ProductQuestion` model, buyer/guest question panel, seller inbox, admin moderation page, notifications, audits, seeders, and tests exist. |
| Order comments | Partial/non-messaging | Seller order status changes have a `comment` field for status history notes. There is no order comment/thread model and buyers do not have an order conversation surface. |
| Notifications | Exists | Standard Laravel `notifications` table, `App\Models\Notification`, `MarketplaceNotification`, buyer/seller/admin notification routes, dropdowns, list pages, seeders, docs, and tests exist. |
| Audit logs | Exists | `audit_logs` and `admin_actions` tables exist. `AuditLogService` sanitizes payloads and records actor/entity metadata. |
| Role architecture | Exists | Separate `admin`, `buyer`, and `seller` guards and user tables are registered with access gates and policies. |
| Translation system | Exists | `lang/en.json` and `lang/lt.json` are parity-tested. New visible messaging copy must be added to both. |

## Current Relevant Architecture

- Buyers use `App\Models\Users\Buyer` and `users_buyers`.
- Sellers use `App\Models\Users\Seller` and `users_sellers`.
- Admins use `App\Models\Users\Admin` and `users_admins`.
- Products belong to sellers through `products.seller_id`.
- Orders belong to buyers through `orders.buyer_id`.
- Sellers are related to orders through `order_items.seller_id`.
- Private pages are route-mounted Livewire components under grouped guard routes:
  - buyer: `routes/buyer.php`
  - seller: `routes/seller.php`
  - admin: `routes/admin.php`
- Business rules are usually placed in actions under `app/Actions`.
- Ownership and moderation rules are usually placed in policies under `app/Policies`.

## Where Messaging Should Be Added

| Area | Addition |
| --- | --- |
| Database | Add `conversations` and `messages` tables with buyer, seller, product, order, status, read, archive, and activity indexes. |
| Models | Add `App\Models\Conversation` and `App\Models\Message`. Add relationships to buyer, seller, product, order, latest message, and messages. |
| Enums | Add stable conversation status and message sender role enums or constants following existing enum style. |
| Actions | Add start, send, mark-read, archive, close, notification, and audit actions under `app/Actions/Messaging`. |
| Policies | Add `ConversationPolicy` and `MessagePolicy`, registered in `AuthServiceProvider`. |
| Buyer routes | Add authenticated buyer message list and detail routes in `routes/buyer.php`. |
| Seller routes | Add authenticated seller message list and detail routes in `routes/seller.php`. |
| Admin routes | Add read-only moderation routes in `routes/admin.php` only for authorized admins. |
| Product page | Add an authenticated buyer "contact seller" action to active public product detail pages. |
| Order pages | Add buyer and seller "message about order" actions on authorized order detail pages. |
| Notifications | Add a message notification class using the existing marketplace notification base. |
| Audit logs | Log conversation/message metadata only. Do not store full message body in audit payloads. |
| Seeders/factories | Add messaging factories and local demo messaging seeder data. |
| Tests | Add feature and policy tests for product/order starts, send/reply, ownership, read state, notifications, audit, translations, and escaping. |
| Docs | Update README, architecture, database, security, testing, seeders, notifications, changelog, and release notes. |

## Buyer Pages Needing Messaging

- Buyer inbox: `buyer.messages.index`.
- Buyer conversation detail: `buyer.messages.show`.
- Buyer product detail integration: `buyer.products.show` should start or reuse a product conversation.
- Buyer order detail integration: `buyer.orders.show` should start or reuse an order conversation for the order seller.
- Frontend header: add buyer messages link and unread count if the count can be queried efficiently.

## Seller Pages Needing Messaging

- Seller inbox: `seller.messages.index`.
- Seller conversation detail: `seller.messages.show`.
- Seller order detail integration: `seller.orders.show` should start or reuse an order conversation for the order buyer.
- Frontend header: add seller messages link and unread count if the count can be queried efficiently.

## Admin Moderation Visibility

Admin access should be policy-controlled and read-only for this feature. Admins should not casually inspect private messages from normal product/order pages. If moderation/support needs access, admins can use a dedicated backend messages page, and admin conversation views should be audited with metadata only.

## Tables Needed

### `conversations`

- `id`
- `buyer_id`
- `seller_id`
- `product_id` nullable
- `order_id` nullable
- `status`
- `last_message_at` nullable
- `buyer_archived_at` nullable
- `seller_archived_at` nullable
- `created_at`
- `updated_at`
- `deleted_at` nullable

Indexes:

- single-column: `buyer_id`, `seller_id`, `product_id`, `order_id`, `status`, `last_message_at`
- composite: `buyer_id, last_message_at`, `seller_id, last_message_at`
- duplicate-prevention indexes for buyer/seller/product and buyer/seller/order where supported safely

### `messages`

- `id`
- `conversation_id`
- `sender_id`
- `sender_role`
- `body`
- `read_at` nullable
- `edited_at` nullable
- `deleted_at` nullable
- `metadata` nullable JSON
- `created_at`
- `updated_at`

Indexes:

- single-column: `conversation_id`, `sender_id`, `read_at`, `created_at`
- composite: `conversation_id, created_at`, `conversation_id, read_at`

## Business Rules To Implement

- Buyer-seller messaging is the first supported private messaging flow.
- A buyer can start a conversation from an active public product owned by an active, verified seller.
- A buyer can start a conversation from their own order.
- A seller can reply to conversations tied to their own seller account.
- A seller can start/open an order conversation only for an order containing their own order items.
- A seller cannot message random buyers unless a conversation or seller-owned order exists.
- A buyer cannot message another buyer.
- A seller cannot access another seller's conversations.
- A buyer cannot access another buyer's conversations.
- Closed, blocked, or archived conversations cannot receive new messages unless a future explicit reopen rule is added.
- Messages are stored as plain text and rendered escaped. Raw HTML is not allowed.
- Email, phone, bank-account, and private profile fields must not be exposed through message payloads, notifications, or audit logs.

## Privacy Risks

- Private message bodies can contain contact details or sensitive order information.
- Admin visibility can become privacy-invasive if exposed without a dedicated moderation route and audit trail.
- Notifications can leak private content if the full message body is stored in notification data.
- Audit logs can leak sensitive content if full message bodies are logged.
- Product and order links can leak data if policies are not checked on Livewire mount and actions.

Mitigation:

- Store notification previews only.
- Audit metadata only, not full bodies.
- Escape message body output.
- Use policies on routes, Livewire mount, and Livewire actions.
- Filter every query through buyer/seller ownership scopes.

## Abuse Risks

- Buyers could spam sellers through product contact forms.
- Sellers could attempt to message buyers outside valid order/conversation context.
- Users could submit HTML/script payloads.
- Users could send empty or very long messages.
- Cross-guard route access could expose conversations if policies resolve the wrong guard.

Mitigation:

- Require authenticated, active, verified buyer/seller accounts.
- Validate required trimmed body text and maximum length.
- Reuse existing conversations instead of creating duplicate product/order threads.
- Add rate limiting later if abuse appears; this first version should document it as a future hardening item.
- Add focused forbidden-access tests for cross-buyer and cross-seller cases.

## Tests Needed

- Buyer can start conversation from active product.
- Buyer cannot start conversation from inactive product.
- Buyer cannot start conversation with inactive/unverified seller.
- Buyer can start/open order conversation for own order.
- Buyer cannot message about another buyer's order.
- Seller can start/open order conversation for own seller order.
- Seller cannot message about another seller's order.
- Buyer can view only own conversations.
- Seller can view only own conversations.
- Admin can view only through moderation policy.
- Buyer and seller can send messages in their own active conversation.
- Cross-buyer and cross-seller send attempts are forbidden.
- Closed conversation blocks new messages.
- Message body is required, trimmed, length-limited, and escaped in UI.
- Opening conversation marks other participant messages as read.
- Buyer and seller unread counts are correct.
- Recipient notification is sent; sender notification is not sent.
- Conversation linked to product works.
- Conversation linked to order works.
- Audit log metadata is created without body content.
- Translation keys exist in every supported locale.

## Files To Create

- `app/Enums/ConversationStatus.php`
- `app/Enums/MessageSenderRole.php`
- `app/Models/Conversation.php`
- `app/Models/Message.php`
- `app/Actions/Messaging/StartConversationAction.php`
- `app/Actions/Messaging/SendMessageAction.php`
- `app/Actions/Messaging/MarkConversationAsReadAction.php`
- `app/Actions/Messaging/ArchiveConversationAction.php`
- `app/Actions/Messaging/CloseConversationAction.php`
- `app/Actions/Messaging/RecordMessagingAuditAction.php`
- `app/Notifications/Marketplace/NewConversationMessageNotification.php`
- `app/Policies/ConversationPolicy.php`
- `app/Policies/MessagePolicy.php`
- `app/Livewire/Frontend/Buyer/Messages/Index.php`
- `app/Livewire/Frontend/Buyer/Messages/Show.php`
- `app/Livewire/Frontend/Seller/Messages/Index.php`
- `app/Livewire/Frontend/Seller/Messages/Show.php`
- `app/Livewire/Backend/Messages/Index.php`
- `app/Livewire/Backend/Messages/Show.php`
- `database/factories/ConversationFactory.php`
- `database/factories/MessageFactory.php`
- `database/seeders/Demo/DemoMessagingSeeder.php`
- `resources/views/frontend/buyer/messages/index.blade.php`
- `resources/views/frontend/buyer/messages/show.blade.php`
- `resources/views/frontend/seller/messages/index.blade.php`
- `resources/views/frontend/seller/messages/show.blade.php`
- `resources/views/livewire/backend/messages/index.blade.php`
- `resources/views/livewire/backend/messages/show.blade.php`
- `tests/Feature/Marketplace/MessagingFeatureTest.php`
- `tests/Unit/Policies/ConversationPolicyTest.php`
- `tests/Unit/Policies/MessagePolicyTest.php`
- `docs/messaging.md`
- `docs/releases/unreleased-messaging.md`

## Files To Update

- `README.md`
- `CHANGELOG.md`
- `docs/README.md`
- `docs/architecture.md`
- `docs/database.md`
- `docs/security.md`
- `docs/testing.md`
- `docs/seeders.md`
- `docs/notifications.md`
- `docs/audit-log-system.md`
- `docs/demo-seeding.md`
- `routes/buyer.php`
- `routes/seller.php`
- `routes/admin.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Providers/ViewServiceProvider.php`
- `app/Models/Product.php`
- `app/Models/Order.php`
- `app/Models/Users/Buyer.php`
- `app/Models/Users/Seller.php`
- `app/Models/Users/Admin.php`
- `app/Livewire/Frontend/Buyer/Products/Show.php`
- `app/Livewire/Frontend/Buyer/Orders/Show.php`
- `app/Livewire/Frontend/Seller/Orders/Show.php`
- `resources/views/frontend/buyer/products/show.blade.php`
- `resources/views/frontend/buyer/orders/show.blade.php`
- `resources/views/frontend/seller/orders/show.blade.php`
- `resources/views/layouts/frontend/header.blade.php`
- `resources/views/layouts/backend/navigation.blade.php`
- `lang/en.json`
- `lang/lt.json`
- `tests/Feature/Translations/TranslationFilesTest.php`
- `tests/Feature/Factories/ModelFactoriesTest.php`
- `tests/Feature/Seeders/DemoScenarioSeederTest.php`

## Implementation Notes

- Use Eloquent relationships and scopes only. No raw SQL.
- Use selected columns, eager loading, pagination, and unread count subqueries.
- Do not query in Blade views.
- Do not load full message histories without pagination.
- Preserve the existing dirty working tree by staging and committing only messaging-specific changes.

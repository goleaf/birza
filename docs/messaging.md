# Marketplace Messaging

Birza supports private buyer-seller conversations for product questions, order delivery details, and seller support. Messaging is authenticated and scoped to marketplace ownership rules.

## Supported Conversation Types

- Product conversation: a verified buyer starts from an active public product owned by an active, verified seller.
- Order conversation: a buyer starts from their own order, or a seller starts from an order containing that seller's order items.
- General seller inquiry: supported at the model/action level for future UI use.

Buyer-admin and seller-admin support conversations are not implemented. Admins have a dedicated read-only moderation view for policy-controlled inspection.

## Privacy Rules

- Buyers and sellers see only conversations where they are participants.
- Sellers cannot message random buyers without an existing conversation or a seller-owned order.
- Buyers cannot message other buyers.
- Admin message visibility is limited to backend moderation pages and creates an audit log entry.
- Message bodies are rendered escaped as plain text.
- Notifications store a short preview only.
- Audit logs store conversation/message metadata and body length, not the full message body.

## Read And Unread

Opening a conversation marks unread messages from the other participant as read. A user's own messages are not counted as unread for that user.

Unread counts are separate from Laravel database notification counts.

## Notifications

`NewConversationMessageNotification` sends a database notification to the recipient only:

- buyer message -> seller notification
- seller message -> buyer notification

The sender is never notified about their own message.

## Admin Moderation

Admins can view conversations from `/admin/messages` when policy checks pass. The view is read-only and logs `conversation.admin_viewed`.

Admins should not use private messages for routine browsing. Moderation access is intended for support, abuse, disputes, and safety review.

## Testing

Focused tests:

```bash
php artisan test --compact tests/Feature/Marketplace/MessagingFeatureTest.php
php artisan test --compact tests/Unit/Policies/ConversationPolicyTest.php
php artisan test --compact tests/Unit/Policies/MessagePolicyTest.php
```

The feature tests cover product/order start flows, ownership boundaries, send/reply behavior, validation, read state, escaped output, notifications, audit logs, and translations.

# Release Notes - Buyer Seller Messaging

Status: unreleased.

## Summary

Adds private buyer-seller marketplace messaging for product and order conversations.

## Added

- `conversations` and `messages` tables with ownership, status, archive, read-state, and activity indexes.
- Conversation and message models, scopes, factories, policies, and messaging actions.
- Buyer and seller inbox/detail Livewire pages.
- Product detail contact-seller flow.
- Buyer and seller order conversation flows.
- Admin read-only moderation pages with audit logging.
- New message database notifications.
- Demo messaging seed data and message notification examples.
- Translation keys, tests, docs, and changelog entries.

## Security Notes

- Message bodies render escaped.
- Admin views are policy-controlled and audited.
- Audit logs do not store full message bodies.
- Notifications store short previews only.

## Verification

Run before release:

```bash
php artisan migrate
php artisan db:seed
php artisan test --compact tests/Feature/Marketplace/MessagingFeatureTest.php
php artisan test --compact tests/Unit/Policies/ConversationPolicyTest.php
php artisan test --compact tests/Unit/Policies/MessagePolicyTest.php
php artisan test --compact tests/Feature/Translations/TranslationFilesTest.php
npm run build
```

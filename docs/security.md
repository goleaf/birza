# Security Guide

Security in Birza is enforced through grouped routes, guard-specific middleware, gates, policies, Livewire authorization, validated actions, safe mass assignment, file upload validation, and audit logs.

## Route Protection

Private route groups:

- Admin: `auth:admin`, `active.account:admin`, `can:accessAdminPanel`
- Buyer: `auth:buyer`, `active.account:buyer`, `verified.account:buyer`, `can:accessBuyerCabinet`
- Seller: `auth:seller`, `active.account:seller`, `verified.account:seller`, `can:accessSellerCabinet`

Every new private route must be named and placed in the correct grouped route file.

## Role Access Rules

- Guests can access public pages and auth routes only.
- Buyers can access only their own cart, orders, wishlists, stock alerts, notifications, profile, and allowed product interactions.
- Sellers can access only their own products, promotions, seller orders, transactions, notifications, and product questions.
- Admins can access backend management pages according to gates and policies.

## Policies And Gates

Use gates for broad area access:

- `accessAdminPanel`
- `accessBuyerCabinet`
- `accessSellerCabinet`
- `manageSystemSettings`
- `viewAnalytics`

Use policies for model ownership and dangerous operations. Register new policies in `AuthServiceProvider`.

## Livewire Action Protection

Treat Livewire parameters as untrusted browser input.

Required pattern:

```php
public function mount(Product $product): void
{
    $this->authorize('update', $product);
}

public function save(): void
{
    $this->authorize('update', $this->product);
}
```

Authorize on mount before loading private data and again before every create, update, delete, restore, status change, moderation action, upload, or balance change.

## Ownership Rules

- Buyers see only their own orders, carts, wishlists, stock alerts, addresses, and notifications.
- Sellers manage only their own products, discounts, promo codes, product bundles, product questions, seller orders, and transactions.
- Admin actions should be policy-checked and audited when sensitive.

## Dangerous Fields

Do not normally make these fields mass assignable:

- ownership fields: `user_id`, `buyer_id`, `seller_id`, `admin_id`
- auth/role fields: `is_admin`, `role`, `is_active`, `is_verified`, `email_verified_at`
- financial fields: `balance`, `credit_balance`, totals, snapshots, payment status
- moderation fields: `status`, `is_approved`, `published_at`, `reviewed_by`, `reviewed_at`
- credential fields: `password`, `remember_token`, reset tokens, API tokens, secrets

Set dangerous fields through factories, seeders, policies, actions, or explicit authorized `forceFill()` paths.

## Audit Logging

Sensitive marketplace actions should write audit logs with:

- actor type/id/role
- action
- auditable type/id
- old values
- new values
- metadata
- reason
- IP address
- user agent
- timestamp

Sensitive values must be redacted before storage. Audit logs are not user notifications.

## File Upload Security

- Validate MIME type, size, and dimensions on the backend.
- Use generated safe filenames.
- Store relative paths through Laravel Storage.
- Do not trust original filenames or client-side previews.
- Use `app/Actions/Images`.
- Do not store absolute local server paths in the database.

## API Protection

- `/api/products/search` is public and throttled.
- `/api/user` is protected by Sanctum.
- Add throttling and auth to every new API route according to sensitivity.

## Production Security

```env
APP_ENV=production
APP_DEBUG=false
DEBUGBAR_ENABLED=false
```

Production must also have:

- real secret `APP_KEY`
- no committed `.env`
- HTTPS
- writable but non-public sensitive storage
- backups
- real mail credentials outside code
- debug packages disabled or unavailable

## Future Feature Checklist

- [ ] Route is protected by the correct middleware.
- [ ] Policy exists for the model.
- [ ] Ownership is checked.
- [ ] Livewire actions authorize before mutation.
- [ ] Form input is validated.
- [ ] Dangerous fields are guarded.
- [ ] File uploads use shared validation/actions.
- [ ] Private fields are hidden from output.
- [ ] Admin dangerous action requires a reason.
- [ ] Audit log is written where sensitive.
- [ ] Feature/policy tests cover forbidden access.

Related docs:

- [Security and authorization audit](security-authorization.md)
- [Audit log system](audit-log-system.md)

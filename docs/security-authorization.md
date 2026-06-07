# Security And Authorization

This project uses separate guards and user tables for admins, buyers, and sellers. Access control must be enforced on the backend with middleware, gates, policies, and Livewire action checks. Hidden buttons are not security.

The working audit for this hardening pass lives at `.planning/quick/260607-security-hardening/SECURITY-REPORT.md`.

## Role Model

- Guests may access public pages, auth pages, language switching, and the public product search API.
- Buyers use the `buyer` guard and may access only their own private cart, orders, addresses, notifications, and profile data.
- Sellers use the `seller` guard and may manage only their own products, product images, seller orders, and seller transactions.
- Admins use the `admin` guard and may access backend management tools only through controlled admin actions.

## Gates

Global gates are defined in `App\Providers\AuthServiceProvider` for area-level abilities:

- `accessAdminPanel`
- `accessSellerCabinet`
- `accessBuyerCabinet`
- `manageSystemSettings`
- `viewAnalytics`

Use gates only for global abilities. Model ownership belongs in policies.

## Middleware

Private route groups are protected with guard-specific middleware:

- Admin: `auth:admin`, `active.account:admin`, `can:accessAdminPanel`
- Buyer: `auth:buyer`, `active.account:buyer`, `verified.account:buyer`, `can:accessBuyerCabinet`
- Seller: `auth:seller`, `active.account:seller`, `verified.account:seller`, `can:accessSellerCabinet`

Every new private route must be added to the correct grouped route file and must use a named route.

## Policies

Policies are registered for the important marketplace models in `AuthServiceProvider`. Product and order ownership rules are centralized there.

When adding a model:

1. Create a policy with only meaningful methods.
2. Register the model-policy mapping in `AuthServiceProvider`.
3. Use policies in controllers, Livewire mounts, and every mutating Livewire action.
4. Add direct policy tests for ownership and role boundaries.

## Livewire Actions

Treat every Livewire action parameter as untrusted browser input.

Required pattern:

```php
public function mount(Product $product): void
{
    $this->authorize('update', $product);
}

public function save(): void
{
    $this->authorize('update', $this->product);
    $this->authorize('manageGallery', $this->product);
}
```

Authorize on mount for private data and again before update, delete, restore, status change, or file upload actions.

## Audit Trail

Sensitive marketplace actions are recorded in `audit_logs` through `App\Services\AuditLogService` and feature-specific actions such as `App\Actions\Products\RecordProductAuditLogsAction`.

Audit records store:

- actor id, type, and role
- action
- auditable entity type and id
- old values and new values
- metadata
- reason
- IP address and user agent
- timestamp

Sensitive values such as passwords, tokens, API keys, secrets, card data, and bank accounts are redacted before storage.

Manual admin status changes and destructive actions must require a reason and must write an audit record.

See [Audit log system](audit-log-system.md) for action naming, table structure, sanitizer behavior, admin UI, and tests.

## Dangerous Fields

Do not add these fields to normal `$fillable` arrays unless there is a strong reviewed reason:

- ownership fields: `user_id`, `seller_id`, `buyer_id`, `admin_id`
- status fields: `status`, `payment_status`, `order_status`, `is_active`, `is_verified`, `is_approved`
- role fields: `role`, `is_admin`
- moderation fields: `published_at`, `approved_at`, `rejected_at`
- financial fields: `credit_balance`, `balance`, `subtotal`, `order_total`, price snapshots
- credential fields: `remember_token`, password reset tokens, API keys

Set these fields through factories, seeders, actions, or explicit `forceFill()` calls in already-authorized code.

## Testing

Authorization changes should include:

- feature tests for manual URL access
- Livewire tests for forbidden direct action calls
- policy tests for ownership and role methods
- mass-assignment tests for protected fields
- audit tests for dangerous admin actions and redaction

Run focused tests first:

```bash
php artisan test --compact tests/Unit/Policies/ProductPolicyTest.php tests/Unit/Policies/OrderPolicyTest.php tests/Feature/Security/AuthorizationSecurityTest.php
```

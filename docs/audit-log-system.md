# Audit Log System

Birza records sensitive marketplace actions in the `audit_logs` table so disputes, moderation decisions, refunds, order conflicts, and debugging have a traceable security record.

## What Is Logged

The audit log captures who performed an action, which role they used, which entity was affected, what changed, why it changed when a reason is required, and request context when available.

Current audited actions include:

- Product actions: `product.created`, `product.updated`, `product.price_changed`, `product.published`, `product.unpublished`, `product.deleted`, `product.restored`, `product.force_deleted`, `product.image_uploaded`, `product.image_deleted`.
- Product report actions: `product_report.created`, `product_report.reviewing`, `product_report.resolved`, `product_report.rejected`, `product_report.dismissed`, `product_report.product_hidden`.
- Cart and order actions: `cart.checked_out`, `order.created`, `order.status_changed`, `order.cancelled`, `order.refunded`, `order.dispute_opened`.
- Account and moderation actions: `buyer.created`, `buyer.updated`, `buyer.deleted`, `seller.created`, `seller.updated`, `seller.deleted`, `seller.approved`, `seller.rejected`, `user.blocked`, `user.unblocked`.
- Finance and settings actions: `buyer.credit_adjusted`, `settings.updated`.

Refunds and disputes are currently represented by order lifecycle statuses, not separate refund or dispute models.

## Table Structure

`audit_logs` stores:

- `actor_id`, `actor_type`, `actor_role`
- `action`
- `auditable_id`, `auditable_type`
- `old_values`, `new_values`, `metadata` as JSON
- `reason`
- `ip_address`, `user_agent`
- `created_at`

Audit logs are append-only through normal application flows. They are not editable or deletable from normal UI.

## Action Naming

Use lowercase dot-separated names:

```text
domain.event
```

Examples:

```text
product.price_changed
order.status_changed
seller.approved
buyer.credit_adjusted
settings.updated
```

Prefer one stable action per business event. Do not use free-form sentence actions.

## How To Log A New Action

Use `App\Services\AuditLogService`:

```php
app(AuditLogService::class)->log(
    actor: $actor,
    action: 'product.price_changed',
    auditable: $product,
    oldValues: ['price' => $oldPrice],
    newValues: ['price' => $newPrice],
    metadata: ['source' => 'seller_product_edit'],
    reason: $reason,
);
```

Request context is optional. If no request is passed, the service uses the current request when one exists. This keeps logging usable from Livewire components, tests, jobs, seeders, and console commands.

For product flows, prefer `App\Actions\Products\RecordProductAuditLogsAction`; it records product updates, price changes, publish/unpublish decisions, and image deltas consistently.

For order statuses, always use `App\Actions\Orders\ChangeOrderStatusAction`. It writes order status history and security audit logs inside the same transaction.

## Sensitive Data

`AuditLogService` sanitizes `old_values`, `new_values`, and `metadata` recursively. It removes sensitive keys such as:

- passwords and password confirmations
- remember tokens, API tokens, access tokens, auth codes, OTPs
- secrets and private keys
- payment card numbers, CVV/CVC values
- bank accounts
- raw uploaded file contents

Do not pass secrets intentionally. Store only the minimum values needed to understand the business decision.

## Admin UI

Admins can view audit logs at `/admin/audit`.

The audit UI supports:

- filtering by action
- filtering by actor ID
- filtering by actor role
- filtering by entity type and entity ID
- filtering by date range
- opening a detail page with old values, new values, metadata, reason, IP address, and user agent

Related audit history appears on admin product, seller, buyer edit, order, and settings pages.

Buyers and sellers cannot access the system audit log. They should see business-facing histories only when a workflow needs them.

## Order Status History vs Audit Log

Order status history is the business timeline for an order. It explains the lifecycle visible to admins and, where appropriate, sellers or buyers.

Audit logs are the security and dispute trace. They include actor type, role, request context, reason, metadata, and sanitized value changes. Do not remove one just because the other exists.

## Testing

Focused tests:

```bash
php artisan test --compact tests/Feature/AuditLogTest.php
php artisan test --compact tests/Feature/Marketplace/AuditLoggingFeatureTest.php
```

Run the full suite before release:

```bash
php artisan test --compact
```

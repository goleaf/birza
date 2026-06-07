# Architecture Guide

Birza is a server-rendered Laravel marketplace. It uses Blade layouts, route-mounted Livewire pages, Eloquent models, policies, actions, services, observers, notifications, and seed/factory-driven test data.

## High-Level Areas

| Area | Route prefix | Primary code |
| --- | --- | --- |
| Public marketplace | `/` | `routes/web.php`, `App\Http\Controllers\Frontend`, public Blade/Livewire views |
| Buyer area | `/buyer` | `routes/buyer.php`, `App\Livewire\Frontend\Buyer` |
| Seller area | `/seller` | `routes/seller.php`, `App\Livewire\Frontend\Seller` |
| Admin area | `/admin` | `routes/admin.php`, `App\Livewire\Backend` |
| API | `/api` | `routes/api.php`, `App\Http\Controllers\Api` |

The app remains Laravel + Blade + Livewire. Do not introduce React, Vue, Inertia, or SPA routing unless the project direction changes.

## Request Flow

1. Route files define grouped route prefixes and middleware.
2. Controllers handle simple HTTP entry points, redirects, API endpoints, and notification POST actions.
3. Livewire components own UI state and user interaction for most pages.
4. Actions/services perform reusable business operations.
5. Policies/gates authorize area access and model ownership.
6. Models define relationships, scopes, casts, and safe query helpers.
7. Events/observers/notifications handle side effects such as audit logs and notifications.
8. Blade views render preloaded data and do not perform queries.

## Business Logic Placement

| Logic type | Place |
| --- | --- |
| Route grouping and middleware | `routes/*.php`, `bootstrap/app.php` |
| Thin HTTP entry points | `app/Http/Controllers` |
| UI state and validation for Livewire pages | `app/Livewire` |
| Reusable business operations | `app/Actions` |
| Cross-cutting behavior | `app/Services`, `app/Observers` |
| Authorization | `app/Policies`, gates in `AuthServiceProvider` |
| Query reuse | model scopes and relationships |
| Data display | Blade views and Blade components |
| Side effects | notifications, observers, actions, audit service |

Do not put business rules in Blade templates or duplicate them across Livewire components.

## Models

Core account models:

- `App\Models\User`
- `App\Models\Users\Admin`
- `App\Models\Users\Buyer`
- `App\Models\Users\Seller`

Core marketplace models include:

- catalog: `Product`, `ProductImage`, `Category`, `Country`, `Attribute`, `AttributeValue`
- commerce: `Cart`, `CartItem`, `CartBundleItem`, `Order`, `OrderItem`, `OrderBundle`, `OrderStatusHistory`
- promotions: `Discount`, `PromoCode`, `PromoCodeRedemption`
- buyer features: `Wishlist`, `WishlistItem`, `ProductStockAlert`, `Address`
- moderation/community: `Review`, `ProductQuestion`, `ProductReport`
- system: `Notification`, `AdminAction`, `AuditLog`, `GlobalSettings`

## Cart And Checkout

The active cart path is database-backed:

- `carts`
- `cart_items`
- `cart_bundle_items`

Checkout uses actions under `app/Actions/Cart`, recalculates prices on the server, validates stock and account status, creates one or more orders, snapshots line data, decrements stock, clears converted carts, and sends notifications after the database transaction commits.

Payment provider integration is not complete. Checkout currently simulates successful payment.

## Orders

Orders keep lifecycle status, payment status, totals, address snapshots, discount snapshots, promo snapshots, and order item snapshots. Status changes should use the status workflow action and write `order_status_histories` plus audit/notification records where implemented.

## Product Images

Product image handling is centralized in `app/Actions/Images` and `config/images.php`.

New code should prefer:

- `product_images`
- `Product::imageUrl()`
- `Product::imageGalleryUrls()`

Legacy product image fields still exist for compatibility and should not be treated as the final single image source.

## Notifications

Marketplace notifications use Laravel database notifications and mail-capable notification classes. Notification actions live under `app/Actions/Notifications`.

Notifications should:

- send after commit
- store translated keys and safe render data
- filter rows by the authenticated notifiable model
- not replace audit logs

## Audit Logs

Sensitive actions should write `audit_logs` with actor, role, action, entity, old/new values, metadata, reason, IP address, and user agent. Sensitive values must be redacted before storage.

## UI Architecture

The project is in a Mary-first migration:

- maryUI is the target component system.
- WireUI, daisyUI, and Flowbite still exist during transition.
- Shared wrappers should live in Blade components and avoid spreading package-specific markup everywhere.
- Livewire components should stay class-based or standard Livewire/Blade patterns.
- Volt is intentionally out of scope for the current roadmap.

## Practical Rules For Future Work

- Start from route, policy, model, and test evidence before changing behavior.
- Add an Action when logic is reused, transactional, or risky.
- Keep Livewire components focused on UI state and orchestration.
- Keep model scopes single-purpose and query-focused.
- Eager-load data read by views.
- Keep all visible UI text translatable.
- Add or update feature tests for user-facing behavior.
- Update docs and changelog when architecture or behavior changes.

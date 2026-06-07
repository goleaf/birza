# Roles And Access Guide

Birza uses separate authentication guards and profile tables instead of a shared role table. `App\Enums\MarketplaceRole` standardizes role values, guard names, login routes, dashboard routes, and notification-capable guards.

## Guards

| Guard | Provider | Model | Main table |
| --- | --- | --- | --- |
| `web` | `users` | `App\Models\User` | `users` |
| `admin` | `admins` | `App\Models\Users\Admin` | `users_admins` |
| `buyer` | `buyers` | `App\Models\Users\Buyer` | `users_buyers` |
| `seller` | `sellers` | `App\Models\Users\Seller` | `users_sellers` |

The current architecture can link a generic `users` record to buyer and seller profiles. A user can be buyer only, seller only, or both buyer and seller when matching profile rows exist, as shown by the seeded `buyer-seller@example.com` account. Admin accounts are separate guard accounts and do not inherit buyer or seller dashboards.

## Guest

Route areas:

- `/`
- `/buyer/login`, `/buyer/register`, buyer password and verification routes
- `/seller/login`, `/seller/register`, seller password and verification routes
- `/admin/login`
- `/buyer/products`
- `/buyer/products/{product}`
- `/buyer/compare`
- `/api/products/search`

Can:

- view public marketplace pages
- switch language
- browse visible active products
- compare public products
- ask public product questions as a guest where the component allows it
- report active products when guest reports are enabled

Cannot:

- access buyer, seller, or admin dashboards
- checkout as an authenticated buyer
- manage products or orders
- view private conversations or notifications

## Buyer

Route prefix: `/buyer`

Main dashboard: `/buyer/dashboard`

Middleware:

- `auth:buyer`
- `active.account:buyer`
- `verified.account:buyer`
- `buyer.access`

Can:

- manage own profile
- browse catalog and product detail pages
- manage own cart and checkout
- apply valid promo codes
- view own orders and order details
- change allowed buyer-side order statuses
- start private conversations from active products and own orders
- send and read messages in own buyer-seller conversations
- manage own wishlists and wishlist items
- create and cancel own stock alerts
- ask product questions
- report active products
- view and mark own notifications

Cannot:

- manage seller products, discounts, promo codes, or seller transactions
- see another buyer's orders, carts, wishlists, conversations, or notifications
- answer seller product questions
- access admin pages

## Seller

Route prefix: `/seller`

Main dashboard: `/seller/dashboard`

Middleware:

- `auth:seller`
- `active.account:seller`
- `verified.account:seller`
- `seller.access`

Can:

- manage own profile and categories
- create, update, restore, and delete own products according to policies
- manage own product image galleries
- manage own discounts and promo codes
- answer or hide questions for own products
- view own seller orders and order details
- change allowed seller-side order statuses
- reply to buyer conversations for own products and seller order items
- start/open order conversations only for orders containing own seller items
- view own transactions
- view and mark own notifications

Cannot:

- manage another seller's products, discounts, promo codes, or order items
- view another seller's conversations
- message random buyers without an existing conversation or seller-owned order
- view reporter private identity for product reports
- use buyer cart/checkout as the seller guard
- access admin pages

## Admin

Route prefix: `/admin`

Main dashboard: `/admin/dashboard`

Middleware:

- `auth:admin`
- `active.account:admin`
- `admin.access`

Can:

- manage catalog entities: products, categories, countries, attributes, and values
- manage buyers, sellers, buyer credit, and settings
- view and manage orders according to policies
- moderate product reports and product questions
- view private buyer-seller messages only through the audited moderation pages when policy allows it
- view admin notifications
- view audit logs
- perform dangerous/status-sensitive actions with audit reasons where implemented

Cannot:

- bypass policy checks in code
- mutate dangerous fields without controlled actions
- access buyer/seller dashboards as those guards unless separately authenticated

## Gates And Policies

Use gates only for global abilities that are not tied to a single model instance:

- `accessBuyerCabinet`
- `accessSellerCabinet`
- `accessAdminPanel`
- `viewAdminDashboard`
- `manageSystemSettings`
- `viewAnalytics`

Use policies for model-specific authorization, ownership, moderation, deletion, status changes, uploads, notification ownership, and private data access. Blade `@can` checks are only for visibility; controllers, Livewire components, and actions still need backend authorization.

## Adding Protected Pages

1. Add the route to the correct grouped route file: `routes/buyer.php`, `routes/seller.php`, or `routes/admin.php`.
2. Keep the route name prefix: `buyer.*`, `seller.*`, or `admin.*`.
3. Use the matching layout: frontend buyer/seller pages use the frontend layout, admin pages use the backend layout.
4. Gate the route group with the correct middleware alias: `buyer.access`, `seller.access`, or `admin.access`.
5. Authorize private Livewire data in `mount()`.
6. Authorize every dangerous Livewire action again before saving, deleting, uploading, status-changing, exporting, or marking notifications.
7. Put model ownership rules in a policy, not in Blade or navigation.
8. Add tests for the allowed role and at least one forbidden cross-role or cross-owner request.

## Access Checklist

- Route is in the correct grouped route file.
- Route has a name.
- Private route uses the correct guard middleware.
- Active/verified middleware is applied where required.
- Area-level access middleware alias is applied.
- Model ownership lives in a policy.
- Livewire `mount()` authorizes private data.
- Every mutating Livewire action authorizes again.
- Tests cover forbidden cross-role and cross-owner access.

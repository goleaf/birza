# Security Authorization Audit

Date: 2026-06-07
Scope: routes, middleware, guards, policies, gates, Livewire pages/actions, controllers, models, actions, factories, tests, seeders, database schema, docs.

## Executive Summary

Birza has a useful guard split for admin, buyer, and seller surfaces, and private route groups already use backend middleware. The current authorization layer is still not strict enough for a real marketplace because model-specific policies are mostly absent and many Livewire actions mutate data directly.

Highest-risk gaps:

- Only `OrderPolicy` exists, and it only covers `view` and `changeStatus`.
- No global gates are defined for admin, buyer, seller, settings, or analytics access.
- Product, buyer, seller, cart, image, settings, credit, and catalog admin actions do not use policies.
- Several dangerous admin actions are confirmed in UI but not audit logged.
- Product ownership is enforced inline in seller product components instead of centrally.
- Buyer order ownership is enforced inline in the buyer order show component instead of centrally.
- Product, order, buyer, and seller models expose dangerous fields through `$fillable`; current safety depends on callers hand-picking arrays.
- API product search is public and rate-limited only by the default API middleware group.
- Livewire action parameters are accepted from the browser and must be authorized inside each mutation method.
- A dirty-tree conflict is present: existing order tests fail because current migrations require `order_items.product_title_snapshot` while older tests/factories still create order items without the new snapshot fields.

## Current Policies

- `app/Policies/OrderPolicy.php`
  - `view(Authenticatable $actor, Order $order)`
  - `changeStatus(Authenticatable $actor, Order $order, OrderStatus $nextStatus)`

Missing policies for existing important entities:

- `User`
- `App\Models\Users\Admin`
- `App\Models\Users\Buyer`
- `App\Models\Users\Seller`
- `Product`
- `ProductImage`
- `Category`
- `Country`
- `Attribute`
- `AttributeValue`
- `OrderItem`
- `OrderStatusHistory`
- `Cart`
- `CartItem`
- `Review`
- `Notification`
- `Address`
- `BuyerCreditHistory`
- `CreditAttachment`
- `SellerTransaction`
- `GlobalSettings`
- `Activity`

Entities requested but not found in the current application schema/code:

- `Favorite`
- `Message`
- `Payment`
- `Dispute`
- `Report`
- `StaticPage`
- `AdminAction` before this hardening pass

## Current Gates

No `Gate::define(...)` calls were found.

Recommended global gates:

- `accessAdminPanel`
- `accessSellerCabinet`
- `accessBuyerCabinet`
- `manageSystemSettings`
- `viewAnalytics`

Model ownership should stay in policies, not gates.

## Current Middleware

Existing middleware relevant to security:

- `auth`
- `guest`
- `active.account`
- `verified`
- `throttle`
- `can`
- `signed`
- `password.confirm`

`active.account` is registered in `bootstrap/app.php` and private admin/buyer/seller route groups already use it.

Missing middleware aliases:

- dedicated seller approval / verified account middleware
- explicit gate middleware on private role route groups

## Routes

Public routes:

- `GET /`
- `GET /language/{locale}`
- `GET /admin` landing redirect
- `GET /admin/login`
- buyer auth routes: login, register, register success, forgot password, reset password, email verification
- seller auth routes: login, register, register success, forgot password, reset password, email verification
- `GET /api/products/search`
- vendor/dev routes from Debugbar, Ignition, Livewire assets, Mary, WireUI, Sanctum CSRF

Private routes:

- Admin routes under `/admin/*` except `/admin` and `/admin/login`
- Buyer routes under `/buyer/dashboard`, `/buyer/profile`, `/buyer/products`, `/buyer/cart`, `/buyer/orders`, and logout
- Seller routes under `/seller/dashboard`, `/seller/profile`, `/seller/products`, `/seller/orders`, `/seller/transactions`, and logout
- `GET /api/user` via `auth:sanctum`

Routes needing stronger middleware:

- Admin protected group should also require `can:accessAdminPanel`.
- Buyer protected group should also require `can:accessBuyerCabinet`.
- Seller protected group should also require seller approval/verification and `can:accessSellerCabinet`.
- Public API product search should receive an explicit throttle.
- Admin audit trail route does not exist yet.

## Hardcoded Role And Ownership Checks

Inline ownership checks found:

- `Frontend\Seller\Products\Edit::mount()` checks `product.seller_id` directly.
- `Frontend\Seller\Products\Index::softDeleteProduct()` and `restoreProduct()` scope by seller id directly.
- `Frontend\Buyer\Orders\Show::mount()` and `updateStatus()` check `order.buyer_id` directly.
- `Frontend\Seller\Orders\Show::mount()` queries `OrderItem` by seller id and order id directly.

These checks are directionally correct but duplicated and should route through policies.

Role checks found:

- route groups use guard-specific `auth:admin`, `auth:buyer`, and `auth:seller`
- login components branch by buyer/seller guard
- `OrderStatusActorRole::fromActor()` maps actor model classes to buyer/seller/admin/system
- no data-driven permission model exists

## Missing Ownership Protection

Seller risks:

- Seller product edit/delete/restore uses inline ownership checks, not policy checks.
- Seller create can set `is_active` from Livewire state. It correctly forces `seller_id` from the guard, but publish permission is not centralized.
- Seller image/gallery sync runs during product save without a separate `manageGallery` policy check.
- Seller order show scopes order items by seller, but action authorization should also call `OrderPolicy`.

Buyer risks:

- Buyer order show uses inline ownership checks instead of `OrderPolicy`.
- Cart is still primarily session-backed in the live UI; database cart action classes are partially implemented in the dirty tree. There is no cart policy.
- Checkout writes orders from Livewire directly. It recalculates price from locked products in the current dirty tree, which is good, but it still does not use a cart/order policy.

Admin risks:

- Admin CRUD pages rely on route guard middleware, not model policies inside Livewire actions.
- Admin buyer credit adjustments are not logged to a central audit trail.
- Admin settings updates are not logged.
- Admin product delete/restore/force-delete are not logged.
- Admin seller verification/activation changes are not logged.

## Dangerous Actions

Dangerous actions currently present:

- Product soft delete, restore, force delete
- Seller product soft delete and restore
- Buyer delete
- Seller delete
- Category, country, attribute, and attribute value delete
- Product create/update with seller, status, price, stock, and image changes
- Buyer credit add/deduct with optional attachment
- Settings update
- Order status changes
- Product image/gallery sync

Existing protections:

- Most delete actions have UI confirmation via existing WireUI/Mary confirmation helpers.
- Order status changes go through `ChangeOrderStatusAction`, validate transitions, and require admin reason.

Missing protections:

- Most dangerous actions do not call `$this->authorize(...)`.
- Most dangerous admin actions are not written to an audit trail.
- Product admin and seller status changes do not require or record a reason.

## Audit Trail

Existing audit-like records:

- `order_status_histories` records order status changes, actor id, actor role, reason, and note.
- `buyer_credit_history` records credit adjustment history but is domain history, not general admin audit.

Missing:

- Generic admin audit log table/model/policy/page.
- Audit records for product delete/restore/force delete, buyer/seller delete, seller approval/rejection/blocking, settings changes, buyer credit adjustment, and admin order status changes.

## API

API routes:

- `GET /api/user` is private via `auth:sanctum`.
- `GET /api/products/search` is public.

API concerns:

- Public product search returns active products only and limits to 5 products and 5 categories.
- It has no explicit route throttle.
- It should not expose private seller data; current response includes product id, name, price, image path/url only.

## Tests

Existing useful coverage:

- Many route authentication tests for admin, buyer, and seller pages.
- Some seller/buyer order ownership behavior is indirectly tested.
- Middleware unit tests exist for auth/redirect pieces.
- Order status helper/model tests exist.
- Cart workflow tests exist but target partially implemented DB cart actions.

Missing or incomplete tests:

- Policy unit tests for Product, Order, Cart, Buyer, Seller, GlobalSettings, Review, Address, Notification, ProductImage.
- Manual URL cross-role tests: buyer cannot access seller/admin, seller cannot access buyer/admin, admin cannot silently bypass business rules.
- Forbidden Livewire action tests for seller editing/deleting another seller product.
- Buyer cannot view/change another buyer order via route and Livewire action.
- Admin audit log is created for dangerous admin actions.
- Admin order status change requires reason and creates both status history and audit log.
- Mass assignment tests for protected fields.
- API throttle/security test for product search.

## Pre-Refactor Verification Notes

Commands/results:

- `php artisan route:list` shows 98 routes.
- `php artisan route:list --path=admin -v` confirms admin protected routes use `auth:admin` and `active.account:admin`.
- `php artisan route:list --path=buyer -v` confirms buyer protected routes use `auth:buyer` and `active.account:buyer`.
- `php artisan route:list --path=seller -v` confirms seller protected routes use `auth:seller` and `active.account:seller`.
- `php artisan route:list --path=api -v` confirms `api/user` uses `auth:sanctum` and product search is public.
- Laravel Boost `database_schema` confirmed current SQLite tables and indexes.
- `php artisan test --compact tests/Feature/Controllers/Frontend/Buyer/OrderControllerTest.php --filter=buyer` currently fails because dirty migrations require snapshot fields on `order_items` that older test setup paths do not provide.

## Implementation Summary

Implemented after this audit:

- Registered policies for the important marketplace entities found in the project, including users, buyers, sellers, products, images, orders, carts, reviews, notifications, addresses, credit history, transactions, settings, catalog entities, wishlist/stock-alert/promo/report-adjacent entities present in the dirty tree, and audit/admin-action records.
- Added global gates for admin, buyer, seller, settings, and analytics area access.
- Added `verified.account` middleware and strengthened admin, buyer, and seller route groups with backend gate middleware.
- Added `admin_actions` with `RecordAdminAction`, an admin audit trail Livewire page, and admin navigation.
- Added policy checks to critical Livewire mounts/actions for seller product create/edit/delete/restore, buyer/seller order show/status changes, backend product create/edit/delete/restore/force-delete, settings update, buyer credit, and buyer/seller admin forms.
- Removed dangerous ownership, status, verification, balance, and total fields from normal mass assignment on product, order, buyer, seller, and base user models; controlled paths now use `forceFill()`.
- Added security docs, README security notes, changelog entries, and focused policy/security tests.

Focused verification after implementation:

- `vendor/bin/pint --dirty --format agent` passed.
- `php artisan test --compact tests/Unit/Policies/ProductPolicyTest.php tests/Unit/Policies/OrderPolicyTest.php tests/Feature/Security/AuthorizationSecurityTest.php` passed with 15 tests and 55 assertions.
- `php artisan migrate:fresh --seed --env=testing --no-interaction` passed.
- `npm run build` passed.

Full-suite verification:

- `php artisan test --compact` currently reports unrelated dirty-tree failures outside this security pass, including translation parity drift, wishlist view data, demo image readability, readonly seeder-test SQLite state, and image pipeline state contamination. The focused security suite passes.

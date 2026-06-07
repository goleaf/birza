# Cart and Checkout Audit

Date: 2026-06-07
Scope: buyer cart, product add-to-cart, checkout/order creation, order display, seller/admin order surfaces, schema, tests, translations, sessions, policies, notifications.

## Executive Summary

The current cart and checkout flow is not ready for real marketplace logic.

Cart state is primarily stored in LaraCart session data. The database has a `carts` table, and the dirty worktree has a pending `cart_items` migration/model, but the buyer cart UI and checkout do not use them. Checkout is executed directly inside `App\Livewire\Frontend\Buyer\Cart\Index`, creates an order immediately from session items, marks it as paid, decrements stock, and clears the session cart.

The biggest risks are:

- No guest cart support despite the requested marketplace behavior.
- No cart merge after buyer login.
- Checkout trusts stale LaraCart item price/options instead of recalculating every price from locked backend product rows.
- Checkout checks stock but does not check active product status, seller activity, buyer activity, deleted sellers, delivery/address data, or multi-seller order rules.
- Orders and order items do not store enough snapshots, so historical orders depend on mutable product/seller/buyer live data.
- Order lifecycle work in the dirty tree is currently broken because `app/Models/Order.php` is deleted while many files still import `App\Models\Order`.
- Tests are currently blocked by the missing Order model and a pending status-normalization migration.

## Implementation Update

The refactor replaced runtime LaraCart usage with database-backed carts and dedicated actions.

Implemented architecture:

- `carts` is now the cart header with `user_id`, `guest_token`, and `status`.
- `cart_items` is the authoritative line-item table.
- Guest carts are linked by `session('cart_guest_token')`.
- Buyer login and buyer registration merge a guest cart into the authenticated buyer cart.
- Product add-to-cart, cart page updates, header count, and checkout now use database-backed cart actions.
- Checkout validates and locks product rows inside a transaction.
- Checkout creates one order per seller.
- Order creation stores product title, product price, seller name, shipping address, billing address, subtotal, and total snapshots.
- Cart items are cleared and the cart is marked converted only after successful order creation.
- Failed checkout does not clear cart items or create partial orders.
- Runtime app code no longer calls LaraCart; the package config remains as legacy installed configuration only.

Implemented action layer:

- `App\Actions\Cart\ResolveCartAction`
- `App\Actions\Cart\AddCartItemAction`
- `App\Actions\Cart\UpdateCartItemQuantityAction`
- `App\Actions\Cart\RemoveCartItemAction`
- `App\Actions\Cart\ClearCartAction`
- `App\Actions\Cart\MergeGuestCartAction`
- `App\Actions\Cart\ValidateCartAction`
- `App\Actions\Cart\CalculateCartTotalsAction`
- `App\Actions\Cart\CreateOrdersFromCartAction`

The multi-seller decision is implemented as one checkout creating separate orders per seller.

## How Cart Works Now

Add-to-cart is handled in `app/Livewire/Frontend/Buyer/Products/Show.php`.

Current behavior:

- Buyer product routes require `auth:buyer`, so guests cannot add products through the normal UI.
- The component reloads the product with `$this->product->fresh()`.
- It rejects deleted, inactive, and out-of-stock products on add.
- It validates requested quantity against `min_order_count` and `stock`.
- It searches all `LaraCart::getItems()` for an existing item with the same product id.
- If found, it increments LaraCart quantity.
- If not found, it adds the product to LaraCart with product id, name, quantity, current price, and option snapshots such as image, unit, seller_id, category_id, min_order_count, stock, etc.

Cart display and update are handled in `app/Livewire/Frontend/Buyer/Cart/Index.php`.

Current behavior:

- Cart page requires `auth:buyer`.
- It reads all items from `LaraCart::getItems()`.
- Quantities are stored in a public Livewire `quantities` array keyed by LaraCart item hash.
- Quantity update finds the LaraCart item by hash, reloads `Product::find($cartItem->id)`, checks minimum quantity and stock, then updates LaraCart.
- Remove deletes the item from LaraCart.
- There is no clear-cart action.
- Header count uses `App\Providers\ViewServiceProvider` and returns `LaraCart::count()` only when `Auth::guard('buyer')->check()`.

## How Checkout Works Now

Checkout is a button on the cart page, not a separate step-by-step flow.

Current behavior in `App\Livewire\Frontend\Buyer\Cart\Index::checkout()`:

- Rejects empty LaraCart.
- Reads buyer id from `Auth::guard('buyer')->id()`.
- Calculates totals from LaraCart item prices plus VAT and `GlobalSettings::first()->portal_additional_price`.
- Opens a database transaction.
- Creates one `orders` row for the buyer.
- Loops over LaraCart items.
- Locks each product with `Product::lockForUpdate()->find($item->id)`.
- Rejects missing product or insufficient stock.
- Creates one `order_items` row per cart item.
- Uses `unit_price` from `$item->price`, not from the locked product.
- Uses seller id from LaraCart options or product seller id.
- Decrements product stock.
- Updates the order to paid immediately.
- On any throwable, flashes the exception message and returns.
- Clears LaraCart only after the transaction block succeeds.
- Redirects to buyer orders.

There is no checkout page, address form, delivery method, payment method selection, confirmation step, or order review step.

## Where Cart Data Is Stored

Runtime cart data is stored in the Laravel session through LaraCart.

Current storage points:

- `config/laracart.php` configures LaraCart.
- LaraCart writes to session keys using the `laracart` prefix.
- `users_buyers.cart_session_id` exists from `2024_12_10_151731_add_cart_session_id_to_users_table.php`, but LaraCart `cross_devices` is false and `guard` is null, so it is effectively unused by the app.
- `carts` table exists with `user_id`, `product_id`, and `quantity`, but current UI/checkout do not use it.
- Dirty worktree has pending `cart_items` migration/model/factory, but it is not migrated and does not yet drive the current flow.

## Guest Cart

Guest cart is not currently supported through normal routes.

Why:

- Buyer products and cart are under `Route::middleware('auth:buyer')`.
- Header cart count returns zero unless a buyer is authenticated.
- Login does not read or merge a guest cart.
- Logout invalidates the full session, clearing any session cart state.

If a LaraCart item somehow exists in a guest session, it is not intentionally surfaced as a guest cart.

## Authenticated Buyer Cart

Authenticated buyer cart is session-scoped LaraCart state, not buyer-owned database state.

Implications:

- It is not reliably cross-device.
- It is not explicitly tied to a buyer id.
- It can disappear when the session is invalidated.
- It cannot be audited from the database.
- It cannot cleanly support a merge after login.
- It cannot safely retain marketplace validation metadata.

## Login And Logout Behavior

After login:

- `App\Livewire\Frontend\Auth\Login::login()` calls `session()->regenerate()`.
- There is no cart merge action.
- There is no database cart lookup for the buyer.
- There is no logic to merge a guest token/session cart into a buyer cart.

After logout:

- `App\Actions\Auth\LogoutGuardAction::handle()` logs out the guard, invalidates the session, and regenerates the token.
- Because LaraCart is session-backed, the cart is effectively lost.
- No buyer-owned cart is preserved or detached.

## Product Price Changes

Current behavior:

- Cart UI shows the price stored in LaraCart when the item was added.
- Checkout uses `$item->price` from LaraCart to create `order_items.unit_price`.
- Checkout does not compare the cart price to `products.price`.
- Checkout does not show a price-change warning.

Risk:

- If product price changes after the item was added, the buyer can checkout at the stale LaraCart price.
- Final order totals are not recalculated strictly from backend product state.

## Product Unavailable, Deleted, Or Inactive

Add-to-cart blocks deleted and inactive products.

Quantity update:

- Uses `Product::find($cartItem->id)`, so soft-deleted products are treated as missing and removed.
- Does not explicitly check `is_active`.

Checkout:

- Uses `Product::lockForUpdate()->find($item->id)`, so soft-deleted products are missing.
- Checks stock only.
- Does not check `is_active`.
- Does not check seller active/blocked state.
- Does not surface unavailable items in the cart UI before checkout.

## Product Stock Changes

Current behavior:

- Add-to-cart validates requested quantity against current stock.
- Quantity update validates requested quantity against current stock.
- Cart UI displays the stale stock value stored in LaraCart options.
- Checkout locks product rows and rejects insufficient stock.
- Checkout decrements stock inside the transaction.

Risk:

- Cart UI may show stale stock.
- There is no per-item warning state before checkout.
- Failed checkout flashes a generic stock-changed message.

## Security And Authorization Issues

Current issues:

- Heavy checkout business logic lives in a Livewire component.
- No dedicated cart policy exists.
- Dirty worktree has `app/Policies/OrderPolicy.php`, but it denies everything and is typed against generic `App\Models\User`, while the app uses buyer/seller/admin guard-specific models.
- Buyer order show manually checks `buyer_id`, which is good but duplicated.
- Seller order show manually checks seller-owned order items, which is good but duplicated.
- Admin order access is route-middleware-only, not policy-backed.
- Checkout does not check buyer active/allowed-to-order at the final transaction point.
- Checkout does not check seller active/allowed-to-sell at the final transaction point.
- Checkout uses stale session item price for order creation.
- Checkout catches every throwable and flashes raw exception messages to the user.
- Product/order views rely on live product and seller relationships, so deleted/changed data can alter historical order display.
- Order cancellation stock restoration is duplicated in buyer order index and buyer order show.
- Seller status updates can affect a shared multi-seller order, so one seller may change a whole order that contains another seller's items.

## Current Dirty Repo Blockers

These are existing local worktree issues discovered during the audit:

- `app/Models/Order.php` is deleted while many classes still import `App\Models\Order`.
- Dirty files and tests reference `OrderStatus::Paid` and `OrderStatus::Failed`, but `App\Enums\OrderStatus` does not define those cases.
- `App\Enums\OrderPaymentStatus` exists and suggests a split between payment status and fulfillment status, but current Livewire code still filters payment status with `OrderStatus`.
- Pending migration `2026_06_07_172030_normalize_existing_order_status_values.php` references `App\Models\Order`, so tests fail during migration while the model is missing.
- Pending `create_cart_items`, `create_addresses`, `create_order_status_histories`, and notification-related migrations exist but are not yet aligned with the checkout architecture.

Focused test attempts:

- `php artisan test --compact tests/Unit/Models/OrderTest.php` fails before assertions because `App\Models\Order` is missing.
- `php artisan test --compact tests/Feature/Controllers/Frontend/Buyer/CartControllerTest.php` fails before assertions for the same reason.
- `php artisan test --compact tests/Feature/Controllers/Frontend/Buyer/OrderControllerTest.php` fails before assertions for the same reason.

## Files Needing Refactor

Core cart and checkout:

- `app/Livewire/Frontend/Buyer/Products/Show.php`
- `app/Livewire/Frontend/Buyer/Cart/Index.php`
- `resources/views/frontend/buyer/cart/index.blade.php`
- `resources/views/frontend/buyer/products/show.blade.php`
- `resources/views/layouts/frontend/header.blade.php`
- `app/Providers/ViewServiceProvider.php`
- `config/laracart.php`

Auth/session transition:

- `app/Livewire/Frontend/Auth/Login.php`
- `app/Actions/Auth/LogoutGuardAction.php`
- `app/Http/Controllers/Auth/LogoutController.php`

Orders and checkout persistence:

- `app/Models/Order.php`
- `app/Models/OrderItem.php`
- `app/Models/Cart.php`
- `app/Models/CartItem.php`
- `app/Models/Product.php`
- `app/Models/Users/Buyer.php`
- `app/Models/Users/Seller.php`
- `database/migrations/2024_03_20_000009_create_orders_table.php`
- `database/migrations/2024_12_18_074617_create_order_item_table.php`
- `database/migrations/2024_03_20_000011_create_carts_table.php`
- pending cart/address/order-status-history migrations

Order display and seller/admin flows:

- `app/Livewire/Frontend/Buyer/Orders/Index.php`
- `app/Livewire/Frontend/Buyer/Orders/Show.php`
- `app/Livewire/Frontend/Seller/Orders/Index.php`
- `app/Livewire/Frontend/Seller/Orders/Show.php`
- `app/Livewire/Backend/Orders/Index.php`
- `app/Livewire/Backend/Orders/Show.php`
- `resources/views/frontend/buyer/orders/index.blade.php`
- `resources/views/frontend/buyer/orders/show.blade.php`
- `resources/views/frontend/seller/orders/index.blade.php`
- `resources/views/frontend/seller/orders/show.blade.php`
- `resources/views/backend/orders/index.blade.php`
- `resources/views/backend/orders/show.blade.php`

Factories, seeders, tests:

- `database/factories/CartFactory.php`
- `database/factories/CartItemFactory.php`
- `database/factories/OrderFactory.php`
- `database/factories/OrderItemFactory.php`
- `database/factories/ProductFactory.php`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/test_information/ProductSeeder.php`
- cart/checkout/order feature and unit tests under `tests/`

Translations and notifications:

- `lang/en.json`
- `lang/lt.json`
- `app/Notifications/OrderStatusChanged.php`
- new order-created notification if needed

## Missing Tests

Missing or insufficient coverage:

- Guest can add item to cart.
- Authenticated buyer can add item to cart via action/component.
- Guest cart merges after login.
- Quantity can be increased.
- Quantity can be decreased.
- Item can be removed.
- Cart can be cleared.
- Cart item count is backend-derived.
- Empty cart cannot checkout.
- Inactive product cannot checkout.
- Deleted product cannot checkout.
- Unavailable product cannot checkout.
- Not enough stock blocks checkout.
- Inactive seller blocks checkout.
- Inactive buyer blocks checkout.
- Price is recalculated on backend.
- Frontend/session price manipulation does not affect order.
- Successful checkout creates order or orders.
- Successful checkout creates order items.
- Successful checkout saves product title snapshot.
- Successful checkout saves product price snapshot.
- Successful checkout saves seller snapshot.
- Successful checkout saves buyer address snapshot.
- Successful checkout creates order status history.
- Successful checkout clears cart only after commit.
- Failed checkout does not clear cart.
- Failed checkout does not create partial order.
- Multi-seller checkout behavior follows one clear rule.
- Buyer cannot access another buyer's cart.
- Seller cannot manipulate buyer cart.
- Admin cannot create buyer orders through buyer checkout.
- Notifications are sent after order creation if enabled.

## Target Architecture

### Cart Storage

Use database-backed carts as the source of truth.

Recommended schema:

- `carts`
  - `id`
  - `buyer_id` nullable foreign key to `users_buyers`
  - `guest_token` nullable unique string
  - `status` string or enum: active, converted, abandoned
  - timestamps
  - indexes on `buyer_id`, `guest_token`, and active status
- `cart_items`
  - `id`
  - `cart_id`
  - `product_id`
  - `quantity`
  - `price_when_added` nullable decimal
  - timestamps
  - unique cart/product pair

Cart items should store enough for validation and warnings, but not final order snapshots. Final immutable snapshots belong to order creation.

LaraCart decision:

- Do not rely on LaraCart as the source of truth for marketplace checkout.
- Either remove it from buyer cart flow or keep it only as a temporary adapter while all cart reads/writes go through `CartService`/actions.
- Do not trust LaraCart item price/options for checkout totals.

### Cart Actions And Services

Create dedicated action/service classes:

- `App\Actions\Cart\ResolveCartAction`
- `App\Actions\Cart\AddCartItemAction`
- `App\Actions\Cart\UpdateCartItemQuantityAction`
- `App\Actions\Cart\RemoveCartItemAction`
- `App\Actions\Cart\ClearCartAction`
- `App\Actions\Cart\MergeGuestCartAction`
- `App\Actions\Cart\ValidateCartAction`
- `App\Actions\Cart\CalculateCartTotalsAction`
- `App\Actions\Checkout\CreateOrdersFromCartAction`

Livewire components should call these actions and only handle UI state, validation display, redirects, and notifications.

### Checkout Flow

Create a dedicated checkout Livewire route/page, separate from cart.

Suggested route:

- `buyer.checkout.index`

Suggested steps:

1. Cart review.
2. Buyer information.
3. Shipping/pickup details using current buyer profile address as the minimum first version.
4. Billing address if needed later.
5. Delivery method if needed later.
6. Payment method if needed later.
7. Validation and confirmation.
8. Final order creation.
9. Success state.

Minimum first implementation for this codebase:

- Use buyer profile fields as address snapshot until normalized addresses are finalized.
- Payment method can default to a configured/manual method only if no real payment methods exist.
- Do not mark orders paid automatically unless payment is actually confirmed. Use `payment_status=pending` and `status=pending/accepted` according to the status model.

### Checkout Transaction

Final order creation must run in one database transaction.

Inside transaction:

- Reload and lock all product rows for cart product ids.
- Validate cart still belongs to current buyer or guest-to-buyer merge result.
- Validate cart is not empty.
- Validate buyer is active and allowed to order.
- Validate every product exists, is not deleted, is active, and has enough stock.
- Validate every seller exists, is not deleted, is active, and is allowed to sell.
- Recalculate unit price, item subtotal, seller subtotal, VAT, portal fee, and total from backend data.
- Create order rows.
- Create order item rows.
- Save product title snapshot.
- Save product price snapshot.
- Save quantity snapshot.
- Save seller snapshot.
- Save buyer address snapshot.
- Decrease stock.
- Create order status history rows.
- Dispatch notifications after commit if needed.
- Clear or mark the cart converted only after successful order creation.

### Multi-Seller Decision

Decision: one checkout creates separate orders per seller.

Rationale:

- The current marketplace has seller-owned order item views and seller balances.
- A single shared order status lets one seller change the status for all sellers in the same buyer order.
- Separate seller orders simplify seller fulfillment, seller transactions, order status history, and partial seller failures.
- Buyer order index can still group orders visually by checkout batch later if needed.

Implementation implication:

- `CreateOrdersFromCartAction` groups validated cart items by seller id.
- It creates one `orders` row per seller.
- Each order contains only that seller's items.
- Buyer sees all created orders after checkout.
- Seller updates only their own order.

### Order Snapshots

Add immutable snapshot columns.

Recommended `order_items` additions:

- `product_title_snapshot`
- `product_image_snapshot` nullable
- `product_unit_snapshot` nullable
- `seller_name_snapshot`
- `seller_company_snapshot` nullable
- `unit_price_snapshot` or use existing `unit_price`
- `total_price_snapshot` or use existing `total_price`

Recommended `orders` additions:

- `seller_id` if one order per seller.
- `subtotal`
- `vat_amount`
- `portal_fee`
- `order_total`
- `currency`
- `buyer_name_snapshot`
- `buyer_email_snapshot`
- `buyer_company_snapshot`
- `buyer_phone_snapshot`
- `shipping_address_snapshot` JSON/text
- `billing_address_snapshot` JSON/text nullable
- `checkout_batch_id` nullable UUID/string if grouping buyer checkout orders is useful.

### Policies

Policies should be guard-aware or explicit to current models.

Needed:

- Cart access: guest token owner or authenticated buyer owner.
- Checkout access: current buyer only.
- Order view: buyer owner, seller owner for seller orders, admin role for admin surface.
- Order update/status: seller can update only their own seller order; buyer can cancel only own pending orders; admin can use explicit admin flow.

Do not use the current generic `OrderPolicy` stub as-is.

## Query Delta Estimate

Current checkout:

- One order insert.
- One product select/lock per cart item.
- One order item insert per cart item.
- One product update per cart item.
- One order update.
- Totals calculated from session.

Target checkout:

- One cart query with items/product/seller eager loaded for display.
- One locked product query for all product ids during final transaction.
- One order insert per seller.
- One batch of order item inserts or one insert per item.
- One stock update per item/product.
- One order status history insert per order.
- No queries in Blade loops.

The target adds a small amount of write work for snapshots/status history but removes stale session totals and enables predictable validation.

## Reusable Snippet Direction

The main reusable surface should be action classes, not Blade or Livewire methods.

Example boundaries:

- `AddCartItemAction::handle(CartOwner $owner, Product $product, int $quantity): Cart`
- `ValidateCartAction::handle(Cart $cart): CartValidationResult`
- `CalculateCartTotalsAction::handle(Cart $cart): CartTotals`
- `CreateOrdersFromCartAction::handle(Cart $cart, Buyer $buyer, CheckoutData $data): Collection`

The result objects should carry warnings for unavailable items, price changes, and validation errors so both cart and checkout UI can display the same backend truth.

## Blade Usage Direction

Blade must receive preloaded view data from Livewire render methods.

Cart Blade should render:

- product image snapshot/current image
- product title
- seller
- backend price
- quantity controls
- item subtotal
- availability warning
- price-change warning
- remove button
- clear cart button
- total summary
- checkout button
- empty state

No product queries, cart lookups, or aggregate calculations should happen in Blade.

## Filament Integration

No Filament package or `app/Filament` resources were detected. The admin surface is custom Livewire + Blade.

The Filament section of `AGENTS.md` does not currently apply unless Filament is introduced later.

## Caveats

- The current dirty worktree includes unrelated or partially completed status/schema work. Cart/checkout implementation should either complete that status split or restore a coherent `Order` model before checkout tests can pass.
- Existing migrations have already run locally, but the prompt asks to run migrations from zero after implementation. New schema changes should be forward migrations unless old pending/unrun local migrations must be corrected before commit.
- SQLite is the current local DB engine. `lockForUpdate()` semantics are limited on SQLite compared with MySQL/Postgres, so tests should verify transaction behavior while production DB locking assumptions should be documented.
- Documentation and changelog updates are requested by the user and should be done after implementation, despite the project default of not creating docs unless requested.

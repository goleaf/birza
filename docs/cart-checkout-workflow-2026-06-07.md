# Cart And Checkout Workflow

Date: 2026-06-07

## Decision

One checkout creates separate orders per seller.

This keeps seller fulfillment, seller balances, order status history, and seller notifications isolated. A buyer can still checkout one mixed cart, but each seller receives a separate `orders` row containing only that seller's `order_items`.

## Cart Source Of Truth

Cart state is database-backed:

- `carts` stores the active cart header.
- `carts.user_id` links authenticated buyer carts.
- `carts.guest_token` links guest carts to the browser session.
- `carts.status` distinguishes active carts from converted carts.
- `cart_items` stores product lines, quantity, and the price when the item was added.

LaraCart is no longer used by the runtime buyer cart, product add-to-cart, header count, or checkout flow.

## Actions

Cart and checkout business logic lives in dedicated actions:

- `ResolveCartAction`
- `AddCartItemAction`
- `UpdateCartItemQuantityAction`
- `RemoveCartItemAction`
- `ClearCartAction`
- `MergeGuestCartAction`
- `ValidateCartAction`
- `CalculateCartTotalsAction`
- `CreateOrdersFromCartAction`

Livewire components call these actions and do not create orders or mutate stock directly.

## Checkout Validation

Checkout revalidates cart items on the backend. It rejects checkout when:

- the cart is empty,
- the buyer is inactive,
- the product is deleted,
- the product is inactive,
- the product price is invalid,
- stock is insufficient,
- the seller is inactive or deleted,
- shipping address is missing,
- payment method is missing,
- the cart belongs to another buyer.

Product price, stock, seller, status, and buyer eligibility are never trusted from the frontend.

## Order Creation

`CreateOrdersFromCartAction` runs inside a database transaction.

During final order creation it:

- locks the cart and product rows,
- validates all cart items before creating orders,
- groups items by seller,
- creates one order per seller,
- creates order items,
- stores product title snapshots,
- stores product price snapshots,
- stores seller name snapshots,
- stores buyer shipping and billing address snapshots,
- calculates subtotal and total from backend product prices,
- decreases stock,
- creates order status history,
- sends marketplace notifications,
- clears and converts the cart only after successful order creation.

Failed checkout does not create partial orders and does not clear cart items.

## UI

The buyer cart page supports:

- guest cart display,
- authenticated buyer cart display,
- cart item count,
- item totals,
- backend-calculated totals,
- quantity increase and decrease,
- manual quantity update,
- item removal,
- clear cart,
- unavailable item warnings,
- price change warnings,
- checkout review and confirmation states,
- shipping address,
- billing address,
- delivery method,
- payment method,
- loading-disabled actions.

Guest checkout redirects to buyer login. Buyer login and buyer registration merge the guest cart into the authenticated buyer cart.

## Tests

Focused coverage is in:

- `tests/Feature/Cart/CartWorkflowTest.php`
- `tests/Feature/Marketplace/CartCheckoutFeatureTest.php`
- `tests/Feature/Controllers/Frontend/Buyer/CartControllerTest.php`
- `tests/Unit/Models/CartTest.php`

These cover guest cart add, authenticated cart add, login merge action behavior, quantity changes, remove, clear, empty cart rejection, deleted product rejection, stock rejection, backend price recalculation, snapshot persistence, transaction rollback, one-order-per-seller checkout, and foreign-cart checkout rejection.

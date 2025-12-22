<?php

namespace App\Http\Controllers\Frontend\Buyer;

use LukePOLO\LaraCart\Facades\LaraCart;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Country;
use Illuminate\Support\Facades\Cache;
use App\Models\GlobalSettings;

class CartController extends Controller
{
    public function index(): View
    {
        $cartTotals = $this->calculateCartTotals();
        
        return view('frontend.buyer.cart.index', [
            'cartTotals' => $cartTotals,
            'countries' => Country::pluck('country_name', 'alpha2')->toArray()
        ]);
    }
    public function calculateCartTotals()
    {
        $cartTotal = LaraCart::total(2);
        $cartTotal = preg_replace('/[^0-9,.]/', '', $cartTotal);
        $cartTotal = number_format((float) str_replace(',', '.', $cartTotal), 2, '.', '');

        $vatRate = config('app.vat_rate');
        $vatAmount = round($cartTotal * $vatRate, 2);
        $portalPrice = $this->getPortalPrice();
        $totalWithVatAndPortal = round($cartTotal + $vatAmount + (int)$portalPrice, 2);

        return [
            'cartTotal' => $cartTotal,
            'vatAmount' => $vatAmount,
            'portalPrice' => $portalPrice,
            'totalWithVatAndPortal' => $totalWithVatAndPortal
        ];
    }

    private function getPortalPrice()
    {
        // Get portal price from GlobalSettings or return default 0
        return GlobalSettings::first()->portal_additional_price ?? 0;
    }

    public function addToCart(Product $product): RedirectResponse
    {
        if ($product->stock === 0) {
            return redirect()->back()->with('message', __('cart.messages.out_of_stock'));
        }

        // Get quantity from form input, default to min order count if not provided
        $quantity = (int) request()->input('quantity', $product->min_order_count);

        // Check minimum order quantity
        if ($quantity < $product->min_order_count) {
            return redirect()->back()->with('message', __('cart.messages.minimum_quantity', [
                'min' => $product->min_order_count,
                'product' => $product->name
            ]));
        }

        // Check maximum order quantity (stock)
        if ($quantity > $product->stock) {
            return redirect()->back()->with('message', __('cart.messages.maximum_quantity', [
                'max' => $product->stock,
                'product' => $product->name
            ]));
        }

        // Check if product already exists in cart
        $cartItems = LaraCart::getItems();
        $cartItem = null;
        foreach ($cartItems as $item) {
            if ($item->id == $product->id) {
                $cartItem = $item;
                break;
            }
        }

        if ($cartItem) {
            // Update quantity if product exists
            $newQty = $cartItem->qty + $quantity;
            
            // Check if new total quantity exceeds stock
            if ($newQty > $product->stock) {
                return redirect()->back()->with('message', __('cart.messages.maximum_total_quantity', [
                    'max' => $product->stock,
                    'product' => $product->name
                ]));
            }

            // Check if new total quantity meets minimum order requirement
            if ($newQty < $product->min_order_count) {
                return redirect()->back()->with('message', __('cart.messages.minimum_total_quantity', [
                    'min' => $product->min_order_count,
                    'product' => $product->name,
                    'current' => $cartItem->qty
                ]));
            }
            
            LaraCart::updateItem($cartItem->getHash(), 'qty', $newQty);
        } else {
            // Add new product if it doesn't exist
            LaraCart::add(
                $product->id,
                $product->name,
                $quantity,
                $product->price,
                [
                    'image' => $product->product_image,
                    'unit' => $product->unit,
                    'seller_id' => $product->seller_id,
                    'category_id' => $product->category_id,
                    'min_order_price' => $product->min_order_price,
                    'min_order_count' => $product->min_order_count,
                    'is_organic' => $product->is_organic,
                    'country_of_origin' => $product->country_of_origin,
                    'package_weight' => $product->package_weight,
                    'price_per_liter' => $product->price_per_liter,
                    'stock' => $product->stock
                ]
            );
        }

        return redirect()->back()->with('success', __('cart.messages.product_added'));
    }

    public function updateQuantity(Request $request, string $itemHash): RedirectResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = null;
        $cartItems = LaraCart::getItems();
        foreach ($cartItems as $item) {
            if ($item->getHash() === $itemHash) {
                $cartItem = $item;
                break;
            }
        }

        if (!$cartItem) {
            return redirect()->back()->with('message', __('cart.messages.item_not_found'));
        }

        $product = Product::find($cartItem->id);
        if (!$product) {
            LaraCart::removeItem($itemHash);
            return redirect()->back()->with('message', __('cart.messages.product_not_found'));
        }

        $quantity = (int) $request->input('quantity');

        if ($quantity < $product->min_order_count) {
            return redirect()->back()->with('message', __('cart.messages.minimum_quantity', ['min' => $product->min_order_count]));
        }

        if ($quantity > $product->stock) {
            return redirect()->back()->with('message', __('cart.messages.insufficient_stock'));
        }

        LaraCart::updateItem($itemHash, 'qty', $quantity);
        LaraCart::update();

        return redirect()->back()->with('success', __('cart.messages.quantity_updated'));
    }

    public function removeFromCart(string $itemHash): RedirectResponse
    {
        $cartItem = null;
        $cartItems = LaraCart::getItems();
        foreach ($cartItems as $item) {
            if ($item->getHash() === $itemHash) {
                $cartItem = $item;
                break;
            }
        }

        if (!$cartItem) {
            return redirect()->back()->with('message', __('cart.messages.item_not_found'));
        }

        LaraCart::removeItem($itemHash);
        LaraCart::update();

        return redirect()->back()->with('success', __('cart.messages.product_removed'));
    }

    public function checkout(Request $request): RedirectResponse
    {
        // dump('Starting checkout process');

        if (LaraCart::count() === 0) {
            dd('Cart is empty');
            return redirect()->back()->with('message', __('cart.messages.empty_cart'));
        }

        // dump('Cart items:', LaraCart::getItems());

        DB::beginTransaction();
        // dump('Started database transaction');

      //  try {
            $orderData = [
                'buyer_id' => Auth::id(),
                'order_total' => (float) collect(LaraCart::getItems())->sum(function($item) {
                    return $item->qty * $item->price;
                }),
                'payment_status' => Order::STATUS['PENDING'],
            ];
            // dump('Creating order with data:', $orderData);

            $order = Order::create($orderData);
            // dump('Created order:', $order);

            foreach (LaraCart::getItems() as $item) {
                // dump('Processing cart item:', $item);

                $product = Product::lockForUpdate()->find($item->id);
                // dump('Found product:', $product);

                if (!$product || $product->stock < $item->qty) {
                    dump('Stock validation failed', [
                        'product' => $product,
                        'requested_qty' => $item->qty
                    ]);
                    throw new \Exception(__('cart.messages.stock_changed'));
                }

                $orderItemData = [
                    'order_id' => $order->id,
                    'product_id' => $item->id,
                    'quantity' => $item->qty,
                    'unit_price' => $item->price,
                    'total_price' => $item->qty * $item->price,
                    'seller_id' => $item->options['seller_id']
                ];
                // dump('Creating order item with data:', $orderItemData);

                $orderItem = OrderItem::create($orderItemData);
                // dump('Created order item:', $orderItem);

                $product->decrement('stock', $item->qty);
                // dump('Updated product stock. New stock:', $product->stock);
            }

            // dump('Processing payment for order:', $order->id);

            if (!$this->processPayment($order)) {
                // dd('Payment processing failed');
                throw new \Exception(__('cart.messages.payment_failed'));
            }

            // dump('Payment processed successfully');

            $order->update([
                'status' => Order::STATUS['PAID'],
                'payment_date' => now()
            ]);

            // dump('Updated order status to PAID:', $order);

            DB::commit();
            // dump('Database transaction committed');

            LaraCart::destroyCart();
            // dd('Cart destroyed');

            return redirect()->route('buyer.orders.index')->with('success', __('cart.messages.order_placed'));
/*
        } catch (\Exception $e) {
            dd('Error occurred:', $e->getMessage());
            DB::rollBack();
            dd('Database transaction rolled back');

            if (isset($order)) {
                $order->update(['status' => Order::STATUS['FAILED']]);
                dd('Updated order status to FAILED:', $order);
            }
            return redirect()->back()->with('message', $e->getMessage());
        }


        */
    }

    protected function processPayment(Order $order): bool
    {
        // dump('Simulating payment processing for order:', $order->id);
        sleep(1);
        // dump('Payment simulation complete');
        return true;
    }

}

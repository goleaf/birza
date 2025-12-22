<?php

namespace App\Http\Controllers\Backend;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Auth::user()->cart()->with('product')->get();
        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return view('cart.index', ['cartItems' => $cartItems, 'total' => $total]);
    }

    public function add(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $product->id
            ],
            [
                'quantity' => Cart::where([
                    ['user_id', Auth::id()],
                    ['product_id', $product->id]
                ])->first()->quantity ?? 0 + $request->quantity
            ]
        );

        return redirect()->route('cart.index')->with('success', 'Product added to cart successfully.');
    }

    public function update(Request $request, Cart $cart)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart->update(['quantity' => $request->quantity]);

        return redirect()->route('cart.index')->with('success', 'Cart updated successfully.');
    }

    public function remove(Cart $cart)
    {
        $cart->delete();

        return redirect()->route('cart.index')->with('success', 'Product removed from cart successfully.');
    }
}

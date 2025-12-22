<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Users\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $query = Seller::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%' . $request->search . '%';
                $q->where(function($query) use ($search) {
                    $query->where('name', 'like', $search)
                          ->orWhere('email', 'like', $search)
                          ->orWhere('company_name', 'like', $search)
                          ->orWhere('vat_code', 'like', $search)
                          ->orWhere('phone', 'like', $search);
                });
            })
            ->when($request->filled('is_active'), function ($q) use ($request) {
                $q->where('is_active', $request->is_active === 'true');
            })
            ->when($request->filled('sort'), function ($q) use ($request) {
                [$column, $direction] = explode(',', $request->sort);
                $q->orderBy($column, $direction);
            }, function ($q) {
                $q->latest();
            });

        $sellers = $query->paginate(15)->withQueryString();
        
        return view('backend.sellers.index', [
            'sellers' => $sellers,
            'filters' => $request->all()
        ]);
    }

    public function create()
    {
        return view('backend.sellers.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users_sellers',
            'password' => 'required|string|min:8',
            'company_name' => 'required|string|max:255',
            'vat_code' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = true; // Set to true by default for admin-created sellers
        Seller::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_name' => $validated['company_name'],
            'vat_code' => $validated['vat_code'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->route('backend.sellers.index')->with('success', __('backend.common.success_message'));
    }

    public function edit(Seller $seller)
    {
        return view('backend.sellers.form', ['seller' => $seller]);
    }

    /**
     * Display the specified seller.
     */
    public function show(Seller $seller)
    {
        $products = $seller->products()
            ->select('id', 'name', 'price', 'is_active', 'product_image')
            ->withCount('orderItems')
            ->latest()
            ->paginate(10);

        $orders = $seller->orders()
            ->with([
                'buyer',
                'orderItems',
            ])
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return view('backend.sellers.show', [
            'seller' => $seller,
            'products' => $products,
            'recentOrders' => $orders,
            'orderStatuses' => Order::STATUS
        ]);
    }

    public function update(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users_sellers,email,' . $seller->id,
            'company_name' => 'required|string|max:255',
            'vat_code' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8',
            ]);
            $seller->update(['password' => Hash::make($request->password)]);
        }

        $seller->update($validated);

        return redirect()->route('backend.sellers.index')->with('success', __('backend.common.success_message'));
    }

    public function destroy(Seller $seller)
    {
        $seller->delete();
        return redirect()->route('backend.sellers.index')->with('success', __('backend.common.delete_message'));
    }

    public function showOrders(Seller $seller)
    {
        $orders = Order::where('seller_id', $seller->id)->get();
        return view('backend.sellers.orders', ['orders' => $orders, 'seller' => $seller]);
    }

    public function orders($id)
    {
        $seller = Seller::findOrFail($id);
        $orders = Order::whereHas('orderItems.product', function($query) use ($seller) {
            $query->where('seller_id', $seller->id);
        })
        ->with([
            'orderItems.product',
            'buyer',
         
        ])->get();

        return view('backend.sellers.orders', [
            'orders' => $orders,
            'seller' => $seller,
            'orderStatuses' => Order::STATUS
        ]);
    }

}
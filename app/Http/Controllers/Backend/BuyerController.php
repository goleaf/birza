<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Users\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class BuyerController extends Controller
{
    public function index(Request $request)
    {
        $query = Buyer::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%' . $request->search . '%';
                $q->where(function($query) use ($search) {
                    $query->where('name', 'like', $search)
                          ->orWhere('email', 'like', $search)
                          ->orWhere('company_name', 'like', $search)
                          ->orWhere('company_code', 'like', $search)
                          ->orWhere('vat_code', 'like', $search);
                });
            })
            ->when($request->filled('is_verified'), function ($q) use ($request) {
                $q->where('is_verified', $request->is_verified === 'true');
            })
            ->when($request->filled('is_active'), function ($q) use ($request) {
                $q->where('is_active', $request->is_active === 'true');
            })
            ->when($request->filled('min_balance'), function ($q) use ($request) {
                $q->where('credit_balance', '>=', $request->min_balance);
            })
            ->when($request->filled('max_balance'), function ($q) use ($request) {
                $q->where('credit_balance', '<=', $request->max_balance);
            })
            ->when($request->filled('sort'), function ($q) use ($request) {
                [$column, $direction] = explode(',', $request->sort);
                $q->orderBy($column, $direction);
            }, function ($q) {
                $q->latest();
            });

        $buyers = $query->paginate(15)->withQueryString();
        
        return view('backend.buyers.index', [
            'buyers' => $buyers,
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
            'email' => 'required|string|email|max:255|unique:users_buyers',
            'password' => 'required|string|min:8',
            'company_name' => 'required|string|max:255',
            'company_code' => 'required|string|max:255',
            'vat_code' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_verified'] = true; // Set to true by default for admin-created buyers
        $validated['is_active'] = true; // Set to true by default for admin-created buyers
        $buyer = Buyer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_name' => $validated['company_name'],
            'company_code' => $validated['company_code'],
            'vat_code' => $validated['vat_code'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'is_verified' => $validated['is_verified'],
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->route('backend.buyers.index')->with('success', __('backend.common_success_message'));
    }

    public function edit(Buyer $buyer)
    {
        return view('backend.buyers.form', ['buyer' => $buyer]);
    }

    public function update(Request $request, Buyer $buyer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users_buyers,email,' . $buyer->id,
            'company_name' => 'required|string|max:255',
            'company_code' => 'required|string|max:255',
            'vat_code' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $buyer->update($validated);

        return redirect()->route('backend.buyers.index')->with('success', __('backend.common_success_message'));
    }

    public function destroy(Buyer $buyer)
    {
        $buyer->delete();
        return redirect()->route('backend.buyers.index')->with('success', __('backend.common_delete_message'));
    }

    public function orders(Request $request, Buyer $buyer)
    {
        $query = Order::with(['items.product', 'items.seller'])
            ->where('buyer_id', $buyer->id);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(15);

        return view('backend.buyers.orders', [
            'buyer' => $buyer,
            'orders' => $orders
        ]);
    }

    public function showCredit(Buyer $buyer)
    {
        $creditHistory = $buyer->creditHistory()
            ->with('admin')
            ->latest()
            ->paginate(10);
            
        return view('backend.buyers.credit', compact('buyer', 'creditHistory'));
    }

    public function updateCredit(Request $request, Buyer $buyer)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'action' => 'required|in:add,deduct',
            'note' => 'nullable|string|max:255',
        ]);

        $amount = (float) $request->amount;
        $adminId = Auth::id();

        if ($request->action === 'add') {
            $buyer->addCredit($amount, $adminId, $request->note);
            $message = __('backend_credit_added', ['amount' => number_format($amount, 2)]);
        } else {
            if ($buyer->deductCredit($amount, $adminId, $request->note)) {
                $message = __('backend_credit_deducted', ['amount' => number_format($amount, 2)]);
            } else {
                return back()->withErrors(['amount' => __('backend_credit_insufficient_funds')]);
            }
        }

        return back()->with('success', $message);
    }
}

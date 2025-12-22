<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::withFullDetails();

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

        // Get orders with pagination
        $orders = $query->latest()->paginate(10);

        // Calculate statistics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('payment_status', Order::STATUS['PENDING'])->count();
        
        $averageOrderValue = Order::where('payment_status', Order::STATUS['PAID'])
            ->avg('order_total') ?? 0;
            
        $totalRevenue = Order::where('payment_status', Order::STATUS['PAID'])
            ->sum('order_total') ?? 0;

        return view('backend.orders.index', compact(
            'orders',
            'totalOrders',
            'pendingOrders',
            'averageOrderValue',
            'totalRevenue'
        ));
    }

    public function show(Order $order)
    {
        $order->load(['buyer', 'orderItems.product', 'orderItems.seller']);
        return view('backend.orders.show', compact('order'));
    }
}

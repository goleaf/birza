<?php

namespace App\Livewire\Backend\Orders;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    public function render()
    {
        $request = request();

        $query = Order::withFullDetails();

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('backend.orders.index', [
            'orders' => $orders,
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('payment_status', Order::STATUS['PENDING'])->count(),
            'averageOrderValue' => Order::where('payment_status', Order::STATUS['PAID'])->avg('order_total') ?? 0,
            'totalRevenue' => Order::where('payment_status', Order::STATUS['PAID'])->sum('order_total') ?? 0,
        ]);
    }
}



<?php

namespace App\Http\Controllers\Frontend\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Controller for managing buyer orders
 */
class OrderController extends Controller
{
    /**
     * Display a listing of buyer's orders
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $buyer = Auth::user();

        // Get filter parameters
        $orderStatuses = Order::STATUS;
        $status = $request->get('status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $filters = [
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ];

        $ordersData = $this->getOrdersData($buyer, $status, $dateFrom, $dateTo);

        return view('frontend.buyer.orders.index', [
            'ordersData' => $ordersData,
            'filters' => $filters,
            'orderStatuses' => $orderStatuses
        ]);
    }

    /**
     * Show the order details
     *
     * @param Order $order
     * @return \Illuminate\View\View
     */
    public function show(Order $order)
    {
        // Ensure the order belongs to the authenticated buyer
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        return view('frontend.buyer.orders.show', [
            'order' => $order->load(['items.product', 'items.seller'])
        ]);
    }

    /**
     * Get orders data with filters
     *
     * @param \App\Models\Users\Buyer $buyer
     * @param string|null $status
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return array
     */
    protected function getOrdersData($buyer, $status = null, $dateFrom = null, $dateTo = null)
    {
        $query = Order::where('buyer_id', $buyer->id);

        // Apply status filter
        if ($status) {
            $query->where('payment_status', $status);
        }

        // Apply date filters
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', Carbon::parse($dateFrom));
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', Carbon::parse($dateTo));
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return [
            'all' => $orders,
            'pending' => $orders->where('payment_status', Order::STATUS['PENDING'])->count(),
            'processing' => $orders->where('payment_status', Order::STATUS['PROCESSING'])->count(),
            'shipped' => $orders->where('payment_status', Order::STATUS['SHIPPED'])->count(),
            'delivered' => $orders->where('payment_status', Order::STATUS['DELIVERED'])->count(),
            'cancelled' => $orders->where('payment_status', Order::STATUS['CANCELLED'])->count(),
            'refunded' => $orders->where('payment_status', Order::STATUS['REFUNDED'])->count(),
            'paid' => $orders->where('payment_status', Order::STATUS['PAID'])->count(),
            'failed' => $orders->where('payment_status', Order::STATUS['FAILED'])->count(),
            'total' => $orders->count(),
            'totalSpent' => $orders->where('payment_status', Order::STATUS['PAID'])
                ->sum('order_total')
        ];
    }

    /**
     * Cancel an order
     *
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Order $order)
    {
        // Ensure the order belongs to the authenticated buyer
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        // Only allow cancellation of pending orders
        if ($order->payment_status !== Order::STATUS['PENDING']) {
            return redirect()->back()->with('error', __('orders.messages.cannot_cancel'));
        }

        DB::transaction(function () use ($order) {
            // Update order status
            $order->update(['payment_status' => Order::STATUS['CANCELLED']]);

            // Restore product stock
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        });

        return redirect()->back()->with('success', __('orders.messages.cancelled_success'));
    }
}

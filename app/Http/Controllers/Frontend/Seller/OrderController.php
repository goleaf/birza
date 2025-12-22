<?php

namespace App\Http\Controllers\Frontend\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Controller for managing seller orders
 */
class OrderController extends Controller
{
    /**
     * Display a listing of seller's orders
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $seller = Auth::guard('seller')->user();

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

        $ordersData = $this->getOrdersData($seller, $status, $dateFrom, $dateTo);

        return view('frontend.seller.orders.index', [
            'ordersData' => $ordersData,
            'filters' => $filters,
            'orderStatuses' => $orderStatuses
        ]);
    }

    /**
     * Update order status
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', Order::STATUS)],
            'comment' => ['nullable', 'string', 'max:500']
        ]);

        $seller = Auth::guard('seller')->user();
        
        // Check if the seller has items in this order
        $hasItems = $order->orderItems()->where('seller_id', $seller->id)->exists();
        if (!$hasItems) {
            return back()->with('error', __('orders.unauthorized_update'));
        }

        // Begin transaction
        DB::beginTransaction();
        try {
            $oldStatus = $order->payment_status;
            $newStatus = $validated['status'];
            
            // Get seller's items in this order
            $sellerItems = $order->orderItems()
                ->where('seller_id', $seller->id)
                ->get();

            // Calculate total amount for seller
            $totalAmount = $sellerItems->sum('total_price');

            // Handle status change
            if ($newStatus === Order::STATUS['PAID'] && $oldStatus === Order::STATUS['PENDING']) {
                // Add to seller's balance when order is confirmed
                $seller->increment('balance', $totalAmount);
                
                // Create transaction record for addition
                $seller->transactions()->create([
                    'order_id' => $order->id,
                    'amount' => $totalAmount,
                    'type' => 'addition',
                    'description' => 'Order #' . $order->id . ' confirmed - Balance added'
                ]);

                // Update order status
                $order->payment_status = $newStatus;
            } 
            // Handle order cancellation
            elseif ($newStatus === Order::STATUS['CANCELLED']) {
                if ($oldStatus === Order::STATUS['PAID']) {
                    // Deduct balance from seller if cancelling a confirmed order
                    $seller->decrement('balance', $totalAmount);
                    
                    // Create transaction record for deduction
                    $seller->transactions()->create([
                        'order_id' => $order->id,
                        'amount' => -$totalAmount,
                        'type' => 'deduction',
                        'description' => 'Order #' . $order->id . ' cancelled - Balance deducted'
                    ]);
                }

                // Update order status
                $order->payment_status = $newStatus;
            }

            // Update comment if provided
            if (!empty($validated['comment'])) {
                $order->status_comment = $validated['comment'];
            }

            // Save the order
            $order->save();

            DB::commit();
            return back()->with('success', __('orders.status_updated'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('orders.update_failed'));
        }
    }

    protected function getOrdersData($seller, $status = null, $dateFrom = null, $dateTo = null)
    {
        $query = OrderItem::with(['order', 'order.buyer', 'product'])
            ->where('seller_id', $seller->id);

        // Apply date filters if provided
        if ($dateFrom) {
            $query->whereHas('order', function($q) use ($dateFrom) {
                $q->whereDate('created_at', '>=', Carbon::parse($dateFrom));
            });
        }
        if ($dateTo) {
            $query->whereHas('order', function($q) use ($dateTo) {
                $q->whereDate('created_at', '<=', Carbon::parse($dateTo));
            });
        }

        // Apply status filter if provided
        if ($status) {
            $query->whereHas('order', function($q) use ($status) {
                $q->where('payment_status', $status);
            });
        }

        $orderItems = $query->get();
        $orders = $orderItems->map(function($item) {
            return $item->order;
        })->unique('id');

        return [
            'all' => $orders,
            'pending' => $orders->where('payment_status', Order::STATUS['PENDING'])->count(),
            'processing' => $orders->where('payment_status', Order::STATUS['PROCESSING'])->count(),
            'shipped' => $orders->where('payment_status', Order::STATUS['SHIPPED'])->count(),
            'delivered' => $orders->where('payment_status', Order::STATUS['DELIVERED'])->count(),
            'cancelled' => $orders->where('payment_status', Order::STATUS['CANCELLED'])->count(),
            'refunded' => $orders->where('payment_status', Order::STATUS['REFUNDED'])->count(),
            'total' => $orders->count(),
            'items' => $orderItems->map(function($item) {
                return [
                    'order' => $item->order,
                    'product' => $item->product,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'buyer' => $item->order->buyer
                ];
            }),
            'totalRevenue' => $orders->where('payment_status', Order::STATUS['PAID'])
                ->sum('order_total'),
            'revenueStats' => $this->getRevenueStatistics($seller),
            'topProducts' => $this->getTopSellingProducts($seller),
            'averageOrderValue' => $this->calculateAverageOrderValue($orders)
        ];
    }

    /**
     * Get revenue statistics by different time periods
     *
     * @param \App\Models\Users\Seller $seller
     * @return array
     */
    protected function getRevenueStatistics($seller)
    {
        $today = Carbon::today();

        return [
            'daily' => OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', function($q) use ($today) {
                    $q->whereDate('created_at', $today)
                      ->where('payment_status', Order::STATUS['PAID']);
                })
                ->sum('total_price'),

            'weekly' => OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', function($q) use ($today) {
                    $q->whereBetween('created_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])
                      ->where('payment_status', Order::STATUS['PAID']);
                })
                ->sum('total_price'),

            'monthly' => OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', function($q) use ($today) {
                    $q->whereMonth('created_at', $today->month)
                      ->where('payment_status', Order::STATUS['PAID']);
                })
                ->sum('total_price'),

            'yearly' => OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', function($q) use ($today) {
                    $q->whereYear('created_at', $today->year)
                      ->where('payment_status', Order::STATUS['PAID']);
                })
                ->sum('total_price')
        ];
    }

    /**
     * Get top selling products for the seller
     *
     * @param \App\Models\Users\Seller $seller
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    protected function getTopSellingProducts($seller, $limit = 5)
    {
        return OrderItem::where('seller_id', $seller->id)
            ->whereHas('order', function($q) {
                $q->where('payment_status', Order::STATUS['PAID']);
            })
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate average order value
     *
     * @param \Illuminate\Support\Collection $orders
     * @return float
     */
    protected function calculateAverageOrderValue($orders)
    {
        $paidOrders = $orders->where('payment_status', Order::STATUS['PAID']);
        if ($paidOrders->isEmpty()) {
            return 0;
        }
        return $paidOrders->avg('order_total');
    }

    /**
     * Display details of a specific order
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\View\View
     */
    public function show(Order $order)
    {
        $orderItems = OrderItem::with(['order', 'product'])
            ->where('seller_id', Auth::guard('seller')->id())
            ->where('order_id', $order->id)
            ->get();

        if ($orderItems->isEmpty()) {
            abort(403, 'Unauthorized access to order');
        }

        return view('frontend.seller.orders.show', [
            'order' => $order,
            'orderItems' => $orderItems,
            'timeline' => $this->getOrderTimeline($order),
            'orderStatuses' => Order::STATUS
        ]);
    }

    /**
     * Get order status timeline
     *
     * @param \App\Models\Order $order
     * @return array
     */
    protected function getOrderTimeline($order)
    {
        return [
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    }



    /**
     * Export orders to CSV/Excel
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        // Implementation for exporting orders
        // Could use Laravel Excel or custom CSV generation
    }
}

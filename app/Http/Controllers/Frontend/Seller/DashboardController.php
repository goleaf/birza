<?php

namespace App\Http\Controllers\Frontend\Seller;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $seller = Auth::guard('seller')->user();
        $categoriesData = $this->getCategoriesData($seller);
        $ordersData = $this->getOrdersData($seller);

        return view('frontend.seller.dashboard.index', [
            'seller' => $seller,
            'categoriesData' => $categoriesData,
            'ordersData' => $ordersData,
        ]);
    }

    protected function getCategoriesData($seller)
    {
        $categories = $seller->categories()
            ->withRelationsForSeller()
            ->with('parent')
            ->get();

        $groupedCategories = $categories->groupBy(function ($category) {
            return $category->parent_category_id ?? $category->id;
        });

        return $groupedCategories->map(function ($categories) {
            $firstCategory = $categories->first();
            $parentCategory = $firstCategory->parent ?? $firstCategory;
            $isSubcategory = (bool) $firstCategory->parent;

            return [
                'parentCategory' => $parentCategory,
                'categories' => $categories,
                'isSubcategory' => $isSubcategory
            ];
        });
    }

    protected function getOrdersData($seller)
    {
        $orderItems = OrderItem::with([
            'order',
            'order.buyer',
            'product',
        ])
            ->where('seller_id', $seller->id)
            ->latest()
            ->get();

        $orders = $orderItems->pluck('order')->unique('id')->values();
        
        $paidOrders = $orders->where('payment_status', Order::STATUS['PAID']);

        return [
            'total' => $orders->count(),
            'pending' => $orders->where('payment_status', Order::STATUS['PENDING'])->count(),
            'paid' => $paidOrders->count(),
            'failed' => $orders->where('payment_status', Order::STATUS['FAILED'])->count(),
            'totalRevenue' => $paidOrders->sum('order_total'),
            'recent' => $orderItems->take(5)
                ->map(function ($item) {
                    return [
                        'order' => $item->order,
                        'product' => $item->product,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'buyer' => $item->order->buyer
                    ];
                }),
            'allOrders' => $orderItems->groupBy('order_id')
                ->map(function ($items) {
                    $order = $items->first()->order;
                    return [
                        'order' => $order,
                        'items' => $items->map(function ($item) {
                            return [
                                'product' => $item->product,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'total_price' => $item->total_price
                            ];
                        }),
                        'buyer' => $order->buyer,
                        'total' => $items->sum('total_price')
                    ];
                })
        ];
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCategories = Category::count();
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $recentActivities = Activity::latest()->take(5)->get();

        return view('backend.dashboard.index', [
            'totalCategories' => $totalCategories,
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'recentActivities' => $recentActivities,
        ]);
    }
}

<?php

namespace App\Livewire\Backend;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Dashboard extends Component
{
    public function render()
    {
        return view('backend.dashboard.index', [
            'totalCategories' => Category::count(),
            'totalProducts' => Product::count(),
            'totalOrders' => Order::count(),
            'recentActivities' => Activity::latest()->take(5)->get(),
        ]);
    }
}



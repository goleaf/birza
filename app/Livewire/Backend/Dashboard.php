<?php

namespace App\Livewire\Backend;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Admin;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Dashboard extends Component
{
    public function mount(): void
    {
        Gate::authorize('viewAdminDashboard');
    }

    public function render(): View
    {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        return view('backend.dashboard.index', [
            'totalCategories' => Category::count(),
            'totalProducts' => Product::count(),
            'totalOrders' => Order::count(),
            'recentActivities' => Activity::latest()->take(5)->get(),
            'recentNotifications' => $admin ? $this->recentNotifications($admin) : collect(),
        ]);
    }

    protected function recentNotifications(Admin $admin): Collection
    {
        return $admin->notifications()
            ->select(['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at'])
            ->latest()
            ->limit(5)
            ->get();
    }
}

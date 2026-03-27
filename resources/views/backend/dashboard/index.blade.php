<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold">{{ __('backend_dashboard_title') }}</h2>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-lg bg-blue-100 p-4">
            <h5 class="mb-2 font-medium text-blue-800">{{ __('backend_dashboard_stats_total_categories') }}</h5>
            <p class="text-center text-4xl font-bold text-blue-900">{{ $totalCategories }}</p>
        </div>
        <div class="rounded-lg bg-green-100 p-4">
            <h5 class="mb-2 font-medium text-green-800">{{ __('backend_dashboard_stats_total_products') }}</h5>
            <p class="text-center text-4xl font-bold text-green-900">{{ $totalProducts }}</p>
        </div>
        <div class="rounded-lg bg-yellow-100 p-4">
            <h5 class="mb-2 font-medium text-yellow-800">{{ __('backend_dashboard_stats_total_orders') }}</h5>
            <p class="text-center text-4xl font-bold text-yellow-900">{{ $totalOrders }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="p-6">
            <h3 class="mb-4 text-xl font-bold">{{ __('backend_dashboard_recent_activity_title') }}</h3>
            <ul class="divide-y divide-gray-200">
                @forelse ($recentActivities as $activity)
                    <li class="py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                                <span class="text-sm font-medium leading-none text-blue-600">
                                    {{ substr($activity->type ?? 'A', 0, 1) }}
                                </span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $activity->description }}</p>
                                <p class="text-sm text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-sm text-gray-500">{{ __('common_no_results') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

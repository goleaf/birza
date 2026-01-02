<x-backend.page :title="__('backend.dashboard.title')">
    <div class="space-y-6">
        <div class="stats stats-vertical lg:stats-horizontal shadow bg-base-100">
            <div class="stat">
                <div class="stat-title">{{ __('backend.dashboard.stats.total_categories') }}</div>
                <div class="stat-value text-primary">{{ $totalCategories }}</div>
            </div>
            <div class="stat">
                <div class="stat-title">{{ __('backend.dashboard.stats.total_products') }}</div>
                <div class="stat-value text-secondary">{{ $totalProducts }}</div>
            </div>
            <div class="stat">
                <div class="stat-title">{{ __('backend.dashboard.stats.total_orders') }}</div>
                <div class="stat-value text-accent">{{ $totalOrders }}</div>
            </div>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="text-xl font-semibold">{{ __('backend.dashboard.recent_activity.title') }}</h3>
                <ul class="divide-y divide-base-200">
                    @foreach ($recentActivities as $activity)
                        <li class="py-4">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-primary/10 text-primary rounded-full w-9">
                                        <span class="text-sm font-semibold">
                                            {{ substr($activity->type ?? 'A', 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium">{{ $activity->description }}</p>
                                    <p class="text-sm text-base-content/60">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-backend.page>

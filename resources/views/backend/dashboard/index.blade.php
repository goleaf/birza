<div class="space-y-6">
    <x-mary-header :title="__('backend_dashboard_title')" separator />

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

    <x-notifications.recent-panel
        :notifications="$recentNotifications"
        :href="route('admin.notifications.index')"
        :title="__('notifications.ui.admin_alerts')"
    />

    <x-mary-card :title="__('backend_dashboard_recent_activity_title')" shadow>
        @forelse ($recentActivities as $activity)
            <x-mary-list-item :item="$activity" @class(['rounded-xl'])>
                <x-slot:avatar>
                    <x-mary-avatar
                        :placeholder="strtoupper(substr((string) ($activity->type ?? 'A'), 0, 1))"
                        :alt="$activity->description ?? __('backend_dashboard_activity_item', ['id' => $activity->id])"
                        class="!w-11"
                    />
                </x-slot:avatar>

                <x-slot:value>
                    {{ $activity->description ?? __('backend_dashboard_activity_item', ['id' => $activity->id]) }}
                </x-slot:value>

                <x-slot:sub-value>
                    {{ $activity->created_at?->diffForHumans() ?? __('common_not_specified') }}
                </x-slot:sub-value>
            </x-mary-list-item>
        @empty
            <x-mary-alert
                :title="__('backend_dashboard_recent_activity_empty')"
                icon="o-exclamation-triangle"
                class="alert-info alert-soft"
                shadow
            />
        @endforelse
    </x-mary-card>
</div>

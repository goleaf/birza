<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('navigation_orders')],
        ]"
    />

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-mary-stat :title="__('orders_stats_total_orders')" :value="$totalOrders" icon="o-shopping-bag" color="text-primary" />
        <x-mary-stat :title="__('orders_pending_orders')" :value="$pendingOrders" icon="o-clock" color="text-warning" />
        <x-mary-stat :title="__('orders_stats_avg_order_value')" :value="'€' . number_format((float) $averageOrderValue, 2)" icon="o-banknotes" color="text-info" />
        <x-mary-stat :title="__('orders_stats_total_revenue')" :value="'€' . number_format((float) $totalRevenue, 2)" icon="o-chart-bar-square" color="text-success" />
    </div>

    <x-mary-header :title="__('orders_order_list')" separator progress-indicator>
        <x-slot:actions>
            <x-mary-button
                :label="__('common_filter')"
                icon="o-funnel"
                responsive
                @click="$wire.drawer = true"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <x-mary-table
            :headers="$headers"
            :rows="$orders"
            :sort-by="$sortBy"
            with-pagination
            per-page="perPage"
            :per-page-values="[10, 25, 50]"
            striped
            show-empty-text
        >
            @scope('cell_id', $order)
                <span class="font-medium">#{{ $order->id }}</span>
            @endscope

            @scope('cell_created_at', $order)
                {{ $order->created_at->format('Y-m-d H:i') }}
            @endscope

            @scope('cell_buyer', $order)
                @if ($order->buyer)
                    <div class="space-y-1">
                        <div class="font-medium">{{ $order->buyer->name }}</div>
                        <div class="text-xs text-base-content/60">{{ $order->buyer->email }}</div>
                    </div>
                @else
                    <span class="text-base-content/60">{{ __('common_not_specified') }}</span>
                @endif
            @endscope

            @scope('cell_status', $order)
                <x-mary-badge
                    :value="$order->paymentStatusLabel()"
                    class="{{ $order->paymentStatusBadgeClass() }}"
                />
            @endscope

            @scope('cell_order_total', $order)
                <span class="font-medium">€{{ number_format((float) $order->order_total, 2) }}</span>
            @endscope

            @scope('actions', $order)
                <div class="flex justify-end gap-1">
                    <x-mary-button
                        icon="o-eye"
                        :link="route('admin.orders.show', $order)"
                        class="btn-ghost btn-sm"
                        :tooltip="__('common_view')"
                    />
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>

    <x-mary-drawer
        wire:model="drawer"
        :title="__('common_filter')"
        right
        separator
        with-close-button
        class="w-full max-w-md"
    >
        <div class="space-y-4">
            <x-mary-select
                :label="__('orders.status.label')"
                wire:model.live="statusFilter"
                :options="$statusOptions"
                option-value="id"
                option-label="name"
                icon="o-check-badge"
                :placeholder="__('orders_filter_all_status')"
                placeholder-value=""
            />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-mary-datetime
                    :label="__('orders_table_date')"
                    wire:model.live="dateFrom"
                    icon="o-calendar-days"
                />
                <x-mary-datetime
                    :label="__('orders_table_date')"
                    wire:model.live="dateTo"
                    icon="o-calendar-days"
                />
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button
                :label="__('common_reset')"
                icon="o-arrow-path"
                wire:click="clear"
                spinner
            />
        </x-slot:actions>
    </x-mary-drawer>
</div>

@php
    $headers = [
        ['key' => 'id', 'label' => __('backend_orders_order_number'), 'class' => 'w-24'],
        ['key' => 'created_at', 'label' => __('backend_orders_date'), 'class' => 'w-40'],
        ['key' => 'order_total', 'label' => __('backend_orders_total'), 'class' => 'text-right w-36'],
        ['key' => 'status', 'label' => __('backend_orders_status'), 'class' => 'text-center w-40'],
        ['key' => 'actions', 'label' => __('backend_common_actions'), 'class' => 'text-right w-24'],
    ];
@endphp

<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('navigation_buyers'), 'link' => route('admin.buyers.index')],
            ['label' => $buyer->company_name ?: $buyer->name],
            ['label' => __('common_orders')],
        ]"
    />

    <x-mary-header
        :title="$buyer->company_name ?: $buyer->name"
        :subtitle="__('common_orders')"
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-mary-button
                :label="__('common_back')"
                :link="route('admin.buyers.index')"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <x-mary-table
            :headers="$headers"
            :rows="$orders"
            striped
            no-hover
            with-pagination
            show-empty-text
            :empty-text="__('backend_orders_no_orders')"
        >
            @scope('cell_id', $order)
                <span class="font-medium">#{{ $order->id }}</span>
            @endscope

            @scope('cell_created_at', $order)
                {{ $order->created_at?->format('Y-m-d H:i') ?? __('common_not_specified') }}
            @endscope

            @scope('cell_order_total', $order)
                <div class="text-right font-semibold">
                    €{{ number_format((float) $order->order_total, 2) }}
                </div>
            @endscope

            @scope('cell_status', $order)
                <div class="text-center">
                    <x-mary-badge
                        :value="$order->paymentStatusLabel()"
                        class="{{ $order->paymentStatusBadgeClass() }}"
                    />
                </div>
            @endscope

            @scope('actions', $order)
                <div class="flex justify-end">
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
</div>

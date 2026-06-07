@php
    $headers = [
        ['key' => 'id', 'label' => __('backend_orders_order_number'), 'class' => 'w-24'],
        ['key' => 'created_at', 'label' => __('backend_orders_date'), 'class' => 'w-40'],
        ['key' => 'order_total', 'label' => __('backend_orders_total'), 'class' => 'text-right w-36'],
        ['key' => 'payment_status', 'label' => __('backend_orders_status'), 'class' => 'text-center w-40'],
        ['key' => 'actions', 'label' => __('backend_common_actions'), 'class' => 'text-right w-24'],
    ];
@endphp

<div class="space-y-6">
    <x-mary-header
        :title="$buyer->company_name ?: $buyer->name"
        :subtitle="__('common_orders')"
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-mary-button
                :label="__('common_back')"
                :link="route('backend.buyers.index')"
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

            @scope('cell_payment_status', $order)
                @php
                    $statusClass = match ($order->payment_status) {
                        'paid' => 'badge-success badge-outline',
                        'pending' => 'badge-warning badge-outline',
                        'processing' => 'badge-info badge-outline',
                        'shipped' => 'badge-secondary badge-outline',
                        'delivered' => 'badge-success',
                        'cancelled', 'failed' => 'badge-error badge-outline',
                        'refunded' => 'badge-neutral badge-outline',
                        default => 'badge-neutral badge-outline',
                    };
                @endphp

                <div class="text-center">
                    <x-mary-badge
                        :value="__('orders_status_3_' . strtolower($order->payment_status))"
                        class="{{ $statusClass }}"
                    />
                </div>
            @endscope

            @scope('actions', $order)
                <div class="flex justify-end">
                    <x-mary-button
                        icon="o-eye"
                        :link="route('backend.orders.show', $order)"
                        class="btn-ghost btn-sm"
                        :tooltip="__('common_view')"
                    />
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>
</div>

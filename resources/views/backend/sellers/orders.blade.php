@php
    $headers = [
        ['key' => 'id', 'label' => __('orders_id'), 'class' => 'w-20'],
        ['key' => 'buyer', 'label' => __('orders_buyer'), 'class' => 'w-64'],
        ['key' => 'items_preview', 'label' => __('orders_products')],
        ['key' => 'created_at', 'label' => __('orders_date'), 'class' => 'w-44'],
        ['key' => 'status', 'label' => __('orders.status.label'), 'class' => 'text-center w-40'],
        ['key' => 'actions', 'label' => __('orders_actions'), 'class' => 'text-right w-24'],
    ];
@endphp

<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('navigation_sellers'), 'link' => route('admin.sellers.index')],
            ['label' => $seller->company_name ?: $seller->name, 'link' => route('admin.sellers.show', $seller)],
            ['label' => __('common_orders')],
        ]"
    />

    <x-mary-header
        :title="$seller->company_name ?: $seller->name"
        :subtitle="__('sellers_orders')"
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-mary-button
                :label="__('common_back')"
                :link="route('admin.sellers.show', $seller)"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <x-mary-table
            :headers="$headers"
            :rows="$orders"
            with-pagination
            per-page="perPage"
            striped
            no-hover
            show-empty-text
            :empty-text="__('orders_none_found')"
        >
            @scope('cell_id', $order)
                <span class="font-medium">#{{ $order->id }}</span>
            @endscope

            @scope('cell_buyer', $order)
                <div>
                    <div class="font-medium">
                        {{ $order->buyer?->company_name ?: $order->buyer?->name ?: __('orders_buyer_not_found') }}
                    </div>
                    <div class="text-sm text-base-content/60">
                        {{ $order->buyer?->email ?: __('common_not_specified') }}
                    </div>
                </div>
            @endscope

            @scope('cell_items_preview', $order)
                <div class="space-y-1">
                    @foreach ($order->orderItems as $item)
                        <div class="text-sm">
                            <span class="font-medium">{{ $item->product?->name ?: __('common_not_specified') }}</span>
                            <span class="text-base-content/60">
                                · {{ $item->quantity }} × {{ number_format((float) $item->unit_price, 2) }} €
                            </span>
                        </div>
                    @endforeach
                </div>
            @endscope

            @scope('cell_created_at', $order)
                {{ $order->created_at?->format('Y-m-d H:i') ?? __('common_not_specified') }}
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
                        :tooltip="__('orders_view_details')"
                    />
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>
</div>

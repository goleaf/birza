@php
    $statusClass = match ($order->payment_status) {
        \App\Models\Order::STATUS['PENDING'] => 'badge-warning badge-outline',
        \App\Models\Order::STATUS['PAID'] => 'badge-success badge-outline',
        \App\Models\Order::STATUS['PROCESSING'] => 'badge-info badge-outline',
        \App\Models\Order::STATUS['SHIPPED'] => 'badge-secondary badge-outline',
        \App\Models\Order::STATUS['DELIVERED'] => 'badge-success',
        \App\Models\Order::STATUS['CANCELLED'], \App\Models\Order::STATUS['FAILED'] => 'badge-error badge-outline',
        \App\Models\Order::STATUS['REFUNDED'] => 'badge-neutral badge-outline',
        default => 'badge-neutral badge-outline',
    };

    $buyerDetails = $order->buyer
        ? [
            [
                'icon' => 'o-building-office-2',
                'value' => $order->buyer->company_name ?: __('common_not_specified'),
                'label' => __('auth_company_name'),
            ],
            [
                'icon' => 'o-user',
                'value' => $order->buyer->name ?: __('common_not_specified'),
                'label' => __('common_name'),
            ],
            [
                'icon' => 'o-envelope',
                'value' => $order->buyer->email ?: __('common_not_specified'),
                'label' => __('common_email'),
            ],
            [
                'icon' => 'o-phone',
                'value' => $order->buyer->phone ?: __('common_not_specified'),
                'label' => __('sellers_phone'),
            ],
        ]
        : [[
            'icon' => 'o-user',
            'value' => __('orders_buyer_not_found'),
            'label' => __('buyer_buyer_information'),
        ]];

    $paymentDetails = [
        [
            'icon' => 'o-credit-card',
            'value' => $order->payment_method ?: __('common_not_specified'),
            'label' => __('orders_payment_method'),
        ],
        [
            'icon' => 'o-banknotes',
            'value' => number_format((float) $order->order_total, 2) . ' €',
            'label' => __('orders_order_total'),
        ],
        [
            'icon' => 'o-calendar',
            'value' => $order->created_at?->format('Y-m-d H:i') ?? __('common_not_specified'),
            'label' => __('orders_placed_on'),
        ],
    ];

    $deletedProductCount = $order->orderItems->filter(fn ($item) => $item->product?->trashed())->count();
@endphp

<div class="space-y-6">
    <x-mary-header
        :title="__('orders_order_details') . ' #' . $order->id"
        :subtitle="__('orders_placed_on') . ': ' . ($order->created_at?->format('Y-m-d H:i') ?? __('common_not_specified'))"
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-mary-badge
                :value="__('orders_status_3_' . strtolower($order->payment_status))"
                class="{{ $statusClass }}"
            />
            <x-mary-button
                :label="__('common_back')"
                :link="route('backend.orders.index')"
            />
        </x-slot:actions>
    </x-mary-header>

    @if ($order->buyer?->trashed())
        <x-mary-alert
            :title="__('common_deleted')"
            :description="__('backend_orders_show_deleted_buyer_alert')"
            icon="o-exclamation-triangle"
            class="alert-warning alert-soft"
            shadow
        />
    @endif

    @if ($deletedProductCount > 0)
        <x-mary-alert
            :title="__('common_deleted')"
            :description="__('backend_orders_show_deleted_products_alert', ['count' => $deletedProductCount])"
            icon="o-exclamation-triangle"
            class="alert-info alert-outline"
            shadow
        />
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        <x-mary-card :title="__('buyer_buyer_information')" shadow>
            @foreach ($buyerDetails as $detail)
                <x-mary-list-item
                    :item="$detail"
                    value="value"
                    sub-value="label"
                    no-hover
                    :no-separator="$loop->last"
                >
                    <x-slot:avatar>
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <x-mary-icon :name="$detail['icon']" class="h-5 w-5" />
                        </div>
                    </x-slot:avatar>
                </x-mary-list-item>
            @endforeach
        </x-mary-card>

        <x-mary-card :title="__('orders_payment_information')" shadow>
            @foreach ($paymentDetails as $detail)
                <x-mary-list-item
                    :item="$detail"
                    value="value"
                    sub-value="label"
                    no-hover
                >
                    <x-slot:avatar>
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-secondary/10 text-secondary">
                            <x-mary-icon :name="$detail['icon']" class="h-5 w-5" />
                        </div>
                    </x-slot:avatar>
                </x-mary-list-item>
            @endforeach

            <x-mary-list-item
                :item="['value' => __('orders_status_3_' . strtolower($order->payment_status)), 'label' => __('orders_status_3')]"
                value="value"
                sub-value="label"
                no-hover
                no-separator
            >
                <x-slot:avatar>
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-accent/10 text-accent">
                        <x-mary-icon name="o-check-badge" class="h-5 w-5" />
                    </div>
                </x-slot:avatar>

                <x-slot:actions>
                    <x-mary-badge
                        :value="__('orders_status_3_' . strtolower($order->payment_status))"
                        class="{{ $statusClass }}"
                    />
                </x-slot:actions>
            </x-mary-list-item>
        </x-mary-card>
    </div>

    <x-mary-card :title="__('orders_order_items')" shadow>
        @forelse ($order->orderItems as $item)
            <x-mary-list-item :item="$item" :no-separator="$loop->last">
                <x-slot:avatar>
                    <x-mary-avatar
                        :image="$item->product?->product_image ? \Illuminate\Support\Facades\Storage::url('products/' . $item->product->product_image) : ''"
                        :alt="$item->product?->name ?: __('common_not_specified')"
                        :placeholder="strtoupper(substr((string) ($item->product?->name ?: __('common_not_specified')), 0, 2))"
                        class="!w-11"
                    />
                </x-slot:avatar>

                <x-slot:value>
                    {{ $item->product?->name ?: __('common_not_specified') }}

                    @if ($item->product?->trashed())
                        <span class="text-xs text-error">({{ __('common_deleted') }})</span>
                    @endif
                </x-slot:value>

                <x-slot:sub-value>
                    {{ $item->seller?->company_name ?: $item->seller?->name ?: __('common_not_specified') }}
                    ·
                    {{ __('orders_quantity') }}: {{ $item->quantity }}
                    ·
                    {{ __('orders_unit_price') }}: {{ number_format((float) $item->unit_price, 2) }} €
                </x-slot:sub-value>

                <x-slot:actions>
                    <span class="whitespace-nowrap text-sm font-semibold">
                        {{ number_format((float) $item->total_price, 2) }} €
                    </span>
                </x-slot:actions>
            </x-mary-list-item>
        @empty
            <x-mary-alert
                :title="__('orders_no_orders')"
                icon="o-exclamation-triangle"
                class="alert-info alert-soft"
            />
        @endforelse
    </x-mary-card>
</div>

<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('navigation_orders'), 'link' => route('admin.orders.index')],
            ['label' => '#' . $order->id],
        ]"
    />

    <x-mary-header
        :title="__('orders_order_details') . ' #' . $order->id"
        :subtitle="__('orders_placed_on') . ': ' . ($order->created_at?->format('Y-m-d H:i') ?? __('common_not_specified'))"
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-mary-badge
                :value="$order->statusLabel()"
                class="{{ $orderStatusClass }}"
            />
            <x-mary-badge
                :value="$order->paymentStatusLabel()"
                class="{{ $statusClass }}"
            />
            <x-mary-button
                :label="__('common_back')"
                :link="route('admin.orders.index')"
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
                :item="['value' => $order->paymentStatusLabel(), 'label' => __('orders.payment_status.label')]"
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
                        :value="$order->paymentStatusLabel()"
                        class="{{ $statusClass }}"
                    />
                </x-slot:actions>
            </x-mary-list-item>
        </x-mary-card>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-mary-card :title="__('orders.status.change_title')" :subtitle="$order->statusDescription()" shadow>
            @if ($statusOptions !== [])
                <x-mary-form wire:submit="changeStatus" class="gap-4">
                    <x-mary-select
                        :label="__('orders.status.label')"
                        wire:model="nextStatus"
                        :options="$statusOptions"
                        option-value="id"
                        option-label="name"
                        required
                    />
                    <x-mary-textarea
                        :label="__('audit_logs.reason')"
                        :hint="__('audit_logs.reason_hint')"
                        wire:model="statusReason"
                        rows="3"
                        required
                    />
                    <x-mary-textarea
                        :label="__('orders.status.note')"
                        wire:model="statusNote"
                        rows="3"
                    />

                    <x-slot:actions>
                        <x-mary-button
                            :label="__('orders.status.change_action')"
                            icon="o-arrow-path"
                            spinner="changeStatus"
                            type="submit"
                            class="btn-primary"
                        />
                    </x-slot:actions>
                </x-mary-form>
            @else
                <x-mary-alert
                    :title="__('orders.status.messages.no_transitions')"
                    icon="o-lock-closed"
                    class="alert-info alert-soft"
                />
            @endif
        </x-mary-card>

        <x-mary-card :title="__('orders.status.history')" shadow>
            @forelse ($order->statusHistory as $history)
                <x-mary-list-item :item="$history" :no-separator="$loop->last">
                    <x-slot:avatar>
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <x-mary-icon :name="$history->new_status->icon()" class="h-5 w-5" />
                        </div>
                    </x-slot:avatar>

                    <x-slot:value>
                        {{ $history->old_status->label() }} &rarr; {{ $history->new_status->label() }}
                    </x-slot:value>

                    <x-slot:sub-value>
                        {{ $history->changed_by_role->label() }}
                        ·
                        {{ $history->created_at?->format('Y-m-d H:i') ?? __('common_not_specified') }}
                        @if ($history->reason)
                            · {{ $history->reason }}
                        @endif
                    </x-slot:sub-value>
                </x-mary-list-item>
            @empty
                <x-mary-alert
                    :title="__('orders.status.messages.no_history')"
                    icon="o-clock"
                    class="alert-info alert-soft"
                />
            @endforelse
        </x-mary-card>
    </div>

    <x-backend.audit-history :logs="$auditLogs" />

    @if ($order->orderBundles->isNotEmpty())
        <x-mary-card :title="__('orders.bundle_snapshot')" shadow>
            <div class="space-y-4">
                @foreach ($order->orderBundles as $orderBundle)
                    <div class="rounded-box border p-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <div class="text-lg font-bold">{{ $orderBundle->bundle_name_snapshot }}</div>
                                <div class="text-sm text-base-content/60">
                                    {{ __('bundles.quantity') }}: {{ $orderBundle->quantity }}
                                </div>
                            </div>
                            <div class="text-sm md:text-right">
                                <div>{{ __('bundles.base_price') }}: €{{ number_format((float) $orderBundle->base_price, 2) }}</div>
                                @if ((float) $orderBundle->discount_amount > 0)
                                    <div class="text-success">{{ __('bundles.discount') }}: -€{{ number_format((float) $orderBundle->discount_amount, 2) }}</div>
                                @endif
                                <div class="font-bold">{{ __('bundles.final_price') }}: €{{ number_format((float) $orderBundle->final_price, 2) }}</div>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @foreach ($orderBundle->products_snapshot ?? [] as $snapshotProduct)
                                <div class="rounded-box bg-base-200 p-3 text-sm">
                                    <div class="font-medium">{{ $snapshotProduct['title'] ?? __('common_unnamed_product') }}</div>
                                    <div class="text-base-content/70">
                                        {{ __('orders_quantity') }}: {{ $snapshotProduct['quantity'] ?? 0 }}
                                        -
                                        {{ __('orders_unit_price') }}: €{{ number_format((float) ($snapshotProduct['unit_price'] ?? 0), 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-mary-card>
    @endif

    <x-mary-card :title="__('orders_order_items')" shadow>
        @forelse ($order->orderItems as $item)
            <x-mary-list-item :item="$item" :no-separator="$loop->last">
                <x-slot:avatar>
                    <x-mary-avatar
                        :image="$item->product?->imageUrl('thumb') ?? asset((string) config('images.fallbacks.product'))"
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

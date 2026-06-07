<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('navigation_sellers'), 'link' => route('backend.sellers.index')],
            ['label' => $seller->company_name ?: $seller->name],
        ]"
    />

    <x-mary-header
        :title="$seller->company_name ?: $seller->name"
        :subtitle="$seller->email"
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-mary-button
                :label="__('common_back')"
                :link="route('backend.sellers.index')"
            />
            <x-mary-button
                :label="__('common_edit')"
                :link="route('backend.sellers.edit', $seller)"
                icon="o-pencil-square"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-mary-header>

    @if (! $seller->is_active)
        <x-mary-alert
            :title="__('common_inactive')"
            :description="__('backend_sellers_show_inactive_alert')"
            icon="o-exclamation-triangle"
            class="alert-warning alert-soft"
            shadow
        >
            <x-slot:actions>
                <x-mary-button
                    :label="__('common_edit')"
                    :link="route('backend.sellers.edit', $seller)"
                    class="btn-sm btn-warning btn-outline"
                />
            </x-slot:actions>
        </x-mary-alert>
    @endif

    @if (! $seller->is_verified)
        <x-mary-alert
            :title="__('backend_sellers_show_unverified_title')"
            :description="__('backend_sellers_show_unverified_alert')"
            icon="o-exclamation-triangle"
            class="alert-info alert-outline"
            shadow
        >
            <x-slot:actions>
                <x-mary-button
                    :label="__('common_edit')"
                    :link="route('backend.sellers.edit', $seller)"
                    class="btn-sm btn-info btn-outline"
                />
            </x-slot:actions>
        </x-mary-alert>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,22rem),minmax(0,1fr)]">
        <x-mary-card :title="__('common_basic_information')" shadow>
            @foreach ($sellerDetails as $detail)
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

        <div class="space-y-6">
            <x-mary-card :title="__('sellers_products')" shadow>
                @forelse ($products as $product)
                    <x-mary-list-item
                        :item="$product"
                        :link="route('backend.products.show', $product)"
                        :no-separator="$loop->last"
                    >
                        <x-slot:avatar>
                            <x-mary-avatar
                                :image="$product->product_image ? \Illuminate\Support\Facades\Storage::url('products/' . $product->product_image) : ''"
                                :alt="$product->name"
                                :placeholder="strtoupper(substr((string) $product->name, 0, 2))"
                                class="!w-11"
                            />
                        </x-slot:avatar>

                        <x-slot:value>
                            {{ $product->name }}
                        </x-slot:value>

                        <x-slot:sub-value>
                            {{ $product->category?->getTranslation('category_name', app()->getLocale()) ?? __('common_not_specified') }}
                            ·
                            {{ __('orders_items') }}: {{ $product->order_items_count }}
                        </x-slot:sub-value>

                        <x-slot:actions>
                            <span class="whitespace-nowrap text-sm font-semibold">
                                {{ number_format((float) $product->price, 2) }} €
                            </span>
                            <x-mary-badge
                                :value="$product->is_active ? __('common_active') : __('common_inactive')"
                                class="{{ $product->is_active ? 'badge-success badge-outline' : 'badge-error badge-outline' }}"
                            />
                        </x-slot:actions>
                    </x-mary-list-item>
                @empty
                    <x-mary-alert
                        :title="__('products_no_products')"
                        icon="o-exclamation-triangle"
                        class="alert-info alert-soft"
                    />
                @endforelse

                @if ($products->hasPages())
                    <div class="pt-4">
                        {{ $products->links() }}
                    </div>
                @endif
            </x-mary-card>

            <x-mary-card :title="__('sellers_orders')" shadow>
                @forelse ($recentOrders as $order)
                    <x-mary-list-item
                        :item="$order"
                        :link="route('backend.orders.show', $order)"
                        :no-separator="$loop->last"
                    >
                        <x-slot:avatar>
                            <div class="flex h-11 min-w-11 items-center justify-center rounded-full bg-accent/10 px-2 text-sm font-semibold text-accent">
                                #{{ $order->id }}
                            </div>
                        </x-slot:avatar>

                        <x-slot:value>
                            {{ $order->buyer?->company_name ?: $order->buyer?->name ?: __('orders_buyer_not_found') }}

                            @if ($order->buyer?->trashed())
                                <span class="text-xs text-error">({{ __('common_deleted') }})</span>
                            @endif
                        </x-slot:value>

                        <x-slot:sub-value>
                            {{ $order->created_at?->format('Y-m-d H:i') ?? __('common_not_specified') }}
                            ·
                            {{ __('orders_items') }}: {{ $order->order_items_count }}
                        </x-slot:sub-value>

                        <x-slot:actions>
                            <span class="whitespace-nowrap text-sm font-semibold">
                                {{ number_format((float) $order->order_total, 2) }} €
                            </span>
                            <x-mary-badge
                                :value="__('orders_status_3_' . strtolower($order->payment_status))"
                                class="{{ $order->payment_status_badge_class }}"
                            />
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
    </div>
</div>

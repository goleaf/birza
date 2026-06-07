<div class="space-y-6">
    <x-ui.header
        :title="__('stock_alerts.title')"
        :subtitle="__('stock_alerts.dashboard_subtitle')"
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('buyer.products.index')"
                secondary
                icon="shopping-bag"
                :label="__('common_back_to_products')"
            />
        </x-slot:actions>
    </x-ui.header>

    <div class="flex flex-wrap items-center gap-2">
        @foreach ($statusFilters as $statusFilter)
            <button
                type="button"
                wire:click="setFilter('{{ $statusFilter['id'] }}')"
                @class([
                    'rounded-md px-3 py-2 text-sm font-medium transition',
                    'bg-blue-600 text-white' => $filter === $statusFilter['id'],
                    'bg-gray-100 text-gray-700 hover:bg-gray-200' => $filter !== $statusFilter['id'],
                ])
            >
                {{ $statusFilter['label'] }}
            </button>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        @forelse ($alerts as $alert)
            @php
                $product = $alert->product;
                $isActiveAlert = $alert->isActive();
                $isPurchasable = $product?->isPurchasableByBuyers() ?? false;
                $imageUrl = $product ? data_get($product->imageLibraryPreview()->first(), 'url') : null;
            @endphp

            <article class="flex flex-col gap-4 border-b border-gray-100 p-4 last:border-b-0 sm:flex-row sm:items-center">
                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-md bg-gray-100">
                    @if ($product)
                        <img
                            src="{{ $imageUrl ?: asset('images/admin-product-placeholder.svg') }}"
                            alt="{{ $product->name }}"
                            class="h-full w-full object-cover"
                        >
                    @else
                        <div class="flex h-full w-full items-center justify-center text-gray-400">
                            <x-ui.icon name="photo" class="h-6 w-6" />
                        </div>
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            @if ($product)
                                <a
                                    href="{{ route('buyer.products.show', $product) }}"
                                    class="font-semibold text-gray-900 hover:text-blue-700"
                                >
                                    {{ $product->name }}
                                </a>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $product->seller?->company_name ?: $product->seller?->name }}
                                </p>
                            @else
                                <h2 class="font-semibold text-gray-900">{{ __('stock_alerts.product_missing') }}</h2>
                            @endif
                        </div>

                        <span @class([
                            'inline-flex w-fit rounded-full px-2.5 py-1 text-xs font-semibold',
                            'bg-blue-100 text-blue-800' => $alert->status === \App\Enums\ProductStockAlertStatus::Active,
                            'bg-green-100 text-green-800' => $alert->status === \App\Enums\ProductStockAlertStatus::Notified,
                            'bg-gray-100 text-gray-700' => ! in_array($alert->status, [\App\Enums\ProductStockAlertStatus::Active, \App\Enums\ProductStockAlertStatus::Notified], true),
                        ])>
                            {{ __($alert->status->labelKey()) }}
                        </span>
                    </div>

                    <dl class="mt-3 grid gap-2 text-sm text-gray-600 sm:grid-cols-3">
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('stock_alerts.product_availability') }}</dt>
                            <dd>{{ $isPurchasable ? __('stock_alerts.product_available') : __('common_out_of_stock') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('common_stock') }}</dt>
                            <dd>{{ $product?->stock ?? 0 }} {{ $product ? __('units_unit_' . strtolower($product->unit)) : '' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('stock_alerts.created_at') }}</dt>
                            <dd>{{ $alert->created_at?->format('Y-m-d') }}</dd>
                        </div>
                    </dl>
                </div>

                @if ($isActiveAlert)
                    <div class="shrink-0">
                        <x-ui.button
                            type="button"
                            negative
                            outline
                            spinner="cancelAlert"
                            wire:click="cancelAlert({{ $alert->id }})"
                            wire:loading.attr="disabled"
                            :label="__('stock_alerts.cancel')"
                        />
                    </div>
                @endif
            </article>
        @empty
            <div class="p-10 text-center">
                <x-ui.icon name="bell-slash" class="mx-auto h-10 w-10 text-gray-400" />
                <h2 class="mt-3 font-semibold text-gray-900">{{ __('stock_alerts.empty') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('stock_alerts.empty_help') }}</p>
            </div>
        @endforelse
    </div>

    {{ $alerts->links() }}
</div>

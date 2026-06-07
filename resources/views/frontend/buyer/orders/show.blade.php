<div>
    <!-- start main container -->
    <div class="max-w-7xl mx-auto">
        <x-buyer.breadcrumbs
            class="mb-6"
            :items="[
                ['label' => __('common_orders'), 'link' => route('buyer.orders.index')],
                ['label' => '#' . $order->id],
            ]"
        />

        <x-ui.header
            class="mb-6"
            :title="__('orders_order_details') . ' #' . $order->id"
            :subtitle="__('orders_placed_on') . ': ' . $order->created_at"
        >
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-4">
                    <x-ui.badge
                        :value="$order->paymentStatusLabel()"
                        :color="$order->paymentStatusUiColor()"
                        soft
                        class="font-medium"
                    />

                    <!-- start back link -->
                    <x-ui.button
                        :href="route('buyer.orders.index')"
                        secondary
                        :label="__('common_back_to_orders')"
                    />
                    <!-- end back link -->
                    
                    @if ($order->canBeCancelled())
                        <x-ui.button
                            negative
                            :label="__('orders_cancel_order')"
                            wire:click="confirmCancelOrder"
                            spinner="confirmCancelOrder"
                        />
                    @endif
                </div>
            </x-slot:actions>
        </x-ui.header>

        <x-ui.steps
            class="mb-6"
            wire:model="currentOrderStep"
            :title="__('orders_steps_title')"
            :subtitle="__('orders_steps_subtitle')"
            :items="$orderStepItems"
            :panel="$orderStepPanel"
            :steps-color="$orderStepsColor"
        />

        <x-ui.timeline
            class="mb-6"
            :title="__('orders_order_timeline')"
            :subtitle="__('orders_timeline_subtitle')"
            :items="$orderTimelineItems"
        />

        @if ($orderSellers->isNotEmpty())
            <x-ui.card
                class="mb-6 rounded-lg shadow-sm"
                :title="__('messages.order_contacts')"
            >
                <div class="flex flex-wrap gap-3">
                    @foreach ($orderSellers as $seller)
                        <x-ui.button
                            type="button"
                            secondary
                            icon="chat-bubble-left-right"
                            spinner="openSellerConversation"
                            wire:click="openSellerConversation({{ $seller->id }})"
                            :label="__('messages.message_seller', ['seller' => $seller->company_name ?: $seller->name])"
                        />
                    @endforeach
                </div>
            </x-ui.card>
        @endif

        @if ($order->orderBundles->isNotEmpty())
            <x-ui.card
                class="mb-6 rounded-lg shadow-sm"
                :title="__('orders.bundle_snapshot')"
            >
                <div class="space-y-4">
                    @foreach ($order->orderBundles as $orderBundle)
                        <div class="rounded-lg border p-4">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $orderBundle->bundle_name_snapshot }}</h3>
                                    <div class="mt-1 text-sm text-gray-600">
                                        {{ __('bundles.quantity') }}: {{ $orderBundle->quantity }}
                                    </div>
                                </div>
                                <div class="text-sm sm:text-right">
                                    <div>{{ __('bundles.base_price') }}: {{ number_format((float) $orderBundle->base_price, 2) }} €</div>
                                    @if ((float) $orderBundle->discount_amount > 0)
                                        <div class="text-green-700">{{ __('bundles.discount') }}: -{{ number_format((float) $orderBundle->discount_amount, 2) }} €</div>
                                    @endif
                                    <div class="font-bold">{{ __('bundles.final_price') }}: {{ number_format((float) $orderBundle->final_price, 2) }} €</div>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                @foreach ($orderBundle->products_snapshot ?? [] as $snapshotProduct)
                                    <div class="rounded-md bg-gray-50 p-3 text-sm text-gray-700">
                                        <div class="font-medium">{{ $snapshotProduct['title'] ?? __('common_unnamed_product') }}</div>
                                        <div>
                                            {{ __('orders_quantity') }}: {{ $snapshotProduct['quantity'] ?? 0 }}
                                            -
                                            {{ __('orders_unit_price') }}: {{ number_format((float) ($snapshotProduct['unit_price'] ?? 0), 2) }} €
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        @endif

        <!-- start order items -->
        <x-ui.card
            class="mb-6 rounded-lg shadow-sm"
            :title="__('orders_order_items')"
            body-class="-mx-5 -mb-5 overflow-hidden"
        >
            <div class="overflow-x-auto">
            <!-- start table -->
            <table class="min-w-full divide-y divide-gray-200">
                <!-- start table head -->
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('orders_product') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('orders_seller') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('orders_quantity') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('orders_unit_price') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('orders_total') }}
                        </th>
                    </tr>
                </thead>
                <!-- end table head -->

                <!-- start table body -->
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($order->items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <!-- start product info -->
                                <div class="flex items-center">
                                    <!-- start product image -->
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img
                                            src="{{ $item->product?->imageUrl('thumb') ?? asset((string) config('images.fallbacks.product')) }}"
                                            alt="{{ $item->product?->name ?? __('common_unnamed_product') }}"
                                            class="h-10 w-10 rounded-full object-cover"
                                            loading="lazy"
                                        >
                                    </div>
                                    <!-- end product image -->
                                    
                                    <!-- start product name -->
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $item->product?->name ?? __('common_unnamed_product') }}
                                        </div>
                                    </div>
                                    <!-- end product name -->
                                </div>
                                <!-- end product info -->
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item->seller->company_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($item->unit_price, 2) }} €
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($item->total_price, 2) }} €
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <!-- end table body -->
                
                <!-- start table footer -->
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                            {{ __('orders_order_total') }}:
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                            {{ number_format($order->order_total, 2) }} € <br>
                            <span class="text-gray-500 font-normal">({{ __('product_price_without_vat') }})</span>
                        </td> 
                    </tr>
                </tfoot>
                <!-- end table footer -->
            </table>
            <!-- end table -->
            </div>
        </x-ui.card>
        <!-- end order items -->

        <x-backend.confirm-modal
            wire:model="confirmModal"
            :title="$confirmModalTitle"
            :description="$confirmModalDescription"
            :confirm-label="$confirmModalAcceptLabel"
        />
    </div>
    <!-- end main container -->
</div>
<!-- end section -->

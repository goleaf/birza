<div>
    <!-- start main container -->
    <div class="max-w-7xl mx-auto">
        <x-seller.breadcrumbs
            class="mb-6"
            :items="[
                ['label' => __('common_orders'), 'link' => route('seller.orders.index')],
                ['label' => '#' . $order->id],
            ]"
        />

        <x-ui.header
            class="mb-6"
            :title="__('orders_order_details') . ' #' . $order->id"
            :subtitle="__('orders_placed_on') . ': ' . $order->created_at->format('Y-m-d H:i')"
        >
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-4">
                    <x-ui.button
                        :href="route('seller.orders.index')"
                        secondary
                        icon="arrow-left"
                        sm
                        :label="__('common_back_to_orders')"
                    />
                    <x-ui.badge
                        :value="$order->paymentStatusLabel()"
                        :color="$order->paymentStatusUiColor()"
                        soft
                        class="font-medium"
                    />
                    <x-ui.button
                        type="button"
                        primary
                        icon="chat-bubble-left-right"
                        spinner="openBuyerConversation"
                        wire:click="openBuyerConversation"
                        :label="__('messages.message_buyer')"
                    />
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

        <!-- start order header -->
        <x-ui.card class="mb-6 rounded-lg shadow-sm">
            <!-- start order info -->
            <div class="border-b border-gray-200 pb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders_placed_on') }}</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders_buyer') }}</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->buyer->name }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders_total_amount') }}</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ number_format($order->total, 2) }} €</p>
                    </div>
                </div>
            </div>
            <!-- end order info -->

            <!-- start status update form -->
            <div class="pt-4">
                @if(! empty($allowedStatusTransitions))
                    <div class="flex space-x-4">
                        @forelse ($allowedStatusTransitions as $nextStatus)
                            @if ($nextStatus === \App\Enums\OrderStatus::Cancelled)
                                <x-ui.button
                                    negative
                                    icon="x-mark"
                                    :label="$nextStatus->label()"
                                    wire:click="confirmCancelOrder"
                                    spinner="confirmCancelOrder"
                                />
                            @else
                                <x-ui.button
                                    :positive="$nextStatus === $acceptedStatus"
                                    :secondary="$nextStatus !== $acceptedStatus"
                                    icon="check"
                                    :label="$nextStatus->label()"
                                    wire:click="updateStatus('{{ $nextStatus->value }}')"
                                    spinner="updateStatus"
                                />
                            @endif
                        @empty
                        @endforelse
                    </div>

                    <div class="mt-4">
                        <x-textarea
                            id="comment"
                            name="comment"
                            :label="__('orders_comment')"
                            :placeholder="__('orders_comment_placeholder')"
                            wire:model="comment"
                            rows="3"
                        />
                    </div>
                @else
                    <div class="text-sm text-gray-500">
                        {{ __('orders.status.messages.cannot_be_changed') }}
                    </div>
                @endif
            </div>
            <!-- end status update form -->
        </x-ui.card>
        <!-- end order header -->

        @if ($orderBundles->isNotEmpty())
            <x-ui.card
                class="mb-6 rounded-lg shadow-sm"
                :title="__('orders.bundle_snapshot')"
            >
                <div class="space-y-4">
                    @foreach ($orderBundles as $orderBundle)
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
            <!-- start table container -->
            <div class="overflow-x-auto">
                <!-- start table -->
                <table class="min-w-full divide-y divide-gray-200">
                    <!-- start table header -->
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders_product') }}
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
                    <!-- end table header -->

                    <!-- start table body -->
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($orderItems as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img 
                                                src="{{ $item->product?->imageUrl('thumb') ?? asset((string) config('images.fallbacks.product')) }}"
                                                alt="{{ $item->product?->name ?? __('common_unnamed_product') }}"
                                                class="h-10 w-10 rounded-full object-cover"
                                                loading="lazy"
                                            >
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item->product?->name ?? __('common_unnamed_product') }}
                                            </div>
                                        </div>
                                    </div>
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
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                {{ __('orders_order_total') }}:
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                {{ number_format($order->order_total, 2) }} €<br>
                                <span class="text-gray-500 font-normal">({{ __('product_price_without_vat') }})</span>
                            </td>
                        </tr>
                    </tfoot>
                    <!-- end table footer -->
                </table>
                <!-- end table -->
            </div>
            <!-- end table container -->
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

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
                        :value="__('orders_status_3_' . strtolower($order->payment_status))"
                        :color="match (strtolower((string) $order->payment_status)) {
                            'pending' => 'warning',
                            'paid' => 'success',
                            'cancelled' => 'error',
                            default => 'neutral',
                        }"
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
                    
                    <!-- start cancel form -->
                    @if($order->payment_status === \App\Models\Order::STATUS['PENDING'])
                        <x-ui.button
                            negative
                            :label="__('orders_cancel_order')"
                            wire:click="confirmCancelOrder"
                            spinner="confirmCancelOrder"
                        />
                    @endif
                    <!-- end cancel form -->
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
                    @foreach($order->orderItems as $item)
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
                                            width="160"
                                            height="160"
                                        >
                                    </div>
                                    <!-- end product image -->
                                    
                                    <!-- start product name -->
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $item->product->name }}
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

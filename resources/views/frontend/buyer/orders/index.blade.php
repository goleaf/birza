<div>
    <!-- start main container -->
    <div class="max-w-7xl mx-auto">
        <x-buyer.breadcrumbs
            class="mb-6"
            :items="[
                ['label' => __('common_orders')],
            ]"
        />

        <x-ui.header
            class="mb-6"
            :title="__('orders_order_list')"
            :subtitle="__('common_orders')"
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('buyer.dashboard')"
                    secondary
                    :label="__('common_back_to_dashboard')"
                />
            </x-slot:actions>
        </x-ui.header>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
            <x-ui.statistic
                :title="__('orders_total_orders')"
                :value="(string) $ordersData['total']"
                icon="shopping-bag"
                color="text-primary"
                class="shadow-sm"
            />

            <x-ui.statistic
                :title="__('orders_pending_orders')"
                :value="(string) $ordersData['pending']"
                icon="clock"
                color="text-warning"
                class="shadow-sm"
            />

            <x-ui.statistic
                :title="__('orders_completed_orders')"
                :value="(string) $ordersData['delivered']"
                icon="check-badge"
                color="text-success"
                class="shadow-sm"
            />

            <x-ui.statistic
                :title="__('orders_total_spent')"
                :value="'€' . number_format((float) $ordersData['totalSpent'], 2)"
                icon="banknotes"
                color="text-info"
                class="shadow-sm"
            />
        </div>

        <!-- start filters container -->
        <x-ui.card class="mb-6 rounded-lg shadow">
            <!-- start form -->
            <form
                wire:submit.prevent="applyFilters"
                class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:space-x-4"
            >
                    <!-- start status select container -->
                    <div class="w-full sm:w-48">
                        <!-- start label -->
                        <label 
                            for="status"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            {{ __('common_status') }}
                        </label>
                        <!-- end label -->
                        
                        <!-- start select -->
                        <select 
                            id="status" 
                            wire:model="status"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        >
                            <option value="">
                                {{ __('common_all') }}
                            </option>
                            @foreach ($orderStatuses as $key => $value)
                                <option 
                                    value="{{ $value }}" 
                                    {{ $filters['status'] === $value ? 'selected' : '' }}
                                >
                                    {{ __('orders_status_3_' . strtolower($key)) }}
                                </option>
                            @endforeach
                        </select>
                        <!-- end select -->
                    </div>
                    <!-- end status select container -->

                    <!-- start date from container -->
                    <div class="w-full sm:w-48">
                        <!-- start label -->
                        <label 
                            for="date_from"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            {{ __('common_date_from') }}
                        </label>
                        <!-- end label -->
                        
                        <!-- start input -->
                        <x-ui.datepicker
                            wire:model="dateFrom"
                            id="date_from"
                            class="w-full"
                            :label="null"
                            clearable
                        />
                        <!-- end input -->
                    </div>
                    <!-- end date from container -->

                    <!-- start date to container -->
                    <div class="w-full sm:w-48">
                        <!-- start label -->
                        <label 
                            for="date_to" 
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            {{ __('common_date_to') }}
                        </label>
                        <!-- end label -->
                        
                        <!-- start input -->
                        <x-ui.datepicker
                            wire:model="dateTo"
                            id="date_to" 
                            class="w-full"
                            :label="null"
                            clearable
                        />
                        <!-- end input -->
                    </div>
                    <!-- end date to container -->

                    <!-- start button container -->
                    <div class="w-full sm:w-auto sm:self-end">
                        <!-- start label -->
                        <label 
                            for="filter_button" 
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            &nbsp;
                        </label>
                        <!-- end label -->
                        
                        <!-- start button -->
                        <x-mary-button
                            type="submit"
                            class="w-full sm:w-auto btn-primary"
                            name="filter_button"
                            :label="__('common_filter')"
                        />
                        <!-- end button -->
                    </div>
                    <!-- end button container -->
            </form>
            <!-- end form -->
        </x-ui.card>
        <!-- end filters container -->

        <x-ui.card
            class="mb-6 rounded-lg shadow"
            :title="__('orders_calendar_title')"
            :subtitle="__('orders_calendar_subtitle')"
        >
            <div class="overflow-x-auto">
                <x-ui.calendar :events="$orderCalendarEvents" />
            </div>
        </x-ui.card>

        <!-- start orders list -->
        <x-ui.card
            class="rounded-lg shadow"
            body-class="-mx-5 -mb-5 overflow-hidden"
        >
            <!-- start overflow container -->
            <div class="overflow-x-auto">
                <!-- start table -->
                <table 
                    class="min-w-full divide-y divide-gray-200"
                >
                    <!-- start table head -->
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders_order_number') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders_date') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders_total') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('common_status') }}
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('common_actions') }}
                            </th>
                        </tr>
                    </thead>
                    <!-- end table head -->

                    <!-- start table body -->
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($ordersData['all'] as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $order->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($order->order_total, 2) }} €
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-ui.popover position="bottom-start" class="inline-block">
                                        <x-slot:trigger>
                                            <span>
                                                <x-ui.badge
                                                    :value="__('orders_status_3_' . strtolower($order->payment_status))"
                                                    :color="match (strtolower((string) $order->payment_status)) {
                                                        'pending' => 'warning',
                                                        'paid', 'delivered' => 'success',
                                                        'failed' => 'error',
                                                        'processing' => 'info',
                                                        'shipped' => 'secondary',
                                                        'cancelled', 'refunded' => 'neutral',
                                                        default => 'neutral',
                                                    }"
                                                    soft
                                                    sm
                                                    class="font-semibold"
                                                />
                                            </span>
                                        </x-slot:trigger>

                                        <x-slot:content>
                                            <div class="space-y-3">
                                                <div class="flex items-center gap-2">
                                                    <x-ui.icon name="receipt-percent" class="h-5 w-5 text-blue-600" />
                                                    <div class="font-semibold text-gray-900">{{ __('orders_order_details') }} #{{ $order->id }}</div>
                                                </div>

                                                <dl class="space-y-2 text-gray-600">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <dt class="font-medium text-gray-500">{{ __('orders_placed_on') }}</dt>
                                                        <dd class="text-right">{{ $order->created_at->format('Y-m-d H:i') }}</dd>
                                                    </div>
                                                    <div class="flex items-start justify-between gap-3">
                                                        <dt class="font-medium text-gray-500">{{ __('orders_order_total') }}</dt>
                                                        <dd class="text-right">{{ number_format($order->order_total, 2) }} €</dd>
                                                    </div>
                                                </dl>
                                            </div>
                                        </x-slot:content>
                                    </x-ui.popover>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <!-- start view link -->
                                    <a 
                                        href="{{ route('buyer.orders.show', $order) }}"
                                        class="text-blue-600 hover:text-blue-900 mr-3"
                                    >
                                        {{ __('common_view') }}
                                    </a>
                                    <!-- end view link -->

                                    @if ($order->payment_status === 'pending')
                                        <button
                                            type="button"
                                            wire:click="cancelOrder({{ $order->id }})"
                                            class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('{{ __('orders_confirm_cancel') }}')"
                                        >
                                            {{ __('common_cancel') }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    {{ __('orders_no_orders_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <!-- end table body -->
                </table>
                <!-- end table -->
            </div>
            <!-- end overflow container -->
        </x-ui.card>
        <!-- end orders list -->
    </div>
</div>

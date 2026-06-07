<div>
    <!-- start main container -->
    <div class="max-w-7xl mx-auto">
        <x-seller.breadcrumbs
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
                    :href="route('seller.dashboard')"
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
                :title="__('dashboard_total_revenue')"
                :value="'€' . number_format((float) $ordersData['totalRevenue'], 2)"
                icon="chart-bar-square"
                color="text-success"
                class="shadow-sm"
            />

            <x-ui.statistic
                :title="__('dashboard_avg_order_value')"
                :value="'€' . number_format((float) $ordersData['averageOrderValue'], 2)"
                icon="banknotes"
                color="text-info"
                class="shadow-sm"
            />
        </div>

        <!-- start orders list -->
        <x-ui.card class="mb-6 rounded-lg shadow-sm">
            <!-- start filters form -->
            <form wire:submit.prevent="applyFilters" class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:space-x-4">
                <!-- start status filter -->
                <div class="w-full sm:w-48">
                    <label
                        for="status"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        {{ __('common_status') }}
                    </label>
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
                                {{ (string)$filters['status'] == (string)$value ? 'selected' : '' }}
                            >
                                {{ __('orders_status_3_' . strtolower($key)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- end status filter -->

                <!-- start date from filter -->
                <div class="w-full sm:w-48">
                    <label
                        for="date_from"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        {{ __('common_date_from') }}
                    </label>
                    <x-ui.datepicker
                        wire:model="dateFrom"
                        id="date_from"
                        class="w-full"
                        :label="null"
                        clearable
                    />
                </div>
                <!-- end date from filter -->

                <!-- start date to filter -->
                <div class="w-full sm:w-48">
                    <label
                        for="date_to"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        {{ __('common_date_to') }}
                    </label>
                    <x-ui.datepicker
                        wire:model="dateTo"
                        id="date_to"
                        class="w-full"
                        :label="null"
                        clearable
                    />
                </div>
                <!-- end date to filter -->

                <!-- start filter button -->
                <div class="w-full sm:w-auto sm:self-end">
                    <label
                        for="filter_button"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        &nbsp;
                    </label>
                    <x-mary-button
                        type="submit"
                        class="w-full sm:w-auto btn-primary"
                        name="filter_button"
                        :label="__('common_filter')"
                    />
                </div>
                <!-- end filter button -->
            </form>
            <!-- end filters form -->
        </x-ui.card>

        <x-ui.card
            class="mb-6 rounded-lg shadow-sm"
            :title="__('orders_calendar_title')"
            :subtitle="__('orders_calendar_subtitle')"
        >
            <div class="overflow-x-auto">
                <x-ui.calendar :events="$orderCalendarEvents" />
            </div>
        </x-ui.card>

        @if ($ordersData['all']->isNotEmpty())
            <!-- start table container -->
            <x-ui.card
                class="rounded-lg shadow-sm"
                body-class="-mx-5 -mb-5 overflow-hidden"
            >
                <div class="overflow-x-auto">
                <!-- start orders table -->
                <table class="min-w-full divide-y divide-gray-200">
                    <!-- start table header -->
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders_order_number') }}
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders_table_date') }}
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders_table_amount') }}
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('common_status') }}
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('common_actions') }}
                            </th>
                        </tr>
                    </thead>
                    <!-- end table header -->

                    <!-- start table body -->
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($ordersData['all'] as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    #{{ $order->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format((float) $order->seller_total, 2) }} €
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-ui.badge
                                        :value="__('orders_status_3_' . strtolower($order->payment_status))"
                                        :color="match (strtolower((string) $order->payment_status)) {
                                            'pending' => 'warning',
                                            'paid', 'delivered' => 'success',
                                            'shipped' => 'secondary',
                                            'cancelled', 'failed' => 'error',
                                            'processing' => 'info',
                                            'refunded' => 'neutral',
                                            default => 'neutral',
                                        }"
                                        soft
                                        sm
                                        class="font-semibold"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a
                                        href="{{ route('seller.orders.show', $order) }}"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        {{ __('orders_view_details') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        <!-- start total row -->
                        <tr class="bg-gray-50 font-bold">
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                {{ __('orders_total') }}:
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                {{ number_format((float) $ordersData['totalAmount'], 2) }} €
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <!-- end total row -->
                    </tbody>
                    <!-- end table body -->
                </table>
                <!-- end orders table -->
                </div>
            </x-ui.card>
            <!-- end table container -->
        @else
            <!-- start no orders message -->
            <x-ui.card class="rounded-lg shadow-sm">
                <div class="text-center py-8 text-gray-500">
                    {{ __('orders_no_orders') }}
                </div>
            </x-ui.card>
            <!-- end no orders message -->
        @endif
    </div>
    <!-- end main container -->
</div>
<!-- end section -->

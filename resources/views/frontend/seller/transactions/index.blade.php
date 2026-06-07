<div>
    <div class="max-w-7xl mx-auto">
        <x-seller.breadcrumbs
            class="mb-6"
            :items="[
                ['label' => __('common_transactions')],
            ]"
        />

        <x-ui.header
            class="mb-6"
            :title="__('common_transactions')"
            :subtitle="__('transactions_current_balance')"
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('seller.dashboard')"
                    secondary
                    :label="__('common_back_to_dashboard')"
                />
            </x-slot:actions>
        </x-ui.header>

        <!-- start stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-ui.statistic
                :title="__('transactions_current_balance')"
                :value="'€' . number_format((float) $stats['current_balance'], 2)"
                icon="banknotes"
                color="text-primary"
                class="shadow-sm"
            />

            <x-ui.statistic
                :title="__('transactions_total_deductions')"
                :value="'€' . number_format((float) $stats['total_deductions'], 2)"
                icon="arrow-trending-down"
                color="text-error"
                class="shadow-sm"
            />

            <x-ui.statistic
                :title="__('transactions_total_refunds')"
                :value="'€' . number_format((float) $stats['total_refunds'], 2)"
                icon="arrow-trending-up"
                color="text-success"
                class="shadow-sm"
            />
        </div>
        <!-- end stats -->

        <!-- start filters -->
        <x-ui.card class="mb-6 rounded-lg shadow-sm">
            <form wire:submit.prevent="applyFilters" class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:space-x-4">
                    <!-- type filter -->
                    <div class="w-full sm:w-48">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('transactions_type') }}
                        </label>
                        <select 
                            id="type" 
                            wire:model="type"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        >
                            <option value="">{{ __('common_all') }}</option>
                            <option value="deduction" {{ $filters['type'] === 'deduction' ? 'selected' : '' }}>
                                {{ __('transactions_type_deduction') }}
                            </option>
                            <option value="refund" {{ $filters['type'] === 'refund' ? 'selected' : '' }}>
                                {{ __('transactions_type_refund') }}
                            </option>
                        </select>
                    </div>

                    <!-- date from filter -->
                    <div class="w-full sm:w-48">
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">
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

                    <!-- date to filter -->
                    <div class="w-full sm:w-48">
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">
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

                    <!-- filter button -->
                    <div class="w-full sm:w-auto sm:self-end">
                        <x-mary-button
                            type="submit"
                            class="w-full sm:w-auto btn-primary"
                            :label="__('common_filter')"
                        />
                    </div>
            </form>
        </x-ui.card>
        <!-- end filters -->

        <!-- start transactions list -->
        <x-ui.card
            class="rounded-lg shadow-sm"
            body-class="-mx-5 overflow-hidden"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('transactions_date') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('transactions_order') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('transactions_type') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('transactions_description') }}
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('transactions_amount') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transaction->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <a 
                                        href="{{ route('seller.orders.show', $transaction->order_id) }}" 
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        #{{ $transaction->order_id }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <x-ui.badge
                                        :value="__('transactions_type_2_' . $transaction->type)"
                                        :color="$transaction->type === 'deduction' ? 'error' : 'success'"
                                        soft
                                        sm
                                        class="font-medium"
                                    />
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $transaction->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium
                                    {{ $transaction->type === 'deduction' ? 'text-red-600' : 'text-green-600' }}"
                                >
                                    {{ number_format($transaction->amount, 2) }} €
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    {{ __('transactions_no_transactions') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <x-slot:footer>
                {{ $transactions->links() }}
            </x-slot:footer>
        </x-ui.card>
        <!-- end transactions list -->
    </div>
</div>

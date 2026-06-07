@php
    $headers = [
        ['key' => 'created_at', 'label' => __('backend_buyers_credit_history_table_date'), 'class' => 'w-40'],
        ['key' => 'type', 'label' => __('backend_buyers_credit_history_table_type'), 'class' => 'w-28'],
        ['key' => 'amount', 'label' => __('backend_buyers_credit_history_table_amount'), 'class' => 'text-right w-36'],
        ['key' => 'note', 'label' => __('backend_buyers_credit_history_table_note')],
        ['key' => 'balance_after', 'label' => __('backend_buyers_credit_history_table_balance_after'), 'class' => 'text-right w-40'],
        ['key' => 'admin_name', 'label' => __('backend_buyers_credit_history_table_admin'), 'class' => 'w-40'],
    ];
@endphp

<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('navigation_buyers'), 'link' => route('backend.buyers.index')],
            ['label' => $buyer->company_name ?: $buyer->name],
            ['label' => __('backend_buyers_credit_manage_credit'), 'link' => route('backend.buyers.credit', $buyer)],
            ['label' => __('backend_buyers_credit_view_history')],
        ]"
    />

    <x-mary-header
        :title="$buyer->name"
        :subtitle="__('backend_buyers_credit_current_balance') . ': €' . number_format((float) $buyer->credit_balance, 2)"
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-mary-button
                :label="__('common_back')"
                :link="route('backend.buyers.credit', $buyer)"
            />
            <x-mary-button
                :label="__('common_filter')"
                icon="o-funnel"
                responsive
                @click="$wire.drawer = true"
            />
            <x-mary-button
                :label="__('backend_common_export_csv')"
                icon="o-arrow-down-tray"
                wire:click="exportCsv"
                spinner="exportCsv"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <x-mary-table
            :headers="$headers"
            :rows="$creditHistory"
            striped
            no-hover
            with-pagination
            show-empty-text
            :empty-text="__('backend_buyers_credit_history_table_no_records')"
        >
            @scope('cell_created_at', $history)
                {{ $history->created_at?->format('Y-m-d H:i') ?? __('common_not_specified') }}
            @endscope

            @scope('cell_type', $history)
                @php($isCredit = in_array($history->type, ['add', 'credit'], true))

                <x-mary-badge
                    :value="$isCredit ? __('backend_buyers_credit_history_table_credit') : __('backend_buyers_credit_history_table_debit')"
                    class="{{ $isCredit ? 'badge-success badge-outline' : 'badge-error badge-outline' }}"
                />
            @endscope

            @scope('cell_amount', $history)
                @php($isCredit = in_array($history->type, ['add', 'credit'], true))

                <div class="text-right font-medium {{ $isCredit ? 'text-success' : 'text-error' }}">
                    {{ $isCredit ? '+' : '-' }}€{{ number_format(abs((float) $history->amount), 2) }}
                </div>
            @endscope

            @scope('cell_note', $history)
                <div class="space-y-2">
                    <div class="max-w-xl truncate text-sm text-base-content/70">
                        {{ $history->note ?: '—' }}
                    </div>

                    @if ($history->attachments->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($history->attachments as $attachment)
                                <button
                                    type="button"
                                    wire:click="downloadAttachment({{ $attachment->id }})"
                                    class="text-sm font-medium text-primary hover:text-primary/80"
                                >
                                    {{ $attachment->original_name }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endscope

            @scope('cell_balance_after', $history)
                <div class="text-right font-semibold">
                    €{{ number_format((float) $history->balance_after, 2) }}
                </div>
            @endscope

            @scope('cell_admin_name', $history)
                {{ $history->admin?->name ?: __('backend_buyers_credit_history_table_system') }}
            @endscope
        </x-mary-table>
    </x-mary-card>

    <x-mary-drawer
        wire:model="drawer"
        :title="__('common_filter')"
        right
        separator
        with-close-button
        close-on-escape
        class="w-full max-w-md"
    >
        <x-mary-form wire:submit="applyFilters" no-separator class="gap-4">
            <div class="space-y-4">
                <x-mary-select
                    :label="__('backend_buyers_credit_history_filter_type')"
                    wire:model="typeFilter"
                    :options="$typeOptions"
                    option-value="id"
                    option-label="name"
                    icon="o-arrows-right-left"
                    :placeholder="__('backend_buyers_credit_history_filter_all_types')"
                    placeholder-value=""
                />

                <x-mary-datetime
                    :label="__('backend_buyers_credit_history_filter_date_from')"
                    wire:model="dateFrom"
                    icon="o-calendar-days"
                />

                <x-mary-datetime
                    :label="__('backend_buyers_credit_history_filter_date_to')"
                    wire:model="dateTo"
                    icon="o-calendar-days"
                />
            </div>

            <x-slot:actions>
                @if ($typeFilter !== '' || $dateFrom !== '' || $dateTo !== '')
                    <x-mary-button
                        :label="__('common_reset')"
                        icon="o-arrow-path"
                        wire:click="clear"
                        spinner="clear"
                    />
                @endif

                <x-mary-button
                    :label="__('backend_buyers_credit_history_filter_apply')"
                    icon="o-funnel"
                    type="submit"
                    class="btn-primary"
                    spinner="applyFilters"
                />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-drawer>
</div>

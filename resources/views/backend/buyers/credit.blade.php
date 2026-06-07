@php
    $historyHeaders = [
        ['key' => 'created_at', 'label' => __('backend_buyers_credit_history_table_date'), 'class' => 'w-40'],
        ['key' => 'type', 'label' => __('backend_buyers_credit_history_table_type'), 'class' => 'w-28'],
        ['key' => 'amount', 'label' => __('backend_buyers_credit_history_table_amount'), 'class' => 'text-right w-36'],
        ['key' => 'note', 'label' => __('backend_buyers_credit_history_table_note')],
        ['key' => 'balance_after', 'label' => __('backend_buyers_credit_history_table_balance_after'), 'class' => 'text-right w-40'],
        ['key' => 'admin_name', 'label' => __('backend_buyers_credit_history_table_admin'), 'class' => 'w-40'],
    ];
@endphp

<div class="space-y-6">
    <x-mary-header
        :title="$buyer->company_name ?: $buyer->name"
        :subtitle="$buyer->email"
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-mary-button
                :label="__('common_back')"
                :link="route('backend.buyers.index')"
            />
            <x-mary-button
                :label="__('backend_buyers_credit_view_history')"
                :link="route('backend.buyers.credit_history', $buyer)"
                icon="o-clock"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-mary-header>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,24rem),minmax(0,1fr)]">
        <x-mary-card :title="__('backend_buyers_credit_manage_credit')" shadow>
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <button
                        type="button"
                        wire:click="selectAction('add')"
                        id="addButton"
                        class="inline-flex items-center justify-center rounded-xl px-4 py-4 text-white {{ $selectedAction === 'add' ? 'bg-success' : 'bg-success/60' }}"
                    >
                        <span class="flex items-center">
                            <x-mary-icon name="o-plus" class="mr-2 h-5 w-5" />
                            {{ __('buyers_credit_history_credit') }}
                        </span>
                    </button>

                    <button
                        type="button"
                        wire:click="selectAction('deduct')"
                        id="deductButton"
                        class="inline-flex items-center justify-center rounded-xl px-4 py-4 text-white {{ $selectedAction === 'deduct' ? 'bg-error' : 'bg-error/60' }}"
                    >
                        <span class="flex items-center">
                            <x-mary-icon name="o-minus" class="mr-2 h-5 w-5" />
                            {{ __('buyers_credit_history_debit') }}
                        </span>
                    </button>
                </div>

                <x-mary-form
                    wire:submit="submitCredit"
                    class="space-y-4 {{ $selectedAction ? '' : 'hidden' }}"
                    enctype="multipart/form-data"
                    no-separator
                >
                    @error('action')
                        <p class="text-sm text-error">{{ $message }}</p>
                    @enderror

                    <x-mary-input
                        :label="__('buyers_credit_history_amount')"
                        wire:model="amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        prefix="€"
                        placeholder="0.00"
                        required
                    />

                    <x-mary-textarea
                        :label="__('buyers_credit_history_note')"
                        :hint="__('backend_buyers_credit_note_hint')"
                        :placeholder="__('backend_buyers_credit_note_placeholder')"
                        wire:model="note"
                        rows="4"
                        maxlength="255"
                    />

                    @php($selectedAttachmentName = $attachment?->getClientOriginalName())

                    <x-mary-file
                        :label="__('common_attachment')"
                        :hint="__('common_optional_upload_supporting_document')"
                        wire:model="attachment"
                        accept="application/pdf,image/png,image/jpeg"
                        :change-text="__('common_edit')"
                    >
                        <div class="rounded-2xl border border-dashed border-base-300 bg-base-100 px-4 py-5 transition hover:border-primary/40 hover:bg-primary/5">
                            <div class="flex items-start gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                    <x-mary-icon name="o-paper-clip" class="h-6 w-6" />
                                </div>

                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-base-content">
                                        {{ $selectedAttachmentName ?: __('common_attachment') }}
                                    </div>
                                    <div class="mt-1 text-xs text-base-content/60">
                                        {{ __('common_optional_upload_supporting_document') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-mary-file>

                    <x-slot:actions>
                        <x-mary-button
                            :label="__('buyers_credit_history_apply')"
                            type="submit"
                            spinner="submitCredit"
                            class="{{ $selectedAction === 'add' ? 'btn-success' : 'btn-error' }}"
                        />
                    </x-slot:actions>
                </x-mary-form>
            </div>
        </x-mary-card>

        <div class="space-y-6">
            <x-mary-card :title="__('backend_buyers_credit_buyer_info')" shadow>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">{{ __('buyers_fields_company') }}</h3>
                        <p class="mt-1 text-lg font-medium text-gray-900">{{ $buyer->company_name }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">{{ __('buyers_fields_company_code') }}</h3>
                        <p class="mt-1 text-lg font-medium text-gray-900">{{ $buyer->company_code }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">{{ __('buyers_fields_vat_code') }}</h3>
                        <p class="mt-1 text-lg font-medium text-gray-900">{{ $buyer->vat_code ?: '—' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">{{ __('buyers_fields_phone') }}</h3>
                        <p class="mt-1 text-lg font-medium text-gray-900">{{ $buyer->phone ?: '—' }}</p>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl bg-primary/5 p-6 text-center">
                    <h2 class="text-sm font-medium uppercase tracking-wide text-primary">{{ __('buyers_credit_current_balance') }}</h2>
                    <div class="mt-2 text-4xl font-extrabold text-primary">€{{ number_format((float) $buyer->credit_balance, 2) }}</div>
                </div>
            </x-mary-card>

            <x-mary-card :title="__('buyers_credit_view_history')" shadow>
                <x-mary-table
                    :headers="$historyHeaders"
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
                        <div class="max-w-xl truncate text-sm text-base-content/70">
                            {{ $history->note ?: '—' }}
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
        </div>
    </div>
</div>

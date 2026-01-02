<x-backend.page :title="$buyer->name" :description="$buyer->email">
    <div class="space-y-6">
        <x-ui.card>
            <div class="flex items-center gap-3">
                <div class="text-sm text-base-content/60">{{ __('backend.buyers.credit.current_balance') }}</div>
                <div class="text-2xl font-bold text-primary">€{{ number_format($buyer->credit_balance, 2) }}</div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="form-control">
                    <label for="type" class="label">
                        <span class="label-text">{{ __('backend.buyers.credit_history.filter.type') }}</span>
                    </label>
                    <select name="type" id="type" class="select select-bordered w-full">
                        <option value="">{{ __('backend.buyers.credit_history.filter.all_types') }}</option>
                        <option value="add" {{ request('type') === 'add' ? 'selected' : '' }}>{{ __('backend.buyers.credit_history.table.credit') }}</option>
                        <option value="deduct" {{ request('type') === 'deduct' ? 'selected' : '' }}>{{ __('backend.buyers.credit_history.table.debit') }}</option>
                    </select>
                </div>

                <div class="form-control">
                    <label for="date_from" class="label">
                        <span class="label-text">{{ __('backend.buyers.credit_history.filter.date_from') }}</span>
                    </label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="input input-bordered w-full">
                </div>

                <div class="form-control">
                    <label for="date_to" class="label">
                        <span class="label-text">{{ __('backend.buyers.credit_history.filter.date_to') }}</span>
                    </label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="input input-bordered w-full">
                </div>

                <div class="flex flex-wrap items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ __('backend.buyers.credit_history.filter.apply') }}
                    </button>
                    <button type="button" wire:click="exportCsv" class="btn btn-ghost btn-sm">
                        {{ __('backend.common.export_csv') }}
                    </button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>{{ __('backend.buyers.credit_history.table.date') }}</th>
                            <th>{{ __('backend.buyers.credit_history.table.type') }}</th>
                            <th class="text-right">{{ __('backend.buyers.credit_history.table.amount') }}</th>
                            <th>{{ __('backend.buyers.credit_history.table.note') }}</th>
                            <th class="text-right">{{ __('backend.buyers.credit_history.table.balance_after') }}</th>
                            <th>{{ __('backend.buyers.credit_history.table.admin') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($creditHistory as $history)
                            <tr>
                                <td>{{ $history->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <span class="badge badge-outline {{ $history->type === 'add' ? 'badge-success' : 'badge-error' }}">
                                        {{ $history->type === 'add' ? __('backend.buyers.credit_history.table.credit') : __('backend.buyers.credit_history.table.debit') }}
                                    </span>
                                </td>
                                <td class="text-right {{ $history->type === 'add' ? 'text-success' : 'text-error' }}">
                                    {{ $history->type === 'add' ? '+' : '-' }}€{{ number_format(abs($history->amount), 2) }}
                                </td>
                                <td class="max-w-xs truncate">{{ $history->note ?: '-' }}</td>
                                <td class="text-right font-semibold">€{{ number_format($history->balance_after, 2) }}</td>
                                <td>{{ $history->admin ? $history->admin->name : __('backend.buyers.credit_history.table.system') }}</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="bg-base-100">
                                    @if($history->attachments->isNotEmpty())
                                        <div class="text-sm">
                                            <span class="text-base-content/60">{{ __('common.attachments') }}:</span>
                                            @foreach($history->attachments as $attachment)
                                                <button type="button" wire:click="downloadAttachment({{ $attachment->id }})" class="btn btn-link btn-xs">
                                                    {{ $attachment->original_name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-sm text-base-content/60">
                                    {{ __('backend.buyers.credit_history.table.no_records') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <div>
            {{ $creditHistory->withQueryString()->links() }}
        </div>
    </div>
</x-backend.page>

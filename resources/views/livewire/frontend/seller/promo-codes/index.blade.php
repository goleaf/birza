<div class="mx-auto max-w-7xl space-y-6">
    <x-seller.breadcrumbs
        :items="[
            ['label' => __('promo_codes.title')],
        ]"
    />

    <x-ui.header :title="__('promo_codes.title')" :subtitle="__('promo_codes.subtitle')">
        <x-slot:actions>
            <x-ui.button href="{{ route('seller.discounts.index') }}" secondary :label="__('discounts.title')" />
        </x-slot:actions>
    </x-ui.header>

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <x-ui.card class="shadow-sm">
        <form wire:submit.prevent="save" class="space-y-5">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="promo_code" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('promo_codes.code') }}
                    </label>
                    <input id="promo_code" type="text" wire:model="code" class="w-full rounded-lg border-gray-300 uppercase focus:border-blue-500 focus:ring focus:ring-blue-200">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="promo_type" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('promo_codes.type.label') }}
                    </label>
                    <select id="promo_type" wire:model="type" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        @foreach ($types as $typeOption)
                            <option value="{{ $typeOption }}">{{ __("promo_codes.type.$typeOption") }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="promo_value" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('promo_codes.value') }}
                    </label>
                    <input id="promo_value" type="number" step="0.01" wire:model="value" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    @error('value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="promo_status" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('promo_codes.status.label') }}
                    </label>
                    <select id="promo_status" wire:model="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ __("promo_codes.status.$statusOption") }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="promo_starts_at" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('promo_codes.starts_at') }}
                    </label>
                    <input id="promo_starts_at" type="datetime-local" wire:model="starts_at" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    @error('starts_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="promo_ends_at" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('promo_codes.ends_at') }}
                    </label>
                    <input id="promo_ends_at" type="datetime-local" wire:model="ends_at" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    @error('ends_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="promo_usage_limit" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('promo_codes.usage_limit') }}
                    </label>
                    <input id="promo_usage_limit" type="number" min="0" wire:model="usage_limit" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    @error('usage_limit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="promo_per_user_limit" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('promo_codes.per_user_limit') }}
                    </label>
                    <input id="promo_per_user_limit" type="number" min="1" wire:model="per_user_limit" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    @error('per_user_limit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="promo_minimum_order_amount" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('promo_codes.minimum_order_amount_label') }}
                    </label>
                    <input id="promo_minimum_order_amount" type="number" step="0.01" min="0" wire:model="minimum_order_amount" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    @error('minimum_order_amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                @if ($editingId)
                    <x-ui.button type="button" secondary wire:click="resetForm" :label="__('ui.actions.cancel')" />
                @endif
                <x-ui.button type="submit" primary wire:loading.attr="disabled" :label="$editingId ? __('promo_codes.update') : __('promo_codes.create')" />
            </div>
        </form>
    </x-ui.card>

    <x-ui.card class="shadow-sm" body-class="-mx-5 -mb-5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">{{ __('promo_codes.code') }}</th>
                        <th class="px-5 py-3">{{ __('promo_codes.type.label') }}</th>
                        <th class="px-5 py-3">{{ __('promo_codes.value') }}</th>
                        <th class="px-5 py-3">{{ __('promo_codes.status.label') }}</th>
                        <th class="px-5 py-3">{{ __('promo_codes.usage') }}</th>
                        <th class="px-5 py-3">{{ __('promo_codes.ends_at') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('common_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($promoCodes as $promoCode)
                        <tr wire:key="promo-code-{{ $promoCode->id }}">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $promoCode->code }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ __("promo_codes.type.$promoCode->type") }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ number_format((float) $promoCode->value, 2) }}</td>
                            <td class="px-5 py-4">
                                <x-ui.badge :value="__(\"promo_codes.status.$promoCode->status\")" :color="$promoCode->status === 'active' ? 'success' : 'warning'" soft />
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $promoCode->used_count }} / {{ $promoCode->usage_limit ?? __('common_unlimited') }}
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $promoCode->ends_at?->format('Y-m-d H:i') ?? __('common_not_specified') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <x-ui.button type="button" xs secondary wire:click="edit({{ $promoCode->id }})" :label="__('ui.actions.edit')" />
                                    <x-ui.button type="button" xs outline wire:click="toggleStatus({{ $promoCode->id }})" :label="$promoCode->status === 'active' ? __('promo_codes.deactivate') : __('promo_codes.activate')" />
                                    <x-ui.button type="button" xs negative wire:click="archive({{ $promoCode->id }})" :label="__('ui.actions.delete')" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                                {{ __('promo_codes.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-4">
            {{ $promoCodes->links() }}
        </div>
    </x-ui.card>
</div>

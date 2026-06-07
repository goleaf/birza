<div class="mx-auto max-w-7xl space-y-6">
    <x-seller.breadcrumbs
        :items="[
            ['label' => __('discounts.title')],
        ]"
    />

    <x-ui.header :title="__('discounts.title')" :subtitle="__('discounts.subtitle')">
        <x-slot:actions>
            <x-ui.button href="{{ route('seller.promo-codes.index') }}" secondary :label="__('promo_codes.title')" />
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
                    <label for="discount_name" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('discounts.name') }}
                    </label>
                    <input
                        id="discount_name"
                        type="text"
                        wire:model="name"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_type" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('discounts.type.label') }}
                    </label>
                    <select id="discount_type" wire:model="type" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        @foreach ($types as $typeOption)
                            <option value="{{ $typeOption }}">{{ __("discounts.type.$typeOption") }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_value" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('discounts.value') }}
                    </label>
                    <input
                        id="discount_value"
                        type="number"
                        step="0.01"
                        wire:model="value"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    >
                    @error('value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_status" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('discounts.status.label') }}
                    </label>
                    <select id="discount_status" wire:model="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ __("discounts.status.$statusOption") }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_product_id" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('discounts.product') }}
                    </label>
                    <select id="discount_product_id" wire:model="product_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">{{ __('discounts.all_products') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_category_id" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('discounts.category') }}
                    </label>
                    <select id="discount_category_id" wire:model="category_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="">{{ __('discounts.all_categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->getTranslation('category_name', app()->getLocale()) }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_starts_at" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('discounts.starts_at') }}
                    </label>
                    <input id="discount_starts_at" type="datetime-local" wire:model="starts_at" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    @error('starts_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_ends_at" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('discounts.ends_at') }}
                    </label>
                    <input id="discount_ends_at" type="datetime-local" wire:model="ends_at" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    @error('ends_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_usage_limit" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('discounts.usage_limit') }}
                    </label>
                    <input id="discount_usage_limit" type="number" min="0" wire:model="usage_limit" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    @error('usage_limit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_minimum_order_amount" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('discounts.minimum_order_amount') }}
                    </label>
                    <input id="discount_minimum_order_amount" type="number" step="0.01" min="0" wire:model="minimum_order_amount" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    @error('minimum_order_amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                @if ($editingId)
                    <x-ui.button type="button" secondary wire:click="resetForm" :label="__('ui.actions.cancel')" />
                @endif
                <x-ui.button type="submit" primary wire:loading.attr="disabled" :label="$editingId ? __('discounts.update') : __('discounts.create')" />
            </div>
        </form>
    </x-ui.card>

    <x-ui.card class="shadow-sm" body-class="-mx-5 -mb-5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">{{ __('discounts.name') }}</th>
                        <th class="px-5 py-3">{{ __('discounts.scope') }}</th>
                        <th class="px-5 py-3">{{ __('discounts.type.label') }}</th>
                        <th class="px-5 py-3">{{ __('discounts.value') }}</th>
                        <th class="px-5 py-3">{{ __('discounts.status.label') }}</th>
                        <th class="px-5 py-3">{{ __('discounts.used_count') }}</th>
                        <th class="px-5 py-3">{{ __('discounts.ends_at') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('common_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($discounts as $discount)
                        <tr wire:key="discount-{{ $discount->id }}">
                            <td class="px-5 py-4 font-medium text-gray-900">{{ $discount->name }}</td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $discount->product?->name
                                    ?? $discount->category?->getTranslation('category_name', app()->getLocale())
                                    ?? __('discounts.all_products') }}
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ __("discounts.type.$discount->type") }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ number_format((float) $discount->value, 2) }}</td>
                            <td class="px-5 py-4">
                                <x-ui.badge :value="__(\"discounts.status.$discount->status\")" :color="$discount->status === 'active' ? 'success' : 'warning'" soft />
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $discount->used_count }} / {{ $discount->usage_limit ?? __('common_unlimited') }}
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $discount->ends_at?->format('Y-m-d H:i') ?? __('common_not_specified') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <x-ui.button type="button" xs secondary wire:click="edit({{ $discount->id }})" :label="__('ui.actions.edit')" />
                                    <x-ui.button type="button" xs outline wire:click="toggleStatus({{ $discount->id }})" :label="$discount->status === 'active' ? __('discounts.deactivate') : __('discounts.activate')" />
                                    <x-ui.button type="button" xs negative wire:click="archive({{ $discount->id }})" :label="__('ui.actions.delete')" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-gray-500">
                                {{ __('discounts.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-4">
            {{ $discounts->links() }}
        </div>
    </x-ui.card>
</div>

<div>
    <form wire:submit.prevent="save" class="space-y-6">
        <x-seller.breadcrumbs
            :items="[
                ['label' => __('bundles.title'), 'url' => route('seller.bundles.index')],
                ['label' => $productBundle ? __('bundles.edit') : __('bundles.create')],
            ]"
        />

        <x-ui.header
            :title="$productBundle ? __('bundles.edit') : __('bundles.create')"
            :subtitle="__('bundles.seller_subtitle')"
        >
            <x-slot:actions>
                <x-ui.button
                    href="{{ route('seller.bundles.index') }}"
                    secondary
                    :label="__('common_cancel')"
                />
                <x-ui.button
                    type="submit"
                    primary
                    wire:loading.attr="disabled"
                    :label="__('common_save')"
                />
            </x-slot:actions>
        </x-ui.header>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
            <div class="space-y-6">
                <x-ui.card class="shadow-sm sm:rounded-lg">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="bundle_name">
                                {{ __('bundles.name') }}
                            </label>
                            <input
                                id="bundle_name"
                                type="text"
                                wire:model.live="name"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                            @error('name')
                                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="bundle_slug">
                                {{ __('bundles.slug') }}
                            </label>
                            <input
                                id="bundle_slug"
                                type="text"
                                wire:model="slug"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                            @error('slug')
                                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="bundle_status">
                                {{ __('common_status') }}
                            </label>
                            <select
                                id="bundle_status"
                                wire:model="status"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                                @foreach ($statusOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="bundle_image">
                                {{ __('bundles.image') }}
                            </label>
                            <input
                                id="bundle_image"
                                type="file"
                                wire:model="image"
                                accept="image/*"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                            @error('image')
                                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="bundle_description">
                                {{ __('bundles.description') }}
                            </label>
                            <textarea
                                id="bundle_description"
                                wire:model="description"
                                rows="4"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                            ></textarea>
                            @error('description')
                                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="shadow-sm sm:rounded-lg">
                    <div class="mb-4">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('bundles.included_products') }}</h2>
                        <p class="text-sm text-gray-600">{{ __('bundles.minimum_products_required') }}</p>
                    </div>

                    <div class="space-y-3">
                        @forelse ($products as $product)
                            <div wire:key="bundle-product-option-{{ $product->id }}" class="grid gap-3 rounded-lg border p-4 md:grid-cols-[minmax(0,1fr)_110px_110px] md:items-center">
                                <label class="flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        value="{{ $product->id }}"
                                        wire:model.live="selectedProductIds"
                                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span>
                                        <span class="block font-medium text-gray-900">{{ $product->name }}</span>
                                        <span class="block text-sm text-gray-600">
                                            {{ number_format((float) $product->price, 2) }} €
                                            - {{ __('product_stock') }}: {{ $product->stock }}
                                            @if (! $product->is_active)
                                                - {{ __('cart_unavailable') }}
                                            @endif
                                        </span>
                                    </span>
                                </label>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600" for="bundle_quantity_{{ $product->id }}">
                                        {{ __('bundles.quantity') }}
                                    </label>
                                    <input
                                        id="bundle_quantity_{{ $product->id }}"
                                        type="number"
                                        min="1"
                                        wire:model="itemQuantities.{{ $product->id }}"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600" for="bundle_sort_{{ $product->id }}">
                                        {{ __('bundles.sort_order') }}
                                    </label>
                                    <input
                                        id="bundle_sort_{{ $product->id }}"
                                        type="number"
                                        min="0"
                                        wire:model="itemSortOrders.{{ $product->id }}"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    >
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                {{ __('bundles.no_seller_products') }}
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            <div class="space-y-6">
                <x-ui.card class="shadow-sm sm:rounded-lg">
                    <h2 class="mb-4 text-lg font-bold text-gray-900">{{ __('bundles.discount') }}</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="bundle_discount_type">
                                {{ __('bundles.discount_type') }}
                            </label>
                            <select
                                id="bundle_discount_type"
                                wire:model.live="discount_type"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                                <option value="">{{ __('common_none') }}</option>
                                @foreach ($discountTypeOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            @error('discount_type')
                                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="bundle_discount_value">
                                {{ __('bundles.discount_value') }}
                            </label>
                            <input
                                id="bundle_discount_value"
                                type="number"
                                min="0"
                                step="0.01"
                                wire:model.live="discount_value"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                            @error('discount_value')
                                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="shadow-sm sm:rounded-lg">
                    <h2 class="mb-4 text-lg font-bold text-gray-900">{{ __('bundles.availability') }}</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="bundle_starts_at">
                                {{ __('bundles.starts_at') }}
                            </label>
                            <input
                                id="bundle_starts_at"
                                type="datetime-local"
                                wire:model="starts_at"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                            @error('starts_at')
                                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="bundle_ends_at">
                                {{ __('bundles.ends_at') }}
                            </label>
                            <input
                                id="bundle_ends_at"
                                type="datetime-local"
                                wire:model="ends_at"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                            >
                            @error('ends_at')
                                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="shadow-sm sm:rounded-lg">
                    <h2 class="mb-4 text-lg font-bold text-gray-900">{{ __('bundles.preview') }}</h2>

                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" alt="{{ __('bundles.image') }}" class="mb-4 h-40 w-full rounded-lg object-cover">
                    @elseif ($currentImagePath)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($currentImagePath) }}" alt="{{ __('bundles.image') }}" class="mb-4 h-40 w-full rounded-lg object-cover">
                    @endif

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-gray-600">{{ __('bundles.base_price') }}</span>
                            <span class="font-medium">{{ number_format($bundlePreviewBasePrice, 2) }} €</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-gray-600">{{ __('bundles.discount') }}</span>
                            <span class="font-medium text-green-700">-{{ number_format($bundlePreviewDiscountAmount, 2) }} €</span>
                        </div>
                        <div class="flex justify-between gap-4 border-t pt-2 text-base font-bold">
                            <span>{{ __('bundles.final_price') }}</span>
                            <span>{{ number_format($bundlePreviewFinalPrice, 2) }} €</span>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </form>
</div>

<div>
    <div class="space-y-6">
        <x-buyer.breadcrumbs
            :items="[
                ['label' => __('common_products'), 'url' => route('buyer.products.index')],
                ['label' => __('bundles.title')],
                ['label' => $bundle->name],
            ]"
        />

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(360px,0.8fr)] lg:items-start">
            <div class="space-y-6">
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <img
                        src="{{ $bundle->imageUrl() }}"
                        alt="{{ $bundle->name }}"
                        class="aspect-[16/9] w-full object-cover"
                    >
                </div>

                <x-ui.card class="shadow-sm sm:rounded-lg">
                    <h2 class="mb-4 text-xl font-bold text-gray-900">{{ __('bundles.included_products') }}</h2>

                    <div class="space-y-4">
                        @foreach ($bundle->items as $bundleItem)
                            @php($product = $bundleItem->product)
                            <div class="flex gap-4 rounded-lg border p-4">
                                <img
                                    src="{{ $product?->imageUrl('thumb') ?? asset((string) config('images.fallbacks.product')) }}"
                                    alt="{{ $product?->name ?? __('common_unnamed_product') }}"
                                    class="h-20 w-20 rounded-lg object-cover"
                                    loading="lazy"
                                >
                                <div class="space-y-1">
                                    <h3 class="font-semibold text-gray-900">
                                        @if ($product)
                                            <a href="{{ route('buyer.products.show', $product) }}" class="hover:text-blue-600">
                                                {{ $product->name }}
                                            </a>
                                        @else
                                            {{ __('common_unnamed_product') }}
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        {{ __('bundles.quantity') }}: {{ $bundleItem->quantity }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ __('product_price') }}: {{ number_format((float) ($product?->price ?? 0), 2) }} €
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            </div>

            <x-ui.card class="shadow-sm sm:rounded-lg">
                <div class="space-y-5">
                    <div>
                        <div class="mb-2 text-sm font-medium text-blue-700">{{ __('cart.bundle') }}</div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $bundle->name }}</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('cart_seller') }}:
                            {{ $bundle->seller?->company_name ?: $bundle->seller?->name }}
                        </p>
                    </div>

                    @if ($bundle->description)
                        <p class="text-gray-700">{{ $bundle->description }}</p>
                    @endif

                    <div class="space-y-2 rounded-lg bg-gray-50 p-4 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-gray-600">{{ __('bundles.base_price') }}</span>
                            <span class="font-medium">{{ number_format($price['base_price'], 2) }} €</span>
                        </div>
                        @if ($price['discount_amount'] > 0)
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-600">{{ __('bundles.discount') }}</span>
                                <span class="font-medium text-green-700">-{{ number_format($price['discount_amount'], 2) }} €</span>
                            </div>
                        @endif
                        <div class="flex justify-between gap-4 border-t pt-2 text-lg font-bold">
                            <span>{{ __('bundles.final_price') }}</span>
                            <span>{{ number_format($price['final_price'], 2) }} €</span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700" for="bundle_quantity">
                            {{ __('bundles.quantity') }}
                        </label>
                        <input
                            id="bundle_quantity"
                            type="number"
                            min="1"
                            wire:model.live="quantity"
                            class="w-28 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                        >
                        @error('quantity')
                            <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <x-ui.button
                        type="button"
                        positive
                        wire:click="addToCart"
                        wire:loading.attr="disabled"
                        :label="__('bundles.add_to_cart')"
                    />
                </div>
            </x-ui.card>
        </div>
    </div>
</div>

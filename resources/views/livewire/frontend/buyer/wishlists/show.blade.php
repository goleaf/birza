<div>
    <div class="space-y-6">
        <x-buyer.breadcrumbs
            :items="[
                ['label' => __('wishlists.title'), 'link' => route('buyer.wishlists.index')],
                ['label' => $wishlist->name],
            ]"
        />

        <x-ui.header
            :title="$wishlist->name"
            :subtitle="__('wishlists.items_count', ['count' => $wishlist->items_count])"
        >
            <x-slot:actions>
                <x-ui.button
                    href="{{ route('buyer.wishlists.index') }}"
                    secondary
                    icon="arrow-left"
                    :label="__('wishlists.back_to_wishlists')"
                />
                @if ($canManage && $wishlist->items_count > 0)
                    <x-ui.button
                        type="button"
                        negative
                        icon="archive-box-x-mark"
                        wire:click="clearWishlist"
                        wire:loading.attr="disabled"
                        :label="__('wishlists.clear')"
                    />
                @endif
            </x-slot:actions>
        </x-ui.header>

        @if ($wishlist->description)
            <x-ui.card body-class="text-sm leading-6 text-gray-700">
                {{ $wishlist->description }}
            </x-ui.card>
        @endif

        <div class="space-y-4">
            @forelse ($wishlistItems as $item)
                <x-ui.card
                    wire:key="wishlist-item-{{ $item['id'] }}"
                    class="shadow-sm"
                    body-class="space-y-5"
                >
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex gap-4">
                            <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100 sm:h-32 sm:w-32">
                                @if ($item['url'])
                                    <a href="{{ $item['url'] }}">
                                        <img
                                            src="{{ $item['image_url'] }}"
                                            alt="{{ $item['name'] }}"
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                        >
                                    </a>
                                @else
                                    <img
                                        src="{{ $item['image_url'] }}"
                                        alt="{{ $item['name'] }}"
                                        class="h-full w-full object-cover opacity-70"
                                        loading="lazy"
                                    >
                                @endif
                            </div>

                            <div class="min-w-0 space-y-2">
                                <h2 class="text-lg font-semibold text-gray-900">
                                    @if ($item['url'])
                                        <a href="{{ $item['url'] }}" class="hover:text-blue-600">
                                            {{ $item['name'] }}
                                        </a>
                                    @else
                                        {{ $item['name'] }}
                                    @endif
                                </h2>

                                <dl class="grid gap-1 text-sm text-gray-600 sm:grid-cols-2">
                                    <div>
                                        <dt class="font-medium text-gray-500">{{ __('product_price') }}</dt>
                                        <dd>
                                            @if ($item['price'])
                                                {{ $item['price'] }} € / {{ $item['unit'] }}
                                            @else
                                                {{ __('common_not_specified') }}
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-500">{{ __('product_seller') }}</dt>
                                        <dd>{{ $item['seller'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-500">{{ __('product_category') }}</dt>
                                        <dd>{{ $item['parent_category'] ? $item['parent_category'].' / '.$item['category'] : $item['category'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-500">{{ __('product_stock') }}</dt>
                                        <dd>{{ $item['stock'] }}</dd>
                                    </div>
                                </dl>

                                <div class="flex flex-wrap gap-2">
                                    @if ($item['is_available'])
                                        <x-ui.badge color="success" soft :value="__('wishlists.available')" />
                                    @else
                                        <x-ui.badge color="error" soft :value="__('wishlists.unavailable')" />
                                    @endif

                                    @if ($item['has_stock_warning'])
                                        <x-ui.badge color="warning" soft :value="__('wishlists.stock_warning')" />
                                    @endif
                                </div>

                                @error('cart.' . $item['id'])
                                    <div class="text-sm text-red-600">{{ $message }}</div>
                                @enderror

                                @error('moveTargetWishlistIds.' . $item['product_id'])
                                    <div class="text-sm text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if ($canManage)
                            <div class="flex w-full flex-col gap-3 lg:w-72">
                                <x-ui.button
                                    type="button"
                                    positive
                                    icon="shopping-cart"
                                    spinner="addItemToCart({{ $item['id'] }})"
                                    wire:click="addItemToCart({{ $item['id'] }})"
                                    wire:loading.attr="disabled"
                                    :label="__('wishlists.actions.add_to_cart')"
                                    @disabled(! $item['is_available'])
                                />

                                @if ($otherWishlists->isNotEmpty() && $item['product_id'])
                                    <div class="flex gap-2">
                                        <select
                                            wire:model="moveTargetWishlistIds.{{ $item['product_id'] }}"
                                            class="min-w-0 flex-1 rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                            aria-label="{{ __('wishlists.actions.move_product') }}"
                                        >
                                            <option value="">{{ __('wishlists.move_to_placeholder') }}</option>
                                            @forelse ($otherWishlists as $targetWishlist)
                                                <option value="{{ $targetWishlist->id }}">{{ $targetWishlist->name }}</option>
                                            @empty
                                                <option value="">{{ __('wishlists.no_other_wishlists') }}</option>
                                            @endforelse
                                        </select>

                                        <x-ui.button
                                            type="button"
                                            secondary
                                            icon="arrow-right"
                                            wire:click="moveProduct({{ $item['product_id'] }})"
                                            wire:loading.attr="disabled"
                                            :label="__('wishlists.actions.move_product')"
                                        />
                                    </div>
                                @endif

                                @if ($item['product_id'])
                                    <x-ui.button
                                        type="button"
                                        negative
                                        outline
                                        icon="trash"
                                        wire:click="removeProduct({{ $item['product_id'] }})"
                                        wire:loading.attr="disabled"
                                        :label="__('wishlists.actions.remove_product')"
                                    />
                                @endif
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            @empty
                <x-ui.card body-class="py-12 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <x-ui.icon name="heart" class="h-6 w-6" />
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('wishlists.items_empty') }}</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ __('wishlists.items_empty_help') }}</p>
                    <div class="mt-6">
                        <x-ui.button
                            href="{{ route('buyer.products.index') }}"
                            primary
                            icon="shopping-bag"
                            :label="__('cart_continue_shopping')"
                        />
                    </div>
                </x-ui.card>
            @endforelse
        </div>
    </div>
</div>

<div>
    <div class="space-y-6">
        <x-buyer.breadcrumbs
            :items="[
                ['label' => __('common_cart')],
            ]"
        />

        <x-ui.header :title="__('cart_shopping_cart')" :subtitle="__('common_cart')">
            <x-slot:actions>
                <x-ui.button
                    href="{{ route('buyer.products.index') }}"
                    secondary
                    :label="__('cart_continue_shopping')"
                />
            </x-slot:actions>
        </x-ui.header>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <x-ui.card class="shadow-sm sm:rounded-lg">
            @if ($hasCartItems)
                <div class="space-y-6">
                    @if ($isGuestCart)
                        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                            {{ __('cart_guest_checkout_notice') }}
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-2 text-sm font-medium text-gray-600">
                        <span class="{{ $checkoutStep === 'review' ? 'text-blue-700' : 'text-gray-500' }}">
                            {{ __('cart_review_step') }}
                        </span>
                        <span>/</span>
                        <span class="{{ $checkoutStep === 'confirmation' ? 'text-blue-700' : 'text-gray-500' }}">
                            {{ __('cart_confirmation_step') }}
                        </span>
                    </div>

                    @foreach ($cartItemRows as $row)
                        @php($item = $row['item'])
                        <div wire:key="cart-item-{{ $item->id }}" class="flex flex-col gap-5 rounded-lg border p-4 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                            <div class="flex gap-4 sm:gap-6">
                                <div class="h-24 w-24 flex-shrink-0 sm:h-32 sm:w-32">
                                    @if ($row['image_url'])
                                        <a href="{{ $row['product_url'] ?? '#' }}">
                                            <img
                                                src="{{ $row['image_url'] }}"
                                                alt="{{ $row['title'] }}"
                                                class="h-full w-full rounded-lg object-cover"
                                                loading="lazy"
                                            >
                                        </a>
                                    @else
                                        <div class="flex h-full w-full items-center justify-center rounded-lg bg-gray-200">
                                            <span class="text-xs text-gray-500">{{ __('common_no_image') }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-2">
                                    <h3 class="text-lg font-bold sm:text-xl">
                                        @if ($row['product_url'])
                                            <a href="{{ $row['product_url'] }}" class="hover:text-blue-600">
                                                {{ $row['title'] }}
                                            </a>
                                        @else
                                            {{ $row['title'] }}
                                        @endif
                                    </h3>

                                    <div class="space-y-1 text-sm text-gray-600">
                                        <p>
                                            <span class="font-medium">{{ __('cart_seller') }}:</span>
                                            {{ $row['seller_name'] }}
                                        </p>
                                        <p>
                                            <span class="font-medium">{{ __('product_price') }}:</span>
                                            {{ number_format($row['current_price'], 2) }} €
                                        </p>
                                        <p>
                                            <span class="font-medium">{{ __('cart_item_subtotal') }}:</span>
                                            {{ number_format($row['subtotal'], 2) }} €
                                        </p>
                                    </div>

                                    @if ($row['is_unavailable'])
                                        <div class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">
                                            {{ __('cart_unavailable') }}
                                        </div>
                                    @endif

                                    @if ($row['has_price_changed'])
                                        <div class="rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                            {{ __('cart_price_changed', [
                                                'old' => number_format($row['stored_price'], 2),
                                                'new' => number_format($row['current_price'], 2),
                                            ]) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 sm:items-end">
                                <div class="flex items-center gap-2">
                                    <x-ui.button
                                        type="button"
                                        sm
                                        secondary
                                        wire:click="decreaseQuantity({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        :label="'-'"
                                    />

                                    <form class="flex items-start gap-2" wire:submit.prevent="updateQuantity({{ $item->id }})">
                                        <div>
                                            <input
                                                type="number"
                                                min="1"
                                                wire:model="quantities.{{ $item->id }}"
                                                class="w-20 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 {{ $errors->has('quantities.' . $item->id) ? 'border-red-500' : '' }}"
                                            >
                                            @error('quantities.' . $item->id)
                                                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <x-ui.button
                                            type="submit"
                                            sm
                                            primary
                                            wire:loading.attr="disabled"
                                            :label="__('cart_update')"
                                        />
                                    </form>

                                    <x-ui.button
                                        type="button"
                                        sm
                                        secondary
                                        wire:click="increaseQuantity({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        :label="'+'"
                                    />
                                </div>

                                <x-ui.button
                                    type="button"
                                    sm
                                    negative
                                    wire:click="removeItem({{ $item->id }})"
                                    wire:loading.attr="disabled"
                                    :label="__('cart_remove')"
                                />
                            </div>
                        </div>
                    @endforeach

                    @foreach ($cartBundleItemRows as $row)
                        @php($item = $row['item'])
                        <div wire:key="cart-bundle-item-{{ $item->id }}" class="flex flex-col gap-5 rounded-lg border border-blue-100 bg-blue-50/40 p-4 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                            <div class="flex gap-4 sm:gap-6">
                                <div class="h-24 w-24 flex-shrink-0 sm:h-32 sm:w-32">
                                    @if ($row['image_url'])
                                        <a href="{{ $row['bundle_url'] ?? '#' }}">
                                            <img
                                                src="{{ $row['image_url'] }}"
                                                alt="{{ $row['title'] }}"
                                                class="h-full w-full rounded-lg object-cover"
                                                loading="lazy"
                                            >
                                        </a>
                                    @else
                                        <div class="flex h-full w-full items-center justify-center rounded-lg bg-gray-200">
                                            <span class="text-xs text-gray-500">{{ __('common_no_image') }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-3">
                                    <div class="space-y-1">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                                            {{ __('cart.bundle') }}
                                        </div>
                                        <h3 class="text-lg font-bold sm:text-xl">
                                            @if ($row['bundle_url'])
                                                <a href="{{ $row['bundle_url'] }}" class="hover:text-blue-600">
                                                    {{ $row['title'] }}
                                                </a>
                                            @else
                                                {{ $row['title'] }}
                                            @endif
                                        </h3>
                                    </div>

                                    <div class="space-y-1 text-sm text-gray-600">
                                        <p>
                                            <span class="font-medium">{{ __('cart_seller') }}:</span>
                                            {{ $row['seller_name'] }}
                                        </p>
                                        <p>
                                            <span class="font-medium">{{ __('bundles.base_price') }}:</span>
                                            {{ number_format($row['base_price'], 2) }} €
                                        </p>
                                        @if ($row['discount_amount'] > 0)
                                            <p class="text-green-700">
                                                <span class="font-medium">{{ __('bundles.discount') }}:</span>
                                                -{{ number_format($row['discount_amount'], 2) }} €
                                            </p>
                                        @endif
                                        <p>
                                            <span class="font-medium">{{ __('bundles.final_price') }}:</span>
                                            {{ number_format($row['current_price'], 2) }} €
                                        </p>
                                        <p>
                                            <span class="font-medium">{{ __('cart_item_subtotal') }}:</span>
                                            {{ number_format($row['subtotal'], 2) }} €
                                        </p>
                                    </div>

                                    @if ($row['included_products']->isNotEmpty())
                                        <div class="space-y-1 text-sm text-gray-700">
                                            <div class="font-medium">{{ __('bundles.included_products') }}</div>
                                            <ul class="space-y-1">
                                                @foreach ($row['included_products'] as $includedProduct)
                                                    <li>
                                                        {{ $includedProduct['quantity'] }} x {{ $includedProduct['title'] }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if ($row['is_unavailable'])
                                        <div class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">
                                            {{ __('bundles.messages.not_available') }}
                                        </div>
                                    @endif

                                    @if ($row['has_price_changed'])
                                        <div class="rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                            {{ __('cart_price_changed', [
                                                'old' => number_format($row['stored_price'], 2),
                                                'new' => number_format($row['current_price'], 2),
                                            ]) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 sm:items-end">
                                <div class="flex items-center gap-2">
                                    <x-ui.button
                                        type="button"
                                        sm
                                        secondary
                                        wire:click="decreaseBundleQuantity({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        :label="'-'"
                                    />

                                    <form class="flex items-start gap-2" wire:submit.prevent="updateBundleQuantity({{ $item->id }})">
                                        <div>
                                            <input
                                                type="number"
                                                min="1"
                                                wire:model="bundleQuantities.{{ $item->id }}"
                                                class="w-20 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 {{ $errors->has('bundleQuantities.' . $item->id) ? 'border-red-500' : '' }}"
                                            >
                                            @error('bundleQuantities.' . $item->id)
                                                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <x-ui.button
                                            type="submit"
                                            sm
                                            primary
                                            wire:loading.attr="disabled"
                                            :label="__('cart_update')"
                                        />
                                    </form>

                                    <x-ui.button
                                        type="button"
                                        sm
                                        secondary
                                        wire:click="increaseBundleQuantity({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        :label="'+'"
                                    />
                                </div>

                                <x-ui.button
                                    type="button"
                                    sm
                                    negative
                                    wire:click="removeBundleItem({{ $item->id }})"
                                    wire:loading.attr="disabled"
                                    :label="__('cart_remove')"
                                />
                            </div>
                        </div>
                    @endforeach

                    <div class="border-t pt-6">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div class="w-full max-w-lg space-y-3">
                                <form wire:submit.prevent="applyPromoCode" class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700" for="promo_code">
                                        {{ __('checkout.promo_code') }}
                                    </label>
                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <input
                                            id="promo_code"
                                            type="text"
                                            wire:model="promoCodeInput"
                                            placeholder="{{ __('checkout.promo_code_placeholder') }}"
                                            class="w-full rounded-lg border-gray-300 uppercase focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        >
                                        <x-ui.button
                                            type="submit"
                                            primary
                                            wire:loading.attr="disabled"
                                            :label="__('promo_codes.apply')"
                                        />
                                        @if ($appliedPromoCode)
                                            <x-ui.button
                                                type="button"
                                                secondary
                                                wire:click="removePromoCode"
                                                wire:loading.attr="disabled"
                                                :label="__('promo_codes.remove')"
                                            />
                                        @endif
                                    </div>

                                    @error('promo_code')
                                        <div class="text-sm text-red-600">{{ $message }}</div>
                                    @enderror

                                    @if ($promoCodeError)
                                        <div class="text-sm text-red-600">{{ $promoCodeError }}</div>
                                    @endif

                                    @if ($cartTotals['promo_code'])
                                        <div class="text-sm text-green-700">
                                            {{ __('promo_codes.applied_code', ['code' => $cartTotals['promo_code']]) }}
                                        </div>
                                    @endif
                                </form>
                            </div>

                            <div class="space-y-2 text-lg font-bold lg:text-right">
                                <div>{{ __('checkout.total_before_discount') }}: {{ $cartTotals['total_before_discount'] }} €</div>
                                @if ((float) $cartTotals['automatic_discount_total'] > 0)
                                    <div class="text-sm font-medium text-green-700">
                                        {{ __('checkout.automatic_discount') }}: -{{ $cartTotals['automatic_discount_total'] }} €
                                    </div>
                                @endif
                                @if ((float) $cartTotals['promo_discount_amount'] > 0)
                                    <div class="text-sm font-medium text-green-700">
                                        {{ __('checkout.discount') }}: -{{ $cartTotals['promo_discount_amount'] }} €
                                    </div>
                                @endif
                                @if ((float) $cartTotals['discount_total'] > 0)
                                    <div>{{ __('checkout.total_after_discount') }}: {{ $cartTotals['total_after_discount'] }} €</div>
                                @else
                                    <div>{{ __('cart_total') }}: {{ $cartTotals['total'] }} €</div>
                                @endif
                                <div class="text-sm font-medium text-gray-600">
                                    {{ __('cart_total_without_vat') }}: {{ $cartTotals['subtotal'] }} €
                                </div>
                                <div class="text-sm font-medium text-gray-600">
                                    {{ __('common_items') }}: {{ $cartTotals['item_count'] }}
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <x-ui.button
                                    type="button"
                                    negative
                                    wire:click="clearCart"
                                    wire:loading.attr="disabled"
                                    :label="__('cart_clear')"
                                />
                                <x-ui.button
                                    href="{{ route('buyer.products.index') }}"
                                    secondary
                                    :label="__('cart_continue_shopping')"
                                />
                                @if ($checkoutStep === 'review')
                                    <x-ui.button
                                        type="button"
                                        positive
                                        wire:click="beginCheckout"
                                        wire:loading.attr="disabled"
                                        :label="__('cart_checkout')"
                                    />
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($checkoutStep === 'confirmation')
                        <div class="rounded-lg border p-4 sm:p-6">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700" for="shipping_address">
                                        {{ __('cart_shipping_address') }}
                                    </label>
                                    <textarea
                                        id="shipping_address"
                                        wire:model="shippingAddress"
                                        rows="3"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    ></textarea>
                                    @error('shipping_address')
                                        <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700" for="billing_address">
                                        {{ __('cart_billing_address') }}
                                    </label>
                                    <textarea
                                        id="billing_address"
                                        wire:model="billingAddress"
                                        rows="3"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700" for="payment_method">
                                        {{ __('cart_payment_method') }}
                                    </label>
                                    <select
                                        id="payment_method"
                                        wire:model="paymentMethod"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    >
                                        <option value="bank_transfer">{{ __('cart_payment_bank_transfer') }}</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700" for="delivery_method">
                                        {{ __('cart_delivery_method') }}
                                    </label>
                                    <input
                                        id="delivery_method"
                                        type="text"
                                        wire:model="deliveryMethod"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    >
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <x-ui.button
                                    type="button"
                                    positive
                                    wire:click="checkout"
                                    wire:loading.attr="disabled"
                                    :label="__('cart_confirm_order')"
                                />
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="py-12 text-center">
                    <p class="mb-6 text-lg text-gray-600">{{ __('cart_empty_cart') }}</p>
                    <x-ui.button href="{{ route('buyer.products.index') }}" primary :label="__('cart_continue_shopping')" />
                </div>
            @endif
        </x-ui.card>
    </div>
</div>

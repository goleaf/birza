<div>
    <!-- start main container -->
    <x-ui.card class="shadow-sm sm:rounded-lg" :title="__('cart_shopping_cart')">

        <!-- start cart items -->
        @if (LaraCart::count() > 0)
            <div class="space-y-6">
                @foreach (LaraCart::getItems() as $item)
                    <!-- start cart item -->
                    <div class="flex items-start justify-between border rounded-lg p-6">
                        <!-- start item left side -->
                        <div class="flex space-x-6">
                            <!-- start image container -->
                            <div class="w-32 h-32">
                                @if (isset($item->options['image']) && $item->options['image'])
                                    <a href="{{ route('buyer.products.show', $item->id) }}">
                                        <img 
                                            src="{{ Storage::url('products/' . $item->options['image']) }}"
                                            alt="{{ $item->name }}" 
                                            class="w-32 h-32 object-cover rounded-lg"
                                        >
                                    </a>
                                @else
                                    <div class="w-32 h-32 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <span class="text-gray-500 text-xs">
                                            {{ __('common_no_image') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <!-- end image container -->

                            <!-- start item details -->
                            <div class="space-y-2">
                                <h3 
                                    class="text-xl font-bold"
                                >
                                    <a href="{{ route('buyer.products.show', $item->id) }}" class="hover:text-blue-600">
                                        {{ $item->name ?? __('common_unnamed_product') }}
                                    </a>
                                </h3>

                                <!-- start product details grid -->
                                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                                    @if(isset($item->price) && isset($item->options['unit']))
                                        <p>
                                            <span class="font-medium">{{ __('product_price') }}:</span> {{ $item->price }} € / {{ $item->options['unit'] }}
                                        </p>
                                    @endif

                                    @if(isset($item->options['stock']))
                                        <p>
                                            <span class="font-medium">{{ __('product_stock') }}:</span> {{ $item->options['stock'] }}
                                        </p>
                                    @endif

                                    @if(isset($item->options['min_order_count']))
                                        <p>
                                            <span class="font-medium">{{ __('product_min_order_count') }}:</span> {{ $item->options['min_order_count'] }}
                                        </p>
                                    @endif

                                    @if(isset($item->options['package_weight']))
                                        <p>
                                            <span class="font-medium">{{ __('product_package_weight') }}:</span> {{ $item->options['package_weight'] }}
                                        </p>
                                    @endif

                                    @if(isset($item->options['is_organic']) && $item->options['is_organic'])
                                        <p class="text-green-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path 
                                                    fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" 
                                                />
                                            </svg>
                                            {{ __('product_organic') }}
                                        </p>
                                    @endif

                                    @if(isset($item->options['country_of_origin']) && isset($countries[$item->options['country_of_origin']]))
                                        <p>
                                            <span class="font-medium">{{ __('product_origin') }}:</span> {{ $countries[$item->options['country_of_origin']] }}
                                        </p>
                                    @endif

                                    @if(isset($item->options['price_per_liter']))
                                        <p>
                                            <span class="font-medium">{{ __('product_price_per_liter') }}:</span> {{ $item->options['price_per_liter'] }} €/L
                                        </p>
                                    @endif
                                </div>
                                <!-- end product details grid -->
                            </div>
                            <!-- end item details -->
                        </div>
                        <!-- end item left side -->

                        <!-- start item right side -->
                        <div class="flex flex-col items-end space-y-4">
                            @if(isset($item->qty) && isset($item->price))
                                <div class="text-lg font-bold">
                                    {{ $item->qty * $item->price }} €
                                </div>
                            @endif

                            <!-- start buttons group -->
                            <div class="flex items-center space-x-2">
                                <!-- start update quantity form -->
                                <form class="flex items-center space-x-2" wire:submit.prevent="updateQuantity('{{ $item->getHash() }}')">
                                    <div class="flex flex-col">
                                        <input 
                                            type="number" 
                                            wire:model.defer="quantities.{{ $item->getHash() }}"
                                            class="w-20 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 {{ $errors->has('quantities.' . $item->getHash()) ? 'border-red-500' : '' }}"
                                        >
                                        @error('quantities.' . $item->getHash())
                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <x-ui.button type="submit" sm primary :label="__('cart_update')" />
                                </form>
                                <!-- end update quantity form -->

                                <!-- start remove item form -->
                                <x-ui.button type="button" sm negative wire:click="removeItem('{{ $item->getHash() }}')" :label="__('cart_remove')" />
                                <!-- end remove item form -->
                            </div>
                            <!-- end buttons group -->
                        </div>
                        <!-- end item right side -->
                    </div>
                    <!-- end cart item -->
                @endforeach

                <!-- start cart footer -->
                <div class="mt-8 flex justify-between items-center border-t b-6 pt-6">
                    <!-- start total amounts -->
                    <div class="text-2xl font-bold mt-4">
                        <div class="flex flex-col">
                            <span>
                                {{ __('cart_total') }}: {{ $cartTotals['totalWithVatAndPortal'] }} €
                            </span>
                            <span class="text-sm text-gray-600">
                                {{ __('cart_total_without_vat') }}: {{ $cartTotals['cartTotal'] }} €
                            </span>
                            <span class="text-sm text-gray-600">
                                {{ __('cart_vat_amount') }}: {{ $cartTotals['vatAmount'] }} €
                            </span>
                            <span class="text-sm text-gray-600">
                                {{ __('cart_portal_additional_price') }}: {{ $cartTotals['portalPrice'] }} €
                            </span>
                        </div>
                    </div>
                    <!-- end total amounts -->

                    <!-- start action buttons -->
                    <div class="flex space-x-4 mt-6">
                        <x-ui.button
                            href="{{ route('buyer.products.index') }}"
                            secondary
                            :label="__('cart_continue_shopping')"
                        />
                        <x-ui.button type="button" positive wire:click="checkout" :label="__('cart_checkout')" />
                    </div>
                    <!-- end action buttons -->
                </div>
                <!-- end cart footer -->
            </div>
        @else
            <!-- start empty cart message -->
            <div class="text-center py-12">
                <p class="text-gray-600 text-lg mb-6">
                    {{ __('cart_empty_cart') }}
                </p>
                <x-ui.button href="{{ route('buyer.products.index') }}" primary :label="__('cart_continue_shopping')" />
            </div>
            <!-- end empty cart message -->
        @endif
        <!-- end cart items -->
    </x-ui.card>
    <!-- end main container -->
</div>
<!-- end section -->

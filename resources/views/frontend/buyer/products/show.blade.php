<div>
    <!-- start main container -->
    <x-ui.card class="shadow-sm sm:rounded-lg" body-class="space-y-6">
        <x-buyer.breadcrumbs
            :items="array_filter([
                ['label' => __('common_products'), 'link' => route('buyer.products.index')],
                $product->category?->parent
                    ? [
                        'label' => $product->category->parent->getTranslation('category_name', app()->getLocale()),
                        'link' => route('buyer.products.index', ['category' => $product->category->parent->id]),
                    ]
                    : null,
                $product->category
                    ? [
                        'label' => $product->category->getTranslation('category_name', app()->getLocale()),
                        'link' => route('buyer.products.index', ['category' => $product->category->id]),
                    ]
                    : null,
                ['label' => $product->name],
            ])"
        />

        <x-ui.header
            :title="$product->name"
            :subtitle="__('product_seller') . ': ' . $product->seller->company_name"
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('buyer.products.index')"
                    secondary
                    :label="__('common_back_to_products')"
                />
                <x-ui.button
                    :href="route('buyer.compare.index')"
                    secondary
                    outline
                    icon="scale"
                    :label="__('compare.actions.view_list', ['count' => $comparisonCount, 'limit' => $comparisonLimit])"
                />
            </x-slot:actions>
        </x-ui.header>

        <!-- start product grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- start images section -->
            <div class="space-y-4">
                @if (count($productSlides) > 1)
                    <x-mary-carousel
                        :slides="$productSlides"
                        class="!h-80 sm:!h-96"
                    />
                @elseif (! empty($productSlides))
                    <img
                        src="{{ $productSlides[0]['image'] }}"
                        alt="{{ $productSlides[0]['alt'] }}"
                        class="w-full h-80 rounded-lg object-cover shadow-sm sm:h-96"
                    >
                @else
                    <div class="flex h-80 items-center justify-center rounded-lg bg-gray-100 text-gray-400 sm:h-96">
                        {{ __('common_no_image') }}
                    </div>
                @endif

                @if (! empty($productGalleryImages))
                    <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                        <div class="mb-3 text-sm font-semibold text-gray-700">
                            {{ __('common_product_images') }}
                        </div>

                        <x-ui.image-gallery
                            :images="$productGalleryImages"
                            class="gap-3 [&_.carousel-item]:w-24 [&_img]:h-24 [&_img]:w-24 [&_img]:rounded-md [&_img]:object-cover"
                        />
                    </div>
                @endif
            </div>
            <!-- end images section -->

            <!-- start product details -->
            <div>
                <!-- start temperature conditions -->
                @if($product->temperature_conditions_from || $product->temperature_conditions_to)
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product_temperature_conditions') }}:
                    </strong>
                    {{ __('product_from') }} {{ $product->temperature_conditions_from }}°C {{ __('product_to') }} {{ $product->temperature_conditions_to }}°C
                </p>
                @endif
                <!-- end temperature conditions -->

                <!-- start use until -->
                @if($product->use_until)
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product_use_until') }}:
                    </strong>
                    {{ $product->use_until->format('Y-m-d') }}
                </p>
                @endif
                <!-- end use until -->

                <!-- start total shelf life -->
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product_total_shelf_life') }}:
                    </strong>
                    {{ $product->total_shelf_life }}
                </p>
                <!-- end total shelf life -->

                <!-- start country -->
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product_country') }}:
                    </strong>
                    {{ $product->country->getTranslation('country_name', app()->getLocale()) }}
                </p>
                <!-- end country -->

                <!-- start organic -->
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product_is_organic') }}:
                    </strong>
                    {{ $product->is_organic ? __('common_yes') : __('common_no') }}
                </p>
                <!-- end organic -->

                <!-- start seller -->
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product_seller') }}:
                    </strong>
                    <x-ui.popover position="bottom-start" class="inline-block align-middle">
                        <x-slot:trigger>
                            <button type="button" class="inline-flex items-center gap-1 font-medium text-blue-600 transition hover:text-blue-800">
                                <span>{{ $product->seller->company_name }}</span>
                                <x-ui.icon name="information-circle" class="h-4 w-4" />
                            </button>
                        </x-slot:trigger>

                        <x-slot:content>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                        <x-ui.icon name="building-office-2" class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $product->seller->company_name ?: $product->seller->name }}</div>
                                        <div class="text-xs text-gray-500">{{ __('product_seller') }}</div>
                                    </div>
                                </div>

                                <dl class="space-y-2 text-gray-600">
                                    <div class="flex items-start justify-between gap-3">
                                        <dt class="font-medium text-gray-500">{{ __('auth_name') }}</dt>
                                        <dd class="text-right">{{ $product->seller->name }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </x-slot:content>
                    </x-ui.popover>
                </p>
                <!-- end seller -->

                <!-- start stock -->
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product_stock') }}:
                    </strong>
                    {{ $product->stock }} {{ __('units_unit_' . strtolower($product->unit)) }}
                </p>
                <!-- end stock -->

                <!-- start min order -->
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product_min_order') }}:
                    </strong>
                    {{ $product->min_order_price }}€ / {{ $product->min_order_count }} {{ __('units_unit_' . strtolower($product->unit)) }}
                </p>
                <!-- end min order -->

                <!-- start package weight -->
                @if ($product->package_weight)
                    <p class="text-gray-600 mb-4">
                        <strong class="font-bold">
                            {{ __('product_package_weight') }}:
                        </strong>
                        {{ $product->formatted_package_weight }}
                    </p>
                @endif
                <!-- end package weight -->

                <!-- start price per liter -->
                @if ($product->price_per_liter)
                    <p class="text-gray-600 mb-4">
                        <strong class="font-bold">
                            {{ __('product_price_per_liter') }}:
                        </strong>
                        {{ $product->formatted_price_per_liter }}
                    </p>
                @endif
                <!-- end price per liter -->

                <!-- start pack type -->
                @if ($product->pack_type)
                    <p class="text-gray-600 mb-4">
                        <strong class="font-bold">
                            {{ __('product_pack_type') }}:
                        </strong>
                        {{ $product->pack_type }}
                    </p>
                @endif
                <!-- end pack type -->

                <!-- start attributes -->
                @if ($product->category?->attributes->isNotEmpty())
                    <div class="text-gray-600 mb-4">
                        <strong class="font-bold">
                            {{ __('product_attributes') }}:
                        </strong>
                        <div class="mt-2 space-y-2">
                            @foreach ($product->category->attributes->where('is_active', true) as $attribute)
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1">
                                        {{ $attribute->getTranslation('name', app()->getLocale()) }}:
                                    </span>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($attributeValuesByAttribute->get($attribute->id, []) as $value)
                                            <span class="text-sm text-gray-700 bg-gray-100 px-2 py-1 rounded">
                                                {{ $value->value }}
                                            </span>
                                        @empty
                                            <span class="text-sm text-gray-400 italic">
                                                {{ __('common_not_specified') }}
                                            </span>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <!-- end attributes -->

                <!-- start description -->
                <p class="text-gray-600 mb-4 font-bold">
                    {{ __('product_description') }}
                </p>
                <div class="markdown-content mb-4 text-gray-700">
                    {!! \Illuminate\Support\Str::markdown(
                        (string) $product->getTranslation('description', app()->getLocale()),
                        [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ],
                    ) !!}
                </div>
                <!-- end description -->

                <!-- start price -->
                <p class="text-2xl font-bold text-green-600 mb-10">
                    <strong class="font-bold">
                        {{ __('product_price_without_vat') }}:
                    </strong>
                    {{ number_format($product->price, 2) }} € / {{ __('units_unit_' . strtolower($product->unit)) }}
                </p>
                <!-- end price -->

                <!-- start compare actions -->
                <div class="mb-6 flex flex-col gap-3 sm:flex-row">
                    <x-ui.button
                        type="button"
                        secondary
                        outline
                        icon="scale"
                        spinner="addToCompare"
                        wire:click="addToCompare"
                        wire:loading.attr="disabled"
                        :disabled="$isCompared"
                    >
                        {{ $isCompared ? __('compare.actions.in_list') : __('compare.actions.add') }}
                    </x-ui.button>
                    <x-ui.button
                        :href="route('buyer.compare.index')"
                        flat
                        icon="arrows-right-left"
                        :label="__('compare.actions.open')"
                    />
	                </div>
	                <!-- end compare actions -->

	                <!-- start wishlist actions -->
	                <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
	                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
	                        <div class="flex-1">
	                            <label for="wishlist_id" class="mb-1 block text-sm font-medium text-gray-700">
	                                {{ __('wishlists.actions.add_product') }}
	                            </label>
	                            <select
	                                id="wishlist_id"
	                                wire:model="wishlistId"
	                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
	                            >
	                                @forelse ($buyerWishlists as $wishlistOption)
	                                    <option value="{{ $wishlistOption->id }}">
	                                        {{ $wishlistOption->name }}
	                                    </option>
	                                @empty
	                                    <option value="">{{ __('wishlists.default_name') }}</option>
	                                @endforelse
	                            </select>
	                        </div>

	                        @if ($wishlistBuyer)
	                            <x-ui.button
	                                type="button"
	                                primary
	                                icon="heart"
	                                spinner="addToWishlist"
	                                wire:click="addToWishlist"
	                                wire:loading.attr="disabled"
	                                :disabled="$isWishlisted"
	                            >
	                                {{ $isWishlisted ? __('wishlists.actions.saved') : __('wishlists.actions.add_product') }}
	                            </x-ui.button>
	                        @else
	                            <x-ui.button
	                                href="{{ route('buyer.login') }}"
	                                primary
	                                icon="arrow-right-on-rectangle"
	                                :label="__('wishlists.actions.login_to_save')"
	                            />
	                        @endif
	                    </div>
                </div>
                <!-- end wishlist actions -->

                <!-- start seller contact action -->
                <div class="mb-6 rounded-lg border border-blue-100 bg-blue-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-semibold text-blue-950">{{ __('messages.contact_seller') }}</h2>
                            <p class="mt-1 text-sm text-blue-800">{{ __('messages.contact_seller_help') }}</p>
                        </div>

                        @if ($isBuyerAuthenticated)
                            <x-ui.button
                                type="button"
                                primary
                                icon="chat-bubble-left-right"
                                spinner="contactSeller"
                                wire:click="contactSeller"
                                wire:loading.attr="disabled"
                                :label="__('messages.ask_seller')"
                            />
                        @else
                            <x-ui.button
                                :href="route('buyer.login')"
                                primary
                                icon="arrow-right-on-rectangle"
                                :label="__('messages.login_to_contact_seller')"
                            />
                        @endif
                    </div>
                </div>
                <!-- end seller contact action -->

                <!-- start report action -->
                <div class="mb-6 rounded-lg border border-red-100 bg-red-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-semibold text-red-950">{{ __('reports.product.title') }}</h2>
                            <p class="mt-1 text-sm text-red-800">{{ __('reports.product.help') }}</p>
                        </div>

                        @if ($isBuyerAuthenticated || $guestReportsEnabled)
                            <x-ui.button
                                type="button"
                                warning
                                outline
                                icon="flag"
                                wire:click="openReportModal"
                                :label="__('reports.product.button')"
                            />
                        @else
                            <x-ui.button
                                :href="route('buyer.login')"
                                warning
                                outline
                                icon="arrow-right-on-rectangle"
                                :label="__('reports.product.login_to_report')"
                            />
                        @endif
                    </div>
                </div>
                <!-- end report action -->

	                <!-- start add to cart form -->
                <form wire:submit.prevent="addToCart" class="mt-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <input 
                                type="number" 
                                id="quantity" 
                                wire:model="quantity"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                {{ $product->stock <= 0 ? 'disabled' : '' }}
                            >
                        </div>
                        <x-ui.button
                            type="submit"
                            positive
                            spinner="addToCart"
                            wire:loading.attr="disabled"
                            :disabled="$product->stock <= 0"
                        >
                            {{ $product->stock <= 0 ? __('common_out_of_stock') : __('common_add_to_cart') }}
                        </x-ui.button>
                    </div>
                </form>
                <!-- end add to cart form -->

                @if ($product->stock <= 0)
                    <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="font-semibold text-amber-950">{{ __('stock_alerts.title') }}</h2>
                                <p class="mt-1 text-sm text-amber-800">
                                    {{ $activeStockAlert ? __('stock_alerts.already_subscribed') : __('stock_alerts.product_unavailable_help') }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if (! $stockAlertBuyer)
                                    <x-ui.button
                                        :href="route('buyer.login')"
                                        positive
                                        icon="arrow-right-on-rectangle"
                                        :label="__('stock_alerts.login_to_subscribe')"
                                    />
                                @elseif ($activeStockAlert)
                                    <x-ui.button
                                        type="button"
                                        negative
                                        outline
                                        spinner="cancelStockAlert"
                                        wire:click="cancelStockAlert({{ $activeStockAlert->id }})"
                                        wire:loading.attr="disabled"
                                        :label="__('stock_alerts.cancel')"
                                    />
                                @else
                                    <x-ui.button
                                        type="button"
                                        positive
                                        icon="bell"
                                        spinner="subscribeToStockAlert"
                                        wire:click="subscribeToStockAlert"
                                        wire:loading.attr="disabled"
                                        :label="__('stock_alerts.notify_me')"
                                    />
                                @endif

                                @if ($stockAlertBuyer)
                                    <x-ui.button
                                        :href="route('buyer.stock-alerts.index')"
                                        flat
                                        icon="list-bullet"
                                        :label="__('stock_alerts.view_alerts')"
                                    />
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

            </div>
            <!-- end product details -->
        </div>
        <!-- end product grid -->

        @if ($relatedBundles->isNotEmpty())
            <div class="rounded-lg border border-blue-100 bg-blue-50/40 p-4 sm:p-6">
                <h2 class="mb-4 text-xl font-bold text-gray-900">{{ __('bundles.related_title') }}</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($relatedBundles as $bundle)
                        <a
                            href="{{ route('buyer.bundles.show', $bundle) }}"
                            class="rounded-lg border bg-white p-4 shadow-sm transition hover:border-blue-200 hover:shadow-md"
                        >
                            <img
                                src="{{ $bundle->imageUrl() }}"
                                alt="{{ $bundle->name }}"
                                class="mb-3 h-32 w-full rounded-lg object-cover"
                                loading="lazy"
                            >
                            <div class="font-semibold text-gray-900">{{ $bundle->name }}</div>
                            <div class="mt-1 text-sm text-gray-600">
                                {{ __('bundles.included_products_count', ['count' => $bundle->items->count()]) }}
                            </div>
                            <div class="mt-2 text-lg font-bold text-green-700">
                                {{ number_format($bundle->finalPrice(), 2) }} €
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <livewire:frontend.product-questions.panel :product="$product" />

        <!-- start back button -->
        <x-slot:footer>
            <x-ui.button
                :href="route('buyer.products.index')"
                primary
                :label="__('common_back_to_products')"
            />
        </x-slot:footer>
        <!-- end back button -->
    </x-ui.card>
    <!-- end main container -->

    <x-mary-modal
        wire:model="reportModal"
        :title="__('reports.product.title')"
        box-class="max-w-lg"
        separator
    >
        <div class="space-y-4">
            @error('product')
                <x-mary-alert
                    :title="$message"
                    icon="o-exclamation-triangle"
                    class="alert-error alert-soft"
                />
            @enderror

            <x-mary-select
                :label="__('reports.product.reason')"
                wire:model="reportReason"
                :options="$reportReasonOptions"
                option-value="id"
                option-label="name"
                icon="o-flag"
                :placeholder="__('reports.product.reason_placeholder')"
                required
            />

            @if (! $isBuyerAuthenticated)
                <x-mary-input
                    :label="__('reports.product.reporter_email')"
                    type="email"
                    wire:model="reporterEmail"
                    icon="o-envelope"
                    :placeholder="__('reports.product.reporter_email_placeholder')"
                    required
                />
            @endif

            <x-mary-textarea
                :label="__('reports.product.message')"
                :hint="__('reports.product.message_hint')"
                wire:model="reportMessage"
                rows="4"
            />
        </div>

        <x-slot:actions>
            <x-mary-button
                :label="__('common_cancel')"
                @click="$wire.reportModal = false"
            />
            <x-mary-button
                :label="__('reports.product.submit')"
                wire:click="submitReport"
                spinner="submitReport"
                class="btn-warning"
            />
        </x-slot:actions>
    </x-mary-modal>
</div>
<!-- end section -->

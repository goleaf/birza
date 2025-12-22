<div>
    <!-- start main container -->
    <div class="bg-white shadow-sm sm:rounded-lg px-6 py-6">
        <!-- start category breadcrumb -->
        <div class="mb-4 text-gray-600">
            <strong>
                {{ __('product.category') }}:
            </strong>
            {{ $product->category->parent->getTranslation('category_name', app()->getLocale()) }} &rarr;
            {{ $product->category->getTranslation('category_name', app()->getLocale()) }}
        </div>
        <!-- end category breadcrumb -->

        <!-- start product grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- start images section -->
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- start main image -->
                    <img 
                        src="{{ Storage::url('products/' . $product->product_image) }}"
                        alt="{{ $product->category->category_name }}" 
                        class="w-full h-64 rounded-lg object-cover"
                    >
                    <!-- end main image -->

                    <!-- start additional image -->
                    @if ($product->product_additional_image)
                        <img 
                            src="{{ Storage::url('products/' . $product->product_additional_image) }}"
                            alt="{{ $product->category->category_name }} additional"
                            class="w-full h-64 rounded-lg object-cover"
                        >
                    @endif
                    <!-- end additional image -->
                </div>
            </div>
            <!-- end images section -->

            <!-- start product details -->
            <div>
                <!-- start product name -->
                <h1 class="text-3xl font-bold mb-6">
                    {{ $product->name }}
                </h1>
                <!-- end product name -->

                <!-- start temperature conditions -->
                @if($product->temperature_conditions_from || $product->temperature_conditions_to)
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product.temperature_conditions') }}:
                    </strong>
                    {{ __('product.from') }} {{ $product->temperature_conditions_from }}°C {{ __('product.to') }} {{ $product->temperature_conditions_to }}°C
                </p>
                @endif
                <!-- end temperature conditions -->

                <!-- start use until -->
                @if($product->use_until)
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product.use_until') }}:
                    </strong>
                    {{ $product->use_until->format('Y-m-d') }}
                </p>
                @endif
                <!-- end use until -->

                <!-- start total shelf life -->
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product.total_shelf_life') }}:
                    </strong>
                    {{ $product->total_shelf_life }}
                </p>
                <!-- end total shelf life -->

                <!-- start country -->
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product.country') }}:
                    </strong>
                    {{ $product->country->getTranslation('country_name', app()->getLocale()) }}
                </p>
                <!-- end country -->

                <!-- start organic -->
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product.is_organic') }}:
                    </strong>
                    {{ $product->is_organic ? __('common.yes') : __('common.no') }}
                </p>
                <!-- end organic -->

                <!-- start seller -->
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product.seller') }}:
                    </strong>
                    {{ $product->seller->company_name }}
                </p>
                <!-- end seller -->

                <!-- start stock -->
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product.stock') }}:
                    </strong>
                    {{ $product->stock }} {{ __("units.$product->unit") }}
                </p>
                <!-- end stock -->

                <!-- start min order -->
                <p class="text-gray-600 mb-4">
                    <strong class="font-bold">
                        {{ __('product.min_order') }}:
                    </strong>
                    {{ $product->min_order_price }}€ / {{ $product->min_order_count }} {{ __("units.$product->unit") }}
                </p>
                <!-- end min order -->

                <!-- start package weight -->
                @if ($product->package_weight)
                    <p class="text-gray-600 mb-4">
                        <strong class="font-bold">
                            {{ __('product.package_weight') }}:
                        </strong>
                        {{ $product->formatted_package_weight }}
                    </p>
                @endif
                <!-- end package weight -->

                <!-- start price per liter -->
                @if ($product->price_per_liter)
                    <p class="text-gray-600 mb-4">
                        <strong class="font-bold">
                            {{ __('product.price_per_liter') }}:
                        </strong>
                        {{ $product->formatted_price_per_liter }}
                    </p>
                @endif
                <!-- end price per liter -->

                <!-- start pack type -->
                @if ($product->pack_type)
                    <p class="text-gray-600 mb-4">
                        <strong class="font-bold">
                            {{ __('product.pack_type') }}:
                        </strong>
                        {{ $product->pack_type }}
                    </p>
                @endif
                <!-- end pack type -->

                <!-- start attributes -->
                @if ($product->category && $product->category->attributes->count() > 0)
                    <div class="text-gray-600 mb-4">
                        <strong class="font-bold">
                            {{ __('product.attributes') }}:
                        </strong>
                        <div class="mt-2 space-y-2">
                            @foreach ($product->category->attributes->where('is_active', true) as $attribute)
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1">
                                        {{ $attribute->getTranslation('name', app()->getLocale()) }}:
                                    </span>
                                    <div class="flex flex-wrap gap-2">
                                        @php
                                            $attributeValues = $product->attributeValues->where(
                                                'attribute_id',
                                                $attribute->id,
                                            );
                                        @endphp
                                        @if ($attributeValues->count() > 0)
                                            @foreach ($attributeValues as $value)
                                                <span class="text-sm text-gray-700 bg-gray-100 px-2 py-1 rounded">
                                                    {{ $value->value }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-sm text-gray-400 italic">
                                                {{ __('common.not_specified') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <!-- end attributes -->

                <!-- start description -->
                <p class="text-gray-600 mb-4 font-bold">
                    {{ __('product.description') }}
                </p>
                <p class="text-gray-700 mb-4">
                    {{ $product->getTranslation('description', app()->getLocale()) }}
                </p>
                <!-- end description -->

                <!-- start price -->
                <p class="text-2xl font-bold text-green-600 mb-10">
                    <strong class="font-bold">
                        {{ __('product.price_without_vat') }}:
                    </strong>
                    {{ number_format($product->price, 2) }} € / {{ __("units.$product->unit") }}
                </p>
                <!-- end price -->

                <!-- start add to cart form -->
                <form wire:submit.prevent="addToCart" class="mt-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <input 
                                type="number" 
                                id="quantity" 
                                wire:model.defer="quantity"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                {{ $product->stock <= 0 ? 'disabled' : '' }}
                            >
                        </div>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ $product->stock <= 0 ? 'disabled' : '' }}
                        >
                            {{ $product->stock <= 0 ? __('common.out_of_stock') : __('common.add_to_cart') }}
                        </button>
                    </div>
                </form>
                <!-- end add to cart form -->

            </div>
            <!-- end product details -->
        </div>
        <!-- end product grid -->

        <!-- start back button -->
        <div class="mt-4">
            <a 
                href="{{ route('buyer.products.index') }}"
                class="inline-block px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700"
            >
                {{ __('common.back_to_products') }}
            </a>
        </div>
        <!-- end back button -->
    </div>
    <!-- end main container -->
</div>
<!-- end section -->

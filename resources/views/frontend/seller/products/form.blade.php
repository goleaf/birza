<div>
        <!-- start main container -->
        <div class="container mx-auto px-4 py-8">
            <!-- start form container -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <!-- start title -->
                <h2 class="text-2xl font-bold mb-6">
                    {{ isset($product->id) ? __('product_edit_product') : __('product_create_new_product') }}
                </h2>
                <!-- end title -->

                <!-- start form -->
                <form wire:submit.prevent="save" enctype="multipart/form-data">
                    <input type="hidden" wire:model="category_id">

                    <!-- start name field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_name') }} *
                        </label>
                        <input 
                            type="text"
                            required 
                            wire:model="name"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('name') ? 'border-red-500' : '' }}"
                        >
                        @error('name')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end name field -->

                    
                    <!-- start price field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_price') }} * <span class="text-gray-500 font-normal">({{ __('product_price_without_vat') }})</span>
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            required 
                            wire:model="price"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('price') ? 'border-red-500' : '' }}"
                        >
                        @error('price')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end price field -->

                    <!-- start pack type field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_pack_type') }} *
                        </label>
                        <input 
                            type="text"
                            wire:model="pack_type"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('pack_type') ? 'border-red-500' : '' }}"
                        >
                        @error('pack_type')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end pack type field -->

                    <!-- start unit field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_unit') }} *
                        </label>
                        <select 
                            required 
                            wire:model="unit"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('unit') ? 'border-red-500' : '' }}"
                        >
                            @foreach (collect(\App\Models\Product::UNITS)->sort() as $unit)
                                <option 
                                    value="{{ $unit }}" 
                                >
                                    {{ __('units_unit_' . strtolower($unit)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end unit field -->

                    <!-- start country field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_country_of_origin') }} *
                        </label>
                        <select 
                            required 
                            wire:model="country_of_origin"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('country_of_origin') ? 'border-red-500' : '' }}"
                        >
                            <option value="">
                                {{ __('common_select_country') }}
                            </option>
                            @foreach ($countries as $country)
                                <option 
                                    value="{{ $country->id }}" 
                                >
                                    {{ $country->getTranslation('country_name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        </select>
                        @error('country_of_origin')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end country field -->

                    <!-- start organic field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_is_organic') }} *
                        </label>
                        <select 
                            required 
                            wire:model="is_organic"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('is_organic') ? 'border-red-500' : '' }}"
                        >
                            <option 
                                value="0" 
                            >
                                {{ __('common_no') }}
                            </option>
                            <option 
                                value="1" 
                            >
                                {{ __('common_yes') }}
                            </option>
                        </select>
                        @error('is_organic')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end organic field -->

                    <!-- start active field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_is_active') }} *
                        </label>
                        <select 
                            required 
                            wire:model="is_active"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('is_active') ? 'border-red-500' : '' }}"
                        >
                            <option 
                                value="0" 
                            >
                                {{ __('common_no') }}
                            </option>
                            <option 
                                value="1" 
                            >
                                {{ __('common_yes') }}
                            </option>
                        </select>
                        @error('is_active')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end active field -->

                    <!-- start min order price field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_min_order_price') }}
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            wire:model="min_order_price"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('min_order_price') ? 'border-red-500' : '' }}"
                        >
                        @error('min_order_price')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end min order price field -->

                    <!-- start min order count field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_min_order_count') }} *
                        </label>
                        <input 
                            type="number" 
                            wire:model="min_order_count"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('min_order_count') ? 'border-red-500' : '' }}"
                            required
                        >
                        @error('min_order_count')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end min order count field -->

                    <!-- start stock field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_stock') }} *
                        </label>
                        <input 
                            type="number" 
                            required 
                            wire:model="stock"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('stock') ? 'border-red-500' : '' }}"
                        >
                        @error('stock')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end stock field -->

                    <!-- start description field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_description') }} *
                        </label>
                        @foreach (config('app.locales') as $locale)
                            <div class="mb-4">
                                <label class="block text-gray-600 text-xs mb-1">
                                    {{ strtoupper($locale) }}{{ $locale == app()->getLocale() ? ' *' : '' }}
                                </label>
                                <textarea 
                                    rows="4" 
                                    {{ $locale == app()->getLocale() ? 'required' : '' }} 
                                    wire:model="description.{{ $locale }}"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has("description.$locale") ? 'border-red-500' : '' }}"
                                ></textarea>
                                @error("description.$locale")
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                    <!-- end description field -->





                    <!-- start temperature conditions fields -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_temperature_conditions') }}
                        </label>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-gray-600 text-xs mb-1">
                                    {{ __('product_from') }}
                                </label>
                                <input
                                    type="number"
                                    wire:model="temperature_conditions_from"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('temperature_conditions_from') ? 'border-red-500' : '' }}"
                                >
                                @error('temperature_conditions_from')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex-1">
                                <label class="block text-gray-600 text-xs mb-1">
                                    {{ __('product_to') }}
                                </label>
                                <input
                                    type="number"
                                    wire:model="temperature_conditions_to"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('temperature_conditions_to') ? 'border-red-500' : '' }}"
                                >
                                @error('temperature_conditions_to')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- end temperature conditions fields -->

                    <!-- start use until field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_use_until') }}
                        </label>
                        <input
                            type="date"
                            wire:model="use_until"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('use_until') ? 'border-red-500' : '' }}"
                        >
                        @error('use_until')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end use until field -->



                    <!-- start total shelf life field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_total_shelf_life') }} *
                        </label>
                        <input
                            type="number"
                            wire:model="total_shelf_life"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('total_shelf_life') ? 'border-red-500' : '' }}"
                            required
                        >
                        @error('total_shelf_life')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end total shelf life field -->



                    <!-- start main image field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_main_image') }}
                            @if (!isset($product->id) || !isset($product->product_image))
                                *
                            @endif
                        </label>
                        @if (isset($product) && $product->product_image)
                            <img 
                                src="{{ asset('storage/products/' . $product->product_image) }}" 
                                class="max-w-xs mb-2"
                            >
                        @endif
                        <label class="block">
                            <span class="sr-only">{{ __('product_choose_file') }}</span>
                            <input 
                                type="file" 
                                wire:model="product_image"
                                accept="image/*" 
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('product_image') ? 'border-red-500' : '' }}"
                            >
                        </label>
                        @error('product_image')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end main image field -->

                    <!-- start additional image field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_additional_image') }}
                        </label>
                        @if (isset($product) && $product->product_additional_image)
                            <img 
                                src="{{ asset('storage/products/' . $product->product_additional_image) }}" 
                                class="max-w-xs mb-2"
                            >
                        @endif
                        <label class="block">
                            <span class="sr-only">{{ __('product_choose_file') }}</span>
                            <input 
                                type="file" 
                                wire:model="product_additional_image"
                                accept="image/*" 
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('product_additional_image') ? 'border-red-500' : '' }}"
                            >
                        </label>
                        @error('product_additional_image')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end additional image field -->

                    <!-- start form buttons -->
                    <div class="flex items-center justify-between">
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            {{ isset($product->id) ? __('product_update') : __('product_create') }}
                        </button>
                        <a 
                            href="{{ route('seller.products.index') }}" 
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        >
                            {{ __('common_cancel') }}
                        </a>
                    </div>
                    <!-- end form buttons -->
                </form>
                <!-- end form -->
            </div>
            <!-- end form container -->
        </div>
        <!-- end main container -->
</div>
    <!-- end section -->

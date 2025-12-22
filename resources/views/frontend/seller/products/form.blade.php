    <!-- start extends -->
    @extends('layouts.frontend.app')
    <!-- end extends -->

    <!-- start section -->
    @section('content')
        <!-- start main container -->
        <div class="container mx-auto px-4 py-8">
            <!-- start form container -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <!-- start title -->
                <h2 class="text-2xl font-bold mb-6">
                    {{ isset($product->id) ? __('product.edit_product') : __('product.create_new_product') }}
                </h2>
                <!-- end title -->

                <!-- start success message -->
                @if (session('success'))
                    <div 
                        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" 
                        role="alert"
                    >
                        <span class="block sm:inline">
                            {{ session('success') }}
                        </span>
                    </div>
                @endif
                <!-- end success message -->

                <!-- start errors -->
                @if ($errors->any())
                    <div 
                        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" 
                        role="alert"
                    >
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <!-- end errors -->

                <!-- start form -->
                <form 
                    method="POST" 
                    action="{{ isset($product->id) ? route('seller.products.update', $product) : route('seller.products.store') }}" 
                    enctype="multipart/form-data"
                >
                    @csrf
                    @if (isset($product->id))
                        @method('PUT')
                    @endif

                    @if (isset($selectedCategory))
                        <input 
                            type="hidden" 
                            name="category_id" 
                            value="{{ $selectedCategory->id }}"
                        >
                    @else
                        <input 
                            type="hidden" 
                            name="category_id" 
                            value="{{ $product->category_id }}"
                        >
                    @endif

                    <!-- start name field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product.name') }} *
                        </label>
                        <input 
                            type="text"
                            name="name" 
                            required 
                            value="{{ old('name', $product->name ?? '') }}"
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
                            {{ __('product.price') }} * <span class="text-gray-500 font-normal">({{ __('product.price_without_vat') }})</span>
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="price" 
                            required 
                            value="{{ old('price', $product->price ?? '') }}" 
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
                            {{ __('product.pack_type') }} *
                        </label>
                        <input 
                            type="text"
                            name="pack_type"
                            value="{{ old('pack_type', $product->pack_type ?? '') }}"
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
                            {{ __('product.unit') }} *
                        </label>
                        <select 
                            name="unit" 
                            required 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('unit') ? 'border-red-500' : '' }}"
                        >
                            @foreach (collect(\App\Models\Product::UNITS)->sort() as $unit)
                                <option 
                                    value="{{ $unit }}" 
                                    {{ old('unit', $product->unit ?? '') == $unit ? 'selected' : '' }}
                                >
                                    {{ __("units.$unit") }}
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
                            {{ __('product.country_of_origin') }} *
                        </label>
                        <select 
                            name="country_of_origin" 
                            required 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('country_of_origin') ? 'border-red-500' : '' }}"
                        >
                            <option value="">
                                {{ __('common.select_country') }}
                            </option>
                            @foreach ($countries as $country)
                                <option 
                                    value="{{ $country->id }}" 
                                    {{ old('country_of_origin', $product->country_of_origin ?? '') == $country->id ? 'selected' : '' }}
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
                            {{ __('product.is_organic') }} *
                        </label>
                        <select 
                            name="is_organic" 
                            required 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('is_organic') ? 'border-red-500' : '' }}"
                        >
                            <option 
                                value="0" 
                                {{ old('is_organic', $product->is_organic ?? 0) == 0 ? 'selected' : '' }}
                            >
                                {{ __('common.no') }}
                            </option>
                            <option 
                                value="1" 
                                {{ old('is_organic', $product->is_organic ?? 0) == 1 ? 'selected' : '' }}
                            >
                                {{ __('common.yes') }}
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
                            {{ __('product.is_active') }} *
                        </label>
                        <select 
                            name="is_active" 
                            required 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('is_active') ? 'border-red-500' : '' }}"
                        >
                            <option 
                                value="0" 
                                {{ old('is_active', $product->is_active ?? 0) == 0 ? 'selected' : '' }}
                            >
                                {{ __('common.no') }}
                            </option>
                            <option 
                                value="1" 
                                {{ old('is_active', $product->is_active ?? 0) == 1 ? 'selected' : '' }}
                            >
                                {{ __('common.yes') }}
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
                            {{ __('product.min_order_price') }}
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="min_order_price" 
                            value="{{ old('min_order_price', $product->min_order_price ?? '') }}" 
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
                            {{ __('product.min_order_count') }} *
                        </label>
                        <input 
                            type="number" 
                            name="min_order_count" 
                            value="{{ old('min_order_count', $product->min_order_count ?? '') }}" 
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
                            {{ __('product.stock') }} *
                        </label>
                        <input 
                            type="number" 
                            name="stock" 
                            required 
                            value="{{ old('stock', $product->stock ?? '') }}" 
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
                            {{ __('product.description') }} *
                        </label>
                        @foreach (config('app.locales') as $locale)
                            <div class="mb-4">
                                <label class="block text-gray-600 text-xs mb-1">
                                    {{ strtoupper($locale) }}{{ $locale == app()->getLocale() ? ' *' : '' }}
                                </label>
                                <textarea 
                                    name="description[{{ $locale }}]" 
                                    rows="4" 
                                    {{ $locale == app()->getLocale() ? 'required' : '' }} 
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has("description.$locale") ? 'border-red-500' : '' }}"
                                >{{ old("description.$locale", isset($product) ? $product->getTranslation('description', $locale) : '') }}</textarea>
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
                            {{ __('product.temperature_conditions') }}
                        </label>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-gray-600 text-xs mb-1">
                                    {{ __('product.from') }}
                                </label>
                                <input
                                    type="number"
                                    name="temperature_conditions_from"
                                    value="{{ old('temperature_conditions_from', $product->temperature_conditions_from ?? '') }}"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('temperature_conditions_from') ? 'border-red-500' : '' }}"
                                >
                                @error('temperature_conditions_from')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex-1">
                                <label class="block text-gray-600 text-xs mb-1">
                                    {{ __('product.to') }}
                                </label>
                                <input
                                    type="number"
                                    name="temperature_conditions_to"
                                    value="{{ old('temperature_conditions_to', $product->temperature_conditions_to ?? '') }}"
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
                            {{ __('product.use_until') }}
                        </label>
                        <input
                            type="date"
                            name="use_until"
                            value="{{ old('use_until', isset($product->use_until) ? $product->use_until->format('Y-m-d') : '') }}"
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
                            {{ __('product.total_shelf_life') }} *
                        </label>
                        <input
                            type="number"
                            name="total_shelf_life"
                            value="{{ old('total_shelf_life', $product->total_shelf_life ?? '') }}"
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
                            {{ __('product.main_image') }}
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
                            <span class="sr-only">{{ __('product.choose_file') }}</span>
                            <input 
                                type="file" 
                                name="product_image" 
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
                            {{ __('product.additional_image') }}
                        </label>
                        @if (isset($product) && $product->product_additional_image)
                            <img 
                                src="{{ asset('storage/products/' . $product->product_additional_image) }}" 
                                class="max-w-xs mb-2"
                            >
                        @endif
                        <label class="block">
                            <span class="sr-only">{{ __('product.choose_file') }}</span>
                            <input 
                                type="file" 
                                name="product_additional_image" 
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
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        >
                            {{ isset($product->id) ? __('product.update') : __('product.create') }}
                        </button>
                        <a 
                            href="{{ route('seller.products.index') }}" 
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        >
                            {{ __('common.cancel') }}
                        </a>
                    </div>
                    <!-- end form buttons -->
                </form>
                <!-- end form -->
            </div>
            <!-- end form container -->
        </div>
        <!-- end main container -->
    @endsection
    <!-- end section -->

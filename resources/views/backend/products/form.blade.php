@extends('layouts.backend.app')

@section('content')
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        {{ isset($product) ? __('backend.products.edit.title') : __('backend.products.create.title') }}
                    </h2>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    {{ __('backend.common.error_message') }}
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ isset($product) ? route('backend.products.update', $product) : route('backend.products.store', $category) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @if (isset($product))
                        @method('PUT')
                    @endif

                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.category') }}
                                </label>
                                <select name="category_id" id="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('category_id') border-red-500 @enderror">
                                    @foreach ($categories->whereNull('parent_category_id') as $cat)
                                        <option value="{{ $cat->id }}" {{ $category->id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->getTranslation('category_name', app()->getLocale()) }}
                                        </option>
                                        @foreach ($cat->subcategories()->orderBy('category_name')->get() as $subcategory)
                                            <option value="{{ $subcategory->id }}" {{ $category->id == $subcategory->id ? 'selected' : '' }}>
                                                -- {{ $subcategory->getTranslation('category_name', app()->getLocale()) }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.price') }} *
                                </label>
                                <input type="number" step="0.01" id="price" name="price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('price') border-red-500 @enderror" value="{{ $product->price ?? old('price') }}" required>
                                @error('price')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.pack_type') }} *
                                </label>
                                <input type="text" name="pack_type" required 
                                       value="{{ old('pack_type', $product->pack_type ?? '') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.unit') }} *
                                </label>
                                <select name="unit" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    @foreach (App\Models\Product::UNITS as $unit)
                                        <option value="{{ $unit }}" @selected(old('unit', $product->unit ?? '') == $unit)>
                                            {{ __("units.$unit") }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.country_of_origin') }} *
                                </label>
                                <select name="country_of_origin" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">{{ __('common.select_country') }}</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}" @selected(old('country_of_origin', $product->country_of_origin ?? '') == $country->id)>
                                            {{ $country->getTranslation('country_name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.is_organic') }} *
                                </label>
                                <select name="is_organic" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="0">{{ __('common.no') }}</option>
                                    <option value="1" @selected(old('is_organic', $product->is_organic ?? 0))>
                                        {{ __('common.yes') }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.is_active') }} *
                                </label>
                                <select name="is_active" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="1">{{ __('product.active') }}</option>
                                    <option value="0" @selected(old('is_active', $product->is_active ?? 1))>
                                        {{ __('product.inactive') }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.stock') }} *
                                </label>
                                <input type="number" name="stock" required 
                                       value="{{ old('stock', $product->stock ?? '') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="min_order_price" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.min_order_price') }}
                                </label>
                                <input type="number" step="0.01" id="min_order_price" name="min_order_price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('min_order_price') border-red-500 @enderror" value="{{ $product->min_order_price ?? old('min_order_price') }}">
                                @error('min_order_price')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="min_order_count" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.min_order_count') }}
                                </label>
                                <input type="number" id="min_order_count" name="min_order_count" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('min_order_count') border-red-500 @enderror" value="{{ $product->min_order_count ?? old('min_order_count') }}">
                                @error('min_order_count')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="package_weight" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.package_weight') }}
                                </label>
                                <input type="number" step="0.003" id="package_weight" name="package_weight" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('package_weight') border-red-500 @enderror" value="{{ $product->package_weight ?? old('package_weight') }}">
                                @error('package_weight')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="price_per_liter" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.price_per_liter') }}
                                </label>
                                <input type="number" step="0.01" id="price_per_liter" name="price_per_liter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('price_per_liter') border-red-500 @enderror" value="{{ $product->price_per_liter ?? old('price_per_liter') }}">
                                @error('price_per_liter')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.description') }}
                                </label>
                                <textarea id="description" name="description[{{ app()->getLocale() }}]" required rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description.' . app()->getLocale()) border-red-500 @enderror">{{ $product->getTranslation('description', app()->getLocale()) ?? old('description.' . app()->getLocale()) }}</textarea>
                                @error('description.' . app()->getLocale())
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="product_image" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.product_image') }}
                                </label>
                                @if (isset($product) && $product->product_image)
                                    <img src="{{ Storage::url('products/' . $product->product_image) }}" alt="Product Image" class="mb-2 w-32 rounded">
                                @endif
                                <input type="file" id="product_image" name="product_image" {{ isset($product) ? '' : 'required' }} class="mt-1 block w-full @error('product_image') border-red-500 @enderror">
                                @error('product_image')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="product_additional_image" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend.products.fields.product_additional_image') }}
                                </label>
                                @if (isset($product) && $product->product_additional_image)
                                    <img src="{{ Storage::url('products/' . $product->product_additional_image) }}" alt="Additional Image" class="mb-2 w-32 rounded">
                                @endif
                                <input type="file" id="product_additional_image" name="product_additional_image" class="mt-1 block w-full @error('product_additional_image') border-red-500 @enderror">
                                @error('product_additional_image')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_organic" value="1" id="is_organic" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 @error('is_organic') border-red-500 @enderror" {{ isset($product) && $product->is_organic ? 'checked' : '' }}>
                                <label for="is_organic" class="ml-2 block text-sm text-gray-700">
                                    {{ __('backend.products.fields.is_organic') }}
                                </label>
                            </div>
                            @error('is_organic')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" id="is_active" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 @error('is_active') border-red-500 @enderror" {{ !isset($product) || $product->is_active ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                    {{ __('backend.products.fields.is_active') }}
                                </label>
                            </div>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if ($attributes && $attributes->count() > 0)
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('product.attributes') }}</h3>
                            <div class="space-y-6">
                                @foreach ($attributes as $attribute)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ $attribute->name }}
                                        </label>

                                        @switch($attribute->type)
                                            @case('select')
                                                <select name="attributes[{{ $attribute->id }}]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('attributes.' . $attribute->id) border-red-500 @enderror" {{ $attribute->is_required ? 'required' : '' }}>
                                                    <option value="">{{ __('common.select_option') }}</option>
                                                    @foreach ($attribute->values as $value)
                                                        <option value="{{ $value->id }}" {{ isset($product) && $value->products_exists ? 'selected' : '' }}>
                                                            {{ $value->value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @break

                                            @case('text')
                                                <input type="text" name="attributes[{{ $attribute->id }}]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('attributes.' . $attribute->id) border-red-500 @enderror" {{ $attribute->is_required ? 'required' : '' }} value="{{ isset($product) && $product->attributeValues->firstWhere('attribute_id', $attribute->id) ? $product->attributeValues->firstWhere('attribute_id', $attribute->id)->value : '' }}">
                                            @break

                                            @case('number')
                                                <input type="number" step="any" name="attributes[{{ $attribute->id }}]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('attributes.' . $attribute->id) border-red-500 @enderror" {{ $attribute->is_required ? 'required' : '' }} value="{{ isset($product) && $product->attributeValues->firstWhere('attribute_id', $attribute->id) ? $product->attributeValues->firstWhere('attribute_id', $attribute->id)->value : '' }}">
                                            @break

                                            @case('boolean')
                                                <div class="mt-1">
                                                    <label class="inline-flex items-center">
                                                        <input type="hidden" name="attributes[{{ $attribute->id }}]" value="0">
                                                        <input type="checkbox" name="attributes[{{ $attribute->id }}]" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('attributes.' . $attribute->id) border-red-500 @enderror" {{ $attribute->is_required ? 'required' : '' }} {{ isset($product) && $product->attributeValues->firstWhere('attribute_id', $attribute->id)?->value == 1 ? 'checked' : '' }}>
                                                        <span class="ml-2">{{ __('common.yes') }}</span>
                                                    </label>
                                                </div>
                                            @break

                                            @case('date')
                                                <input type="date" name="attributes[{{ $attribute->id }}]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('attributes.' . $attribute->id) border-red-500 @enderror" {{ $attribute->is_required ? 'required' : '' }} value="{{ isset($product) && $product->attributeValues->firstWhere('attribute_id', $attribute->id) ? $product->attributeValues->firstWhere('attribute_id', $attribute->id)->value : '' }}">
                                            @break
                                        @endswitch
                                        @error('attributes.' . $attribute->id)
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('backend.products.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('backend.common.cancel') }}
                        </a>
                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ isset($product) ? __('backend.common.update') : __('backend.common.create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

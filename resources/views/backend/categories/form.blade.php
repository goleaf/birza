@extends('layouts.backend.app')

@section('content')
    <div class="max-w-7xl mx-auto py-6 px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold mb-4">{{ isset($category) ? __('backend.categories.edit.title') : __('backend.categories.create.title') }}</h2>

                <form action="{{ isset($category) ? route('backend.categories.update', $category) : route('backend.categories.store') }}" method="POST">
                    @csrf
                    @if (isset($category))
                        @method('PUT')
                    @endif

                    <div class="mb-4">
                        <label for="parent_category_id" class="block font-medium text-gray-700 mb-1">{{ __('backend.categories.fields.parent_category') }}</label>
                        <select id="parent_category_id" name="parent_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('parent_category_id') border-red-500 @enderror">
                            <option value="">
                                {{ __('backend.categories.select_parent') }}
                            </option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('parent_category_id', isset($category) ? $category->parent_category_id : '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->getTranslation('category_name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_category_id')
                            <p class="mt-1 text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Translatable fields --}}
                    @foreach (config('app.locales') as $locale)
                        <div class="mb-4">
                            <label for="category_name_{{ $locale }}" class="block font-medium text-gray-700 mb-1">{{ strtoupper($locale) }} {{ __('backend.categories.fields.name') }}</label>
                            <input type="text" id="category_name_{{ $locale }}" name="name[{{ $locale }}]" value="{{ old('name.' . $locale, isset($category) ? $category->getTranslation('category_name', $locale) : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name.' . $locale) border-red-500 @enderror" required>
                            @error('name.' . $locale)
                                <p class="mt-1 text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    {{-- Attributes --}}
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-2">{{ __('backend.categories.fields.attributes') }}</label>

                        {{-- Notice for parent categories --}}
                        @if(!old('parent_category_id', isset($category) ? $category->parent_category_id : null))
                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                                <p>{{ __('backend.categories.attributes_reset_notice') }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            @foreach ($attributes->sortBy(function($attribute) { return $attribute->getTranslation('name', app()->getLocale()); }) as $attribute)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="attributes[]" value="{{ $attribute->id }}" {{ isset($category) && $category->attributes->contains($attribute->id) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="ml-2 text-gray-600">
                                        {{ $attribute->getTranslation('name', app()->getLocale()) }}
                                        @if (!$attribute->is_active)
                                            <span class="inline-block bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">{{ __('backend.common.disabled') }}</span>
                                        @endif
                                        @if ($attribute->is_filterable)
                                            <span class="text-xs text-indigo-600">({{ __('backend.attributes.fields.is_filterable') }})</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('attributes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('backend.categories.index') }}" class="mr-3 inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('backend.common.cancel') }}
                        </a>
                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ isset($category) ? __('backend.categories.actions.update') : __('backend.categories.actions.create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

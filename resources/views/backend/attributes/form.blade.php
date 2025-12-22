@extends('layouts.backend.app')

@section('content')
<!-- start main container -->
<div class="max-w-7xl mx-auto py-6 lg:px-8">
    <!-- start white container -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <!-- start content container -->
        <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-bold mb-4">
                {{ isset($attribute) ? __('backend.attributes.edit.title') : __('backend.attributes.create.title') }}
            </h2>

            <!-- start form -->
            <form 
                action="{{ isset($attribute) ? route('backend.attributes.update', $attribute) : route('backend.attributes.store') }}"
                method="POST"
            >
                @csrf
                @if (isset($attribute))
                    @method('PUT')
                @endif

                <!-- start errors -->
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                        <div class="text-sm text-red-600">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>
                                        {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                <!-- end errors -->

                <!-- start translatable fields -->
                @foreach (config('app.locales') as $locale)
                    <div class="mb-4">
                        <label for="name_{{ $locale }}" class="block font-medium text-gray-700 mb-1">
                            {{ strtoupper($locale) }} {{ __('backend.attributes.fields.name') }}
                        </label>
                        <input 
                            type="text" 
                            id="name_{{ $locale }}" 
                            name="name[{{ $locale }}]" 
                            value="{{ old('name.' . $locale, isset($attribute) ? $attribute->getTranslation('name', $locale) : '') }}" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                            required
                        >
                        @error('name.' . $locale)
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                @endforeach
                <!-- end translatable fields -->

                <!-- start type field -->
                <div class="mb-4">
                    <label for="type" class="block font-medium text-gray-700 mb-1">
                        {{ __('backend.attributes.fields.type') }}
                    </label>
                    <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($types as $key => $label)
                            <option value="{{ $key }}" {{ old('type', isset($attribute) ? $attribute->type : '') == $key ? 'selected' : '' }}>
                                {{ __('backend.attributes.types.' . $key) }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
                <!-- end type field -->

                <!-- start checkboxes -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_filterable" name="is_filterable" value="1" {{ old('is_filterable', isset($attribute) ? $attribute->is_filterable : false) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="is_filterable" class="ml-2 block text-sm text-gray-900">
                            {{ __('backend.attributes.fields.is_filterable') }}
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_required" name="is_required" value="1" {{ old('is_required', isset($attribute) ? $attribute->is_required : false) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="is_required" class="ml-2 block text-sm text-gray-900">
                            {{ __('backend.attributes.fields.is_required') }}
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', isset($attribute) ? $attribute->is_active : true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            {{ __('backend.attributes.fields.is_active') }}
                        </label>
                    </div>
                </div>
                <!-- end checkboxes -->

                <!-- start form buttons -->
                <div class="flex justify-end mt-6">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ isset($attribute) ? __('backend.common.update') : __('backend.common.create') }}
                    </button>
                    <a href="{{ route('backend.attributes.index') }}" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        {{ __('backend.common.cancel') }}
                    </a>
                </div>
                <!-- end form buttons -->
            </form>
            <!-- end form -->
        </div>
        <!-- end content container -->
    </div>
    <!-- end white container -->
</div>
<!-- end main container -->
@endsection

<div>
<!-- start main container -->
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- start white container -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <!-- start content container -->
        <div class="p-6 bg-white border-b border-gray-200">
            <!-- start title -->
            <h2 class="text-2xl font-bold mb-4">
                {{ isset($attributeValue) ? __('backend_attribute_values_edit_title') : __('backend_attribute_values_create_title') }}: 
                {{ $attribute->getTranslation('name', app()->getLocale()) }}
            </h2>
            <!-- end title -->

            <!-- start form -->
            <form wire:submit.prevent="save">

                <!-- start translatable fields -->
                @foreach(config('app.locales') as $locale)
                    <div class="mb-4">
                        <label for="value_{{ $locale }}" class="block font-medium text-gray-700 mb-1">
                            {{ strtoupper($locale) }} {{ __('backend_attribute_values_fields_value') }}
                        </label>
                        <input type="text" id="value_{{ $locale }}" wire:model.defer="value.{{ $locale }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @error('value.' . $locale)
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
                <!-- end translatable fields -->

                <!-- start is active checkbox -->
                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model.defer="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2">
                            {{ __('backend_attribute_values_fields_is_active') }}
                        </span>
                    </label>
                </div>
                <!-- end is active checkbox -->

                <!-- start form buttons -->
                <div class="flex justify-end mt-6 space-x-3">
                    <a href="{{ route('backend.attributes.values.index', $attribute) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                        {{ __('backend_common_cancel') }}
                    </a>
                    <button type="submit" wire:loading.attr="disabled" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed">
                        {{ isset($attributeValue) ? __('backend_common_update') : __('backend_common_create') }}
                    </button>
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
</div>

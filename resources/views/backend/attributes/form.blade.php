<div>
<!-- start main container -->
<div class="max-w-7xl mx-auto py-6 lg:px-8">
    <!-- start white container -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <!-- start content container -->
        <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-bold mb-4">
                {{ isset($attribute) ? __('backend_attributes_edit_title') : __('backend_attributes_create_title') }}
            </h2>

            <!-- start form -->
            <form wire:submit.prevent="save">

                <!-- start translatable fields -->
                @foreach (config('app.locales') as $locale)
                    <div class="mb-4">
                        <label for="name_{{ $locale }}" class="block font-medium text-gray-700 mb-1">
                            {{ strtoupper($locale) }} {{ __('backend_attributes_fields_name') }}
                        </label>
                        <input 
                            type="text" 
                            id="name_{{ $locale }}" 
                            wire:model="name.{{ $locale }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                            required
                        >
                            {{ strtoupper($locale) }}
                        </button>
                    @endforeach
                </div>

                @foreach ($locales as $locale)
                    <div x-show="locale === '{{ $locale }}'" x-cloak>
                        <div class="form-control">
                            <label for="name_{{ $locale }}" class="label">
                                <span class="label-text">{{ strtoupper($locale) }} {{ __('backend.attributes.fields.name') }}</span>
                            </label>
                            <input
                                type="text"
                                id="name_{{ $locale }}"
                                wire:model="name.{{ $locale }}"
                                class="input input-bordered w-full @error('name.' . $locale) input-error @enderror"
                                required
                            >
                            @error('name.' . $locale)
                                <span class="mt-1 text-sm text-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>

                <!-- start type field -->
                <div class="mb-4">
                    <label for="type" class="block font-medium text-gray-700 mb-1">
                        {{ __('backend_attributes_fields_type') }}
                    </label>
                    <select id="type" wire:model="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($types as $key => $label)
                            <option value="{{ $key }}">
                                {{ __('backend_attributes_types_' . $key) }}
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
                        <input type="checkbox" id="is_filterable" wire:model="is_filterable" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="is_filterable" class="ml-2 block text-sm text-gray-900">
                            {{ __('backend_attributes_fields_is_filterable') }}
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_required" wire:model="is_required" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="is_required" class="ml-2 block text-sm text-gray-900">
                            {{ __('backend_attributes_fields_is_required') }}
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" wire:model="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            {{ __('backend_attributes_fields_is_active') }}
                        </label>
                    </div>
                </div>
                <!-- end checkboxes -->

                <!-- start form buttons -->
                <div class="flex justify-end mt-6">
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed">
                        {{ isset($attribute) ? __('backend_common_update') : __('backend_common_create') }}
                    </button>
                    <a href="{{ route('backend.attributes.index') }}" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        {{ __('backend_common_cancel') }}
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
</div>

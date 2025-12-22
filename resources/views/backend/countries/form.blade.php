<div>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-6 border-b pb-3">
                {{ $country->exists ? __('backend.countries.edit') : __('backend.countries.create') }}
            </h2>

            <form wire:submit.prevent="save" class="space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="form-group">
                        <label for="alpha2" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('backend.countries.fields.alpha2') }}
                        </label>
                        <input type="text" id="alpha2" wire:model.defer="alpha2"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               maxlength="2">
                        @error('alpha2')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="region" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('backend.countries.fields.region') }}
                        </label>
                        <select id="region" wire:model.defer="region"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach ($regionOptions as $region)
                                <option value="{{ $region }}">
                                    {{ $region }}
                                </option>
                            @endforeach
                        </select>
                        @error('region')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('backend.countries.fields.is_active') }}
                        </label>
                        <select id="is_active" wire:model.defer="is_active"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="1">
                                Yes
                            </option>
                            <option value="0">
                                No
                            </option>
                        </select>
                        @error('is_active')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8">
                    @foreach ($locales as $locale)
                        <div class="bg-gray-50 p-4 rounded-md mb-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">
                                {{ strtoupper($locale) }} {{ __('backend.countries.translations') }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="{{ $locale }}[country_name]"
                                           class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('backend.countries.fields.country_name') }}
                                    </label>
                                    <input type="text" wire:model.defer="country_name.{{ $locale }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @error('country_name.' . $locale)
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between mt-6">
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                        {{ $country->exists ? __('backend.common.update') : __('backend.common.create') }}
                    </button>
                    <a href="{{ route('backend.countries.index') }}"
                       class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                        {{ __('backend.common.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

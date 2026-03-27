<x-ui.page :title="__('backend_countries_title')">
    <x-slot:actions>
        <x-button
            primary
            :href="route('backend.countries.create')"
            :label="__('backend_countries_actions_create')"
        />
    </x-slot:actions>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('backend_countries_fields_code') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('backend_countries_fields_region') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('backend_countries_fields_country_name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('common_actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($countries as $country)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $country->alpha2 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $country->region }}</td>
                            @foreach (config('app.locales') as $locale)
                                <td class="px-6 py-4 whitespace-nowrap language-column" data-locale="{{ $locale }}" style="{{ $locale === config('app.locales')[0] ? '' : 'display: none' }}">
                                    {{ $country->getTranslation('country_name', $locale) }}
                                </td>
                            @endforeach
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <x-button
                                        xs
                                        flat
                                        primary
                                        :href="route('backend.countries.edit', $country)"
                                        :label="__('common_edit')"
                                    />
                                    <x-button
                                        xs
                                        flat
                                        negative
                                        wire:click="confirmDeleteCountry({{ $country->id }})"
                                        :label="__('common_delete')"
                                    />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $countries->links() }}
        </div>
    </x-card>
</x-ui.page>

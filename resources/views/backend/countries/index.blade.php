<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">
            {{ __('backend.countries.title') }}
        </h2>
        <a href="{{ route('backend.countries.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            {{ __('backend.countries.actions.create') }}
        </a>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="bg-white border-b border-gray-200">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('backend.countries.fields.code') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('backend.countries.fields.region') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('backend.countries.fields.country_name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('backend.common.actions') }}
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
                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                <a href="{{ route('backend.countries.edit', $country) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ __('backend.common.edit') }}
                                </a>
                                <button type="button"
                                        class="text-red-600 hover:text-red-900"
                                        onclick="confirm('{{ __('backend.common.confirm_delete') }}') || event.stopImmediatePropagation()"
                                        wire:click="deleteCountry({{ $country->id }})">
                                    {{ __('backend.common.delete') }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 px-6">
                {{ $countries->links() }}
            </div>
        </div>
    </div>
</div>

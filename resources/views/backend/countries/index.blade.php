<x-backend.page :title="__('backend.countries.title')">
    <x-slot:actions>
        <a href="{{ route('backend.countries.create') }}" class="btn btn-primary btn-sm">
            {{ __('backend.countries.actions.create') }}
        </a>
    </x-slot:actions>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>{{ __('backend.countries.fields.code') }}</th>
                            <th>{{ __('backend.countries.fields.region') }}</th>
                            <th>{{ __('backend.countries.fields.country_name') }}</th>
                            <th class="text-right">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($countries as $country)
                            <tr>
                                <td>{{ $country->alpha2 }}</td>
                                <td>{{ $country->getRegionLabel() }}</td>
                                @foreach (config('app.locales') as $locale)
                                    <td class="language-column" data-locale="{{ $locale }}" style="{{ $locale === config('app.locales')[0] ? '' : 'display: none' }}">
                                        {{ $country->getTranslation('country_name', $locale) }}
                                    </td>
                                @endforeach
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('backend.countries.edit', $country) }}" class="btn btn-ghost btn-xs">
                                            {{ __('common.edit') }}
                                        </a>
                                        <button type="button" wire:click="confirmDeleteCountry({{ $country->id }})" class="btn btn-ghost btn-xs text-error">
                                            {{ __('common.delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $countries->links() }}
            </div>
        </div>
    </div>
</x-backend.page>

<x-backend.page :title="__('backend.attribute_values.index.title') . ': ' . $attribute->getTranslation('name', app()->getLocale())">
    <x-slot:actions>
        <a href="{{ route('backend.attributes.values.create', $attribute) }}" class="btn btn-primary btn-sm">
            {{ __('backend.attribute_values.actions.create') }}
        </a>
    </x-slot:actions>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>{{ strtoupper(app()->getLocale()) }}</th>
                        <th>{{ __('backend.attribute_values.fields.status') }}</th>
                        <th class="text-right">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($values as $value)
                        <tr>
                            <td>{{ $value->getTranslation('value', app()->getLocale()) }}</td>
                            <td>
                                <span class="badge {{ $value->is_active ? 'badge-success' : 'badge-error' }} badge-outline">
                                    {{ $value->is_active ? __('backend.common.active') : __('backend.common.inactive') }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('backend.attributes.values.edit', [$attribute, $value]) }}" class="btn btn-ghost btn-xs">
                                        {{ __('common.edit') }}
                                    </a>
                                    <button type="button" wire:click="confirmDeleteValue({{ $value->id }})" class="btn btn-ghost btn-xs text-error">
                                        {{ __('common.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-backend.page>

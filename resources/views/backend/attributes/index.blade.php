<x-backend.page :title="__('backend.attributes.title')">
    <x-slot:actions>
        <a href="{{ route('backend.attributes.create') }}" class="btn btn-primary btn-sm">
            {{ __('backend.attributes.actions.create') }}
        </a>
    </x-slot:actions>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>{{ __('backend.attributes.fields.name') }}</th>
                        <th>{{ __('backend.attributes.fields.type') }}</th>
                        <th>{{ __('backend.attributes.fields.values_count') }}</th>
                        <th>{{ __('backend.attributes.fields.status') }}</th>
                        <th>{{ __('backend.attributes.fields.is_filterable') }}</th>
                        <th>{{ __('backend.attributes.fields.is_required') }}</th>
                        <th class="text-right">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attributes as $attribute)
                        <tr>
                            <td>{{ $attribute->getTranslation('name', app()->getLocale()) }}</td>
                            <td>{{ __('backend.attributes.types.' . $attribute->type) }}</td>
                            <td>
                                <a href="{{ route('backend.attributes.values.index', $attribute) }}" class="link link-primary">
                                    {{ $attribute->values_count ?? $attribute->values->count() }}
                                    {{ __('backend.attributes.fields.values') }}
                                </a>
                            </td>
                            <td>
                                <span class="badge {{ $attribute->is_active ? 'badge-success' : 'badge-error' }} badge-outline">
                                    {{ $attribute->is_active ? __('common.active') : __('common.inactive') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $attribute->is_filterable ? 'badge-success' : 'badge-error' }} badge-outline">
                                    {{ $attribute->is_filterable ? __('common.yes') : __('common.no') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $attribute->is_required ? 'badge-success' : 'badge-error' }} badge-outline">
                                    {{ $attribute->is_required ? __('common.yes') : __('common.no') }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('backend.attributes.values.create', $attribute) }}" class="btn btn-ghost btn-xs">
                                        {{ __('backend.attributes.actions.add_value') }}
                                    </a>
                                    <a href="{{ route('backend.attributes.edit', $attribute) }}" class="btn btn-ghost btn-xs">
                                        {{ __('common.edit') }}
                                    </a>
                                    <button type="button" wire:click="confirmDeleteAttribute({{ $attribute->id }})" class="btn btn-ghost btn-xs text-error">
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
            {{ $attributes->links() }}
        </div>
    </x-ui.card>
</x-backend.page>

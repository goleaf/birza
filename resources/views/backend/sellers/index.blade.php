<x-backend.page :title="__('sellers.title')">
    <x-slot:actions>
        <a href="{{ route('backend.sellers.create') }}" class="btn btn-primary btn-sm">
            {{ __('common.create') }}
        </a>
    </x-slot:actions>

    <div class="space-y-6">
        <x-ui.card>
            <form action="{{ route('backend.sellers.index') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="form-control md:col-span-2">
                    <label for="search" class="label">
                        <span class="label-text">{{ __('common.search') }}</span>
                    </label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        class="input input-bordered w-full">
                </div>
                <div class="form-control">
                    <label for="sort" class="label">
                        <span class="label-text">{{ __('common.sort_by') }}</span>
                    </label>
                    <select name="sort" id="sort" class="select select-bordered w-full">
                        <option value="created_at,desc" {{ request('sort') === 'created_at,desc' ? 'selected' : '' }}>{{ __('common.newest') }}</option>
                        <option value="created_at,asc" {{ request('sort') === 'created_at,asc' ? 'selected' : '' }}>{{ __('common.oldest') }}</option>
                        <option value="name,asc" {{ request('sort') === 'name,asc' ? 'selected' : '' }}>{{ __('common.name_az') }}</option>
                        <option value="name,desc" {{ request('sort') === 'name,desc' ? 'selected' : '' }}>{{ __('common.name_za') }}</option>
                        <option value="company_name,asc" {{ request('sort') === 'company_name,asc' ? 'selected' : '' }}>{{ __('common.company_az') }}</option>
                        <option value="company_name,desc" {{ request('sort') === 'company_name,desc' ? 'selected' : '' }}>{{ __('common.company_za') }}</option>
                    </select>
                </div>
                <div class="flex flex-wrap items-center gap-2 md:col-span-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ __('common.filter') }}
                    </button>
                    <a href="{{ route('backend.sellers.index') }}" class="btn btn-ghost btn-sm">
                        {{ __('common.reset') }}
                    </a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>{{ __('sellers.field_name') }}</th>
                            <th>{{ __('sellers.field_email') }}</th>
                            <th>{{ __('sellers.field_company_name') }}</th>
                            <th>{{ __('sellers.field_stock') }}</th>
                            <th>{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sellers as $seller)
                            <tr>
                                <td>{{ $seller->name }}</td>
                                <td>{{ $seller->email }}</td>
                                <td>{{ $seller->company_name }}</td>
                                <td>{{ $seller->stock }}</td>
                                <td>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('backend.sellers.show', $seller) }}" class="btn btn-ghost btn-xs">
                                            {{ __('common.view') }}
                                        </a>
                                        <a href="{{ route('backend.sellers.edit', $seller) }}" class="btn btn-ghost btn-xs">
                                            {{ __('common.edit') }}
                                        </a>
                                        <button type="button" wire:click="confirmDeleteSeller({{ $seller->id }})" class="btn btn-ghost btn-xs text-error">
                                            {{ __('common.delete') }}
                                        </button>
                                        <a href="{{ route('backend.sellers.orders', $seller->id) }}" class="btn btn-ghost btn-xs">
                                            {{ __('sellers.orders_list') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <div>
            {{ $sellers->links() }}
        </div>
    </div>
</x-backend.page>

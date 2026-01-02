<x-backend.page :title="__('products.title')">
    <x-slot:actions>
        <a href="{{ route('backend.products.create') }}" class="btn btn-primary btn-sm">
            {{ __('products.actions.create') }}
        </a>
    </x-slot:actions>

    <div class="space-y-6">
        <x-ui.card>
            <form action="{{ route('backend.products.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="form-control">
                        <label for="search" class="label">
                            <span class="label-text">{{ __('common.search') }}</span>
                        </label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" class="input input-bordered w-full">
                    </div>

                    <div class="form-control">
                        <label for="category" class="label">
                            <span class="label-text">{{ __('product.category') }}</span>
                        </label>
                        <select name="category" id="category" class="select select-bordered w-full">
                            <option value="">{{ __('common.all') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label for="seller" class="label">
                            <span class="label-text">{{ __('product.seller') }}</span>
                        </label>
                        <select name="seller" id="seller" class="select select-bordered w-full">
                            <option value="">{{ __('common.all') }}</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" {{ request('seller') == $seller->id ? 'selected' : '' }}>
                                    {{ $seller->company_name ?: $seller->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label for="min_price" class="label">
                            <span class="label-text">{{ __('product.min_price') }}</span>
                        </label>
                        <input type="number" name="min_price" id="min_price" value="{{ request('min_price') }}" step="0.01"
                            class="input input-bordered w-full">
                    </div>
                    <div class="form-control">
                        <label for="max_price" class="label">
                            <span class="label-text">{{ __('product.max_price') }}</span>
                        </label>
                        <input type="number" name="max_price" id="max_price" value="{{ request('max_price') }}" step="0.01"
                            class="input input-bordered w-full">
                    </div>

                    <div class="form-control">
                        <label for="status" class="label">
                            <span class="label-text">{{ __('common.status') }}</span>
                        </label>
                        <select name="status" id="status" class="select select-bordered w-full">
                            <option value="">{{ __('common.all') }}</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('common.active') }}</option>
                            <option value="trashed" {{ request('status') === 'trashed' ? 'selected' : '' }}>{{ __('common.trashed') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="form-control md:col-span-2">
                        <label for="sort" class="label">
                            <span class="label-text">{{ __('common.sort_by') }}</span>
                        </label>
                        <select name="sort" id="sort" class="select select-bordered w-full">
                            <option value="created_at,desc" {{ request('sort') === 'created_at,desc' ? 'selected' : '' }}>{{ __('common.newest') }}</option>
                            <option value="created_at,asc" {{ request('sort') === 'created_at,asc' ? 'selected' : '' }}>{{ __('common.oldest') }}</option>
                            <option value="price,asc" {{ request('sort') === 'price,asc' ? 'selected' : '' }}>{{ __('product.price_low_high') }}</option>
                            <option value="price,desc" {{ request('sort') === 'price,desc' ? 'selected' : '' }}>{{ __('product.price_high_low') }}</option>
                            <option value="name,asc" {{ request('sort') === 'name,asc' ? 'selected' : '' }}>{{ __('common.name_az') }}</option>
                            <option value="name,desc" {{ request('sort') === 'name,desc' ? 'selected' : '' }}>{{ __('common.name_za') }}</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            {{ __('common.filter') }}
                        </button>
                        <a href="{{ route('backend.products.index') }}" class="btn btn-ghost btn-sm">
                            {{ __('common.reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            @if($products->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>{{ __('product.image') }}</th>
                                <th>{{ __('product.name') }}</th>
                                <th>{{ __('product.category') }}</th>
                                <th>{{ __('product.seller') }}</th>
                                <th class="text-right">{{ __('product.price') }}</th>
                                <th class="text-center">{{ __('common.status') }}</th>
                                <th class="text-right">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <img src="{{ Storage::url('products/' . $product->product_image) }}" alt="{{ $product->name }}"
                                            class="w-16 h-16 object-cover rounded-lg">
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ $product->name }}</div>
                                        @if($product->description)
                                            <div class="text-sm text-base-content/60">{{ Str::limit($product->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $product->category->category_name }}</td>
                                    <td>{{ $product->seller->company_name ?: $product->seller->name }}</td>
                                    <td class="text-right">€{{ number_format($product->price, 2) }}</td>
                                    <td class="text-center">
                                        @if($product->trashed())
                                            <span class="badge badge-error badge-outline">{{ __('common.trashed') }}</span>
                                        @else
                                            <span class="badge badge-success badge-outline">{{ __('common.active') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if ($product->trashed())
                                                <button type="button" wire:click="restoreProduct({{ $product->id }})" class="btn btn-ghost btn-xs text-success">
                                                    {{ __('common.restore') }}
                                                </button>
                                                <button type="button" wire:click="confirmForceDeleteProduct({{ $product->id }})" class="btn btn-ghost btn-xs text-error">
                                                    {{ __('common.force_delete') }}
                                                </button>
                                            @else
                                                <a href="{{ route('backend.products.edit', $product) }}" class="btn btn-ghost btn-xs">
                                                    {{ __('common.edit') }}
                                                </a>
                                                <button type="button" wire:click="confirmDeleteProduct({{ $product->id }})" class="btn btn-ghost btn-xs text-error">
                                                    {{ __('common.delete') }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4">
                    {{ $products->links() }}
                </div>
            @else
                <div class="py-12 text-center text-base-content/60">
                    {{ __('products.no_products') }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-backend.page>

<div>
    <div class="space-y-6">
        <x-buyer.breadcrumbs
            :items="[
                ['label' => __('wishlists.title')],
            ]"
        />

        <x-ui.header :title="__('wishlists.title')" :subtitle="__('wishlists.subtitle')">
            <x-slot:actions>
                <x-ui.button
                    href="{{ route('buyer.products.index') }}"
                    secondary
                    icon="shopping-bag"
                    :label="__('cart_continue_shopping')"
                />
            </x-slot:actions>
        </x-ui.header>

        <x-ui.card :title="__('wishlists.create')" separator body-class="space-y-4">
            <form wire:submit.prevent="createWishlist" class="grid gap-4 lg:grid-cols-[1fr_1fr_auto] lg:items-start">
                <div>
                    <label for="wishlist_name" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('wishlists.fields.name') }}
                    </label>
                    <input
                        id="wishlist_name"
                        type="text"
                        wire:model="name"
                        maxlength="120"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    >
                    @error('name')
                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="wishlist_description" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('wishlists.fields.description') }}
                    </label>
                    <input
                        id="wishlist_description"
                        type="text"
                        wire:model="description"
                        maxlength="1000"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                    >
                    @error('description')
                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 lg:min-w-44">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input
                            type="checkbox"
                            wire:model="isPrivate"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span>{{ __('wishlists.fields.is_private') }}</span>
                    </label>

                    <x-ui.button
                        type="submit"
                        primary
                        icon="plus"
                        spinner="createWishlist"
                        wire:loading.attr="disabled"
                        :label="__('wishlists.create')"
                    />
                </div>
            </form>
        </x-ui.card>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($wishlists as $wishlist)
                <x-ui.card
                    wire:key="wishlist-card-{{ $wishlist->id }}"
                    class="shadow-sm"
                    body-class="space-y-4"
                >
                    @if ($editingWishlistId === $wishlist->id)
                        <form wire:submit.prevent="updateWishlist" class="space-y-4">
                            <div>
                                <label for="edit_wishlist_name_{{ $wishlist->id }}" class="mb-1 block text-sm font-medium text-gray-700">
                                    {{ __('wishlists.fields.name') }}
                                </label>
                                <input
                                    id="edit_wishlist_name_{{ $wishlist->id }}"
                                    type="text"
                                    wire:model="editName"
                                    maxlength="120"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                >
                                @error('editName')
                                    <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="edit_wishlist_description_{{ $wishlist->id }}" class="mb-1 block text-sm font-medium text-gray-700">
                                    {{ __('wishlists.fields.description') }}
                                </label>
                                <input
                                    id="edit_wishlist_description_{{ $wishlist->id }}"
                                    type="text"
                                    wire:model="editDescription"
                                    maxlength="1000"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                                >
                                @error('editDescription')
                                    <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="flex flex-wrap gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input
                                        type="checkbox"
                                        wire:model="editIsPrivate"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span>{{ __('wishlists.fields.is_private') }}</span>
                                </label>

                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input
                                        type="checkbox"
                                        wire:model="editIsDefault"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span>{{ __('wishlists.fields.is_default') }}</span>
                                </label>
                            </div>

                            <div class="flex flex-wrap justify-end gap-2">
                                <x-ui.button
                                    type="button"
                                    secondary
                                    :label="__('ui.actions.cancel')"
                                    wire:click="cancelEditing"
                                />
                                <x-ui.button
                                    type="submit"
                                    primary
                                    spinner="updateWishlist"
                                    wire:loading.attr="disabled"
                                    :label="__('ui.actions.save')"
                                />
                            </div>
                        </form>
                    @else
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="truncate text-lg font-semibold text-gray-900">
                                    {{ $wishlist->name }}
                                </h2>
                                @if ($wishlist->description)
                                    <p class="mt-1 line-clamp-2 text-sm text-gray-600">
                                        {{ $wishlist->description }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-col items-end gap-2">
                                @if ($wishlist->is_default)
                                    <x-ui.badge color="primary" soft :value="__('wishlists.default_badge')" />
                                @endif
                                <x-ui.badge
                                    :color="$wishlist->is_private ? 'neutral' : 'info'"
                                    soft
                                    :value="$wishlist->is_private ? __('wishlists.private_badge') : __('wishlists.public_badge')"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-600">
                            <span>{{ __('common_items') }}</span>
                            <strong class="text-gray-900">{{ $wishlist->items_count }}</strong>
                        </div>

                        <div class="flex flex-wrap justify-end gap-2">
                            <x-ui.button
                                href="{{ route('buyer.wishlists.show', $wishlist) }}"
                                primary
                                sm
                                icon="eye"
                                :label="__('ui.actions.view')"
                            />
                            <x-ui.button
                                type="button"
                                secondary
                                sm
                                icon="pencil-square"
                                wire:click="startEditing({{ $wishlist->id }})"
                                :label="__('ui.actions.edit')"
                            />
                            <x-ui.button
                                type="button"
                                secondary
                                sm
                                icon="archive-box-x-mark"
                                wire:click="clearWishlist({{ $wishlist->id }})"
                                wire:loading.attr="disabled"
                                :label="__('wishlists.clear')"
                            />
                            <x-ui.button
                                type="button"
                                negative
                                sm
                                icon="trash"
                                wire:click="deleteWishlist({{ $wishlist->id }})"
                                wire:loading.attr="disabled"
                                :label="__('wishlists.delete')"
                            />
                        </div>
                    @endif
                </x-ui.card>
            @empty
                <x-ui.card class="md:col-span-2 xl:col-span-3" body-class="py-12 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <x-ui.icon name="heart" class="h-6 w-6" />
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('wishlists.empty') }}</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ __('wishlists.empty_help') }}</p>
                </x-ui.card>
            @endforelse
        </div>
    </div>
</div>

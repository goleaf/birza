<div class="mx-auto max-w-5xl space-y-6">
    <x-seller.breadcrumbs
        :items="[
            ['label' => __('messages.title')],
        ]"
    />

    <x-ui.header
        :title="__('messages.inbox')"
        :subtitle="__('messages.seller_subtitle')"
    >
        <x-slot:actions>
            <select
                wire:model.live="filter"
                class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                aria-label="{{ __('messages.filters.label') }}"
            >
                @foreach ($filterOptions as $option)
                    <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                @endforeach
            </select>
        </x-slot:actions>
    </x-ui.header>

    <x-ui.card class="rounded-lg shadow-sm" body-class="divide-y divide-gray-100">
        @forelse ($conversations as $conversation)
            <a
                href="{{ route('seller.messages.show', $conversation) }}"
                class="block px-1 py-4 transition hover:bg-blue-50 sm:px-3"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="truncate text-base font-semibold text-gray-900">
                                {{ $conversation->buyer?->company_name ?: $conversation->buyer?->name }}
                            </h2>

                            @if (($conversation->unread_messages_count ?? 0) > 0)
                                <x-ui.badge
                                    :value="__('messages.unread_count', ['count' => $conversation->unread_messages_count])"
                                    color="primary"
                                    sm
                                />
                            @endif
                        </div>

                        <p class="mt-1 text-sm text-gray-500">{{ $conversation->relatedLabel() }}</p>
                        <p class="mt-2 line-clamp-2 text-sm text-gray-700">
                            {{ $conversation->latestMessage?->preview() ?? __('messages.no_messages_yet') }}
                        </p>
                    </div>

                    <div class="text-sm text-gray-500 sm:text-right">
                        {{ $conversation->last_message_at?->diffForHumans() ?? $conversation->updated_at?->diffForHumans() }}
                    </div>
                </div>
            </a>
        @empty
            <div class="py-12 text-center">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.no_conversations') }}</h2>
                <p class="mt-2 text-sm text-gray-500">{{ __('messages.empty_seller') }}</p>
            </div>
        @endforelse
    </x-ui.card>

    {{ $conversations->links() }}
</div>

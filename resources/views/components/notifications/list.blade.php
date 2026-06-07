@props([
    'notifications',
    'unreadCount' => 0,
    'filter' => 'all',
    'interactive' => false,
])

<section class="space-y-4">
    <div class="flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ __('notifications.ui.title') }}</h1>
            <p class="text-sm text-gray-500">
                {{ __('notifications.ui.unread_count', ['count' => $unreadCount]) }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @foreach (['all', 'unread', 'read'] as $filterOption)
                <button
                    type="button"
                    wire:click="setFilter('{{ $filterOption }}')"
                    @class([
                        'rounded-md px-3 py-2 text-sm font-medium transition',
                        'bg-blue-600 text-white' => $filter === $filterOption,
                        'bg-gray-100 text-gray-700 hover:bg-gray-200' => $filter !== $filterOption,
                    ])
                >
                    {{ __('notifications.filters.'.$filterOption) }}
                </button>
            @endforeach

            @if ($interactive && $unreadCount > 0)
                <button
                    type="button"
                    wire:click="markAllAsRead"
                    class="rounded-md border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    {{ __('notifications.actions.mark_all_read') }}
                </button>
            @endif
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $title = __($data['title_key'] ?? 'notifications.fallback.title', $data['title_params'] ?? []);
                $message = __($data['message_key'] ?? 'notifications.fallback.message', $data['message_params'] ?? []);
                $url = $data['url'] ?? null;
                $icon = $data['icon'] ?? 'bell';
                $isUnread = $notification->read_at === null;
            @endphp

            <article @class([
                'flex gap-4 border-b border-gray-100 p-4 last:border-b-0',
                'bg-blue-50/70' => $isUnread,
            ])>
                <div @class([
                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-full',
                    'bg-blue-100 text-blue-700' => $isUnread,
                    'bg-gray-100 text-gray-500' => ! $isUnread,
                ])>
                    <x-ui.icon :name="$icon" class="h-5 w-5" />
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            @if ($url)
                                <a href="{{ $url }}" class="font-semibold text-gray-900 hover:text-blue-700">
                                    {{ $title }}
                                </a>
                            @else
                                <h2 class="font-semibold text-gray-900">{{ $title }}</h2>
                            @endif

                            <p class="mt-1 text-sm text-gray-600">{{ $message }}</p>
                        </div>

                        <div class="flex shrink-0 items-center gap-2 text-xs text-gray-500">
                            <time datetime="{{ $notification->created_at?->toIso8601String() }}">
                                {{ $notification->created_at?->diffForHumans() }}
                            </time>

                            @if ($isUnread)
                                <span class="h-2 w-2 rounded-full bg-blue-600" aria-label="{{ __('notifications.ui.unread') }}"></span>
                            @endif
                        </div>
                    </div>

                    @if ($interactive && $isUnread)
                        <button
                            type="button"
                            wire:click="markAsRead('{{ $notification->id }}')"
                            class="mt-3 text-sm font-medium text-blue-700 hover:text-blue-900"
                        >
                            {{ __('notifications.actions.mark_read') }}
                        </button>
                    @endif
                </div>
            </article>
        @empty
            <div class="p-10 text-center">
                <x-ui.icon name="bell-slash" class="mx-auto h-10 w-10 text-gray-400" />
                <h2 class="mt-3 font-semibold text-gray-900">{{ __('notifications.empty.title') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('notifications.empty.message') }}</p>
            </div>
        @endforelse
    </div>

    @if (method_exists($notifications, 'links'))
        {{ $notifications->links() }}
    @endif
</section>

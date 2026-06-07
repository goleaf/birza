@props([
    'notifications',
    'unreadCount' => 0,
    'indexRoute',
    'markReadRouteName',
    'markAllRoute',
])

<x-ui.popover position="bottom-end">
    <x-slot:trigger>
        <button
            type="button"
            class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
            aria-label="{{ __('notifications.ui.title') }}"
        >
            <x-ui.icon name="bell" class="h-5 w-5" />
            @if ($unreadCount > 0)
                <span class="absolute -right-1 -top-1 min-w-5 rounded-full bg-blue-600 px-1.5 py-0.5 text-center text-xs font-semibold text-white">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </button>
    </x-slot:trigger>

    <x-slot:content>
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-semibold text-gray-900">{{ __('notifications.ui.title') }}</h2>

                @if ($unreadCount > 0)
                    <form method="POST" action="{{ $markAllRoute }}">
                        @csrf
                        <button type="submit" class="text-xs font-medium text-blue-700 hover:text-blue-900">
                            {{ __('notifications.actions.mark_all_read') }}
                        </button>
                    </form>
                @endif
            </div>

            <div class="max-h-96 space-y-2 overflow-y-auto">
                @forelse ($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $title = __($data['title_key'] ?? 'notifications.fallback.title', $data['title_params'] ?? []);
                        $message = __($data['message_key'] ?? 'notifications.fallback.message', $data['message_params'] ?? []);
                        $url = $data['url'] ?? $indexRoute;
                        $isUnread = $notification->read_at === null;
                    @endphp

                    <div @class([
                        'rounded-md border p-3',
                        'border-blue-100 bg-blue-50/70' => $isUnread,
                        'border-gray-100 bg-white' => ! $isUnread,
                    ])>
                        <a href="{{ $url }}" class="block">
                            <div class="flex items-start gap-2">
                                @if ($isUnread)
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-blue-600"></span>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-gray-900">{{ $title }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs text-gray-600">{{ $message }}</p>
                                </div>
                            </div>
                        </a>

                        @if ($isUnread)
                            <form method="POST" action="{{ route($markReadRouteName, $notification) }}" class="mt-2">
                                @csrf
                                <button type="submit" class="text-xs font-medium text-blue-700 hover:text-blue-900">
                                    {{ __('notifications.actions.mark_read') }}
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="rounded-md border border-dashed border-gray-200 p-4 text-center text-sm text-gray-500">
                        {{ __('notifications.empty.message') }}
                    </div>
                @endforelse
            </div>

            <a href="{{ $indexRoute }}" class="block rounded-md bg-gray-100 px-3 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-200">
                {{ __('dashboard_view_all_notifications') }}
            </a>
        </div>
    </x-slot:content>
</x-ui.popover>

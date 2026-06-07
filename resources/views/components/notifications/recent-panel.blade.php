@props([
    'notifications',
    'href',
    'title' => null,
])

<section class="rounded-lg bg-white p-4 shadow-sm">
    <div class="mb-4 flex items-center justify-between gap-3">
        <h2 class="font-semibold text-gray-900">{{ $title ?? __('dashboard_recent_notifications') }}</h2>
        <a href="{{ $href }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
            {{ __('dashboard_view_all_notifications') }}
        </a>
    </div>

    <div class="space-y-3">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $titleText = __($data['title_key'] ?? 'notifications.fallback.title', $data['title_params'] ?? []);
                $messageText = __($data['message_key'] ?? 'notifications.fallback.message', $data['message_params'] ?? []);
                $url = $data['url'] ?? $href;
                $isUnread = $notification->read_at === null;
            @endphp

            <a
                href="{{ $url }}"
                @class([
                    'block rounded-md border p-3 transition hover:border-blue-200 hover:bg-blue-50',
                    'border-blue-100 bg-blue-50/60' => $isUnread,
                    'border-gray-100 bg-white' => ! $isUnread,
                ])
            >
                <div class="flex items-start gap-3">
                    <span @class([
                        'mt-1 h-2 w-2 shrink-0 rounded-full',
                        'bg-blue-600' => $isUnread,
                        'bg-gray-300' => ! $isUnread,
                    ])></span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-gray-900">{{ $titleText }}</p>
                        <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ $messageText }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-md border border-dashed border-gray-200 p-4 text-sm text-gray-500">
                {{ __('notifications.empty.message') }}
            </div>
        @endforelse
    </div>
</section>

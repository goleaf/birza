<div class="space-y-6">
    <x-mary-header :title="__('notifications.ui.title')" :subtitle="__('notifications.ui.subtitle')" separator />

    <x-notifications.list
        :notifications="$notifications"
        :unread-count="$unreadCount"
        :filter="$filter"
        interactive
    />
</div>

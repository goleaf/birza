<div class="space-y-6">
    <x-notifications.list
        :notifications="$notifications"
        :unread-count="$unreadCount"
        :filter="$filter"
        interactive
    />
</div>

<x-ui.auth-card :title="__('common_reset_password')">
    @if (session('error'))
        <x-alert negative :title="session('error')" class="mb-4" />
    @endif

    <form class="space-y-6" wire:submit.prevent="resetPassword">
        <input type="hidden" wire:model="token">
        <input type="hidden" wire:model="email">

        <x-input
            id="email_display"
            name="email_display"
            type="email"
            :label="__('common_email')"
            :value="$email"
            disabled
        />

        <x-password
            id="password"
            name="password"
            autocomplete="new-password"
            :label="__('common_password')"
            wire:model="password"
        />

        <x-password
            id="password_confirmation"
            name="password_confirmation"
            autocomplete="new-password"
            :label="__('common_confirm_password')"
            wire:model="password_confirmation"
        />

        <x-ui.button
            type="submit"
            primary
            class="w-full"
            :label="__('common_reset_password')"
            spinner="resetPassword"
        />
    </form>
</x-ui.auth-card>

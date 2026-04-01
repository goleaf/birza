<x-ui.auth-card :title="__('common_forgot_password')">
    @if (session('status'))
        <x-alert positive :title="session('status')" class="mb-4" />
    @endif

    <form class="space-y-6" wire:submit.prevent="sendResetLink">
        <x-input
            id="email"
            name="email"
            type="email"
            autocomplete="email"
            :label="__('common_email')"
            wire:model="email"
        />

        <x-button
            type="submit"
            primary
            class="w-full"
            :label="__('common_send_password_reset_link')"
            spinner="sendResetLink"
        />

        <div class="text-center">
            <x-link
                :href="route(\"{$userType}.login\")"
                :label="__('common_sign_in')"
                bluesm
            />
        </div>
    </form>
</x-ui.auth-card>

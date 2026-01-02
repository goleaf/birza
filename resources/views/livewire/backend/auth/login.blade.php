<x-ui.auth-card
    full-screen
    background-class="bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500"
    max-width-class="w-full"
    :title="__('common.sign_in')"
>
    <form class="space-y-6" wire:submit.prevent="login">
        <x-input
            id="email"
            name="email"
            type="email"
            autocomplete="email"
            :label="__('common.email_address')"
            wire:model.defer="email"
        />

        <x-password
            id="password"
            name="password"
            autocomplete="current-password"
            :label="__('common.password')"
            wire:model.defer="password"
        />

        <x-checkbox
            id="remember"
            name="remember"
            :label="__('common.remember_me')"
            wire:model="remember"
        />

        <x-button
            type="submit"
            primary
            class="w-full"
            :label="__('common.sign_in')"
            spinner="login"
        />
    </form>
</x-ui.auth-card>

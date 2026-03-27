<x-ui.auth-card :title="__('common_sign_in')">
    <form class="space-y-6" wire:submit.prevent="login">
        <x-input
            id="email"
            name="email"
            type="email"
            autocomplete="email"
            :label="__('common_email')"
            wire:model.defer="email"
        />

        <x-password
            id="password"
            name="password"
            autocomplete="current-password"
            :label="__('common_password')"
            wire:model.defer="password"
        />

        <div class="flex items-center justify-between">
            <x-checkbox
                id="remember"
                name="remember"
                :label="__('common_remember_me')"
                wire:model="remember"
            />

            <x-link
                :href="route(\"{$userType}.password.request\")"
                :label="__('common_forgot_password')"
                bluesm
            />
        </div>

        <x-button
            type="submit"
            primary
            class="w-full"
            :label="__('common_sign_in')"
            spinner="login"
        />

        <div class="text-center">
            <x-link
                :href="route(\"{$userType}.register\")"
                :label="__('common_register_button')"
                bluesm
            />
        </div>
    </form>
</x-ui.auth-card>

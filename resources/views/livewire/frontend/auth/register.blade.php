<x-ui.auth-card :title="__('common_register_button')">
    <form class="space-y-6" wire:submit.prevent="register">
        <x-input
            id="email"
            name="email"
            type="email"
            autocomplete="email"
            :label="__('common_email')"
            wire:model="email"
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
            :label="__('common_register_button')"
            spinner="register"
        />

        <div class="text-center">
            <span class="text-sm text-gray-600">{{ __('common_already_have_account') }}</span>
            <x-link
                :href="route(\"{$userType}.login\")"
                :label="__('common_sign_in')"
                bluesm
            />
        </div>
    </form>
</x-ui.auth-card>

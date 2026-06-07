<div class="space-y-6">
    <x-mary-header
        :title="__('auth_profile_information')"
        :subtitle="__('auth_update_profile_info')"
        separator
        progress-indicator
    />

    <div class="grid gap-6 xl:grid-cols-2">
        <x-mary-card
            :title="__('auth_profile_information')"
            :subtitle="__('auth_update_profile_info')"
            shadow
        >
            <x-mary-form wire:submit="saveProfile" no-separator>
                <x-mary-input :label="__('auth_name')" wire:model="name" icon="o-user" clearable required />
                <x-mary-input :label="__('auth_email')" wire:model="email" type="email" icon="o-envelope" clearable required />

                <x-slot:actions>
                    <x-mary-button
                        :label="__('common_save')"
                        icon="o-paper-airplane"
                        spinner="saveProfile"
                        type="submit"
                        class="btn-primary"
                    />
                </x-slot:actions>
            </x-mary-form>
        </x-mary-card>

        <x-mary-card
            :title="__('auth_update_password')"
            :subtitle="__('auth_ensure_long_password')"
            shadow
        >
            <x-mary-form wire:submit="savePassword" no-separator>
                <x-mary-password
                    :label="__('auth_current_password')"
                    wire:model="current_password"
                    right
                    required
                />
                <x-mary-password
                    :label="__('auth_new_password')"
                    wire:model="password"
                    right
                    required
                />
                <x-mary-password
                    :label="__('auth_confirm_password')"
                    wire:model="password_confirmation"
                    right
                    required
                />

                <x-slot:actions>
                    <x-mary-button
                        :label="__('auth_update_password')"
                        icon="o-lock-closed"
                        spinner="savePassword"
                        type="submit"
                        class="btn-primary"
                    />
                </x-slot:actions>
            </x-mary-form>
        </x-mary-card>
    </div>
</div>

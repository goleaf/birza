@php($isEditing = $buyer?->exists ?? false)

<div class="space-y-6">
    <x-mary-header
        :title="$isEditing ? __('backend_buyers_edit_title') : __('backend_buyers_create_title')"
        :subtitle="$isEditing ? $email : __('buyers_title')"
        separator
        progress-indicator
    />

    <x-mary-form wire:submit="save" class="gap-6">
        <div class="grid gap-6 xl:grid-cols-2">
            <x-mary-card :title="__('backend_buyers_fields_name')" :subtitle="__('backend_buyers_fields_email')" shadow>
                <div class="space-y-4">
                    <x-mary-input :label="__('backend_buyers_fields_name')" wire:model="name" icon="o-user" clearable required />
                    <x-mary-input :label="__('backend_buyers_fields_email')" wire:model="email" type="email" icon="o-envelope" clearable required />

                    @unless ($isEditing)
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-mary-password
                                :label="__('backend_buyers_fields_password')"
                                wire:model="password"
                                right
                                required
                            />
                            <x-mary-password
                                :label="__('backend_buyers_fields_password_confirmation')"
                                wire:model="password_confirmation"
                                right
                                required
                            />
                        </div>
                    @endunless

                    <div class="grid gap-4 md:grid-cols-2">
                        <x-mary-input :label="__('backend_buyers_fields_company_name')" wire:model="company_name" required />
                        <x-mary-input :label="__('backend_buyers_fields_company_code')" wire:model="company_code" required />
                    </div>

                    <x-mary-input :label="__('backend_buyers_fields_vat_code')" wire:model="vat_code" />
                </div>
            </x-mary-card>

            <x-mary-card :title="__('backend_buyers_fields_address')" :subtitle="__('backend_buyers_fields_credit_balance')" shadow>
                <div class="space-y-4">
                    <x-mary-input :label="__('backend_buyers_fields_address')" wire:model="address" icon="o-map-pin" />
                    <x-mary-input :label="__('backend_buyers_fields_phone')" wire:model="phone" icon="o-phone" />
                    <x-mary-input :label="__('backend_buyers_fields_bank_account')" wire:model="bank_account" />
                    <x-mary-input
                        :label="__('backend_buyers_fields_credit_balance')"
                        wire:model="credit_balance"
                        type="number"
                        step="0.01"
                        prefix="€"
                        icon="o-banknotes"
                    />
                </div>
            </x-mary-card>
        </div>

        <x-slot:actions>
            <x-mary-button
                :label="__('backend_common_cancel')"
                :link="route('backend.buyers.index')"
            />
            <x-mary-button
                :label="$isEditing ? __('backend_common_update') : __('backend_common_create')"
                icon="o-paper-airplane"
                spinner="save"
                type="submit"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-mary-form>
</div>

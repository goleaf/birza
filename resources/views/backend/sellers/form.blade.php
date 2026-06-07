@php($isEditing = $seller?->exists ?? false)

<div class="space-y-6">
    <x-mary-header
        :title="$isEditing ? __('backend_sellers_edit_title') : __('backend_sellers_create_title')"
        :subtitle="$isEditing ? $email : __('sellers_title')"
        separator
        progress-indicator
    />

    <x-mary-form wire:submit="save" class="gap-6">
        <div class="grid gap-6 xl:grid-cols-2">
            <x-mary-card :title="__('backend_sellers_fields_name')" :subtitle="__('backend_sellers_fields_email')" shadow>
                <div class="space-y-4">
                    <x-mary-input :label="__('backend_sellers_fields_name')" wire:model="name" icon="o-user" clearable required />
                    <x-mary-input :label="__('backend_sellers_fields_email')" wire:model="email" type="email" icon="o-envelope" clearable required />

                    @unless ($isEditing)
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-mary-password
                                :label="__('backend_sellers_fields_password')"
                                wire:model="password"
                                right
                                required
                            />
                            <x-mary-password
                                :label="__('backend_sellers_fields_password_confirmation')"
                                wire:model="password_confirmation"
                                right
                                required
                            />
                        </div>
                    @endunless

                    <div class="grid gap-4 md:grid-cols-2">
                        <x-mary-input :label="__('backend_sellers_fields_company_name')" wire:model="company_name" />
                        <x-mary-input :label="__('backend_sellers_fields_company_code')" wire:model="company_code" />
                    </div>

                    <x-mary-input :label="__('backend_sellers_fields_vat_code')" wire:model="vat_code" />
                    <x-mary-input
                        :label="__('backend_sellers_fields_veterinary_certificate_number')"
                        wire:model="veterinary_certificate_number"
                    />
                </div>
            </x-mary-card>

            <x-mary-card :title="__('backend_sellers_fields_address')" :subtitle="__('common_status')" shadow>
                <div class="space-y-4">
                    <x-mary-input :label="__('backend_sellers_fields_address')" wire:model="address" icon="o-map-pin" />
                    <x-mary-input :label="__('backend_sellers_fields_phone')" wire:model="phone" icon="o-phone" />
                    <x-mary-input :label="__('backend_sellers_fields_bank_account')" wire:model="bank_account" />

                    <div class="space-y-3 pt-2">
                        <x-mary-toggle
                            :label="__('backend_sellers_fields_is_verified')"
                            wire:model="is_verified"
                            right
                        />
                        <x-mary-toggle
                            :label="__('backend_sellers_fields_is_active')"
                            wire:model="is_active"
                            right
                        />
                    </div>
                </div>
            </x-mary-card>
        </div>

        <x-slot:actions>
            <x-mary-button
                :label="__('backend_common_cancel')"
                :link="route('backend.sellers.index')"
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

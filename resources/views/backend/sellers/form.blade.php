<x-backend.page :title="isset($seller) ? __('backend.sellers.edit.title') : __('backend.sellers.create.title')">
    <x-ui.card>
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="form-control">
                    <label for="name" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.name') }}</span>
                    </label>
                    <input type="text" id="name" wire:model.defer="name" class="input input-bordered w-full @error('name') input-error @enderror">
                    @error('name')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="email" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.email') }}</span>
                    </label>
                    <input type="email" id="email" wire:model.defer="email" class="input input-bordered w-full @error('email') input-error @enderror">
                    @error('email')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                @if(!isset($seller))
                    <div class="form-control">
                        <label for="password" class="label">
                            <span class="label-text">{{ __('backend.sellers.fields.password') }}</span>
                        </label>
                        <input type="password" id="password" wire:model.defer="password" class="input input-bordered w-full @error('password') input-error @enderror">
                        @error('password')
                            <span class="mt-1 text-sm text-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label for="password_confirmation" class="label">
                            <span class="label-text">{{ __('backend.sellers.fields.password_confirmation') }}</span>
                        </label>
                        <input type="password" id="password_confirmation" wire:model.defer="password_confirmation" class="input input-bordered w-full">
                    </div>
                @endif

                <div class="form-control">
                    <label for="company_name" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.company_name') }}</span>
                    </label>
                    <input type="text" id="company_name" wire:model.defer="company_name" class="input input-bordered w-full @error('company_name') input-error @enderror">
                    @error('company_name')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="company_code" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.company_code') }}</span>
                    </label>
                    <input type="text" id="company_code" wire:model.defer="company_code" class="input input-bordered w-full @error('company_code') input-error @enderror">
                    @error('company_code')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="vat_code" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.vat_code') }}</span>
                    </label>
                    <input type="text" id="vat_code" wire:model.defer="vat_code" class="input input-bordered w-full @error('vat_code') input-error @enderror">
                    @error('vat_code')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="address" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.address') }}</span>
                    </label>
                    <input type="text" id="address" wire:model.defer="address" class="input input-bordered w-full @error('address') input-error @enderror">
                    @error('address')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="phone" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.phone') }}</span>
                    </label>
                    <input type="text" id="phone" wire:model.defer="phone" class="input input-bordered w-full @error('phone') input-error @enderror">
                    @error('phone')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="bank_account" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.bank_account') }}</span>
                    </label>
                    <input type="text" id="bank_account" wire:model.defer="bank_account" class="input input-bordered w-full @error('bank_account') input-error @enderror">
                    @error('bank_account')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="veterinary_certificate_number" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.veterinary_certificate_number') }}</span>
                    </label>
                    <input type="text" id="veterinary_certificate_number" wire:model.defer="veterinary_certificate_number" class="input input-bordered w-full @error('veterinary_certificate_number') input-error @enderror">
                    @error('veterinary_certificate_number')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <label class="flex items-center gap-3">
                    <input type="checkbox" id="is_verified" wire:model.defer="is_verified" class="checkbox checkbox-primary">
                    <span class="label-text">{{ __('backend.sellers.fields.is_verified') }}</span>
                </label>
                <label class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" wire:model.defer="is_active" class="checkbox checkbox-primary">
                    <span class="label-text">{{ __('backend.sellers.fields.is_active') }}</span>
                </label>
            </div>

            <x-ui.form-actions
                :submit-label="isset($seller) ? __('backend.common.update') : __('backend.common.create')"
                :cancel-href="route('backend.sellers.index')"
                submit-target="save"
            />
        </form>
    </x-ui.card>
</x-backend.page>

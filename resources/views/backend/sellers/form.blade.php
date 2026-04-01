<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">
            @if(isset($seller))
                {{ __('backend_sellers_edit_title') }}
            @else
                {{ __('backend_sellers_create_title') }}
            @endif
        </h2>
    </div>

                <div class="form-control">
                    <label for="email" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.email') }}</span>
                    </label>
                    <input type="email" id="email" wire:model="email" class="input input-bordered w-full @error('email') input-error @enderror">
                    @error('email')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Basic Information -->
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('backend_sellers_fields_name') }}</label>
                        <input type="text" id="name" wire:model="name"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('backend_sellers_fields_email') }}</label>
                        <input type="email" id="email" wire:model="email"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(!isset($seller))
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">{{ __('backend_sellers_fields_password') }}</label>
                        <input type="password" id="password" wire:model="password"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('password')
                            <span class="mt-1 text-sm text-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('backend_sellers_fields_password_confirmation') }}</label>
                        <input type="password" id="password_confirmation" wire:model="password_confirmation"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                @endif

                    <!-- Company Information -->
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700">{{ __('backend_sellers_fields_company_name') }}</label>
                        <input type="text" id="company_name" wire:model="company_name"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('company_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="company_code" class="block text-sm font-medium text-gray-700">{{ __('backend_sellers_fields_company_code') }}</label>
                        <input type="text" id="company_code" wire:model="company_code"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('company_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="vat_code" class="block text-sm font-medium text-gray-700">{{ __('backend_sellers_fields_vat_code') }}</label>
                        <input type="text" id="vat_code" wire:model="vat_code"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('vat_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">{{ __('backend_sellers_fields_address') }}</label>
                        <input type="text" id="address" wire:model="address"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('backend_sellers_fields_phone') }}</label>
                        <input type="text" id="phone" wire:model="phone"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Banking Information -->
                    <div>
                        <label for="bank_account" class="block text-sm font-medium text-gray-700">{{ __('backend_sellers_fields_bank_account') }}</label>
                        <input type="text" id="bank_account" wire:model="bank_account"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('bank_account')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Seller Specific Fields -->
                    <div>
                        <label for="veterinary_certificate_number" class="block text-sm font-medium text-gray-700">{{ __('backend_sellers_fields_veterinary_certificate_number') }}</label>
                        <input type="text" id="veterinary_certificate_number" wire:model="veterinary_certificate_number"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('veterinary_certificate_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Fields -->
                    <div class="flex space-x-4">
                        <div>
                            <label for="is_verified" class="flex items-center">
                                <input type="checkbox" id="is_verified" wire:model="is_verified"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-600">{{ __('backend_sellers_fields_is_verified') }}</span>
                            </label>
                        </div>
                        <div>
                            <label for="is_active" class="flex items-center">
                                <input type="checkbox" id="is_active" wire:model="is_active"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-600">{{ __('backend_sellers_fields_is_active') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('backend.sellers.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ __('backend_common_cancel') }}
                    </a>
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed">
                        {{ isset($seller) ? __('backend_common_update') : __('backend_common_create') }}
                    </button>
                </div>

                <div class="form-control">
                    <label for="vat_code" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.vat_code') }}</span>
                    </label>
                    <input type="text" id="vat_code" wire:model="vat_code" class="input input-bordered w-full @error('vat_code') input-error @enderror">
                    @error('vat_code')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="address" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.address') }}</span>
                    </label>
                    <input type="text" id="address" wire:model="address" class="input input-bordered w-full @error('address') input-error @enderror">
                    @error('address')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="phone" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.phone') }}</span>
                    </label>
                    <input type="text" id="phone" wire:model="phone" class="input input-bordered w-full @error('phone') input-error @enderror">
                    @error('phone')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="bank_account" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.bank_account') }}</span>
                    </label>
                    <input type="text" id="bank_account" wire:model="bank_account" class="input input-bordered w-full @error('bank_account') input-error @enderror">
                    @error('bank_account')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="veterinary_certificate_number" class="label">
                        <span class="label-text">{{ __('backend.sellers.fields.veterinary_certificate_number') }}</span>
                    </label>
                    <input type="text" id="veterinary_certificate_number" wire:model="veterinary_certificate_number" class="input input-bordered w-full @error('veterinary_certificate_number') input-error @enderror">
                    @error('veterinary_certificate_number')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <label class="flex items-center gap-3">
                    <input type="checkbox" id="is_verified" wire:model="is_verified" class="checkbox checkbox-primary">
                    <span class="label-text">{{ __('backend.sellers.fields.is_verified') }}</span>
                </label>
                <label class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" wire:model="is_active" class="checkbox checkbox-primary">
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

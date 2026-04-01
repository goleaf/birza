<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">
            @if(isset($buyer))
                {{ __('backend_buyers_edit_title') }}
            @else
                {{ __('backend_buyers_create_title') }}
            @endif
        </h2>
    </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.email') }}</label>
                    <input type="email" id="email" wire:model="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Basic Information -->
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_name') }}</label>
                        <input type="text" id="name" wire:model="name"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
 
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_email') }}</label>
                        <input type="email" id="email" wire:model="email"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(!isset($buyer))
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_password') }}</label>
                        <input type="password" id="password" wire:model="password"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('password')
                            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_password_confirmation') }}</label>
                        <input type="password" id="password_confirmation" wire:model="password_confirmation"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                @endif

                    <!-- Company Information -->
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_company_name') }}</label>
                        <input type="text" id="company_name" wire:model="company_name"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('company_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="company_code" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_company_code') }}</label>
                        <input type="text" id="company_code" wire:model="company_code"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('company_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="vat_code" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_vat_code') }}</label>
                        <input type="text" id="vat_code" wire:model="vat_code"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('vat_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_address') }}</label>
                        <input type="text" id="address" wire:model="address"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_phone') }}</label>
                        <input type="text" id="phone" wire:model="phone"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Banking Information -->
                    <div>
                        <label for="bank_account" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_bank_account') }}</label>
                        <input type="text" id="bank_account" wire:model="bank_account"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('bank_account')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="credit_balance" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_credit_balance') }}</label>
                        <input type="number" step="0.01" id="credit_balance" wire:model="credit_balance"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('credit_balance')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('backend.buyers.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ __('backend_common_cancel') }}
                    </a>
                    <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed">
                        {{ isset($buyer) ? __('backend_common_update') : __('backend_common_create') }}
                    </button>
                </div>

                <div>
                    <label for="vat_code" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.vat_code') }}</label>
                    <input type="text" id="vat_code" wire:model="vat_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('vat_code') border-red-500 @enderror">
                    @error('vat_code')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.address') }}</label>
                    <input type="text" id="address" wire:model="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('address') border-red-500 @enderror">
                    @error('address')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.phone') }}</label>
                    <input type="text" id="phone" wire:model="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="bank_account" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.bank_account') }}</label>
                    <input type="text" id="bank_account" wire:model="bank_account" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('bank_account') border-red-500 @enderror">
                    @error('bank_account')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="credit_balance" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.credit_balance') }}</label>
                    <input type="number" step="0.01" id="credit_balance" wire:model="credit_balance" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('credit_balance') border-red-500 @enderror">
                    @error('credit_balance')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <x-ui.form-actions
                :submit-label="isset($buyer) ? __('backend.common.update') : __('backend.common.create')"
                :cancel-href="route('backend.buyers.index')"
                submit-target="save"
            />
        </form>
    </x-ui.card>
</x-backend.page>

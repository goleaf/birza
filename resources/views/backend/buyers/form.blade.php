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

    <div class="bg-white shadow-sm rounded-lg">
        <div class="p-6">
            <form wire:submit.prevent="save">

                <!-- Basic Information -->
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_name') }}</label>
                        <input type="text" id="name" wire:model.defer="name"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
 
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_email') }}</label>
                        <input type="email" id="email" wire:model.defer="email"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(!isset($buyer))
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_password') }}</label>
                        <input type="password" id="password" wire:model.defer="password"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_password_confirmation') }}</label>
                        <input type="password" id="password_confirmation" wire:model.defer="password_confirmation"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    @endif

                    <!-- Company Information -->
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_company_name') }}</label>
                        <input type="text" id="company_name" wire:model.defer="company_name"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('company_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="company_code" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_company_code') }}</label>
                        <input type="text" id="company_code" wire:model.defer="company_code"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('company_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="vat_code" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_vat_code') }}</label>
                        <input type="text" id="vat_code" wire:model.defer="vat_code"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('vat_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_address') }}</label>
                        <input type="text" id="address" wire:model.defer="address"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_phone') }}</label>
                        <input type="text" id="phone" wire:model.defer="phone"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Banking Information -->
                    <div>
                        <label for="bank_account" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_bank_account') }}</label>
                        <input type="text" id="bank_account" wire:model.defer="bank_account"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('bank_account')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="credit_balance" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_fields_credit_balance') }}</label>
                        <input type="number" step="0.01" id="credit_balance" wire:model.defer="credit_balance"
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
            </form>
        </div>
    </div>
</div>

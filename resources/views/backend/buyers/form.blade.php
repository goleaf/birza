<x-backend.page :title="isset($buyer) ? __('backend.buyers.edit.title') : __('backend.buyers.create.title')">
    <x-ui.card>
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.name') }}</label>
                    <input type="text" id="name" wire:model.defer="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.email') }}</label>
                    <input type="email" id="email" wire:model.defer="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                @if (!isset($buyer))
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.password') }}</label>
                        <input type="password" id="password" wire:model.defer="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.password_confirmation') }}</label>
                        <input type="password" id="password_confirmation" wire:model.defer="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                @endif

                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.company_name') }}</label>
                    <input type="text" id="company_name" wire:model.defer="company_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('company_name') border-red-500 @enderror">
                    @error('company_name')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="company_code" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.company_code') }}</label>
                    <input type="text" id="company_code" wire:model.defer="company_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('company_code') border-red-500 @enderror">
                    @error('company_code')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="vat_code" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.vat_code') }}</label>
                    <input type="text" id="vat_code" wire:model.defer="vat_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('vat_code') border-red-500 @enderror">
                    @error('vat_code')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.address') }}</label>
                    <input type="text" id="address" wire:model.defer="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('address') border-red-500 @enderror">
                    @error('address')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.phone') }}</label>
                    <input type="text" id="phone" wire:model.defer="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="bank_account" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.bank_account') }}</label>
                    <input type="text" id="bank_account" wire:model.defer="bank_account" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('bank_account') border-red-500 @enderror">
                    @error('bank_account')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="credit_balance" class="block text-sm font-medium text-gray-700">{{ __('backend.buyers.fields.credit_balance') }}</label>
                    <input type="number" step="0.01" id="credit_balance" wire:model.defer="credit_balance" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('credit_balance') border-red-500 @enderror">
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

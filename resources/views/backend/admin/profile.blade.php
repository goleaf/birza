<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-900">{{ __('auth_profile_information') }}</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ __('auth_update_profile_info') }}</p>
                </div>

                <form wire:submit.prevent="saveProfile" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('auth_name') }}</label>
                        <input
                            type="text"
                            id="name"
                            wire:model.defer="name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('auth_email') }}</label>
                        <input
                            type="email"
                            id="email"
                            wire:model.defer="email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            {{ __('common_save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-900">{{ __('auth_update_password') }}</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ __('auth_ensure_long_password') }}</p>
                </div>

                <form wire:submit.prevent="savePassword" class="space-y-6">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">{{ __('auth_current_password') }}</label>
                        <input
                            type="password"
                            id="current_password"
                            wire:model.defer="current_password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        >
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">{{ __('auth_new_password') }}</label>
                        <input
                            type="password"
                            id="password"
                            wire:model.defer="password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('auth_confirm_password') }}</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            wire:model.defer="password_confirmation"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        >
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            {{ __('auth_update_password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

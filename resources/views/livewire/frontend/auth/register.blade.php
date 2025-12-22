<div class="flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">
            {{ __('auth.register') }}
        </h2>

        <form class="space-y-6" wire:submit.prevent="register" x-data="{ showPassword: false, showConfirmPassword: false }">
            <div class="space-y-2">
                <label for="email" class="text-sm font-semibold text-gray-700">
                    {{ __('common.email') }}
                </label>
                <input
                    type="email"
                    id="email"
                    wire:model.defer="email"
                    autocomplete="email"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm"
                    required
                    placeholder="{{ __('auth.enter_email') }}"
                >
            </div>

            <div class="space-y-2">
                <label for="password" class="text-sm font-semibold text-gray-700">
                    {{ __('common.password') }}
                </label>
                <div class="relative flex items-center">
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        wire:model.defer="password"
                        autocomplete="new-password"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm pr-10"
                        required
                        placeholder="{{ __('auth.enter_password') }}"
                    >
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer"
                        @click="showPassword = !showPassword"
                    >
                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="space-y-2">
                <label for="password_confirmation" class="text-sm font-semibold text-gray-700">
                    {{ __('common.confirm_password') }}
                </label>
                <div class="relative flex items-center">
                    <input
                        :type="showConfirmPassword ? 'text' : 'password'"
                        id="password_confirmation"
                        wire:model.defer="password_confirmation"
                        autocomplete="new-password"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm pr-10"
                        required
                        placeholder="{{ __('auth.confirm_password') }}"
                    >
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer"
                        @click="showConfirmPassword = !showConfirmPassword"
                    >
                        <svg x-show="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full py-3 px-4 text-white font-medium bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition duration-200 hover:scale-[1.02] shadow-lg disabled:opacity-60 disabled:cursor-not-allowed"
            >
                {{ __('auth.register') }}
            </button>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    {{ __('auth.already_have_account') }}
                    <a href="{{ route("{$userType}.login") }}" class="text-blue-600 hover:text-blue-800 font-medium transition duration-200">
                        {{ __('auth.login_here') }}
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>



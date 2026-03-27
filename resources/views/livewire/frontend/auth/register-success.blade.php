<div class="flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-gray-800 mb-4">
                {{ __('messages_registration_success') }}
            </h2>

            <p class="text-gray-600 mb-8">
                {{ __('messages_verification_required') }}: <span class="font-medium">{{ $email }}</span>
            </p>

            @if (session('success'))
                <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <a href="{{ route("{$userType}.login") }}"
               class="inline-block w-full py-3 px-4 text-white font-medium bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition duration-200 hover:scale-[1.02] shadow-lg">
                {{ __('common_sign_in') }}
            </a>

            <div class="mt-6 text-sm text-gray-600">
                <p class="mb-2">{{ __('messages_verification_sent') }}</p>
                <button type="button"
                        wire:click="resendVerification"
                        wire:loading.attr="disabled"
                        class="text-blue-600 hover:text-blue-800 font-medium transition duration-200 disabled:opacity-60 disabled:cursor-not-allowed">
                    {{ __('messages_verification_sent') }}
                </button>
            </div>
        </div>
    </div>
</div>



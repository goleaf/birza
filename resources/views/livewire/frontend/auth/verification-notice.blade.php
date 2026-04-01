<div class="flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
            {{ __('messages_verification_required') }}
        </h2>

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

        <form class="space-y-6" wire:submit.prevent="resendVerification">
            <div class="space-y-2">
                <label for="email" class="text-sm font-semibold text-gray-700">
                    {{ __('common_email') }}
                </label>
                <input
                    type="email"
                    id="email"
                    wire:model="email"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('email') border-red-500 @enderror"
                    required
                    autofocus
                >
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    wire:loading.attr="disabled"
                    class="w-full py-3 px-4 text-white font-medium bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition duration-200 hover:scale-[1.02] shadow-lg disabled:opacity-60 disabled:cursor-not-allowed">
                {{ __('messages_verification_sent') }}
            </button>

            <div class="text-center">
                <a href="{{ route("{$userType}.login") }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition duration-200">
                    {{ __('common_sign_in') }}
                </a>
            </div>
        </form>
    </div>
</div>



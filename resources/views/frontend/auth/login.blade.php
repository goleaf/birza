@extends('layouts.frontend.app')

@section('content')
    <div class="flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
                        <!-- Title -->
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">
                {{ __('auth.login') }}
            </h2>

            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <ul class="list-disc list-inside text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <div class="text-red-500 text-sm">{{ $error }}</div>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form class="space-y-6" action="{{ route("{$userType}.login.submit") }}" method="POST">
                @csrf

                <!-- Email Field -->
                <div class="space-y-2">
                    <label for="email" class="text-sm font-semibold text-gray-700">
                        {{ __('common.email') }}
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm" 
                        required 
                        value="{{ old('email') }}"
                        placeholder="{{ __('auth.enter_email') }}"
                    >
                </div>

                <!-- Password Field -->
                <div class="mb-4">
                    <label for="password" class="text-sm font-semibold text-gray-700">
                        {{ __('common.password') }}
                    </label>
                    <div class="relative flex items-center">
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm" 
                            required 
                            value="{{ old('password') ?: 'password123' }}"
                            placeholder="{{ __('auth.enter_password') }}"
                        >
                        <div 
                            class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer"
                            id="togglePassword"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" id="eyeIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 hidden" id="eyeSlashIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            id="remember" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition duration-200"
                        >
                        <label for="remember" class="ml-2 text-sm text-gray-600">
                            {{ __('auth.remember_me') }}
                        </label>
                    </div>

                    <a href="{{ route("{$userType}.password.request") }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition duration-200">
                        {{ __('auth.forgot_password') }}
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 px-4 text-white font-medium bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition duration-200 hover:scale-[1.02] shadow-lg">
                    {{ __('auth.login') }}
                </button>

                <!-- Register Link -->
                <div class="text-center mt-8">
                    <p class="text-sm text-gray-600">
                        {{ __('auth.no_account') }}
                        <a href="{{ route("{$userType}.register") }}" class="text-blue-600 hover:text-blue-800 font-medium transition duration-200">
                            {{ __('auth.register_here') }}
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

<!-- Script remains the same -->
<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const eyeIcon = document.querySelector('#eyeIcon');
    const eyeSlashIcon = document.querySelector('#eyeSlashIcon');

    togglePassword.addEventListener('click', function (e) {
        // Toggle the type attribute
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Toggle the icons
        eyeIcon.classList.toggle('hidden');
        eyeSlashIcon.classList.toggle('hidden');
    });
</script>
@endsection
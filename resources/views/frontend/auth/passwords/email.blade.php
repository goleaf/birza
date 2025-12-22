@extends('layouts.frontend.app')

@section('content')
    <div class="flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
            <!-- Title -->
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">
                {{ __('auth.forgot_password') }}
            </h2>

            <!-- Status Message -->
            @if (session('status'))
                <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                {{ session('status') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form class="space-y-6" method="POST" action="{{ route($userType . '.password.email') }}">
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
                        autofocus
                        value="{{ old('email') }}"
                        placeholder="{{ __('auth.enter_email') }}"
                    >
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 px-4 text-white font-medium bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition duration-200 hover:scale-[1.02] shadow-lg">
                    {{ __('auth.send_password_reset_link') }}
                </button>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        {{ __('auth.remember_password') }}
                        <a href="{{ route($userType . '.login') }}" class="text-blue-600 hover:text-blue-800 font-medium transition duration-200">
                            {{ __('auth.login_here') }}
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
@endsection
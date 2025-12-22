@extends('layouts.frontend.app')

@section('content')
    <div class="flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center">
                <!-- Success Icon -->
                <div class="mb-6">
                    <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <!-- Title -->
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    {{ __('auth.registration_successful') }}
                </h2>

                <!-- Message -->
                <p class="text-gray-600 mb-8">
                    {{ __('auth.verification_email_sent', ['email' => $email]) }}
                </p>

                <!-- Login Button -->
                <a href="{{ route("{$userType}.login") }}" 
                   class="inline-block w-full py-3 px-4 text-white font-medium bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition duration-200 hover:scale-[1.02] shadow-lg">
                    {{ __('auth.proceed_to_login') }}
                </a>

                <!-- Resend Verification Email -->
                <p class="mt-6 text-sm text-gray-600">
                    {{ __('auth.didnt_receive_email') }}
                    <form class="inline" method="POST" action="{{ route("{$userType}.verification.resend") }}">
                        @csrf
                        <button type="submit" class="text-blue-600 hover:text-blue-800 font-medium transition duration-200">
                            {{ __('auth.resend_verification_email') }}
                        </button>
                    </form>
                </p>
            </div>
        </div>
    </div>
@endsection 
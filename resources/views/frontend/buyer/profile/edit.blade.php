<!-- start extends -->
@extends('layouts.frontend.app')
<!-- end extends -->

<!-- start section -->
@section('content')

    <!-- start main container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- start success message -->
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-800 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        <!-- end success message -->

        <!-- start errors -->
        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- end errors -->

        <!-- start tabs navigation -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <nav class="flex divide-x divide-gray-200 border-b border-gray-200" aria-label="Tabs">
                <!-- start profile tab button -->
                <button 
                    onclick="switchTab('profile')"
                    class="tab-button flex-1 py-4 px-4 text-center text-sm font-medium relative hover:bg-gray-50 focus:z-10 focus:outline-none transition-colors"
                    id="profile-tab"
                >
                    <span class="inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ __('profile.edit_profile') }}
                    </span>
                </button>
                <!-- end profile tab button -->

                @if (!empty($buyer->company_name) && !empty($buyer->address) && !empty($buyer->phone))
                    <!-- start password tab button -->
                    <button 
                        onclick="switchTab('password')"
                        class="tab-button flex-1 py-4 px-4 text-center text-sm font-medium relative hover:bg-gray-50 focus:z-10 focus:outline-none transition-colors"
                        id="password-tab"
                    >
                        <span class="inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 8z"/>
                            </svg>
                            {{ __('profile.update_password') }}
                        </span>
                    </button>
                    <!-- end password tab button -->
                @endif
            </nav>
        </div>
        <!-- end tabs navigation -->

        <!-- start profile tab content -->
        <div id="profile-content" class="tab-content">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-8">
                    <form method="POST" action="{{ route('buyer.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- start name field -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('auth.name') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    id="name" 
                                    value="{{ old('name', $buyer->name) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('name') border-red-500 @enderror"
                                    required
                                >
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- end name field -->

                            <!-- start email field -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('auth.email') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email" 
                                    value="{{ old('email', $buyer->email) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('email') border-red-500 @enderror"
                                    required
                                >
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- end email field -->

                            <!-- start company name field -->
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('auth.company_name') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    name="company_name" 
                                    id="company_name" 
                                    value="{{ old('company_name', $buyer->company_name) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('company_name') border-red-500 @enderror"
                                    required
                                >
                                @error('company_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- end company name field -->

                            <!-- start company code field -->
                            <div>
                                <label for="company_code" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('auth.company_code') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    name="company_code" 
                                    id="company_code" 
                                    value="{{ old('company_code', $buyer->company_code) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('company_code') border-red-500 @enderror"
                                    required
                                >
                                @error('company_code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- end company code field -->

                            <!-- start vat code field -->
                            <div>
                                <label for="vat_code" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('auth.vat_code') }}
                                </label>
                                <input 
                                    type="text" 
                                    name="vat_code" 
                                    id="vat_code" 
                                    value="{{ old('vat_code', $buyer->vat_code) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm"
                                >
                            </div>
                            <!-- end vat code field -->

                            <!-- start address field -->
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('auth.address') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    name="address" 
                                    id="address" 
                                    value="{{ old('address', $buyer->address) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('address') border-red-500 @enderror"
                                    required
                                >
                                @error('address')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- end address field -->

                            <!-- start phone field -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('auth.phone') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="tel" 
                                    name="phone" 
                                    id="phone" 
                                    value="{{ old('phone', $buyer->phone) }}"
                                    placeholder="+370" 
                                    data-mask="+370 99999999"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('phone') border-red-500 @enderror"
                                    required
                                >
                                @error('phone')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- end phone field -->

                            <!-- start bank account field -->
                            <div>
                                <label for="bank_account" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('auth.bank_account') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    name="bank_account" 
                                    id="bank_account" 
                                    value="{{ old('bank_account', $buyer->bank_account) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('bank_account') border-red-500 @enderror"
                                    required
                                >
                                @error('bank_account')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- end bank account field -->
                        </div>

                        <!-- start form buttons -->
                        <div class="mt-6 flex justify-end space-x-4">
                            <button 
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                {{ __('profile.update_profile') }}
                            </button>

                            @if (!empty($buyer->company_name) && !empty($buyer->address) && !empty($buyer->phone))
                                <a 
                                    href="{{ route('buyer.dashboard') }}"
                                    class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-200"
                                >
                                    {{ __('dashboard.dashboard_title') }}
                                </a>
                            @endif
                        </div>
                        <!-- end form buttons -->
                    </form>
                </div>
            </div>
        </div>
        <!-- end profile tab content -->

        <!-- start password tab content -->
        @if (!empty($buyer->company_name) && !empty($buyer->address) && !empty($buyer->phone))
            <div id="password-content" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="p-8">
                        <!-- start password success message -->
                        @if (session('password_success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                                {{ session('password_success') }}
                            </div>
                        @endif
                        <!-- end password success message -->

                        <!-- start password errors -->
                        @if ($errors->updatePassword->any())
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->updatePassword->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <!-- end password errors -->

                        <!-- start password form -->
                        <form method="POST" action="{{ route('buyer.profile.password') }}" class="max-w-md mx-auto space-y-6">
                            @csrf
                            @method('PUT')

                            <div class="space-y-2">
                                <!-- start current password field -->
                                <div>
                                    <label 
                                        for="current_password" 
                                        class="block text-gray-700 text-sm font-bold mb-2"
                                    >
                                        {{ __('auth.current_password') }}
                                        <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                            {{ __('common.required') }}
                                        </span>
                                    </label>
                                    <input 
                                        type="password" 
                                        name="current_password" 
                                        id="current_password"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('current_password') border-red-500 @enderror"
                                        required
                                    >
                                    @error('current_password')
                                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <!-- end current password field -->

                                <!-- start new password field -->
                                <div>
                                    <label 
                                        for="password" 
                                        class="block text-gray-700 text-sm font-bold mb-2"
                                    >
                                        {{ __('auth.new_password') }}
                                        <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                            {{ __('common.required') }}
                                        </span>
                                    </label>
                                    <input 
                                        type="password" 
                                        name="password" 
                                        id="password"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('password') border-red-500 @enderror"
                                        required
                                    >
                                    @error('password')
                                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <!-- end new password field -->

                                <!-- start confirm password field -->
                                <div>
                                    <label 
                                        for="password_confirmation" 
                                        class="block text-gray-700 text-sm font-bold mb-2"
                                    >
                                        {{ __('auth.confirm_password') }}
                                        <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                            {{ __('common.required') }}
                                        </span>
                                    </label>
                                    <input 
                                        type="password" 
                                        name="password_confirmation" 
                                        id="password_confirmation"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm"
                                        required
                                    >
                                </div>
                                <!-- end confirm password field -->

                                <!-- start password form button -->
                                <button 
                                    type="submit"
                                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-200"
                                >
                                    {{ __('profile.update_password') }}
                                </button>
                                <!-- end password form button -->
                            </div>
                        </form>
                        <!-- end password form -->
                    </div>
                </div>
            </div>
        @endif
        <!-- end password tab content -->
    </div>
    <!-- end main container -->

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Show selected tab content
            document.getElementById(tabName + '-content').classList.remove('hidden');

            // Update tab button styles
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white');
                button.classList.add('bg-gray-100', 'text-gray-700');
            });

            document.getElementById(tabName + '-tab').classList.remove('bg-gray-100', 'text-gray-700');
            document.getElementById(tabName + '-tab').classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash;
            const profileTab = document.getElementById('profile-tab');
            const passwordTab = document.getElementById('password-tab');
            const profileContent = document.getElementById('profile-content');
            const passwordContent = document.getElementById('password-content');

            function resetTabs() {
                profileTab.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white');
                profileTab.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');

                if (passwordTab) {
                    passwordTab.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white');
                    passwordTab.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                }

                profileContent.classList.add('hidden');
                if (passwordContent) {
                    passwordContent.classList.add('hidden');
                }
            }

            function showTab(tabButton, tabContent) {
                resetTabs();
                tabButton.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                tabButton.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white');
                tabContent.classList.remove('hidden');
            }

            // Default to profile tab
            showTab(profileTab, profileContent);

            // Check hash and show corresponding tab
            switch (hash) {
                case '#password':
                    if (passwordTab && passwordContent) {
                        showTab(passwordTab, passwordContent);
                    }
                    break;
                case '#profile':
                default:
                    showTab(profileTab, profileContent);
                    break;
            }
        });
    </script>

@endsection

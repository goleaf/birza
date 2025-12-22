<!-- start extends -->
@extends('layouts.frontend.app')
<!-- end extends -->

<!-- start section -->
@section('content')
    <!-- start main container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- start success and error messages -->
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-800 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- end success and error messages -->

        <!-- start tabs container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- start nav -->
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

                <!-- start categories tab button -->
                <button 
                    onclick="switchTab('categories')"
                    class="tab-button flex-1 py-4 px-4 text-center text-sm font-medium relative hover:bg-gray-50 focus:z-10 focus:outline-none transition-colors"
                    id="categories-tab"
                >
                    <span class="inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20h.01M10 16.01V16a4 4 0 014-4h3.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('profile.product_categories') }}
                    </span>
                </button>
                <!-- end categories tab button -->

                @if (!empty($seller->company_name) && !empty($seller->address) && !empty($seller->phone))
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
            <!-- end nav -->

            <!-- Update the content sections with consistent padding and spacing -->
            <div class="p-6">
                <!-- Profile content -->
                <div id="profile-content" class="tab-content">
                    <form method="POST" action="{{ route('seller.profile.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Update input fields with consistent styling -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('auth.name') }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    id="name" 
                                    value="{{ old('name', $seller->name) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm "
                                    required
                                >
                            </div>
                            
                            <!-- Similar updates for other input fields -->
                            <div>
                                <label 
                                    for="email" 
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                >
                                    {{ __('auth.email') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email"
                                    value="{{ old('email', $seller->email) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('email') border-red-500 @enderror"
                                    required
                                >
                                @error('email')
                                    <p class="text-red-500 text-xs italic mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label 
                                    for="company_name" 
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                >
                                    {{ __('auth.company_name') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    name="company_name" 
                                    id="company_name"
                                    value="{{ old('company_name', $seller->company_name) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('company_name') border-red-500 @enderror"
                                    required
                                >
                                @error('company_name')
                                    <p class="text-red-500 text-xs italic mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label 
                                    for="company_code" 
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                >
                                    {{ __('auth.company_code') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    name="company_code" 
                                    id="company_code"
                                    value="{{ old('company_code', $seller->company_code) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('company_name') border-red-500 @enderror"
                                    required
                                >
                                @error('company_code')
                                    <p class="text-red-500 text-xs italic mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label 
                                    for="vat_code" 
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                >
                                    {{ __('auth.vat_code') }}
                                </label>
                                <input 
                                    type="text" 
                                    name="vat_code" 
                                    id="vat_code"
                                    value="{{ old('vat_code', $seller->vat_code) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm"
                                >
                            </div>

                            <div>
                                <label 
                                    for="address" 
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                >
                                    {{ __('auth.address') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    name="address" 
                                    id="address"
                                    value="{{ old('address', $seller->address) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('address') border-red-500 @enderror"
                                    required
                                >
                                @error('address')
                                    <p class="text-red-500 text-xs italic mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label 
                                    for="phone" 
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                >
                                    {{ __('auth.phone') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="tel" 
                                    name="phone" 
                                    id="phone"
                                    value="{{ old('phone', $seller->phone) }}" 
                                    placeholder="+370" 
                                    data-mask="+370 99999999"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('phone') border-red-500 @enderror"
                                    required
                                >
                                @error('phone')
                                    <p class="text-red-500 text-xs italic mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label 
                                    for="bank_account" 
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                >
                                    {{ __('auth.bank_account') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    name="bank_account" 
                                    id="bank_account"
                                    value="{{ old('bank_account', $seller->bank_account) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('bank_account') border-red-500 @enderror"
                                    required
                                >
                                @error('bank_account')
                                    <p class="text-red-500 text-xs italic mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label 
                                    for="veterinary_certificate_number" 
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                >
                                    {{ __('seller.veterinary_certificate_number') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common.required') }}
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    name="veterinary_certificate_number" 
                                    id="veterinary_certificate_number"
                                    value="{{ old('veterinary_certificate_number', $seller->veterinary_certificate_number) }}"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm @error('veterinary_certificate_number') border-red-500 @enderror"
                                    required
                                >
                                @error('veterinary_certificate_number')
                                    <p class="text-red-500 text-xs italic mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Update button styling -->
                        <div class="mt-6 flex justify-end space-x-4">
                            <button 
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                {{ __('profile.update_profile') }}
                            </button>
                        </div>
                    </form>
                </div>

        {{-- Categories Tab Content --}}
        <!-- start categories content -->
        <div id="categories-content" class="tab-content hidden">
            <!-- start categories container -->
            <div class="bg-white rounded-lg shadow-sm">
                <!-- start categories form container -->
                <div class="p-8">
                    <!-- start categories form -->
                    <form 
                        action="{{ route('seller.profile.categories.update') }}" 
                        method="POST"
                    >
                        @csrf
                        @method('PUT')
                        <!-- start categories grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            @foreach ($categories as $category)
                                <!-- start category card -->
                                <div class="bg-white border border-blue-100 rounded-xl shadow-sm transition-all duration-300 overflow-hidden">
                                    <!-- start category header -->
                                    <div class="bg-blue-50 p-6 border-b border-blue-100">
                                        <h3 class="text-xl font-bold text-blue-900">
                                            {{ $category->getTranslation('category_name', app()->getLocale()) }}
                                        </h3>
                                    </div>
                                    <!-- end category header -->

                                    <!-- start subcategories list -->
                                    <div class="p-3 space-y-4">
                                        @foreach ($category->subcategories as $subcategory)
                                            <!-- start subcategory item -->
                                            <div class="relative">
                                                <!-- start subcategory checkbox container -->
                                                <div class="flex items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-all duration-300 ease-in-out group cursor-pointer">
                                                    <input 
                                                        type="checkbox" 
                                                        name="categories[]"
                                                        value="{{ $subcategory->id }}"
                                                        {{ $seller->categories->contains($subcategory->id) ? 'checked' : '' }}
                                                        class="h-5 w-5 rounded-md border-gray-300 text-gray-600 focus:ring-gray-500 transition duration-200 transform hover:scale-110 cursor-pointer category-checkbox"
                                                        data-dashboard-visible="{{ $subcategory->show_in_dashboard ? 'true' : 'false' }}"
                                                        id="subcategory-{{ $subcategory->id }}"
                                                    >
                                                    <!-- start subcategory label -->
                                                    <div class="flex-grow ml-4">
                                                        <label 
                                                            for="subcategory-{{ $subcategory->id }}"
                                                            class="block text-base font-semibold category-label {{ $seller->categories->contains($subcategory->id) ? 'text-blue-900' : 'text-gray-900' }} group-hover:text-gray-700 transition duration-200 cursor-pointer"
                                                        >
                                                            {{ $subcategory->getTranslation('category_name', app()->getLocale()) }}
                                                        </label>
                                                    </div>
                                                    <!-- end subcategory label -->
                                                </div>
                                                <!-- end subcategory checkbox container -->
                                            </div>
                                            <!-- end subcategory item -->
                                        @endforeach
                                    </div>
                                    <!-- end subcategories list -->
                                </div>
                                <!-- end category card -->
                            @endforeach
                        </div>
                        <!-- end categories grid -->

                        <!-- start update button -->
                        <div class="mt-8">
                            <button 
                                type="submit"
                                class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-200"
                            >
                                {{ __('profile.update_categories') }}
                            </button>
                        </div>
                        <!-- end update button -->
                    </form>
                    <!-- end categories form -->
                </div>
                <!-- end categories form container -->
            </div>
            <!-- end categories container -->
        </div>
        <!-- end categories content -->

        {{-- Password Tab Content --}}
        @if (!empty($seller->company_name) && !empty($seller->address) && !empty($seller->phone))
            <!-- start password content -->
            <div id="password-content" class="tab-content hidden">
                <!-- start password container -->
                <div class="bg-white rounded-lg shadow-sm">
                    <!-- start password form container -->
                    <div class="p-8">
                        <!-- start success message -->
                        @if (session('password_success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                                {{ session('password_success') }}
                            </div>
                        @endif
                        <!-- end success message -->

                        <!-- start errors -->
                        @if ($errors->updatePassword->any())
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->updatePassword->all() as $error)
                                        <li>
                                            {{ $error }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <!-- end errors -->

                        <!-- start password form -->
                        <form 
                            method="POST" 
                            action="{{ route('seller.profile.password') }}" 
                            class="max-w-md mx-auto"
                        >
                            @csrf
                            @method('PUT')

                            <!-- start form fields -->
                            <div class="space-y-6">
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
                                    >
                                    @error('current_password')
                                        <p class="text-red-500 text-xs italic mt-1">
                                            {{ $message }}
                                        </p>
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
                                    >
                                    @error('password')
                                        <p class="text-red-500 text-xs italic mt-1">
                                            {{ $message }}
                                        </p>
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
                                        class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                </div>
                                <!-- end confirm password field -->

                                <!-- start update button -->
                                <button 
                                    type="submit"
                                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-200"
                                >
                                    {{ __('profile.update_password') }}
                                </button>
                                <!-- end update button -->
                            </div>
                            <!-- end form fields -->
                        </form>
                        <!-- end password form -->
                    </div>
                    <!-- end password form container -->
                </div>
                <!-- end password container -->
            </div>
            <!-- end password content -->
        @endif
    </div>
    <!-- end main container -->

    <!-- start javascript -->
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
            document.getElementById(tabName + '-tab').classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600',
                'text-white');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash;
            const profileTab = document.getElementById('profile-tab');
            const categoriesTab = document.getElementById('categories-tab');
            const passwordTab = document.getElementById('password-tab');
            const profileContent = document.getElementById('profile-content');
            const categoriesContent = document.getElementById('categories-content');
            const passwordContent = document.getElementById('password-content');

            function resetTabs() {
                // Reset all tabs and contents
                profileTab.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white');
                profileTab.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                categoriesTab.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white');
                categoriesTab.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');

                if (passwordTab) {
                    passwordTab.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white');
                    passwordTab.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                }

                profileContent.classList.add('hidden');
                categoriesContent.classList.add('hidden');
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
                case '#categories':
                    showTab(categoriesTab, categoriesContent);
                    break;
                case '#update_password':
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
    <!-- end javascript -->

@endsection

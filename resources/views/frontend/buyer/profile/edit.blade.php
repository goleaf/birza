<div>

    <!-- start main container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-buyer.breadcrumbs
            class="mb-6"
            :items="[
                ['label' => __('profile')],
            ]"
        />

        <x-ui.header
            class="mb-6"
            :title="__('profile')"
            :subtitle="__('profile_edit_profile')"
        />

        <x-ui.tabs wire:model.live="selectedTab">
            <x-mary-tab
                name="profile-tab"
                :label="__('profile_edit_profile')"
                icon="o-user-circle"
                class="!px-0 !pb-0 !pt-6"
            >
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="p-8">
                        <form wire:submit.prevent="saveProfile">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- start name field -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('auth_name') }}
                                        <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                            {{ __('common_required') }}
                                        </span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="name" 
                                        wire:model="name"
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
                                        {{ __('auth_email') }}
                                        <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                            {{ __('common_required') }}
                                        </span>
                                    </label>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        wire:model="email"
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
                                        {{ __('auth_company_name') }}
                                        <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                            {{ __('common_required') }}
                                        </span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="company_name" 
                                        wire:model="company_name"
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
                                        {{ __('auth_company_code') }}
                                        <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                            {{ __('common_required') }}
                                        </span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="company_code" 
                                        wire:model="company_code"
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
                                        {{ __('auth_vat_code') }}
                                    </label>
                                    <input 
                                        type="text" 
                                        id="vat_code" 
                                        wire:model="vat_code"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm"
                                    >
                                </div>
                                <!-- end vat code field -->

                                <!-- start address field -->
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('auth_address') }}
                                        <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                            {{ __('common_required') }}
                                        </span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="address" 
                                        wire:model="address"
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
                                        {{ __('auth_phone') }}
                                        <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                            {{ __('common_required') }}
                                        </span>
                                    </label>
                                    <input 
                                        type="tel" 
                                        id="phone" 
                                        wire:model="phone"
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
                                        {{ __('auth_bank_account') }}
                                        <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                            {{ __('common_required') }}
                                        </span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="bank_account" 
                                        wire:model="bank_account"
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
                                <x-ui.button
                                    type="submit"
                                    primary
                                    spinner="saveProfile"
                                    :label="__('profile_update_profile')"
                                />

                                @if ($canUpdatePassword)
                                    <x-ui.button
                                        :href="route('buyer.dashboard')"
                                        positive
                                        :label="__('dashboard_dashboard_title')"
                                    />
                                @endif
                            </div>
                            <!-- end form buttons -->
                        </form>
                    </div>
                </div>
            </x-mary-tab>

            @if ($canUpdatePassword)
                <x-mary-tab
                    name="password-tab"
                    :label="__('profile_update_password')"
                    icon="o-key"
                    class="!px-0 !pb-0 !pt-6"
                >
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
                            <form wire:submit.prevent="savePassword" class="max-w-md mx-auto space-y-6">

                                <div class="space-y-2">
                                    <!-- start current password field -->
                                    <div>
                                        <label 
                                            for="current_password" 
                                            class="block text-gray-700 text-sm font-bold mb-2"
                                        >
                                            {{ __('auth_current_password') }}
                                            <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                                {{ __('common_required') }}
                                            </span>
                                        </label>
                                        <input 
                                            type="password" 
                                            id="current_password"
                                            wire:model="current_password"
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
                                            {{ __('auth_new_password') }}
                                            <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                                {{ __('common_required') }}
                                            </span>
                                        </label>
                                        <input 
                                            type="password" 
                                            id="password"
                                            wire:model="password"
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
                                            {{ __('auth_confirm_password') }}
                                            <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                                {{ __('common_required') }}
                                            </span>
                                        </label>
                                        <input 
                                            type="password" 
                                            id="password_confirmation"
                                            wire:model="password_confirmation"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm"
                                            required
                                        >
                                    </div>
                                    <!-- end confirm password field -->

                                    <!-- start password form button -->
                                    <x-ui.button
                                        type="submit"
                                        primary
                                        class="w-full"
                                        spinner="savePassword"
                                        wire:loading.attr="disabled"
                                        :label="__('profile_update_password')"
                                    />
                                    <!-- end password form button -->
                                </div>
                            </form>
                            <!-- end password form -->
                        </div>
                    </div>
                </x-mary-tab>
            @endif
        </x-ui.tabs>
    </div>
    <!-- end main container -->
</div>

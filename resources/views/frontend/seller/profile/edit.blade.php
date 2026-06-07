<div>
    <!-- start main container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-seller.breadcrumbs
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
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <form wire:submit.prevent="saveProfile">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('auth_name') }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    wire:model="name"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm"
                                    required
                                >
                            </div>

                            <div>
                                <label
                                    for="email"
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                >
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
                                    {{ __('auth_vat_code') }}
                                </label>
                                <input
                                    type="text"
                                    id="vat_code"
                                    wire:model="vat_code"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 shadow-sm"
                                >
                            </div>

                            <div>
                                <label
                                    for="address"
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                >
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
                                    {{ __('seller_veterinary_certificate_number') }}
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded">
                                        {{ __('common_required') }}
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    id="veterinary_certificate_number"
                                    wire:model="veterinary_certificate_number"
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

                        <div class="mt-6 flex justify-end space-x-4">
                            <x-ui.button
                                type="submit"
                                primary
                                spinner="saveProfile"
                                wire:loading.attr="disabled"
                                :label="__('profile_update_profile')"
                            />
                        </div>
                    </form>
                </div>
            </x-mary-tab>

            <x-mary-tab
                name="categories-tab"
                class="!px-0 !pb-0 !pt-6"
            >
                <x-slot:label>
                    <div class="inline-flex items-center gap-2 whitespace-nowrap">
                        <x-mary-icon name="o-squares-2x2" class="h-5 w-5" />
                        <span>{{ __('profile_product_categories') }}</span>
                        <x-ui.badge
                            :value="(string) count($selectedCategories)"
                            color="primary"
                            soft
                            sm
                        />
                    </div>
                </x-slot:label>

                <div class="bg-white rounded-lg shadow-sm">
                    <div class="p-8">
                        <form wire:submit.prevent="saveCategories">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                @foreach ($categories as $category)
                                    <div class="bg-white border border-blue-100 rounded-xl shadow-sm transition-all duration-300 overflow-hidden">
                                        <div class="bg-blue-50 p-6 border-b border-blue-100">
                                            <h3 class="text-xl font-bold text-blue-900">
                                                {{ $category->getTranslation('category_name', app()->getLocale()) }}
                                            </h3>
                                        </div>

                                        <div class="p-3 space-y-4">
                                            @foreach ($category->subcategories as $subcategory)
                                                <div class="relative">
                                                    <div class="flex items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-all duration-300 ease-in-out group cursor-pointer">
                                                        <input
                                                            type="checkbox"
                                                            value="{{ $subcategory->id }}"
                                                            wire:model="selectedCategories"
                                                            class="h-5 w-5 rounded-md border-gray-300 text-gray-600 focus:ring-gray-500 transition duration-200 transform hover:scale-110 cursor-pointer category-checkbox"
                                                            data-dashboard-visible="{{ $subcategory->show_in_dashboard ? 'true' : 'false' }}"
                                                            id="subcategory-{{ $subcategory->id }}"
                                                        >
                                                        <div class="flex-grow ml-4">
                                                            <label
                                                                for="subcategory-{{ $subcategory->id }}"
                                                                class="block text-base font-semibold category-label {{ in_array($subcategory->id, $selectedCategories) ? 'text-blue-900' : 'text-gray-900' }} group-hover:text-gray-700 transition duration-200 cursor-pointer"
                                                            >
                                                                {{ $subcategory->getTranslation('category_name', app()->getLocale()) }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-8">
                                <x-ui.button
                                    type="submit"
                                    primary
                                    spinner="saveCategories"
                                    wire:loading.attr="disabled"
                                    :label="__('profile_update_categories')"
                                />
                            </div>
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
                            @if (session('password_success'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                                    {{ session('password_success') }}
                                </div>
                            @endif

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

                            <form wire:submit.prevent="savePassword" class="max-w-md mx-auto">
                                <div class="space-y-6">
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
                                        >
                                        @error('current_password')
                                            <p class="text-red-500 text-xs italic mt-1">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

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
                                        >
                                        @error('password')
                                            <p class="text-red-500 text-xs italic mt-1">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

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
                                            class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        >
                                    </div>

                                    <x-ui.button
                                        type="submit"
                                        primary
                                        class="w-full"
                                        spinner="savePassword"
                                        wire:loading.attr="disabled"
                                        :label="__('profile_update_password')"
                                    />
                                </div>
                            </form>
                        </div>
                    </div>
                </x-mary-tab>
            @endif
        </x-ui.tabs>
    </div>
</div>

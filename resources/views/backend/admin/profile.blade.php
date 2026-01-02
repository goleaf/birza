<x-backend.page :title="__('navigation.profile')">
    <div class="space-y-6">
        <x-ui.card>
            <div class="mb-4">
                <h2 class="text-xl font-semibold">{{ __('auth.profile_information') }}</h2>
                <p class="text-sm text-base-content/70">{{ __('auth.update_profile_info') }}</p>
            </div>

            <form wire:submit.prevent="saveProfile" class="space-y-4">
                <div class="form-control">
                    <label for="name" class="label">
                        <span class="label-text">{{ __('auth.name') }}</span>
                    </label>
                    <input type="text" id="name" wire:model.defer="name"
                        class="input input-bordered w-full @error('name') input-error @enderror" required>
                    @error('name')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="email" class="label">
                        <span class="label-text">{{ __('auth.email') }}</span>
                    </label>
                    <input type="email" id="email" wire:model.defer="email"
                        class="input input-bordered w-full @error('email') input-error @enderror" required>
                    @error('email')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                        {{ __('common.save') }}
                    </button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <div class="mb-4">
                <h2 class="text-xl font-semibold">{{ __('auth.update_password') }}</h2>
                <p class="text-sm text-base-content/70">{{ __('auth.ensure_long_password') }}</p>
            </div>

            <form wire:submit.prevent="savePassword" class="space-y-4">
                <div class="form-control">
                    <label for="current_password" class="label">
                        <span class="label-text">{{ __('auth.current_password') }}</span>
                    </label>
                    <input type="password" id="current_password" wire:model.defer="current_password"
                        class="input input-bordered w-full @error('current_password') input-error @enderror" required>
                    @error('current_password')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="password" class="label">
                        <span class="label-text">{{ __('auth.new_password') }}</span>
                    </label>
                    <input type="password" id="password" wire:model.defer="password"
                        class="input input-bordered w-full @error('password') input-error @enderror" required>
                    @error('password')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="password_confirmation" class="label">
                        <span class="label-text">{{ __('auth.confirm_password') }}</span>
                    </label>
                    <input type="password" id="password_confirmation" wire:model.defer="password_confirmation"
                        class="input input-bordered w-full" required>
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                        {{ __('auth.update_password') }}
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-backend.page>

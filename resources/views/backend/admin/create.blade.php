<x-backend.page title="Create New Admin">
    <x-ui.card>
        <form method="POST" action="{{ route('backend.admin.store') }}" class="space-y-4">
            @csrf

            <div class="form-control">
                <label for="name" class="label">
                    <span class="label-text">Name</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="input input-bordered w-full @error('name') input-error @enderror"
                    required>
                @error('name')
                    <span class="mt-1 text-sm text-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control">
                <label for="email" class="label">
                    <span class="label-text">Email</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="input input-bordered w-full @error('email') input-error @enderror"
                    required>
                @error('email')
                    <span class="mt-1 text-sm text-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control">
                <label for="password" class="label">
                    <span class="label-text">Password</span>
                </label>
                <input type="password" name="password" id="password"
                    class="input input-bordered w-full @error('password') input-error @enderror"
                    required>
                @error('password')
                    <span class="mt-1 text-sm text-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control">
                <label for="password_confirmation" class="label">
                    <span class="label-text">Confirm Password</span>
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="input input-bordered w-full" required>
            </div>

            <x-ui.form-actions
                submit-label="Create Admin"
                :cancel-href="route('backend.admin.profile')"
            />
        </form>
    </x-ui.card>
</x-backend.page>

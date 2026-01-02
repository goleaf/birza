<x-backend.page :title="__('backend.global_settings.edit_title')">
    <x-ui.card>
        <form action="{{ route('backend.global_settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="form-control">
                <label for="portal_additional_price" class="label">
                    <span class="label-text">{{ __('backend.global_settings.fields.portal_additional_price') }}</span>
                </label>
                <div class="flex items-center gap-2">
                    <input
                        type="number"
                        name="portal_additional_price"
                        id="portal_additional_price"
                        class="input input-bordered w-full @error('portal_additional_price') input-error @enderror"
                        value="{{ old('portal_additional_price', $globalSettings->portal_additional_price) }}"
                        required
                    >
                    <span class="text-sm text-base-content/60">€</span>
                </div>
                @error('portal_additional_price')
                    <span class="mt-1 text-sm text-error">{{ $message }}</span>
                @enderror
            </div>

            <x-ui.form-actions
                :submit-label="__('backend.common.save')"
                :cancel-href="route('backend.settings.index')"
            />
        </form>
    </x-ui.card>
</x-backend.page>

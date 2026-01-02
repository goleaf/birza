<x-backend.page :title="__('backend.settings.edit_title')">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <form wire:submit.prevent="save" class="space-y-6">
                <div class="form-control">
                    <label for="portal_additional_price" class="label">
                        <span class="label-text">{{ __('backend.settings.fields.portal_additional_price') }}</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            id="portal_additional_price"
                            wire:model.defer="portal_additional_price"
                            class="input input-bordered w-full @error('portal_additional_price') input-error @enderror"
                            required
                        >
                        <span class="text-sm text-base-content/60">€</span>
                    </div>
                    @error('portal_additional_price')
                        <span class="mt-1 text-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                        {{ __('backend.common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-backend.page>

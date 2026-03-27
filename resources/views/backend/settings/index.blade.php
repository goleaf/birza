<div>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold mb-4">
            {{ __('backend_settings_edit_title') }}
        </h2>
        <form wire:submit.prevent="save" class="bg-white p-6 rounded-lg shadow">

            <div class="mb-4">
                <label for="portal_additional_price" class="block text-sm font-medium text-gray-700">
                    {{ __('backend_settings_fields_portal_additional_price') }}
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

            <div class="text-center">
                <button type="submit" wire:loading.attr="disabled"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-60 disabled:cursor-not-allowed">
                    {{ __('backend_common_save') }}
                </button>
            </div>
        </form>
    </x-ui.card>
</x-backend.page>

<div class="py-12">
    <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
        <h2 class="mb-4 text-2xl font-bold">{{ __('backend_settings_edit_title') }}</h2>

        <form wire:submit.prevent="save" class="rounded-lg bg-white p-6 shadow-sm">
            <div class="mb-4">
                <label for="portal_additional_price" class="block text-sm font-medium text-gray-700">
                    {{ __('backend_settings_fields_portal_additional_price') }}
                </label>
                <div class="mt-1 flex items-center gap-2">
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="portal_additional_price"
                        wire:model.defer="portal_additional_price"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                    <span class="text-sm text-gray-500">€</span>
                </div>
                @error('portal_additional_price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="text-center">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="rounded bg-green-500 px-4 py-2 font-bold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ __('backend_common_save') }}
                </button>
            </div>
        </form>
    </div>
</div>

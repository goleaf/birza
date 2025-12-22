<div>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold mb-4">
            {{ __('backend.settings.edit_title') }}
        </h2>
        <form wire:submit.prevent="save" class="bg-white p-6 rounded-lg shadow">

            <div class="mb-4">
                <label for="portal_additional_price" class="block text-sm font-medium text-gray-700">
                    {{ __('backend.settings.fields.portal_additional_price') }}
                </label>
                <div class="flex items-center space-x-2">
                    <input type="number" step="0.01" min="0" id="portal_additional_price"
                        wire:model.defer="portal_additional_price"
                        class="flex-grow border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('portal_additional_price') border-red-500 focus:ring-red-500 @enderror" required>
                    <span class="text-gray-600">€</span>
                </div>
                @error('portal_additional_price')
                    <p class="text-red-500 mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="text-center">
                <button type="submit" wire:loading.attr="disabled"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-60 disabled:cursor-not-allowed">
                    {{ __('backend.common.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
</div>

<div id="product-questions" class="space-y-6 border-t border-gray-200 pt-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('products.questions.title') }}</h2>
            <p class="text-sm text-gray-600">{{ __('products.questions.subtitle') }}</p>
        </div>
        <span class="text-sm font-medium text-gray-500">{{ trans_choice('products.questions.count', $questions->count(), ['count' => $questions->count()]) }}</span>
    </div>

    @if ($successMessage)
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ $successMessage }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($questions as $productQuestion)
            <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-gray-900">{{ $productQuestion->question }}</p>
                        <p class="text-xs text-gray-500">
                            {{ __('products.questions.asked_by', ['name' => $productQuestion->authorLabel()]) }}
                            ·
                            {{ $productQuestion->created_at?->format('Y-m-d') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 rounded-md bg-gray-50 p-4">
                    <div class="mb-2 flex flex-col gap-1 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between">
                        <span class="font-semibold text-gray-700">{{ __('products.questions.seller_answer') }}</span>
                        <span>{{ __('products.questions.answered_at', ['date' => $productQuestion->answered_at?->format('Y-m-d')]) }}</span>
                    </div>
                    <p class="text-sm text-gray-700">{{ $productQuestion->answer }}</p>
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-sm text-gray-600">
                {{ __('products.questions.no_questions') }}
            </div>
        @endforelse
    </div>

    <form wire:submit="submit" class="space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
        <div>
            <label for="product-question" class="mb-1 block text-sm font-medium text-gray-700">
                {{ __('products.questions.ask') }}
            </label>
            <textarea
                id="product-question"
                wire:model="question"
                rows="4"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="{{ __('products.questions.question_placeholder') }}"
            ></textarea>
            @error('question')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($buyer === null)
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="product-question-guest-name" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('products.questions.guest_name') }}
                    </label>
                    <input
                        id="product-question-guest-name"
                        type="text"
                        wire:model="guestName"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                    @error('guestName')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="product-question-guest-email" class="mb-1 block text-sm font-medium text-gray-700">
                        {{ __('products.questions.guest_email_optional') }}
                    </label>
                    <input
                        id="product-question-guest-email"
                        type="email"
                        wire:model="guestEmail"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                    @error('guestEmail')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-gray-500">{{ __('products.questions.public_after_answer') }}</p>
            <x-ui.button
                type="submit"
                primary
                spinner="submit"
                wire:loading.attr="disabled"
                :label="__('products.questions.submit')"
            />
        </div>

        <div wire:loading wire:target="submit" class="text-sm text-gray-500">
            {{ __('products.questions.submitting') }}
        </div>
    </form>
</div>

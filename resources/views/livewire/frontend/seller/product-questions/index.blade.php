<div>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-seller.breadcrumbs
            class="mb-6"
            :items="[
                ['label' => __('products.questions.seller_title')],
            ]"
        />

        <x-ui.header
            :title="__('products.questions.seller_title')"
            :subtitle="__('products.questions.seller_subtitle')"
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('seller.products.index')"
                    secondary
                    :label="__('common_products')"
                />
            </x-slot:actions>
        </x-ui.header>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <label for="seller-question-status" class="mb-1 block text-sm font-medium text-gray-700">
                {{ __('products.questions.filter_status') }}
            </label>
            <select
                id="seller-question-status"
                wire:model.live="status"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:max-w-xs"
            >
                @foreach ($statusOptions as $option)
                    <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="space-y-4">
            @forelse ($questions as $productQuestion)
                <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="grid gap-4 lg:grid-cols-[1fr_18rem]">
                        <div class="space-y-3">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h2 class="text-base font-semibold text-gray-900">{{ $productQuestion->product?->name }}</h2>
                                    <p class="text-xs text-gray-500">
                                        {{ __('products.questions.asked_by', ['name' => $productQuestion->authorLabel()]) }}
                                        ·
                                        {{ $productQuestion->created_at?->format('Y-m-d H:i') }}
                                    </p>
                                </div>
                                <x-ui.badge
                                    :value="$productQuestion->statusLabel()"
                                    :color="$productQuestion->statusBadgeColor()"
                                    soft
                                    sm
                                    class="font-semibold"
                                />
                            </div>

                            <p class="text-sm text-gray-700">{{ $productQuestion->question }}</p>

                            @if ($productQuestion->answer)
                                <div class="rounded-md bg-gray-50 p-3 text-sm text-gray-700">
                                    <div class="mb-1 text-xs font-semibold text-gray-500">{{ __('products.questions.answer') }}</div>
                                    {{ $productQuestion->answer }}
                                </div>
                            @else
                                <form wire:submit="answer({{ $productQuestion->id }})" class="space-y-3">
                                    <div>
                                        <label for="answer-{{ $productQuestion->id }}" class="mb-1 block text-sm font-medium text-gray-700">
                                            {{ __('products.questions.answer') }}
                                        </label>
                                        <textarea
                                            id="answer-{{ $productQuestion->id }}"
                                            wire:model="answers.{{ $productQuestion->id }}"
                                            rows="3"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        ></textarea>
                                        @error("answers.{$productQuestion->id}")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <x-ui.button
                                        type="submit"
                                        positive
                                        sm
                                        spinner="answer({{ $productQuestion->id }})"
                                        wire:loading.attr="disabled"
                                        :label="__('products.questions.submit_answer')"
                                    />
                                </form>
                            @endif
                        </div>

                        <div class="flex flex-col gap-2 lg:items-end">
                            @if ($productQuestion->product)
                                <x-ui.button
                                    :href="route('seller.products.edit', $productQuestion->product)"
                                    secondary
                                    sm
                                    :label="__('products.questions.view_product')"
                                />
                            @endif

                            @if ($productQuestion->status->value !== 'hidden')
                                <x-ui.button
                                    type="button"
                                    negative
                                    flat
                                    sm
                                    spinner="hide({{ $productQuestion->id }})"
                                    wire:click="hide({{ $productQuestion->id }})"
                                    :label="__('products.questions.hide')"
                                />
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-gray-300 bg-white px-4 py-10 text-center text-sm text-gray-600">
                    {{ __('products.questions.seller_empty') }}
                </div>
            @endforelse
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4">
            {{ $questions->links() }}
        </div>
    </div>
</div>

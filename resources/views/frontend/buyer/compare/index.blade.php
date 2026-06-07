<div class="space-y-6">
    <x-buyer.breadcrumbs
        :items="[
            ['label' => __('common_products'), 'link' => route('buyer.products.index')],
            ['label' => __('compare.title')],
        ]"
    />

    <x-ui.header
        :title="__('compare.title')"
        :subtitle="__('compare.subtitle', ['count' => count($cards), 'limit' => $comparisonLimit])"
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('buyer.products.index')"
                secondary
                icon="arrow-left"
                :label="__('compare.actions.back_to_catalog')"
            />

            @if ($cards !== [])
                <x-ui.button
                    type="button"
                    negative
                    outline
                    icon="trash"
                    spinner="clearCompare"
                    wire:click="clearCompare"
                    wire:loading.attr="disabled"
                    :label="__('compare.actions.clear')"
                />
            @endif
        </x-slot:actions>
    </x-ui.header>

    <div
        wire:loading.flex
        wire:target="removeProduct,clearCompare"
        class="items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-700"
    >
        <x-ui.icon name="arrow-path" class="h-4 w-4 animate-spin" />
        <span>{{ __('compare.loading') }}</span>
    </div>

    @if ($cards === [])
        <x-ui.card class="border border-dashed border-gray-300 bg-white text-center" body-class="space-y-4 py-12">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                <x-ui.icon name="scale" class="h-7 w-7" />
            </div>

            <div class="space-y-2">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('compare.empty') }}</h2>
                <p class="mx-auto max-w-2xl text-sm text-gray-600">{{ __('compare.empty_help') }}</p>
            </div>

            <x-ui.button
                :href="route('buyer.products.index')"
                primary
                icon="squares-2x2"
                :label="__('compare.actions.back_to_catalog')"
            />
        </x-ui.card>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:hidden">
            @foreach ($cards as $card)
                <x-ui.card
                    wire:key="compare-mobile-{{ $card['id'] }}"
                    class="border border-gray-200 bg-white shadow-sm"
                    body-class="space-y-4"
                >
                    @if ($card['image_url'])
                        <img
                            src="{{ $card['image_url'] }}"
                            alt="{{ $card['title'] }}"
                            loading="lazy"
                            class="h-48 w-full rounded-lg object-cover"
                        >
                    @else
                        <div class="flex h-48 w-full items-center justify-center rounded-lg bg-gray-100 text-sm font-medium text-gray-500">
                            {{ __('common_no_image') }}
                        </div>
                    @endif

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ $card['url'] }}" class="block truncate text-lg font-semibold text-gray-900 hover:text-blue-700">
                                {{ $card['title'] }}
                            </a>
                            <p class="text-sm text-gray-500">{{ $card['category'] }}</p>
                        </div>

                        <x-ui.button
                            type="button"
                            negative
                            outline
                            xs
                            icon="x-mark"
                            spinner="removeProduct({{ $card['id'] }})"
                            wire:click="removeProduct({{ $card['id'] }})"
                            wire:loading.attr="disabled"
                            :label="__('compare.actions.remove')"
                        />
                    </div>

                    <dl class="divide-y divide-gray-100 text-sm">
                        @foreach ($comparisonRows as $row)
                            <div class="grid grid-cols-2 gap-3 py-2" wire:key="compare-mobile-{{ $card['id'] }}-{{ $row['key'] }}">
                                <dt class="font-medium text-gray-500">{{ $row['label'] }}</dt>
                                <dd class="text-right text-gray-900">{{ $row['values'][$card['id']] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-ui.card>
            @endforeach
        </div>

        <x-ui.card class="hidden border border-gray-200 bg-white shadow-sm lg:block" body-class="p-0">
            <div class="overflow-x-auto" data-testid="compare-scroll-container">
                <table class="min-w-[960px] table-fixed divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="w-48 px-4 py-4 text-left font-semibold text-gray-700">
                                {{ __('compare.fields.product') }}
                            </th>

                            @foreach ($cards as $card)
                                <th scope="col" class="w-64 px-4 py-4 text-left" wire:key="compare-heading-{{ $card['id'] }}">
                                    <div class="space-y-3">
                                        @if ($card['image_url'])
                                            <img
                                                src="{{ $card['image_url'] }}"
                                                alt="{{ $card['title'] }}"
                                                loading="lazy"
                                                class="h-36 w-full rounded-lg object-cover"
                                            >
                                        @else
                                            <div class="flex h-36 w-full items-center justify-center rounded-lg bg-gray-100 text-sm font-medium text-gray-500">
                                                {{ __('common_no_image') }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <a href="{{ $card['url'] }}" class="block truncate font-semibold text-gray-900 hover:text-blue-700">
                                                {{ $card['title'] }}
                                            </a>
                                            <p class="mt-1 text-xs font-normal text-gray-500">{{ $card['category'] }}</p>
                                        </div>
                                        <x-ui.button
                                            type="button"
                                            negative
                                            outline
                                            xs
                                            icon="x-mark"
                                            spinner="removeProduct({{ $card['id'] }})"
                                            wire:click="removeProduct({{ $card['id'] }})"
                                            wire:loading.attr="disabled"
                                            :label="__('compare.actions.remove')"
                                        />
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($comparisonRows as $row)
                            <tr wire:key="compare-row-{{ $row['key'] }}">
                                <th scope="row" class="px-4 py-4 text-left font-semibold text-gray-700">
                                    {{ $row['label'] }}
                                </th>

                                @foreach ($cards as $card)
                                    <td class="align-top px-4 py-4 text-gray-700" wire:key="compare-row-{{ $row['key'] }}-{{ $card['id'] }}">
                                        {{ $row['values'][$card['id']] }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif
</div>

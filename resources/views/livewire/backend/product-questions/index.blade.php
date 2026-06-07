<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('products.questions.admin_title')],
        ]"
    />

    <x-mary-header
        :title="__('products.questions.admin_title')"
        :subtitle="__('products.questions.admin_subtitle')"
        separator
        progress-indicator
    />

    @if (session('success'))
        <x-mary-alert
            :title="session('success')"
            icon="o-check-circle"
            class="alert-success alert-soft"
            shadow
        />
    @endif

    <x-mary-card shadow>
        <label for="admin-question-status" class="mb-1 block text-sm font-medium text-base-content/70">
            {{ __('products.questions.filter_status') }}
        </label>
        <select
            id="admin-question-status"
            wire:model.live="status"
            class="select select-bordered w-full sm:max-w-xs"
        >
            @foreach ($statusOptions as $option)
                <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
            @endforeach
        </select>
    </x-mary-card>

    <x-mary-card shadow>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('products.questions.product') }}</th>
                        <th>{{ __('products.questions.question') }}</th>
                        <th>{{ __('products.questions.answer') }}</th>
                        <th>{{ __('products.questions.status') }}</th>
                        <th>{{ __('products.questions.moderation') }}</th>
                        <th class="text-right">{{ __('common_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($questions as $productQuestion)
                        <tr class="align-top">
                            <td>
                                <div class="font-medium">{{ $productQuestion->product?->name ?? __('common_not_specified') }}</div>
                                <div class="text-xs text-base-content/60">
                                    {{ $productQuestion->seller?->company_name ?: $productQuestion->seller?->name ?: __('common_not_specified') }}
                                </div>
                                @if ($productQuestion->product)
                                    <a
                                        href="{{ route('backend.products.show', $productQuestion->product) }}"
                                        class="text-xs text-primary hover:underline"
                                    >
                                        {{ __('products.questions.view_product') }}
                                    </a>
                                @endif
                            </td>
                            <td class="min-w-72">
                                <div class="text-sm">{{ $productQuestion->question }}</div>
                                <div class="mt-2 text-xs text-base-content/60">
                                    {{ __('products.questions.asked_by', ['name' => $productQuestion->authorLabel()]) }}
                                    ·
                                    {{ $productQuestion->created_at?->format('Y-m-d H:i') }}
                                </div>
                            </td>
                            <td class="min-w-72">
                                @if ($productQuestion->answer)
                                    <div class="text-sm">{{ $productQuestion->answer }}</div>
                                    <div class="mt-2 text-xs text-base-content/60">
                                        {{ __('products.questions.answered_at', ['date' => $productQuestion->answered_at?->format('Y-m-d')]) }}
                                    </div>
                                @else
                                    <span class="text-sm text-base-content/50">{{ __('products.questions.no_answer') }}</span>
                                @endif
                            </td>
                            <td>
                                <x-mary-badge
                                    :value="$productQuestion->statusLabel()"
                                    class="{{ $productQuestion->statusMaryBadgeClass() }}"
                                />
                            </td>
                            <td class="min-w-64">
                                <input
                                    type="text"
                                    wire:model="reasons.{{ $productQuestion->id }}"
                                    class="input input-bordered input-sm w-full"
                                    placeholder="{{ __('products.questions.moderation_reason') }}"
                                >
                                @error("reasons.{$productQuestion->id}")
                                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                                @enderror
                                @if ($productQuestion->moderation_reason)
                                    <p class="mt-2 text-xs text-base-content/60">
                                        {{ $productQuestion->moderation_reason }}
                                    </p>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-col gap-2 sm:items-end">
                                    <x-mary-button
                                        :label="__('products.questions.approve')"
                                        class="btn-success btn-sm"
                                        wire:click="approve({{ $productQuestion->id }})"
                                        spinner="approve({{ $productQuestion->id }})"
                                    />
                                    <x-mary-button
                                        :label="__('products.questions.reject')"
                                        class="btn-error btn-sm"
                                        wire:click="reject({{ $productQuestion->id }})"
                                        spinner="reject({{ $productQuestion->id }})"
                                    />
                                    <x-mary-button
                                        :label="__('products.questions.hide')"
                                        class="btn-warning btn-sm"
                                        wire:click="hide({{ $productQuestion->id }})"
                                        spinner="hide({{ $productQuestion->id }})"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-base-content/60">
                                {{ __('products.questions.admin_empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $questions->links() }}
        </div>
    </x-mary-card>
</div>

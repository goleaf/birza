<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SwitchLocaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'locale' => ['nullable', 'string'],
        ];
    }

    public function resolvedLocale(): string
    {
        $locale = $this->validated('locale');

        if (is_string($locale) && in_array($locale, (array) config('app.locales', []), true)) {
            return $locale;
        }

        return (string) config('app.fallback_locale');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'locale' => $this->route('locale'),
        ]);
    }
}

<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductSearchRequest extends FormRequest
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
            'query' => ['nullable', 'string', 'max:120'],
            'locale' => ['nullable', 'string', Rule::in(array_values((array) config('app.locales', [])))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'query' => __('validation.attributes.query'),
            'locale' => __('validation.attributes.locale'),
        ];
    }

    public function searchTerm(): string
    {
        return trim((string) $this->validated('query', ''));
    }

    public function searchLocale(): string
    {
        return (string) ($this->validated('locale') ?: app()->getLocale());
    }
}

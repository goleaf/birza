<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    private const IMAGE_VALIDATION_RULES = 'mimes:jpeg,png,jpg,gif|max:15048';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $imageRule = $this->isMethod('PUT')
            ? 'nullable|' . self::IMAGE_VALIDATION_RULES
            : 'required|' . self::IMAGE_VALIDATION_RULES;

        return [
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'is_organic' => 'boolean',
            'is_active' => 'boolean',
            'country_of_origin' => 'required|exists:countries,id',
            'product_image' => $imageRule,
            'product_additional_image' => 'nullable|' . self::IMAGE_VALIDATION_RULES,
            'min_order_price' => 'nullable|numeric|min:0',
            'min_order_count' => 'nullable|integer|min:1',
            'description.*' => 'nullable|string',
            'attributes.*' => 'nullable|string|max:255',
        ];
    }
}

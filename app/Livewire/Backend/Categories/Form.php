<?php

namespace App\Livewire\Backend\Categories;

use App\Models\Attribute;
use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Form extends Component
{
    public ?Category $category = null;

    public ?int $parent_category_id = null;

    public array $name = [];

    public array $selectedAttributes = [];

    public function mount(?Category $category = null): void
    {
        $this->category = $category;

        $this->parent_category_id = $category?->parent_category_id;

        foreach (config('app.locales') as $locale) {
            $this->name[$locale] = (string) ($category?->getTranslation('category_name', $locale) ?? '');
        }

        $this->selectedAttributes = $category
            ? $category->attributes->pluck('id')->values()->all()
            : [];
    }

    public function save(): void
    {
        $rules = [
            'parent_category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'selectedAttributes' => ['nullable', 'array'],
            'selectedAttributes.*' => ['integer', Rule::exists('attributes', 'id')],
        ];

        foreach (config('app.locales') as $locale) {
            $rules["name.$locale"] = ['required', 'string', 'max:255'];
        }

        $validated = $this->validate($rules);

        $category = $this->category ?? new Category;
        $category->parent_category_id = $validated['parent_category_id'] ?? null;
        $category->setTranslations('category_name', $validated['name']);
        $category->save();

        $category->attributes()->sync($validated['selectedAttributes'] ?? []);

        // If this is a main category, propagate its attributes to subcategories.
        if (is_null($category->parent_category_id)) {
            $this->propagateAttributesToSubcategories($category, $validated['selectedAttributes'] ?? []);
        }

        session()->flash('success', __('backend_common_success_message'));
        $this->redirectRoute('backend.categories.index');
    }

    private function propagateAttributesToSubcategories(Category $category, array $attributes): void
    {
        $subcategories = Category::where('parent_category_id', $category->id)
            ->orWhereIn('parent_category_id', function ($query) use ($category) {
                $query->select('id')
                    ->from('categories')
                    ->where('parent_category_id', $category->id);
            })
            ->get();

        foreach ($subcategories as $subcategory) {
            $subcategory->attributes()->sync($attributes);
        }
    }

    public function render()
    {
        $availableAttributes = Attribute::query()
            ->select(['id', 'name', 'is_active'])
            ->orderBy('name->en')
            ->get();

        $parentCategories = Category::query()
            ->select(['id', 'category_name'])
            ->whereNull('parent_category_id')
            ->when($this->category?->id, function ($query, $categoryId) {
                $query->whereKeyNot($categoryId);
            })
            ->orderBy('category_name->en')
            ->get();

        return view('backend.categories.form', [
            'attributeOptions' => $availableAttributes
                ->map(fn (Attribute $attribute) => [
                    'id' => $attribute->id,
                    'name' => $attribute->getTranslation('name', app()->getLocale()),
                    'status' => $attribute->is_active ? __('common_active') : __('common_inactive'),
                ])
                ->values()
                ->all(),
            'category' => $this->category,
            'parentCategoryOptions' => $parentCategories
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->getTranslation('category_name', app()->getLocale()),
                ])
                ->values()
                ->all(),
        ]);
    }
}

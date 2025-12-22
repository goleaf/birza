<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Support\Str;
use App\Models\Attribute;
use Carbon\Carbon;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['subcategories', 'attributes'])
            ->whereNull('parent_category_id')
            ->get();

        return view('backend.categories.index', ['categories' => $categories]);
    }

    public function create()
    {
        $parentCategories = Category::whereNull('parent_category_id')->get();
        $attributes = Attribute::all();

        return view('backend.categories.form', [
            'categories' => $parentCategories,
            'attributes' => $attributes
        ]);
    }

    public function store(Request $request)
    {
        $validationRules = [
            'parent_category_id' => 'nullable|exists:categories,id',
            'attributes' => 'nullable|array',
            'attributes.*' => 'exists:attributes,id',
        ];

        foreach (config('app.locales') as $locale) {
            $validationRules["name.{$locale}"] = 'required|string|max:255';
        }

        $validatedData = $request->validate($validationRules);

        $category = new Category();
        $category->parent_category_id = $request->parent_category_id;

        foreach (config('app.locales') as $locale) {
            $category->setTranslation('category_name', $locale, $request->input("name.{$locale}"));
        }

        $category->save();

        // Save attributes to the category
        if ($request->has('attributes')) {
            $category->attributes()->sync($request->attributes);
        }

        return redirect()->route('backend.categories.index')
            ->with('success', __('messages.categories_created_success'));
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_category_id')
                                    ->where('id', '!=', $category->id)
                                    ->get();

        $categories = Category::whereNull('parent_category_id')->get();
        $attributes = Attribute::all();

        return view('backend.categories.form', [
            'category' => $category,
            'parentCategories' => $parentCategories,
            'categories' => $categories,
            'attributes' => $attributes,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validatedData = $request->validate([
            'parent_category_id' => 'nullable|exists:categories,id',
            'attributes' => 'nullable|array|exists:attributes,id',
            'name' => 'required|array',
            'name.*' => 'required|string|max:255'
        ]);

        $category->parent_category_id = $request->parent_category_id;
        $category->setTranslations('category_name', $request->input('name'));
        $category->save();

        $this->syncCategoryAttributes($category, $request);

        return redirect()->route('backend.categories.index')
            ->with('success', __('messages.categories_updated_success'));
    }

    private function syncCategoryAttributes(Category $category, Request $request): void
    {
        $attributes = $request->input('attributes', []);
        $category->attributes()->sync($attributes);

        // If this is a main category, always propagate attributes to subcategories
        if (is_null($category->parent_category_id)) {
            $this->propagateAttributesToSubcategories($category, $attributes);
        }
    }

    private function propagateAttributesToSubcategories(Category $category, array $attributes): void
    {
        // Get all subcategories at any level
        $subcategories = Category::where('parent_category_id', $category->id)
            ->orWhereIn('parent_category_id', function($query) use ($category) {
                $query->select('id')
                    ->from('categories')
                    ->where('parent_category_id', $category->id);
            })
            ->get();

        // Reset and sync attributes for all subcategories
        foreach ($subcategories as $subcategory) {
            $subcategory->attributes()->sync($attributes);
        }
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('backend.categories.index')->with('success', __('messages.categories_deleted_success'));
    }
}

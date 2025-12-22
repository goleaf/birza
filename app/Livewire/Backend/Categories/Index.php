<?php

namespace App\Livewire\Backend\Categories;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    public function deleteCategory(int $categoryId): void
    {
        Category::query()->findOrFail($categoryId)->delete();

        session()->flash('success', __('backend.common.delete_success'));
    }

    public function render()
    {
        $categories = Category::with(['subcategories', 'attributes'])
            ->whereNull('parent_category_id')
            ->get();

        return view('backend.categories.index', [
            'categories' => $categories,
        ]);
    }
}



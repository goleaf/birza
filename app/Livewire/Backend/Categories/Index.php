<?php

namespace App\Livewire\Backend\Categories;

use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use InteractsWithWireUi;

    public function confirmDeleteCategory(int $categoryId): void
    {
        $this->confirmDelete(method: 'deleteCategory', params: $categoryId);
    }

    public function deleteCategory(int $categoryId): void
    {
        Category::query()->findOrFail($categoryId)->delete();

        $this->notifySuccess(__('backend_common_delete_success'));
    }

    public function render()
    {
        $categories = Category::query()
            ->whereNull('parent_category_id')
            ->with([
                'attributes',
                'subcategories.attributes',
            ])
            ->get();

        return view('backend.categories.index', [
            'categories' => $categories,
        ]);
    }
}


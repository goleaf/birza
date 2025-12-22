<?php

namespace App\Livewire\Backend\Products;

use App\Models\Category;
use App\Models\Country;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Create extends Component
{
    public function render()
    {
        $category = null;

        $categories = Category::select('id', 'category_name')->get();
        $countries = Country::active()
            ->select('id', 'country_name', 'alpha2')
            ->orderBy('alpha2')
            ->get();

        $attributes = null;

        return view('backend.products.form', [
            'categories' => $categories,
            'category' => $category,
            'countries' => $countries,
            'attributes' => $attributes,
        ]);
    }
}



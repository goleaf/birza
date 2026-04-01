<?php

namespace App\Livewire\Backend\Products;

use App\Models\Category;
use App\Models\Country;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.backend.app')]
class Create extends Component
{
    use WithFileUploads;

    public ?int $category_id = null;

    public float $price = 0.0;

    public string $pack_type = '';

    public string $unit = '';

    public ?int $country_of_origin = null;

    public int $is_organic = 0;

    public int $is_active = 1;

    public int $stock = 0;

    public ?float $min_order_price = null;

    public ?int $min_order_count = null;

    public ?float $package_weight = null;

    public ?float $price_per_liter = null;

    public string $description = '';

    public $product_image = null;

    public $product_additional_image = null;

    public array $attributeSelections = [];

    public function render()
    {
        $category = null;

        $categories = Category::select('id', 'category_name')->get();
        $countries = Country::active()
            ->select('id', 'country_name', 'alpha2')
            ->orderBy('alpha2')
            ->get();

        $productAttributes = null;

        return view('backend.products.form', [
            'categories' => $categories,
            'category' => $category,
            'countries' => $countries,
            'productAttributes' => $productAttributes,
        ]);
    }
}

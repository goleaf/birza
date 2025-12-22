<?php

namespace App\Livewire\Backend\Products;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Show extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        $this->product = $product->load(['category', 'seller', 'attributeValues.attribute']);
    }

    public function render()
    {
        return view('backend.products.show', [
            'product' => $this->product,
        ]);
    }
}



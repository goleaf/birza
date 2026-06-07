<?php

namespace App\Livewire\Backend\Products;

use App\Models\Product;
use App\Support\SafeMarkdown;
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
        $description = (string) $this->product->getTranslation('description', app()->getLocale());

        return view('backend.products.show', [
            'product' => $this->product,
            'descriptionHtml' => SafeMarkdown::render($description),
            'hasDescription' => $description !== '',
        ]);
    }
}
